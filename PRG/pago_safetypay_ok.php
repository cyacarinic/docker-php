<?
header('Content-Type: text/html;charset=utf-8');
require_once('libs/aplicaciones.php');

	clsSafetyPay::LogPut($_SERVER);
try{
	// cargar las notificaciones pendientes
	$safetypay = new clsSafetyPay();
	$toconfirm = $safetypay->RCPGetNewOperationActivity();

 	if (count($toconfirm)=='0'){
 		$safetypay->DisplayMessage("No hay notificaciones pendientes por procesar.");
        return;
	}

	// procesando las ordenes canceladas
	for($i=0;$i<count($toconfirm['ConfirmOperation']);$i++){

		$codigo_oc=$toconfirm['ConfirmOperation'][$i]['MerchantSalesID'];
		$safetypay->LogPut("oc:".$codigo_oc);

		//insertar a la cola			
		$result=$safetypay->InsertJobQueue($codigo_oc,'saftpay');
        if ($result != TRUE){
        	throw new Exception("No se pudo insertar en la cola safetypay.");
        }
        $safetypay->LogPut("insert job ok:".$codigo_oc);

        //actualizamos el estado de una sola notificación procesada.
		$confirm = $safetypay->ConfirmNewOperationActivity($codigo_oc, $toconfirm);
        if ( $confirm['ErrorManager']['ErrorNumber']['@content'] != '0' ){
            throw new Exception("no se pudo notificar el pago a safetypay.");
        }
		$safetypay->LogPut("notif ok:".$codigo_oc);			

		//mandando correo al cliente
		$safetypay->MailConfirmPayment($codigo_oc);
		$safetypay->LogPut("mail ok:".$codigo_oc);

	}//for
	$safetypay->DisplayMessage("Se ha enviado un correo con la confirmación de su pago.");
	} catch (Exception $e) {
		$safetypay->LogPut($e->getMessage());
		mail(clsConfig::webmaster,"Pago safetypay","error al activar la orden compra:$codigo_oc ".$e->getMessage());
		$safetypay->DisplayMessage("Problemas con la pasarela SAFETYPAY favor de reportarlo a atención al cliente.");
	}
	$safetypay->LogPut(str_repeat("-",80));
?>