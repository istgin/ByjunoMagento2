<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 08.12.2016
 * Time: 19:31
 */

namespace Byjuno\ByjunoCore\Model;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Byjuno\ByjunoCore\Helper\DataHelper;


/**
 * Pay In Store payment method model
 */
class Byjunopayment extends \Magento\Payment\Model\Method\Adapter
{

    /* @var $_scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfig;
    protected $eventManager;
    protected $_eavConfig;
    /* @var $_dataHelper DataHelper */
    protected $_dataHelper;

    /* @var $_scopeConfig \Magento\Checkout\Model\Session */
    protected $_checkoutSession;

    public function void(InfoInterface $payment)
    {
        $this->cancel($payment);
        return $this;
    }

    /* @var $quote \Magento\Quote\Model\Quote */
    public function isAvailable(CartInterface $quote = null)
    {
        if ($quote != null) {
            $total = $quote->getGrandTotal();
            $active = true;
            if ($total < $this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/minamount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ||
                $total > $this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/maxamount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $active = false;
            }
            return parent::isAvailable($quote) && $active;
        }
        return parent::isAvailable($quote);
    }

    /* @var $payment \Magento\Sales\Model\Order\Payment */
    public function cancel(InfoInterface $payment)
    {
        if ($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjunos5transacton', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '0') {
            return $this;
        }

        /* @var $order \Magento\Sales\Model\Order */
        $order = $payment->getOrder();
        $request = $this->_dataHelper->CreateMagentoShopRequestS5Paid($order, $order->getTotalDue(), "EXPIRED");
        $ByjunoRequestName = 'Byjuno S5 Cancel';
        $xml = $request->createRequest();
        $byjunoCommunicator = new \Byjuno\ByjunoCore\Helper\Api\ByjunoCommunicator();
        $mode = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/currentmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($mode == 'production') {
            $byjunoCommunicator->setServer('live');
            $email = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjuno_prod_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            $byjunoCommunicator->setServer('test');
            $email = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjuno_test_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        $response = $byjunoCommunicator->sendS4Request($xml, (int)$this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/timeout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        if ($response) {
            $this->_dataHelper->_responseS4->setRawResponse($response);
            $this->_dataHelper->_responseS4->processResponse();
            $status = $this->_dataHelper->_responseS4->getProcessingInfoClassification();
            $this->_dataHelper->saveS5Log($order, $request, $xml, $response, $status, $ByjunoRequestName);
        } else {
            $status = "ERR";
            $this->_dataHelper->saveS5Log($order, $request, $xml, "empty response", $status, $ByjunoRequestName);
        }
        if ($status == 'ERR') {
            throw new LocalizedException(
                __($this->_scopeConfig->getValue('byjunocheckoutsettings/localization/byjuno_s5_fail', \Magento\Store\Model\ScopeInterface::SCOPE_STORE). " (error code: CDP_FAIL)")
            );
        }

        $payment->setTransactionId($payment->getParentTransactionId().'-void');
        $transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_VOID, null, true);
        $transaction->setIsClosed(true);
        $payment->save();
        $transaction->save();
        return $this;
    }

    public function validateCustomFields(\Magento\Payment\Model\InfoInterface $payment) {

        if ($this->_scopeConfig->getValue("byjunocheckoutsettings/byjuno_setup/gender_birthday_enable",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 1) {
            if ($payment->getAdditionalInformation('customer_gender') == null || $payment->getAdditionalInformation('customer_gender') == '') {
                throw new LocalizedException(
                    __("Gender not selected")
                );
            }
            if ($payment->getAdditionalInformation('customer_dob') == null || $payment->getAdditionalInformation('customer_dob') == '') {
                throw new LocalizedException(
                    __("Birthday not selected")
                );
            }
        }
    }

    /* @var $payment \Magento\Sales\Model\Order\Payment */
    public function refund(InfoInterface $payment, $amount)
    {
        if ($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjunos5transacton', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '0') {
            return $this;
        }
        /* @var $order \Magento\Sales\Model\Order */
        $order = $payment->getOrder();
        /* @var $memo \Magento\Sales\Model\Order\Creditmemo */
        $memo = $payment->getCreditmemo();
        $incoiceId = $memo->getInvoice()->getIncrementId();
        $request = $this->_dataHelper->CreateMagentoShopRequestS5Paid($order, $amount, "REFUND", $incoiceId);
        $ByjunoRequestName = 'Byjuno S5 Refund';
        $xml = $request->createRequest();
        $byjunoCommunicator = new \Byjuno\ByjunoCore\Helper\Api\ByjunoCommunicator();
        $mode = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/currentmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($mode == 'production') {
            $byjunoCommunicator->setServer('live');
            $email = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjuno_prod_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            $byjunoCommunicator->setServer('test');
            $email = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjuno_test_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        $response = $byjunoCommunicator->sendS4Request($xml, (int)$this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/timeout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        if ($response) {
            $this->_dataHelper->_responseS4->setRawResponse($response);
            $this->_dataHelper->_responseS4->processResponse();
            $status = $this->_dataHelper->_responseS4->getProcessingInfoClassification();
            $this->_dataHelper->saveS5Log($order, $request, $xml, $response, $status, $ByjunoRequestName);
        } else {
            $status = "ERR";
            $this->_dataHelper->saveS5Log($order, $request, $xml, "empty response", $status, $ByjunoRequestName);
        }
        if ($status == 'ERR') {
            throw new LocalizedException(
                __($this->_scopeConfig->getValue('byjunocheckoutsettings/localization/byjuno_s5_fail', \Magento\Store\Model\ScopeInterface::SCOPE_STORE). " (error code: CDP_FAIL)")
            );
        } else {
            $this->_dataHelper->_byjunoCreditmemoSender->sendCreditMemo($memo, $email);
        }

        $payment->setTransactionId($payment->getParentTransactionId().'-refund');
        $transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND, null, true);
        $transaction->setIsClosed(true);
        $payment->save();
        $transaction->save();
        return $this;
    }

    /* @var $payment \Magento\Sales\Model\Order\Payment */
    public function capture(InfoInterface $payment, $amount)
    {
        /* @var $invoice \Magento\Sales\Model\Order\Invoice */
        $order = $payment->getOrder();
        $invoice = \Byjuno\ByjunoCore\Observer\InvoiceObserver::$Invoice;
        if ($invoice == null) {
            throw new LocalizedException(
                __("Internal invoice (InvoiceObserver) error")
            );
        }

        if ($this->_scopeConfig->getValue("byjunocheckoutsettings/byjuno_setup/byjunos4transacton", \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '0') {
            //return $this;
        }
        if ($payment->getAdditionalInformation("s3_ok") == null || $payment->getAdditionalInformation("s3_ok") == 'false') {
            throw new LocalizedException (
                __($this->_scopeConfig->getValue('byjunocheckoutsettings/localization/byjuno_s4_fail', \Magento\Store\Model\ScopeInterface::SCOPE_STORE). " (error code: S3_NOT_CREATED)")
            );
        }
        $webshopProfileId = $payment->getAdditionalInformation("webshop_profile_id");
        $incrementValue =  $this->_eavConfig->getEntityType($invoice->getEntityType())->fetchNewIncrementId($invoice->getStore()->getId());
        $request = $this->_dataHelper->CreateMagentoShopRequestS4Paid($order, $invoice, $webshopProfileId);


        $ByjunoRequestName = 'Byjuno S4';
        $xml = $request->createRequest();
        $byjunoCommunicator = new \Byjuno\ByjunoCore\Helper\Api\ByjunoCommunicator();
        $mode = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/currentmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($mode == 'production') {
            $byjunoCommunicator->setServer('live');
            $email = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjuno_prod_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            $byjunoCommunicator->setServer('test');
            $email = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjuno_test_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        $response = $byjunoCommunicator->sendS4Request($xml, (int)$this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/timeout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        if ($response) {
            $this->_dataHelper->_responseS4->setRawResponse($response);
            $this->_dataHelper->_responseS4->processResponse();
            $status = $this->_dataHelper->_responseS4->getProcessingInfoClassification();
            $this->_dataHelper->saveS4Log($order, $request, $xml, $response, $status, $ByjunoRequestName);
        } else {
            $status = "ERR";
            $this->_dataHelper->saveS4Log($order, $request, $xml, "empty response", $status, $ByjunoRequestName);
        }
        if ($status == 'ERR') {
            throw new LocalizedException(
                __($this->_scopeConfig->getValue('byjunocheckoutsettings/localization/byjuno_s4_fail', \Magento\Store\Model\ScopeInterface::SCOPE_STORE). " (error code: CDP_FAIL)")
            );
        } else {
            $this->_dataHelper->_byjunoInvoiceSender->sendInvoice($invoice, $email);
        }

        $invoice->setIncrementId($incrementValue);
        $payment->setTransactionId($incrementValue.'-invoice');
        $transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE, null, true);
        $transaction->setIsClosed(true);
        $payment->save();

        $transaction->save();
        return $this;
    }


}