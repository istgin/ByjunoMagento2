<?php

namespace Byjuno\ByjunoCore\Helper\CembraApi;

use Byjuno\ByjunoCore\Helper\Api\CembraPayCheckoutAutRequest;
use Byjuno\ByjunoCore\Helper\Api\DeliveryDetails;
use Byjuno\ByjunoCore\Helper\Api\SettlementDetails;

class CembraPayCheckoutCreditRequest extends CembraPayCheckoutAutRequest
{
    public $requestMsgType; //String
    public $requestMsgId; //String
    public $requestMsgDateTime; //Date
    public $merchantOrderRef; //String
    public $amount; //int
    public $currency; //String
    public $settlementDetails; //seliveryDetails
    public $transactionId;

    public function __construct()
    {
        $this->deliveryDetails = new DeliveryDetails();
        $this->settlementDetails = new SettlementDetails();
    }

    public function createRequest() {
        return json_encode($this);
    }
}
