<?php
/**
 * Created by CembraPay.
 */
namespace Byjuno\ByjunoCore\Helper\CembraApi;

/*
 * {
    "requestMsgType": "AUT",
    "requestMsgId": "c8acad10-c106-41e9-b6cc-fd7c139df14d",
    "requestMsgDateTime": "2022-05-23T11:23:28Z",
    "replyMsgId": "f301a44d-9280-4c62-8327-dc949fa59ecc",
    "replyMsgDateTime": "2022-05-23T11:23:28Z",
    "transactionId": "170358830100004509",
    "merchantCustRef": "2",
    "token": "abfe4802-9f01-4c07-a6bf-ee1896b3ba9d",
    "isTokenDeleted": false,
    "merchantOrderRef": "000000283",
    "processingStatus": "AUTHORIZED",
    "settlement": {},
    "authorization": {
        "authorizationValidTill": "2022-05-27T23:23:28Z",
        "authorizedRemainingAmount": 6400,
        "authorizationCurrency": "CHF"
    }
}
 */
class Checkout {
    public $authorizationValidTill; //Date
    public $authorizedRemainingAmount; //int
    public $authorizationCurrency; //String

}
class CembraPayCheckoutChkResponse {
    public $requestMsgType; //String
    public $requestMsgId; //String
    public $requestMsgDateTime; //Date
    public $idempotencyKey; //String
    public $replyMsgId; //String
    public $replyMsgDateTime; //Date
    public $transactionId; //String
    public $merchantCustRef; //String
    public $token; //String
    public $merchantOrderRef; //String
    public $processingStatus; //String
    public $authorization; //Authorization
    public $redirectUrlCheckout;
}
