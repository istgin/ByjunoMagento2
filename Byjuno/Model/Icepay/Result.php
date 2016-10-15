<?php

namespace Icepay\IcpCore\Model\Icepay;
class Result {

    protected $sqlModel;

    public function __construct()
    {
        $this->sqlModel = Mage::getModel('icecore/mysql4_iceCore');
    }

    public function handle(array $_vars)
    {

        if (count($_vars) == 0)
            die("ICEPAY result page installed correctly.");
        if (!$_vars['OrderID'])
            die("No orderID found");

        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId(($_vars['OrderID'] == "DUMMY") ? $_vars['Reference'] : $_vars['OrderID'])
                ->addStatusHistoryComment(sprintf(Mage::helper("icecore")->__("Customer returned with status: %s"), $_vars['StatusCode']))
                ->save();

        switch (strtoupper($_vars['Status'])) {
            case "ERR":                
                $quoteID = Mage::getSingleton('checkout/session')->getQuoteId();
                Mage::getSingleton('core/session')->setData('ic_quoteid', $quoteID);
                Mage::getSingleton('core/session')->setData('ic_quotedate', date("Y-m-d H:i:s"));
                
                $msg = sprintf(Mage::helper("icecore")->__("The payment provider has returned the following error message: %s"), Mage::helper("icecore")->__($_vars['Status'] . ": " . $_vars['StatusCode']));
                $url = 'checkout/cart';
                Mage::getSingleton('checkout/session')->addError($msg);
                break;
            case "OK":
            case "OPEN":
            default:
                Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
                $url = 'checkout/onepage/success';
        };

        /* Redirect based on store */
        Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl($url));
        $url = Mage::app()->getStore($order->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true) . $url;
        Mage::app()->getFrontController()->getResponse()->setRedirect($url);
    }

}