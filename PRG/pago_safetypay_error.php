<?

require_once('libs/aplicaciones.php');

clsSafetyPay::LogPut("pago_safetypay_error.php");
clsSafetyPay::LogPut($_SERVER);

try{
	$smarty= new Clase_Smarty;
	$titulo="RCP - SafetyPay";
	$mensaje="Hubo una falla en la operaci&oacute;n, no se proceso su pago con SafetyPay.";
	$smarty->assign("titulo",$titulo);
	$smarty->assign("mensaje",$mensaje);
	$smarty->display("mensaje.html");

}catch(Exception $e){
	$safetypay = new clsSafetyPay();
	$safetypay->LogPut($e->getMessage());
	mail(clsConfig::webmaster,"Error : Pago safetypay","error notificado por safetypay.".$e->getMessage());
	$safetypay->DisplayMessage("Error : Problemas con la pasarela SAFETYPAY favor de reportarlo a atenci&oacute;n al cliente.");
}
clsSafetyPay::LogPut(str_repeat("-",80),$output);
?>