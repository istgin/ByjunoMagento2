<?php
/**
 * Created by CembraPay.
 */
namespace Byjuno\ByjunoCore\Helper\CembraApi;

class CembraPayCheckoutScreeningDetails {
    public $allowedByjunoPaymentMethods;  //array( String )
    public function __construct() {
        $this->allowedByjunoPaymentMethods = Array();
    }

}
class CembraPayCheckoutScreeningResponse {
    public $requestMsgId; //String
    public $requestMsgDateTime; //Date
    public $replyMsgId; //String
    public $replyMsgDateTime; //Date
    public $transactionId; //String
    public $merchantCustRef; //String
    public $processingStatus; //String
    public $screeningDetails; //ScreeningDetails

    public function __construct() {
        $this->screeningDetails = new CembraPayCheckoutScreeningDetails();
    }

}
