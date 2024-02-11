<?php

namespace Byjuno\ByjunoCore\Helper\CembraApi;

/*
 * {
  "requestMsgType": "SET",
  "requestMsgId": "51c81c4b-6538-474a-9371-f61afe9e5b00",
  "requestMsgDateTime": "2022-05-21T11:43:59Z",
  "transactionId": "210728105911212199",
  "merchantOrderRef": "kejee83339k20",
  "amount": 9000,
  "currency": "CHF",
  "settlementDetails": {
    "merchantInvoiceRef": "kejee83339k20",
    "isFinal": true
  },
  "deliveryDetails": {
    "deliveryMethod": "PICK-UP"
  }
}
 */

class CembraPayCheckoutSettleRequest extends CembraPayCheckoutAutRequest
{
    public $requestMsgType; //String
    public $requestMsgId; //String
    public $requestMsgDateTime; //Date
    public $merchantOrderRef; //String
    public $amount; //int
    public $currency; //String
    public $settlementDetails; //seliveryDetails
    public $deliveryDetails; //DeliveryDetails
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
