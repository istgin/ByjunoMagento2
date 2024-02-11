<?php

namespace Byjuno\ByjunoCore\Helper\CembraApi;

/*
{
  "requestMerchantId": "1234567890",
  "requestMsgType": "TST",
  "requestMsgId": "46617d0f-1705-47a0-9f02-eb643306a630",
  "requestMsgDateTime": "2023-10-27T14:21:51Z",
  "replyMsgId": "0b3f5be2-65cb-401e-a01e-1dd7580553af",
  "replyMsgDateTime": "2023-10-27T14:21:51Z",
  "transactionId": "210728105911212199",
  "merchantCustRef": "kejee83339k20",
  "token": "a51f704e-7cda-44cf-a53c-9345d0e0c327",
  "isTokenDeleted": false,
  "merchantOrderRef": "ORD-072021-K3948Z29",
  "processingStatus": "SUCCESS",
  "authorization": {
    "authorizationValidTill": "2023-10-27T14:21:51Z",
    "authorizedRemainingAmount": 0,
    "authorizationCurrency": "CHF"
  },
  "transactionStatus": {
    "transactionId": "21072815911212199",
    "transactionStatus": "SETTLED",
    "transactionMessages": [
      {
        "requestMsgType": "AUT",
        "requestMsgId": "6c91f0ff-1170-4d45-b4da-231f81d91571",
        "requestMsgDateTime": "2021-07-28T10:59:10Z",
        "requestReceivedDateTime": "2021-07-28T10:59:10Z",
        "responseMsgId": "bc273d8e-b021-491a-9c42-afec9ba6f44f",
        "responseMsgDateTime": "2021-07-28T10:59:11Z",
        "processingStatus": "SUCCESS"
      },
      {
        "requestMsgType": "SET",
        "requestMsgId": "401225e0-5da0-43b5-bee5-a8fc858c674e",
        "requestMsgDateTime": "2021-07-28T11:09:11Z",
        "requestReceivedDateTime": "2021-07-28T11:09:12Z",
        "responseMsgId": "ce789a12-87e2-40c0-999b-21a4c4697d63",
        "responseMsgDateTime": "2021-07-28T11:09:12Z",
        "processingStatus": "SUCCESS"
      }
    ]
  }
}
 */


class CembraPayAuthorization
{
    public $authorizationValidTill; //String
    public $authorizedRemainingAmount; //int
    public $authorizationCurrency; //String
}
class CembraPayTransactionStatus
{
    public $transactionId; //String
    public $transactionStatus; //int
    public $transactionMessages; //Array
}

class CembraPayGetStatusResponse
{
    public $requestMerchantId; //String
    public $requestMsgType; //boolean
    public $requestMsgId; //String
    public $requestMsgDateTime; //String
    public $replyMsgId; //String
    public $replyMsgDateTime; //String
    public $token; //String
    public $merchantCustRef; //String
    public $isTokenDeleted; //String
    public $merchantOrderRef; //String
    public $processingStatus; //String
    public $authorization; //String
    public $transactionStatus; //String

    public function __construct()
    {
        $this->authorization = new CembraPayAuthorization();
        $this->transactionStatus = new CembraPayTransactionStatus();
    }
}
