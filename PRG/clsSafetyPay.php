<?php
require_once 'safetypay/nanolink-sha256.php';
require_once 'safetypay/SafetyPayWSDL.php';
require_once 'safetypay/simplexml.class.php';

// error_reporting(E_ALL);
// ini_set('display_errors', '1');

define('STP_SDK_NAME',          'POST PHP');
define('STP_SDK_VERSION',       '4.0.0.0');
define('STP_SERVICE_NAME',      'POST Express');
define('STP_SERVICE_VERSION',   '3.0');

/**
 * SafetyPay Proxy Class
 *
 * @author      SafetyPay IT Team
 * @version     1.0
 * @package     class
 */
class clsSafetyPay
{
    var $conf = array();

    function clsSafetyPay()
    {
        /**
         * API and Signature Key
         * Set your Sandbox/Prod Credential.
         * Generate your own keys in the MMS, option: Profile > Credentials
         */
        $this->conf['ApiKey']       = cfgSafetyPay::ApiKey;
        $this->conf['SignatureKey'] = cfgSafetyPay::SignatureKey;
        /**
         * 1: For Sandbox (Test);
         * 0: For Production
         */
        $this->conf['Environment'] = cfgSafetyPay::Environment;
        $this->conf['Amount'] = '0';
        /**
         * Currency Code
         * Samples: USD, PEN, MXN, EUR.
         * Register your Default Currency market products, this must match your
         * Bank Account Affiliate. MMS, option: Profile > Bank Accounts.
         */
        $this->conf['CurrencyID'] = 'PEN';
        /**
         * ISO Code Language
         * Samples: EN, ES, DE, PT.
         */
        $this->conf['Language'] = 'ES';
        $this->conf['MerchantSalesID'] = '';
        /**
         * Tracking Code.
         * Leave blank
         */
        $this->conf['TrackingCode'] = '';
        $this->conf['Protocol'] = 'https';
        /**
         * URL Token Expiration Time
         * In minutes. Default: 120 minutes.
         */
        $this->conf['ExpirationTime'] = 120;

        /**
         * Filter By
         * Filter options in screen Express service as Countries, Banks
         * or Currencies. Leave blank. Optional.
         * Samples:
         * COUNTRY(PER)CURRENCY(USD): Show only to Peru and pay with US Dollar
         * BANK(1011,1019)COUNTRY(ESP): Shown only to Spain and banks selected.
         */
        $this->conf['FilterBy'] = '';
        $sn=$_SERVER["SERVER_NAME"];

        $this->conf['TransactionOkURL'] = "http://$sn/".cfgSafetyPay::TransactionOk;
        $this->conf['TransactionErrorURL'] = "http://$sn/".cfgSafetyPay::TransactionError;

        $this->conf['port_ssl'] = 443;
        $this->conf['RequestDateTime'] = $this->getDateIso8601(time());
        /**
         * Data Responde Format
         * Options: XML, CSV (Default)
         */
        $this->conf['ResponseFormat'] = 'XML';

        $this->setAccessPoint();
        $this->wsdl = $this->GetProxy();
    }

    function setConf( $conf )
    {
        foreach( $conf as $k => $v )
            $this->conf[$k] = $v;
    }
    /*
     * Setting correctly the Service URL
     */
    function setAccessPoint()
    {
        $_env = '';
        $domain_srv = 'mws2.safetypay.com';

        if ( $this->conf['Environment'] )
            $_env = '/sandbox';

        $this->conf['WsdlURL'] = strtolower( $this->conf['Protocol'] )
            . '://' . $domain_srv
            . "$_env/express/ws/v.3.0/";
    }
    /*
     * Get current ISO date from UNIX Timestamp
     */
    function getDateIso8601( $int_date )
    {
        $date_mod = date('Y-m-d\TH:i:s', $int_date);
        $pre_timezone = date('O', $int_date);
        $time_zone = substr($pre_timezone, 0, 3) . ':'
                            . substr($pre_timezone, 3, 2);
        $pos = strpos($time_zone, "-");
        if (PHP_VERSION >= '4.0')
            if ($pos === false) {
                // nothing
            }
            else
                if ($pos != 0)
                    $date_mod = $time_zone;
                else
                    if (is_string($pos) && !$pos) {
                    // nothing
                    }
                    else
                        if ($pos != 0)
                            $date_mod = $time_zone;

        return $date_mod;
    }

