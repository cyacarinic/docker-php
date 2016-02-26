<?php

require_once('libs/aplicaciones.php');
require_once('functions.php');

function Envio_Safetypay($smarty){
	$output='E';
	//salvar log de cabecera y url
	clsSafetyPay::LogPut($_SERVER,$output);
	clsSafetyPay::LogPut('get->'.$_GET["x"],$output);

    ValidarSesion();

	try {

	    $parameters = clsSafetyPay::DesencriptarURL($_GET["x"]);
		clsSafetyPay::LogPut($parameters,$output);

        if (!isset($parameters['cod_orden_compra'])) {
            throw new Exception("Los parametros por la URL no son reconocidos.");
        }

        $codigo_oc=$parameters['cod_orden_compra'];
        //obtener codigo y total de la oc.
        $oc = new nicVtaOrdenCompra($codigo_oc);
        $safetypay = new clsSafetyPay();
		$safetypay->conf["MerchantSalesID"]=$oc->cod_orden_compra;
		$safetypay->conf["Amount"]=round($oc->total,2);

		//creando token
		$result = $safetypay->CreateExpressToken();
		clsSafetyPay::LogPut($result,$output);
		//validando token
		if ( $result['ErrorManager']['ErrorNumber']['@content'] != '0' ){
            throw new Exception("Error al generar el token.");
		}
		$tokenURL = $result['ShopperRedirectURL'] . $channel;

		//completando url para opcion de pago
		if($parameters['opcion']=='safetypay_cash')
			$tokenURL.="&ChannelID=CASH";
		else
			$tokenURL.="&ChannelID=ONLINE";

		header("location: $tokenURL");
	} catch (Exception $e) {
		clsSafetyPay::LogPut($e->getMessage(),$output);
		clsSafetyPay::DisplayMessage("Problemas con la pasarela SAFETYPAY favor de reportarlo a atención al cliente.");
	}
	clsSafetyPay::LogPut(str_repeat("-",80),$output);
}	

$smarty= new Clase_Smarty;
$tarea = isset($_REQUEST["hdnAccion"])?$_REQUEST["hdnAccion"]:"Envio_Safetypay";

$funciones=array("Envio_Safetypay");
if(in_array($tarea,$funciones)){
	$tarea($smarty);
}else{
	header("location: /");
}

?>
