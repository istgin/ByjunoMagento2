<?php
/**
 * Created by CembraPay.
 */
namespace Byjuno\ByjunoCore\Helper\CembraApi;

/* sample response
{
  "requestMerchantId": "1234567890",
  "requestMsgType": "CAN",
  "requestMsgId": "13d2182e-acd1-4232-83f8-6342c0a67cb9",
  "requestMsgDateTime": "2023-11-03T14:18:45Z",
  "replyMsgId": "dd4f2f3b-d89c-4402-a9b2-6c9d78bb4f42",
  "replyMsgDateTime": "2023-11-03T14:18:46Z",
  "transactionId": "210728105911219999",
  "isTokenDeleted": false,
  "merchantOrderRef": "ORD-072021-K3948Z22",
  "processingStatus": "SUCCESS",
  "authorization": {
    "authorizationValidTill": "2023-11-07T14:18:45Z",
    "authorizedRemainingAmount": 15800,
    "authorizationCurrency": "CHF"
  },
  "cancellation": {
    "cancellationId": "99849849"
  }
}
 */

class CembraPayCheckoutCancelResponse {
    public $processingStatus;
    public $transactionId;
}
