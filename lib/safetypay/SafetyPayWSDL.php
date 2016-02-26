<?php
/* SafetyPay Integration Code
 *
 * Where call the Web Service WSDL and is declared the XML schema for each
 * method to request data.
 *
 * @copyright   2012 SaftPay Inc. IT Team - support@safetypay.com
 * @license     http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @package     class
 */

if (function_exists('ini_set'))	ini_set('error_reporting','E_ALL & ~E_NOTICE');

define('STP_SERVICE_NAME', 'WS API');
define('STP_SERVICE_VERSION', '3.0');

/*
 * SafetyPay WSDL Class
 *
 * To handle the WSDL XML Schemas
 *
 * @author   	SafetyPay IT Team
 * @version   	2.0
 * @package   	class
 */
class SafetyPayWSDL
{
    var $url;
    var $port_ssl;
    var $operation;

    /*
     * Declaration of all "Request XML Schema" for each method.
     */
    function SafetyPayWSDL()
    {
        $this->operation['CommunicationTest'] = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:safetypay:messages:mws:api">
                   <soap:Header />
                   <soap:Body>
                      <urn:TestRequest>
                         <urn:ApiKey>?</urn:ApiKey>
                         <urn:RequestDateTime>?</urn:RequestDateTime>
                         <urn:Signature>?</urn:Signature>
                      </urn:TestRequest>
                   </soap:Body>
                </soap:Envelope>';

        $this->operation['ConfirmNewOperationActivity'] = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:safetypay:messages:mws:api" xmlns:urn1="urn:safetypay:schema:mws:api">
                   <soap:Header />
                   <soap:Body>
                      <urn:OperationActivityNotifiedRequest>
                         <urn:ApiKey>?</urn:ApiKey>
                         <urn:RequestDateTime>?</urn:RequestDateTime>
                         <urn:ListOfOperationsActivityNotified>?</urn:ListOfOperationsActivityNotified>
                         <urn:Signature>?</urn:Signature>
                      </urn:OperationActivityNotifiedRequest>
                   </soap:Body>
                </soap:Envelope>';

        $this->operation['ConfirmShippedOrders'] = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:safetypay:messages:mws:api" xmlns:urn1="urn:safetypay:schema:mws:api">
                   <soap:Header />
                   <soap:Body>
                      <urn:ShippedOrderRequest>
                         <urn:ApiKey>?</urn:ApiKey>
                         <urn:RequestDateTime>?</urn:RequestDateTime>
                         <urn:ShippingDetail>
                            <urn1:SalesOperationID>?</urn1:SalesOperationID>
                            <urn1:InvoiceDate>?</urn1:InvoiceDate>
                            <urn1:InvoiceNo>?</urn1:InvoiceNo>
                            <urn1:ShipDate>?</urn1:ShipDate>
                            <urn1:ShipMethod>?</urn1:ShipMethod>
                            <urn1:DeliveryCompanyName>?</urn1:DeliveryCompanyName>
                            <urn1:TrackingNumber>?</urn1:TrackingNumber>
                            <urn1:RecipientName>?</urn1:RecipientName>
                         </urn:ShippingDetail>
                         <urn:Signature>?</urn:Signature>
                      </urn:ShippedOrderRequest>
                   </soap:Body>
                </soap:Envelope>';

        $this->operation['CreateExpressToken'] = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:safetypay:messages:mws:api" xmlns:urn1="urn:safetypay:schema:mws:api">
                   <soap:Header />
                   <soap:Body>
                      <urn:ExpressTokenRequest>
                         <urn:ApiKey>?</urn:ApiKey>
                         <urn:RequestDateTime>?</urn:RequestDateTime>
                         <urn:CurrencyID>?</urn:CurrencyID>
                         <urn:Amount>?</urn:Amount>
                         <urn:MerchantSalesID>?</urn:MerchantSalesID>
                         <urn1:Language>?</urn1:Language>
                         <urn:TrackingCode>?</urn:TrackingCode>
                         <urn:ExpirationTime>?</urn:ExpirationTime>
                         <urn:FilterBy>?</urn:FilterBy>
                         <urn:TransactionOkURL>?</urn:TransactionOkURL>
                         <urn:TransactionErrorURL>?</urn:TransactionErrorURL>
                         <urn:TransactionExpirationTime>?</urn:TransactionExpirationTime>
                         <urn:CustomMerchantName>?</urn:CustomMerchantName>
                         <urn:ShopperEmail>?</urn:ShopperEmail>
						 <urn:ProductID>?</urn:ProductID>
                         <urn:Signature>?</urn:Signature>
                      </urn:ExpressTokenRequest>
                   </soap:Body>
                </soap:Envelope>';

        $this->operation['CreateRefund'] = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:safetypay:messages:mws:api">
                   <soap:Header />
                   <soap:Body>
                      <urn:RefundProcessRequest>
                         <urn:ApiKey>?</urn:ApiKey>
                         <urn:RequestDateTime>?</urn:RequestDateTime>
                         <urn:SalesOperationID>?</urn:SalesOperationID>
                         <urn:AmountToRefund>?</urn:AmountToRefund>
                         <urn:TotalPartial>?</urn:TotalPartial>
                         <urn:Reason>?</urn:Reason>
                         <urn:Comments>?</urn:Comments>
                         <urn:Signature>?</urn:Signature>
                      </urn:RefundProcessRequest>
                   </soap:Body>
                </soap:Envelope>';

        $this->operation['GetNewOperationActivity'] = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:safetypay:messages:mws:api">
                   <soap:Header />
                   <soap:Body>
                      <urn:OperationActivityRequest>
                         <urn:ApiKey>?</urn:ApiKey>
                         <urn:RequestDateTime>?</urn:RequestDateTime>
                         <urn:Signature>?</urn:Signature>
                      </urn:OperationActivityRequest>
                   </soap:Body>
                </soap:Envelope>';

        $this->operation['GetOperation'] = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:safetypay:messages:mws:api">
                   <soap:Header />
				   <soap:Body>
					  <urn:OperationRequest>
						 <urn:ApiKey>?</urn:ApiKey>
						 <urn:RequestDateTime>?</urn:RequestDateTime>
						 <urn:MerchantSalesID>?</urn:MerchantSalesID>
						 <urn:Signature>?</urn:Signature>
					  </urn:OperationRequest>
				   </soap:Body>
				</soap:Envelope>';
    }

