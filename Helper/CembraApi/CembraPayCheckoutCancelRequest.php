<?php

namespace Byjuno\ByjunoCore\Helper\CembraApi;

use Byjuno\ByjunoCore\Helper\Api\CembraPayCheckoutAutRequest;
use Byjuno\ByjunoCore\Helper\Api\DeliveryDetails;
use Byjuno\ByjunoCore\Helper\Api\SettlementDetails;

/* sample request
{
"requestMsgType": "CAN",
"requestMsgId": "13d2182e-acd1-4232-83f8-6342c0a67cb9",
"requestMsgDateTime": "2023-11-03T14:18:44Z",
"transactionId": "168657970100001042",
"merchantOrderRef": "ORD-072021-K3948Z22",
"amount": 100,
"currency": "CHF"
}
 */

class CembraPayCheckoutCancelRequest extends CembraPayCheckoutAutRequest
{
    public $requestMsgType; //String
    public $requestMsgId; //String
    public $requestMsgDateTime; //Date
    public $transactionId;
    public $merchantOrderRef; //String
    public $amount; //int
    public $currency; //String

    public function createRequest() {
        return json_encode($this);
    }
}