    /**
     * Get Signature
     */
    function GetSignature( $aparams, $slist = '' )
    {
        $allparams = '';
        $alist = explode( ',', $slist );
        if ( !isset($aparams[0]) )
            foreach( $alist as $k => $v )
                $allparams .= $aparams[rtrim(ltrim($v))];
        else
            foreach( $aparams as $k => $v )
                foreach( $alist as $x => $z )
                    $allparams .= $v[rtrim(ltrim($z))];

        if ( ereg('RequestDateTime', $slist) )
            $this->conf['Signature'] = sha256( $allparams
                                                . $this->conf['SignatureKey'] );
        else
            $this->conf['Signature'] = sha256( $this->conf['RequestDateTime']
                                                . $allparams
                                                . $this->conf['SignatureKey']);

        return $this->conf['Signature'];
    }

    /*
     * Create new instance
     */
    function GetProxy()
    {
        $this->wsdl = new SafetyPayWSDL();
        $this->wsdl->url = $this->conf['WsdlURL'];
        $this->wsdl->port_ssl = $this->conf['port_ssl'];

        return $this->wsdl;
    }

    /*
     * New Operation Activities retrieved by Merchants in previous process
     * GetNewOperationActivity must be confirmed using this process. Those activities will not be sent again in the next
     * GetNewOperationActivity call.
     */
    function ConfirmNewOperationActivity( $codigo_oc, $params)
    {
        //filtrando oc procesada para su confirmación
        if (count($params['ConfirmOperation'])>1){
            for ($x=0;$x<count($params['ConfirmOperation']);$x++ ){
                if ($params['ConfirmOperation'][$x]['MerchantSalesID'] != $codigo_oc){
                    unset($params['ConfirmOperation'][$x]);
                }
            }
        }

        $p = array( 'Username' => $this->conf['ApiKey'],
                    'ApiKey' => $this->conf['ApiKey'],
                    'RequestDateTime' => $this->conf['RequestDateTime'],
                    'ListOfOperationsActivityNotified' => $params
                    );

        $p['Signature'] = $this->GetSignature( $params['ConfirmOperation'],
                                                'OperationID, MerchantSalesID, '
                                                . 'MerchantOrderID, '
                                                . 'OperationStatus'
                                                );


        $result = $this->wsdl->call( 'ConfirmNewOperationActivity',
                                    array( 'OperationActivityNotifiedRequest' =>
                                            $p )
                                    );
        return $result;
    }
    /*
     * Merchants can notify SAFETYPAY about shipping to have a consolidated
     * report of their transactions.
     */
    function ConfirmShippedOrders( $params )
    {
        $p = array( 'Username' => $this->conf['ApiKey'],
                    'ApiKey' => $this->conf['ApiKey'],
                    'RequestDateTime' => $this->conf['RequestDateTime'],
                    'ShippingDetail' => $params
                    );

        $p['Signature'] = $this->GetSignature( $params,
                                        'SalesOperationID, InvoiceDate, '
                                        .'InvoiceNo, ShipDate, ShipMethod, '
                                        .'DeliveryCompanyName, TrackingNumber, '
                                        .'RecipientName'
                                                );

        $Result = $this->wsdl->call( 'ConfirmShippedOrders',
                                    array( 'ShippedOrderRequest' => $p )
                                    );

        return $Result;
    }


    /*
     * To create a Token URL, it can be sent by email
     * using an automatic system, or any other method.
     * With this method you can implement "SafetyPay Express" Mode.
     */
    function CreateExpressToken()
    {
        $p = array(
                'Username' => $this->conf['ApiKey'],
                'ApiKey' => $this->conf['ApiKey'],
                'RequestDateTime' => $this->conf['RequestDateTime'],
                'CurrencyID' => $this->conf['CurrencyID'],
                'Amount' => round((float)strip_tags($this->conf['Amount']), 2),
                'MerchantSalesID' => $this->conf['MerchantSalesID'],
                'Language' => $this->conf['Language'],
                'TrackingCode' => $this->conf['TrackingCode'],
                'ExpirationTime' => $this->conf['ExpirationTime'],
                'FilterBy' => $this->conf['FilterBy'],
                'TransactionOkURL' => $this->conf['TransactionOkURL'],
                'TransactionErrorURL' => $this->conf['TransactionErrorURL'],
                'TransactionExpirationTime' => $this->conf['TransactionExpirationTime'],
                'CustomMerchantName' => $this->conf['CustomMerchantName'],
                'ShopperEmail' => $this->conf['ShopperEmail'],
                'ProductID' => $this->conf['ProductID']
                );


        $p['Signature'] = $this->GetSignature(
                                    $this->conf,
                                    'CurrencyID, Amount, MerchantSalesID,'
                                    . 'Language, TrackingCode, ExpirationTime,'
                                    . 'TransactionOkURL, TransactionErrorURL'
                                    );

        $Result = $this->wsdl->call( 'CreateExpressToken',
                                        array( 'ExpressTokenRequest' =>
                                                $p )
                                    );

        return $Result;
    }