    /*
     * Call to determined method of Web Service. Response include error codes.
     * Build valid "Request XML Schema" with data referenced from Proxy Class.
     *
     * @param   string  $method     Method Name to request data
     * @param   array   $params     Parameters to include in each XML Scheme
     *
     * @return  array
     */
    function call( $method, $params )
    {
        $url = parse_url($this->url);
        $host = $url['host'];
        $path = $url['path'];

        $stream_data = '<?xml version="1.0" encoding="UTF-8"?'.">" . "\n";
        $stream_data.= $this->operation[$method];

        foreach ($params as $key => $val)
        {
            if ( is_array( $val ) )
            {
                $item = '';
                foreach ( $val as $l => $v )
                {
                    if (is_array($v))
                    {
                        foreach ($v as $idx => $idv)
                        {
                            if (is_array($idv))
                            {
                                foreach ($idv as $k => $v)
                                    if (is_array($v))
                                    {
                                        $px = '';
                                        if ( $method == 'ConfirmNewOperationActivity' )
                                            $px = 'urn1:';

                                        $item.= "\n\t\t\t<$px$idx>\n";
                                        foreach ($v as $_k => $_v)
                                            $item.= "\t\t\t\t"
                                                    . "<$px$_k>$_v</$px$_k>\n";
                                        $item.= "\t\t\t</$px$idx>";
                                    }
                            }
                            else
                                $stream_data = $this->update( $stream_data,
                                                                $idx, $idv );
                        }
                        $stream_data = $this->update( $stream_data, $l, $item );
                    }
                    else
                        $stream_data = $this->update($stream_data, $l, $v);
                }
            }
            $stream_data = $this->update( $stream_data, $key, $val );
        }

        if ( $url['scheme'] == 'https' )
            $hostRemote = array( 'ssl://' . $host, $this->port_ssl );
        else
            $hostRemote = array($host, 80);

        $fp = fsockopen( $hostRemote[0], $hostRemote[1], $errno, $errstr );
        if ( $fp )
        {
            fputs($fp, "POST $path HTTP/1.1\r\n");
            fputs($fp, "Host: $host\r\n");
            fputs($fp, "Content-Type: text/xml; charset=UTF-8\r\n");
            fputs($fp, "Content-Length: ". strlen($stream_data) ."\r\n");
            fputs($fp, "SOAPAction: \"urn:safetypay:contract:mws:api:$method\"\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $stream_data);
            $result = '';
            while( !feof($fp) )
                $result = fgets($fp);

            $resp = $this->xml2object($result);
        }

        $respId = array_keys($params);
        preg_grep("/Request/U", $respId);
        $responseItem = str_replace('Request', 'Response', $respId[0]);

        if ($responseItem == 'OperationActivityResponse')
            $responseItem = 'OperationResponse';

        if ( gettype($resp) != 'string' )
        {
            if ( is_array( $resp['s:Body']['s:Fault'] ) )
            {
                $resp['s:Body'][$responseItem]['ErrorManager']['ErrorNumber']['@content'] = $resp['s:Body']['s:Fault']['faultcode'];
                $resp['s:Body'][$responseItem]['ErrorManager']['Description']['@content'] = $resp['s:Body']['s:Fault']['faultstring'];
            }
        }
        else
        {
            $reterror = $resp;
            unset($resp);
            $resp['s:Body'][$responseItem]['ErrorManager']['ErrorNumber']['@content'] = '999';
            $resp['s:Body'][$responseItem]['ErrorManager']['Description']['@content'] = 'Unrecognized format. General failure: ' . $reterror;
        }

        return $resp['s:Body'][$responseItem];
    }

    /*
     * Interpret an XML document into an array object
     *
     * @param   string  $data       Response of Web Service in string format
     *
     * @return  array
     */
    function xml2object( $data )
    {
        $sxml = new simplexml;
        return $sxml->xml_load_file( '', $data );
    }

    /*
     * Replaced all parameters into the node respective of "Request XML Schema"
     * for a determined method.
     *
     * @param   string  $text       "Request XML Schema" template
     * @param   string  $tag        Tag Node to replace the $value
     * @param   string  $value      Value to replace into tag node
     *
     * @return  string
     */
    function update( $text, $tag, $value )
    {
        if ( !is_array($value) )
            return str_replace("$tag>?", "$tag>$value", $text);
        else
            return $text;
    }
}
?>