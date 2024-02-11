<?php

namespace Byjuno\ByjunoCore\Helper\CembraApi;

class CustDetails
{
    public $merchantCustRef; //String
    public $loggedIn; //boolean
    public $custType; //String
    public $firstName; //String
    public $lastName; //String
    public $language; //String
    public $salutation; //String
    public $dateOfBirth; //String
    public $companyName; //String
    public $companyRegNum; //String

}

class CustomerConsents
{
    public $consentType; //String
    public $consentProvidedAt; //String
    public $consentDate; //Date
    public $consentReference; //String

}

class BillingAddr
{
    public $addrFirstLine; //String
    public $postalCode; //String
    public $town; //String
    public $country; //String

}

class CustContacts
{
    public $phonePrivate; //String
    public $phoneBusiness; //String
    public $phoneMobile; //String
    public $email; //String

}

class DeliveryDetails
{
    public $deliveryDetailsDifferent; //boolean
    public $deliveryMethod; //String
    public $deliveryFirstName; //String
    public $deliverySecondName; //String
    public $deliveryCompanyName; //String
    public $deliverySalutation; //String
    public $deliveryAddrFirstLine; //String
    public $deliveryAddrPostalCode; //String
    public $deliveryAddrTown; //String
    public $deliveryAddrCountry; //String

}

class SettlementDetails
{
    public $merchantInvoiceRef; //String
    public $isFinal; //boolean
}

class Order
{
    public $basketItemsGoogleTaxonomies;  //array( String )
    public $basketItemsPrices;  //array( number )

}

class SessionInfo
{
    public $fingerPrint; //String

}

class CembraPayDetails
{
    public $byjunoPaymentMethod; //String
    public $invoiceDeliveryType; //String

}

class MerchantDetails
{
    public $transactionChannel; //String
    public $integrationModule; //String

}

class CembraPayCheckoutAutRequest
{
    public $requestMsgType; //String
    public $requestMsgId; //String
    public $requestMsgDateTime; //Date
    public $merchantOrderRef; //String
    public $amount; //int
    public $currency; //String
    public $custDetails; //CustDetails
    public $customerConsents; //array( CustomerConsents )
    public $billingAddr; //BillingAddr
    public $custContacts; //CustContacts
    public $deliveryDetails; //DeliveryDetails
    public $order; //Order
    public $sessionInfo; //SessionInfo
    public $byjunoDetails; //CembraPayDetails
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
        $this->merchantDetails = new MerchantDetails();
    }

    public static function GUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public static function Date()
    {
        $date = new \DateTime();
        return $date->format('Y-m-d\TH:i:s\Z');
    }

    public function createRequest() {
        return json_encode($this);
    }

}
