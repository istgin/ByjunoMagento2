<?php


namespace Icepay\IcpCore\Model\Webservice;
class Core {

    protected $merchantID;
    protected $secretCode;
    protected $client;
    protected $serviceURL = 'https://connect.icepay.com/webservice/icepay.svc?wsdl';

    /**
     * Create the SOAP client
     * 
     * @param int $merchantID
     * @param string $secretCode
     * 
     * @since 1.0.0
     */
    public function init($merchantID, $secretCode)
    {
        $this->merchantID = (int) $merchantID;
        $this->secretCode = (string) $secretCode;

        $this->client = new SoapClient($this->serviceURL, array(
            'encoding' => 'UTF-8',
            'cache_wsdl' => 'WSDL_CACHE_NONE'
        ));
    }

    /**
     * Return Merchant ID
     * 
     * @since 1.0.1
     * @return string
     */
    public function getMerchantID()
    {
        return $this->merchantID;
    }

    /**
     * Return SecretCode
     * 
     * @since 1.0.1
     * @return string
     */
    public function getSecretCode()
    {
        return $this->secretCode;
    }

    /**
     * Return the user IP address
     * 
     * @since 1.0.0
     * @return string
     */
    protected function getIP()
    {
        return (string) $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Return the current timestamp
     * 
     * @since 1.0.0
     * @return type
     */
    protected function getTimestamp()
    {
        return (string) gmdate("Y-m-d\TH:i:s\Z");
    }

    /**
     * Generate and return the checksum
     * 
     * @param object $obj
     * @param string $secretCode
     * 
     * @since 1.0.0
     * @return string
     */
    protected function generateChecksum($obj = null, $secretCode = null)
    {
        $arr = array();

        if ($secretCode)
            array_push($arr, $secretCode);

        foreach ($obj as $val) {
            if (is_bool($val))
                $val = ($val) ? 'true' : 'false';
            array_push($arr, $val);
        }

        return (string) sha1(implode("|", $arr));
    }

}