    /*
     * Allows you to refund a specific Sales Operation ID.
     */
    function CreateRefund( $params )
    {
        $p = array( 'Username' => $this->conf['ApiKey'],
                    'ApiKey' => $this->conf['ApiKey'],
                    'RequestDateTime' => $this->conf['RequestDateTime'],
                    'SalesOperationID' => $params['SalesOperationID'],
                    'AmountToRefund' => $params['AmountToRefund'],
                    'TotalPartial' => $params['TotalPartial'],
                    'Reason' => $params['Reason'],
                    'Comments' => $params['Comments']
                    );

        $p['Signature'] = $this->GetSignature(
                                    $params,
                                    'SalesOperationID, AmountToRefund, '
                                    . 'TotalPartial, Reason'
                                    );

        $Result = $this->wsdl->call( 'CreateRefund',
                                        array( 'RefundProcessRequest' =>
                                            $p )
                                    );

        return $Result;
    }

    /*
     * Retrieve all new operation activity. It includes new Paid Orders.
     * The activity will be sent again if they are not confirmed using
     * the process ConfirmNewOperationActivity.
     */
    function GetNewOperationActivity()
    {
        $p = array( 'Username' => $this->conf['ApiKey'],
                    'ApiKey' => $this->conf['ApiKey'],
                    'RequestDateTime' => $this->conf['RequestDateTime']
                    );

        $p['Signature'] = $this->GetSignature( $this->conf );

        $Result = $this->wsdl->call( 'GetNewOperationActivity',
                                    array( 'OperationActivityRequest' =>
                                            $p )
                                    );
        return $Result;
    }


    /*
     * Retrieve all operation activity for a specific operation.
     */
    function GetOperation()
    {
        $p = array( 'Username' => $this->conf['ApiKey'],
                    'ApiKey' => $this->conf['ApiKey'],
                    'RequestDateTime' => $this->conf['RequestDateTime'],
                    'MerchantSalesID' => $this->conf['MerchantSalesID']
                    );

        $p['Signature'] = $this->GetSignature( $this->conf, 'MerchantSalesID' );

        $Result = $this->wsdl->call( 'GetOperation',
                                        array( 'OperationRequest' =>
                                                $p )
                                    );

        return $Result;
    }

////////////////////////////////////////////////////////////////////////////////////////////////

    public static function DesencriptarURL($url){
        $x = Desencriptar($url, 'hex');

        if ($x===false) {
            return false;
        }
        $parameters = array();
        parse_str($x, $parameters);
        $expected_keys = array("cod_orden_compra", "txtAmount", "opcion");
        foreach($expected_keys as $key) {
            if (!array_key_exists($key, $parameters)) {
                return array();
            }
        }
        return $parameters;
    }


