<?php

namespace Byjuno\ByjunoCore\Helper\CembraApi;

/*
 *
 *
 * Copy
Expand allCollapse all
{
"requestMsgType": "CHK",
"requestMsgId": "4b07ca69-6906-4794-8770-f4c17af60bff",
"requestMsgDateTime": "2022-06-06T06:14:04Z",
"idempotencyKey": "d464a714-24a2-4d47-8592-b7c4569c098a",
"merchantOrderRef": "Checkout21244",
"amount": 5000,
"currency": "CHF",
"settlementDetails": {
"instantSettlement": true,
"merchantInvoiceRef": "100115682"
},
"custDetails": {
"merchantCustRef": "kejee83339k20",
"loggedIn": false,
"custType": "P",
"firstName": "Hanna",
"lastName": "Mustermann",
"language": "de",
"salutation": "N"
},
"billingAddr": {
"addrFirstLine": "12 Bonappetite str",
"postalCode": "4444",
"town": "Bonbon",
"country": "CH"
},
"custContacts": {
"phoneMobile": "+41777717777",
"email": "SI1-sampleemail@mustermann.sample.ch"
},
"deliveryDetails": {
"deliveryDetailsDifferent": false,
"deliveryMethod": "PICK-UP"
},
"order": {
"basketItemsGoogleTaxonomies": [],
"basketItemsPrices": []
},
"sessionInfo": {
"tmxSessionId": "63b5a205d9sasfdad50a079bd69dded2ad421a207b657ae1b8262a8efe___"
},
"byjunoDetails": {
"byjunoPaymentMethod": "BYJUNO-INVOICE"
},
"merchantDetails": {
"returnUrlSuccess": "www.merchant.ch/Checkout21244/success",
"returnUrlError": "www.merchant.ch/Checkout21244/error",
"transactionChannel": "WEB",
"integrationModule": "Datatrans Direct Integration"
}
}
 */

class MerchantCheckoutDetails
{
    public $returnUrlSuccess; //String
    public $returnUrlError; //String
    public $transactionChannel; //String
    public $integrationModule; //String

}


class CembraPayCheckoutChkRequest extends CembraPayCheckoutAutRequest
{
    public $merchantDetails; //MerchantDetails
    public function __construct()
    {
        $this->custDetails = new CustDetails();
        $this->billingAddr = new BillingAddr();
        $this->custContacts = new CustContacts();
        $this->deliveryDetails = new DeliveryDetails();
        $this->order = new Order();
        $this->sessionInfo = new SessionInfo();
        $this->byjunoDetails = new CembraPayDetails();
        $this->merchantDetails = new MerchantCheckoutDetails();
    }

    public function createRequest() {
        return json_encode($this);
    }
}
