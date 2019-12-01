<?php
/**
 * Copyright © 2015 Pay.nl All rights reserved.
 */
namespace Byjuno\ByjunoCore\Controller\Checkout;

use Byjuno\ByjunoCore\Helper\DataHelper;
use Byjuno\ByjunoCore\Helper\Api\ByjunoLogger;
use Magento\Framework\App\Action\Action;

class Startpayment extends Action
{
    protected $_config;
    /**
     * @var DataHelper
     */
    protected static $_dataHelper;
    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        DataHelper $helper
    )
    {
        self::$_dataHelper = $helper;
        parent::__construct($context);
    }

    public static function executeS3($order, \Magento\Sales\Model\Order\Payment $payment, $transaction, $accept)
    {
        $request = self::$_dataHelper->CreateMagentoShopRequestPaid($order, $payment, $payment->getAdditionalInformation('customer_gender'), $payment->getAdditionalInformation('customer_dob'), $transaction, $accept, $payment->getAdditionalInformation('pref_lang'));
        $ByjunoRequestName = "Order paid";
        $requestType = 'b2c';
        if ($request->getCompanyName1() != '' && self::$_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/businesstobusiness', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '1') {
            $ByjunoRequestName = "Order paid for Company";
            $requestType = 'b2b';
            $xml = $request->createRequestCompany();
        } else {
            $xml = $request->createRequest();
        }
        $mode = self::$_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/currentmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($mode == 'live') {
            self::$_dataHelper->_communicator->setServer('live');
        } else {
            self::$_dataHelper->_communicator->setServer('test');
        }
        $response = self::$_dataHelper->_communicator->sendRequest($xml, (int)self::$_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/timeout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $status = 0;
        if ($response) {
            self::$_dataHelper->_response->setRawResponse($response);
            self::$_dataHelper->_response->processResponse();
            $status = (int)self::$_dataHelper->_response->getCustomerRequestStatus();
            if (intval($status) > 15) {
                $status = 0;
            }
            self::$_dataHelper->saveLog($request, $xml, $response, $status, $ByjunoRequestName);
        } else {
            self::$_dataHelper->saveLog($request, $xml, "empty response", "0", $ByjunoRequestName);
        }
        return array($status, $requestType);
    }

    public static function executeS2Quote(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Payment $payment, DataHelper $_internalDataHelper)
    {
        $request = $_internalDataHelper->CreateMagentoShopRequestOrderQuote($quote, $payment, $payment->getAdditionalInformation('customer_gender'), $payment->getAdditionalInformation('customer_dob'), $payment->getAdditionalInformation('pref_lang'));

        $ByjunoRequestName = "Order request";
        $requestType = 'b2c';
        if ($request->getCompanyName1() != '' && $_internalDataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/businesstobusiness', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '1') {
            $ByjunoRequestName = "Order request for Company";
            $requestType = 'b2b';
            $xml = $request->createRequestCompany();
        } else {
            $xml = $request->createRequest();
        }
        $mode = $_internalDataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/currentmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($mode == 'live') {
            $_internalDataHelper->_communicator->setServer('live');
        } else {
            $_internalDataHelper->_communicator->setServer('test');
        }

        $response = $_internalDataHelper->_communicator->sendRequest($xml, (int)$_internalDataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/timeout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $status = 0;
        if ($response) {
            $_internalDataHelper->_response->setRawResponse($response);
            $_internalDataHelper->_response->processResponse();
            $status = (int)$_internalDataHelper->_response->getCustomerRequestStatus();
            if ($_internalDataHelper->_checkoutSession != null) {
                $_internalDataHelper->_checkoutSession->setByjunoTransaction($_internalDataHelper->_response->getTransactionNumber());
                $_internalDataHelper->_checkoutSession->setS2Response($response);
            }
            $_internalDataHelper->saveLog($request, $xml, $response, $status, $ByjunoRequestName);
            if (intval($status) > 15) {
                $status = 0;
            }
        } else {
            $_internalDataHelper->saveLog($request, $xml, "empty response", "0", $ByjunoRequestName);
            if ($_internalDataHelper->_checkoutSession != null) {
                $_internalDataHelper->_checkoutSession->setS2Response("");
            }
        }
        if ($_internalDataHelper->_checkoutSession != null) {
            $_internalDataHelper->_checkoutSession->setIntrumStatus($status);
            $_internalDataHelper->_checkoutSession->setIntrumRequestType($requestType);
        }
        return array($status, $requestType, $_internalDataHelper->_response);

    }


    public static function executeS2($order, \Magento\Sales\Model\Order\Payment $payment)
    {
        $request = self::$_dataHelper->CreateMagentoShopRequestOrder($order, $payment, $payment->getAdditionalInformation('customer_gender'), $payment->getAdditionalInformation('customer_dob'), $payment->getAdditionalInformation('pref_lang'));

        $ByjunoRequestName = "Order request";
        $requestType = 'b2c';
        if ($request->getCompanyName1() != '' && self::$_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/businesstobusiness', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '1') {
            $ByjunoRequestName = "Order request for Company";
            $requestType = 'b2b';
            $xml = $request->createRequestCompany();
        } else {
            $xml = $request->createRequest();
        }
        $mode = self::$_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/currentmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($mode == 'live') {
            self::$_dataHelper->_communicator->setServer('live');
        } else {
            self::$_dataHelper->_communicator->setServer('test');
        }
        $response = self::$_dataHelper->_communicator->sendRequest($xml, (int)self::$_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/timeout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $status = 0;
        if ($response) {
            self::$_dataHelper->_response->setRawResponse($response);
            self::$_dataHelper->_response->processResponse();
            $status = (int)self::$_dataHelper->_response->getCustomerRequestStatus();
            if (self::$_dataHelper->_checkoutSession != null) {
                self::$_dataHelper->_checkoutSession->setByjunoTransaction(self::$_dataHelper->_response->getTransactionNumber());
            }
            self::$_dataHelper->saveLog($request, $xml, $response, $status, $ByjunoRequestName);
            if (intval($status) > 15) {
                $status = 0;
            }
            $trxId = self::$_dataHelper->_response->getResponseId();
        } else {
            self::$_dataHelper->saveLog($request, $xml, "empty response", "0", $ByjunoRequestName);
            $trxId = "empty";
        }
        $payment->setTransactionId($trxId);
        $payment->setParentTransactionId($payment->getTransactionId());

        $transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH, null, true);
        $accept = "";
        if (self::$_dataHelper->byjunoIsStatusOk($status, "byjunocheckoutsettings/byjuno_setup/merchant_risk")) {
            $accept = "CLIENT";
        }
        if (self::$_dataHelper->byjunoIsStatusOk($status, "byjunocheckoutsettings/byjuno_setup/byjuno_risk")) {
            $accept = "IJ";
        }
        if ($accept != "") {
            $transaction->setIsClosed(false);
        } else {
            $transaction->setIsClosed(true);
        }
        $transaction->save();
        $payment->save();
        if (self::$_dataHelper->_checkoutSession != null) {
            self::$_dataHelper->_checkoutSession->setIntrumStatus($status);
            self::$_dataHelper->_checkoutSession->setIntrumRequestType($requestType);
            self::$_dataHelper->_checkoutSession->setIntrumOrder($order->getId());
        }
        return array($status, $requestType, clone self::$_dataHelper->_response);
    }

    public static function executeBackendOrder(DataHelper $helper, \Magento\Sales\Model\Order $order)
    {
        self::$_dataHelper = $helper;
        /* @var $payment \Magento\Sales\Model\Order\Payment */
        $payment = $order->getPayment();

        try {
            /* @var $responseS2 \Byjuno\ByjunoCore\Helper\Api\ByjunoResponse */
            list($statusS2, $requestTypeS2, $responseS2) = self::executeS2($order, $payment);
            $accept = "";
            if (self::$_dataHelper->byjunoIsStatusOk($statusS2, "byjunocheckoutsettings/byjuno_setup/merchant_risk")) {
                $accept = "CLIENT";
            }
            if (self::$_dataHelper->byjunoIsStatusOk($statusS2, "byjunocheckoutsettings/byjuno_setup/byjuno_risk")) {
                $accept = "IJ";
            }
            if ($accept != "") {
                list($statusS3, $requestTypeS3) = self::executeS3($order, $payment, $responseS2->getTransactionNumber(), $accept);
                if (self::$_dataHelper->byjunoIsStatusOk($statusS3, "byjunocheckoutsettings/byjuno_setup/accepted_s3")) {
                    $payment->setAdditionalInformation("s3_ok", 'true')->save();
                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $order->setStatus("byjuno_confirmed");
                    self::$_dataHelper->saveStatusToOrder($order, $responseS2);
                    try {
                        $mode = self::$_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/currentmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        if ($mode == 'live') {
                            $email = self::$_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjuno_prod_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        } else {
                            $email = self::$_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjuno_test_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        }
                        self::$_dataHelper->_originalOrderSender->send($order);
                        self::$_dataHelper->_byjunoOrderSender->sendOrder($order, $email);
                    } catch (\Exception $e) {
                        self::$_dataHelper->_loggerPsr->critical($e);
                    }
                    // ALL OK
                } else {
                    $error = self::$_dataHelper->getByjunoErrorMessage($statusS3, $requestTypeS3). "(S3)";
                    $order->registerCancellation($error)->save();
                    throw new \Exception($error);
                }
            } else {
                $error = self::$_dataHelper->getByjunoErrorMessage($statusS2, $requestTypeS2);
                $order->registerCancellation($error)->save();
                throw new \Exception($error);
            }

        } catch (\Exception $e) {
            $error = __($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function execute()
    {
        $order = self::$_dataHelper->_checkoutSession->getLastRealOrder();
        /* @var $payment \Magento\Sales\Model\Order\Payment */
        $payment = $order->getPayment();
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $statusS2 = self::$_dataHelper->_checkoutSession->getIntrumStatus();
            $typeS2 = self::$_dataHelper->_checkoutSession->getIntrumRequestType();
            $responseS2String = self::$_dataHelper->_checkoutSession->getS2Response();
            if ($responseS2String == "") {
                throw new \Exception("Empty response set");
            }
            self::$_dataHelper->_response->setRawResponse($responseS2String);
            self::$_dataHelper->_response->processResponse();
            $responseS2 = clone self::$_dataHelper->_response;
            if ($payment->getAdditionalInformation('accept') != "") {
                $byjunoTrx =  self::$_dataHelper->_checkoutSession->getByjunoTransaction();
                list($statusS3, $requestTypeS3) = self::executeS3($order, $payment, $byjunoTrx, $payment->getAdditionalInformation('accept'));
                if (self::$_dataHelper->byjunoIsStatusOk($statusS3, "byjunocheckoutsettings/byjuno_setup/accepted_s3")) {
                    if ($byjunoTrx != "") {
                        $payment->setTransactionId($byjunoTrx);
                        $payment->setParentTransactionId($payment->getTransactionId());
                        $transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH, null, true);
                        $transaction->setIsClosed(true);
                        $transaction->save();
                    }
                    $payment->setAdditionalInformation("s3_ok", 'true');
                    $payment->save();
                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $order->setStatus("byjuno_confirmed");
                    self::$_dataHelper->saveStatusToOrder($order, $responseS2);
                    try {
                        $mode = self::$_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/currentmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        if ($mode == 'live') {
                            $email = self::$_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjuno_prod_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        } else {
                            $email = self::$_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjuno_test_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        }
                        self::$_dataHelper->_originalOrderSender->send($order);
                        self::$_dataHelper->_byjunoOrderSender->sendOrder($order, $email);
                    } catch (\Exception $e) {
                        self::$_dataHelper->_loggerPsr->critical($e);
                    }
                    self::$_dataHelper->_checkoutSession->setTmxSession('');
                    self::$_dataHelper->_checkoutSession->setCDPStatus('');
                    $resultRedirect->setPath('checkout/onepage/success');
                } else {
                    $error = self::$_dataHelper->getByjunoErrorMessage($statusS3, $requestTypeS3). "(S3)";
                    $order->registerCancellation($error)->save();
                    $this->restoreQuote();
                    $this->messageManager->addExceptionMessage(new \Exception($statusS3), $error);
                    $resultRedirect->setPath('checkout/cart');
                }
            } else {
                $error = self::$_dataHelper->getByjunoErrorMessage(
                    $statusS2,
                    $typeS2
                );
                $order->registerCancellation($error)->save();
                $this->restoreQuote();
                $this->messageManager->addExceptionMessage(new \Exception($statusS2), $error);
                $resultRedirect->setPath('checkout/cart');
            }

        } catch (\Exception $e) {
            $order = self::$_dataHelper->_checkoutSession->getLastRealOrder();
            $error = __("Unexpected error");
            $order->registerCancellation($error)->save();
            $this->restoreQuote();
            $this->messageManager->addExceptionMessage(new \Exception("ex"), $error);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/cart');
        }
        return $resultRedirect;
    }

    private function restoreQuote()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = self::$_dataHelper->_checkoutSession->getLastRealOrder();
        if ($order->getId()) {
            try {
                $quote = self::$_dataHelper->quoteRepository->get($order->getQuoteId());
                $quote->setIsActive(1)->setReservedOrderId(null);
                self::$_dataHelper->quoteRepository->save($quote);
                self::$_dataHelper->_checkoutSession->replaceQuote($quote)->unsLastRealOrderId();
                return true;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            }
        }
        return false;
    }
}