    function RCPGetNewOperationActivity(){
        //obtengo las ordenes por cancelar
        $result = $this -> GetNewOperationActivity();
        //en el manual dice que devuelve el error..??

        // no es necesario validar el StatusCode siempre sera 102->conf. de pago
        if (isset($result['ListOfOperations']['Operation']['OperationID']))
            $notificaciones = $result['ListOfOperations'];
        else//si son mas un pago para confirmar
            $notificaciones = $result['ListOfOperations']['Operation'];

        $opStatus = 0;
        foreach( $notificaciones as $k => $v ){
            $merchantOrderID = $v['MerchantSalesID'];

            if (isset($v['OperationActivities']['OperationActivity']))
                $oActivities = $v['OperationActivities']['OperationActivity'];
            else
                $oActivities = $v['OperationActivities'];

            if (isset($oActivities['CreationDateTime'])){
                $opStatus = $oActivities['Status']['StatusCode'];
            }else{
                foreach( $oActivities as $key => $va ){
                    $opStatus = $va['Status']['StatusCode'];
                }
            }

            $toconfirm['ConfirmOperation'][] = array(
                                'CreationDateTime' => $v['CreationDateTime'],
                                'OperationID' => $v['OperationID'],
                                'MerchantSalesID' => $v['MerchantSalesID'],
                                'MerchantOrderID' => $merchantOrderID,
                                'OperationStatus' => $opStatus
                                                    );

            $ConfirmTransactions[] = $v['OperationID']
                                        . ' (' . $v['MerchantSalesID'] . ')';
        }
        return $toconfirm;
    }


    function UpdateOrdenCompra($codigo_oc){
        $oc = new nicVtaOrdenCompra($codigo_oc);
        $oc->cod_estado_orden_compra = cfgSafetyPay::oc_estado_completado;
        $oc->fecha_pago = date('Y-m-d H:i:s');
        //$oc->debug = TRUE;
        $oc->Update();
        $estado_oc = TRUE;
        return $estado_oc;
    }

    function LogPut($logx,$output){
        if(is_array($logx))
            $log=json_encode($logx);
        else
            $log=$logx;

        $log=$log."\r\n";
        if ($output=="E"){
            file_put_contents("/var/log/safetypay/envio_datos.log",$log,FILE_APPEND);
        }else{
            file_put_contents("/var/log/safetypay/retorno_datos.log",$log,FILE_APPEND);
        }
    }

    function InsertJobQueue($codigo_oc,$opcion_pago){
        $cola =new clsCobPagoEfectivoCola();
        $cola->campos->fecha_actualizacion=date("Y-m-d H:i:s");
        $cola->campos->cod_orden_compra=$codigo_oc;
        $cola->campos->ip_origen=MiIP();
        $sql="SELECT * FROM nic_cob_pago_efectivo_cola WHERE estado='pendiente' AND cod_orden_compra=$codigo_oc";
        $r=EjecutarSQL($sql);
        if(count($r)==0){
            $cola->campos->opcion_pago=$opcion_pago;
            $cola->campos->fecha_registro=date('Y-m-d H:i:s');
            $cola->campos->estado='pendiente';
            $cola->insert();
        }
        $estado_job = TRUE;
        return $estado_job;
    }

    function DisplayMessage($msj){
        $smarty= new Clase_Smarty;
        $smarty->assign("mensaje",$msj);
        $smarty->display("mensaje.html");
    }


    function MailConfirmPayment($codigo_oc){
        header('Content-Type: text/html;charset=utf-8');
        $smarty = new Clase_Smarty;
        $oc = new clsVtaOrdenCompra($codigo_oc);
        $cliA = new clsCliente($oc->campos->cod_cliente_admin);
        $cliF = new clsCliente($oc->campos->cod_cliente_facturacion);
        $m = new clsMstMoneda($oc->campos->cod_moneda);
        $titulo="Punto.pe - Confirmación de pago de compra : ".$codigo_oc. " - SafetyPay";
        $mensaje_prov_online="
        Te informamos que hemos recibido la confirmaci&oacute;n de pago de tu transacci&oacute;n SafetyPay.
        <br/>
        ";
        $smarty->assign('mensaje_prov_online',$mensaje_prov_online);
        $smarty->assign('razon_social',ucfirst($cliF->campos->razon_social));
        $smarty->assign('codigo_orden',$codigo_oc);
        $smarty->assign('monto_orden',$oc->campos->total);
        $smarty->assign('moneda_orden',ucfirst($m->campos->moneda));
        $enviando=$smarty->fetch('email/nic_confirmacion_pago_safetypay.html');

        mail($cliF->campos->email, $titulo, $enviando, "From: punto@punto.pe\nX-Mailer:PHP/".phpversion()."\nMime-Version: 1.0\nContent-Type: text/html");
        if($cliF->campos->email!=$cliA->campos->email)
            mail($cliA->campos->email, $titulo, $enviando, "From: punto@punto.pe\nX-Mailer:PHP/".phpversion()."\nMime-Version: 1.0\nContent-Type: text/html");
}


}//class
?>
