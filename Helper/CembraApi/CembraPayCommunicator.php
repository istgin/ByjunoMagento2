<?php

namespace Byjuno\ByjunoCore\Helper\CembraApi;

class CembraPayCommunicator
{

    /**
     * @var CembraPayAzure
     */
    public $cembraPayAzure;

    public function __construct(
        CembraPayAzure $cembraPayAzure
    )
    {
        $this->cembraPayAzure = $cembraPayAzure;
    }
    private $server;

    /**
     * @param mixed $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

    /**
     * @return mixed
     */
    public function getServer()
    {
        return $this->server;
    }

    public function sendScreeningRequest($xmlRequest, CembraPayLoginDto $accessData, $cb) {
        return $this->sendRequest($xmlRequest, 'api/v1.0/screening', $accessData, $cb);
    }

    public function sendAuthRequest($xmlRequest, CembraPayLoginDto $accessData, $cb) {
        return $this->sendRequest($xmlRequest, 'api/v1.0/transactions/authorize', $accessData, $cb);
    }

    public function sendCheckoutRequest($xmlRequest, CembraPayLoginDto $accessData, $cb) {
        return $this->sendRequest($xmlRequest, 'api/v1.0/checkout', $accessData, $cb);
    }

    public function sendSettleRequest($xmlRequest, CembraPayLoginDto $accessData, $cb) {
        return $this->sendRequest($xmlRequest, 'api/v1.0/transactions/settle', $accessData, $cb);
    }

    public function sendCreditRequest($xmlRequest, CembraPayLoginDto $accessData, $cb) {
        return $this->sendRequest($xmlRequest, 'api/v1.0/transactions/credit', $accessData, $cb);
    }

    public function sendCancelRequest($xmlRequest, CembraPayLoginDto $accessData, $cb) {
        return $this->sendRequest($xmlRequest, 'api/v1.0/transactions/cancel', $accessData, $cb);
    }

    public function sendGetTransactionRequest($xmlRequest, CembraPayLoginDto $accessData, $cb) {
        return $this->sendRequest($xmlRequest, 'api/v1.0/transactions/status', $accessData, $cb);
    }

    private function sendRequest($xmlRequest, $endpoint, CembraPayLoginDto $accessData, $cb) {
        $token = $accessData->accessToken;
        if (!CembraPayAzure::validToken($token)) {
            $token = $this->cembraPayAzure->getToken($accessData);
        }
        if (empty($token)) {
            return "";
        }
        $response = "";
        if (intval($accessData->timeout) < 0) {
            $timeout = 30;
        } else {
            $timeout = $accessData->timeout;
        }
        if ($this->server == 'test') {
            $url = 'https://ext-test.cembrapay.ch/'.$endpoint;
        } else {
            //TODO: live server
            $url = 'https://ext-test.cembrapay.ch/'.$endpoint;
        }
        $request_data = $xmlRequest;

        $headers = [
            "Content-type: application/json",
            "accept: text/plain",
            "Authorization: Bearer ".$token
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = @curl_exec($curl);
        @curl_close($curl);

        $response = trim($response);
        $cb($accessData->helperObject, $token);
        return $response;
    }

}
