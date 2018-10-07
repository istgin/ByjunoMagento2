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
    protected $_dataHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $_logger;
    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        DataHelper $helper,
        ByjunoLogger $logger
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_dataHelper = $helper;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    public function executeS3($order, \Magento\Sales\Model\Order\Payment $payment, $transaction, $accept)
    {
        $request = $this->_dataHelper->CreateMagentoShopRequestPaid($order, $payment, $payment->getAdditionalInformation('customer_gender'), $payment->getAdditionalInformation('customer_dob'), $transaction, $accept);
        $ByjunoRequestName = "Order paid";
        $requestType = 'b2c';
        if ($request->getCompanyName1() != '' && $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/businesstobusiness', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '1') {
            $ByjunoRequestName = "Order paid for Company";
            $requestType = 'b2b';
            $xml = $request->createRequestCompany();
        } else {
            $xml = $request->createRequest();
        }
        $mode = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/currentmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($mode == 'live') {
            $this->_dataHelper->_communicator->setServer('live');
        } else {
            $this->_dataHelper->_communicator->setServer('test');
        }
        $response = $this->_dataHelper->_communicator->sendRequest($xml, (int)$this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/timeout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $status = 0;
        if ($response) {
            $this->_dataHelper->_response->setRawResponse($response);
            $this->_dataHelper->_response->processResponse();
            $status = (int)$this->_dataHelper->_response->getCustomerRequestStatus();
            if (intval($status) > 15) {
                $status = 0;
            }
            $this->_dataHelper->saveLog($request, $xml, $response, $status, $ByjunoRequestName);
        } else {
            $this->_dataHelper->saveLog($request, $xml, "empty response", "0", $ByjunoRequestName);
        }
        return array($status, $requestType);
    }

    public function executeS2($order, \Magento\Sales\Model\Order\Payment $payment)
    {
        $request = $this->_dataHelper->CreateMagentoShopRequestOrder($order, $payment, $payment->getAdditionalInformation('customer_gender'), $payment->getAdditionalInformation('customer_dob'));

        $ByjunoRequestName = "Order request";
        $requestType = 'b2c';
        if ($request->getCompanyName1() != '' && $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/businesstobusiness', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '1') {
            $ByjunoRequestName = "Order request for Company";
            $requestType = 'b2b';
            $xml = $request->createRequestCompany();
        } else {
            $xml = $request->createRequest();
        }
        $mode = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/currentmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($mode == 'live') {
            $this->_dataHelper->_communicator->setServer('live');
        } else {
            $this->_dataHelper->_communicator->setServer('test');
        }
        $response = $this->_dataHelper->_communicator->sendRequest($xml, (int)$this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/timeout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $status = 0;
        if ($response) {
            $this->_dataHelper->_response->setRawResponse($response);
            $this->_dataHelper->_response->processResponse();
            $status = (int)$this->_dataHelper->_response->getCustomerRequestStatus();
            $this->_checkoutSession->setByjunoTransaction($this->_dataHelper->_response->getTransactionNumber());
            $this->_dataHelper->saveLog($request, $xml, $response, $status, $ByjunoRequestName);
            if (intval($status) > 15) {
                $status = 0;
            }
            $trxId = $this->_dataHelper->_response->getResponseId();
        } else {
            $this->_dataHelper->saveLog($request, $xml, "empty response", "0", $ByjunoRequestName);
            $trxId = "empty";
        }
        $payment->setTransactionId($trxId);
        $payment->setParentTransactionId($payment->getTransactionId());

        $transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH, null, true);
        $accept = "";
        if ($this->_dataHelper->byjunoIsStatusOk($status, "byjunocheckoutsettings/byjuno_setup/merchant_risk")) {
            $accept = "CLIENT";
        }
        if ($this->_dataHelper->byjunoIsStatusOk($status, "byjunocheckoutsettings/byjuno_setup/byjuno_risk")) {
            $accept = "IJ";
        }
        if ($accept != "") {
            $transaction->setIsClosed(false);
        } else {
            $transaction->setIsClosed(true);
        }
        $transaction->save();
        $payment->save();

        $this->_checkoutSession->setIntrumStatus($status);
        $this->_checkoutSession->setIntrumRequestType($requestType);
        $this->_checkoutSession->setIntrumOrder($order->getId());
        return array($status, $requestType, $this->_dataHelper->_response);
    }

    public function execute()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        /* @var $payment \Magento\Sales\Model\Order\Payment */
        $payment = $order->getPayment();

        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            /* @var $responseS2 \Byjuno\ByjunoCore\Helper\Api\ByjunoResponse */
            list($statusS2, $requestTypeS2, $responseS2) = $this->executeS2($order, $payment);
            $accept = "";
            if ($this->_dataHelper->byjunoIsStatusOk($statusS2, "byjunocheckoutsettings/byjuno_setup/merchant_risk")) {
                $accept = "CLIENT";
            }
            if ($this->_dataHelper->byjunoIsStatusOk($statusS2, "byjunocheckoutsettings/byjuno_setup/byjuno_risk")) {
                $accept = "IJ";
            }
            if ($accept != "") {
                list($statusS3, $requestTypeS3) = $this->executeS3($order, $payment, $responseS2->getTransactionNumber(), $accept);
                if ($this->_dataHelper->byjunoIsStatusOk($statusS3, "byjunocheckoutsettings/byjuno_setup/accepted_s3")) {
                    $payment->setAdditionalInformation("s3_ok", 'true')->save();
                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $order->setStatus("byjuno_confirmed");
                    if (!empty($statusToPayment) && !empty($ByjunoResponseSession)) {
                        $this->_dataHelper->saveStatusToOrder($order, $responseS2);
                    }
                    $order->save();
                    try {
                        $mode = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/currentmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        if ($mode == 'live') {
                            $email = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjuno_prod_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        } else {
                            $email = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjuno_test_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        }
                        $this->_dataHelper->_originalOrderSender->send($order);
                        $this->_dataHelper->_byjunoOrderSender->sendOrder($order, $email);
                    } catch (\Exception $e) {
                        $this->_dataHelper->_loggerPsr->critical($e);
                    }
                    $this->_dataHelper->_checkoutSession->setTmxSession('');
                    $this->_dataHelper->_checkoutSession->setCDPStatus('');
                    $resultRedirect->setPath('checkout/onepage/success');
                } else {
                    $error = $this->_dataHelper->getByjunoErrorMessage($statusS3, $requestTypeS3). "(S3)";
                    $order->registerCancellation($error)->save();
                    $this->restoreQuote();
                    $this->messageManager->addExceptionMessage(new \Exception($statusS3), $error);
                    $resultRedirect->setPath('checkout/cart');
                }
            } else {
                $error = $this->_dataHelper->getByjunoErrorMessage($statusS2, $requestTypeS2);
                $order->registerCancellation($error)->save();
                $this->restoreQuote();
                $this->messageManager->addExceptionMessage(new \Exception($statusS2), $error);
                $resultRedirect->setPath('checkout/cart');
            }

        } catch (\Exception $e) {
            $order = $this->_checkoutSession->getLastRealOrder();
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
        $order = $this->_checkoutSession->getLastRealOrder();
        if ($order->getId()) {
            try {
                $quote = $this->_dataHelper->quoteRepository->get($order->getQuoteId());
                $quote->setIsActive(1)->setReservedOrderId(null);
                $this->_dataHelper->quoteRepository->save($quote);
                $this->_checkoutSession->replaceQuote($quote)->unsLastRealOrderId();
                return true;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            }
        }
        return false;
    }
}