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

    protected $_savedUser = Array(
        "FirstName" => "",
        "LastName" => "",
        "FirstLine" => "",
        "CountryCode" => "",
        "PostCode" => "",
        "Town" => "",
        "CompanyName1",
        "DateOfBirth",
        "Email",
        "Fax",
        "TelephonePrivate",
        "TelephoneOffice",
        "Gender",
        "DELIVERY_FIRSTNAME",
        "DELIVERY_LASTNAME",
        "DELIVERY_FIRSTLINE",
        "DELIVERY_HOUSENUMBER",
        "DELIVERY_COUNTRYCODE",
        "DELIVERY_POSTCODE",
        "DELIVERY_TOWN",
        "DELIVERY_COMPANYNAME"
    );


    public function isTheSame(\Byjuno\ByjunoCore\Helper\Api\ByjunoRequest $request) {

        if ($request->getFirstName() != $this->_savedUser["FirstName"]
            || $request->getLastName() != $this->_savedUser["LastName"]
            || $request->getFirstLine() != $this->_savedUser["FirstLine"]
            || $request->getCountryCode() != $this->_savedUser["CountryCode"]
            || $request->getPostCode() != $this->_savedUser["PostCode"]
            || $request->getTown() != $this->_savedUser["Town"]
            || $request->getCompanyName1() != $this->_savedUser["CompanyName1"]
            || $request->getDateOfBirth() != $this->_savedUser["DateOfBirth"]
            || $request->getEmail() != $this->_savedUser["Email"]
            || $request->getFax() != $this->_savedUser["Fax"]
            || $request->getTelephonePrivate() != $this->_savedUser["TelephonePrivate"]
            || $request->getTelephoneOffice() != $this->_savedUser["TelephoneOffice"]
            || $request->getGender() != $this->_savedUser["Gender"]
            || $request->getExtraInfoByKey("DELIVERY_FIRSTNAME") != $this->_savedUser["DELIVERY_FIRSTNAME"]
            || $request->getExtraInfoByKey("DELIVERY_LASTNAME") != $this->_savedUser["DELIVERY_LASTNAME"]
            || $request->getExtraInfoByKey("DELIVERY_FIRSTLINE") != $this->_savedUser["DELIVERY_FIRSTLINE"]
            || $request->getExtraInfoByKey("DELIVERY_HOUSENUMBER") != $this->_savedUser["DELIVERY_HOUSENUMBER"]
            || $request->getExtraInfoByKey("DELIVERY_COUNTRYCODE") != $this->_savedUser["DELIVERY_COUNTRYCODE"]
            || $request->getExtraInfoByKey("DELIVERY_POSTCODE") != $this->_savedUser["DELIVERY_POSTCODE"]
            || $request->getExtraInfoByKey("DELIVERY_TOWN") != $this->_savedUser["DELIVERY_TOWN"]
            || $request->getExtraInfoByKey("DELIVERY_COMPANYNAME") != $this->_savedUser["DELIVERY_COMPANYNAME"]
        ) {
            return false;
        }
        return true;
    }

    /* @var $quote \Magento\Quote\Model\Quote */
    public function CDPRequest($quote) {
        if ($quote == null) {
            return null;
        }
        if ($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/cdpbeforeshow', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '1'
            && $quote != null
            && $quote->getBillingAddress() != null) {
            $theSame = $this->_dataHelper->_checkoutSession->getIsTheSame();
            if (!empty($theSame) && is_array($theSame)) {
                $this->_savedUser = $theSame;
            }
            $CDPStatus = $this->_dataHelper->_checkoutSession->getCDPStatus();
            try {
                $request = $this->_dataHelper->CreateMagentoShopRequestCreditCheck($quote);
                if (!empty($CDPStatus) && intval($CDPStatus) != 2 && $this->isTheSame($request)) {
                    return false;
                }
                if (!$this->isTheSame($request) || empty($CDPStatus)) {
                    $ByjunoRequestName = "Credit check request";
                    if ($request->getCompanyName1() != '' && $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/businesstobusiness',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '1') {
                        $ByjunoRequestName = "Credit check request for Company";
                        $xml = $request->createRequestCompany();
                    } else {
                        $xml = $request->createRequest();
                    }
                    $byjunoCommunicator = new \Byjuno\ByjunoCore\Helper\Api\ByjunoCommunicator();
                    $mode = $this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/currentmode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    if ($mode == 'production') {
                        $byjunoCommunicator->setServer('live');
                    } else {
                        $byjunoCommunicator->setServer('test');
                    }
                    $response = $byjunoCommunicator->sendRequest($xml, (int)$this->_dataHelper->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/timeout',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
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

                    $this->_savedUser = Array(
                        "FirstName" => $request->getFirstName(),
                        "LastName" => $request->getLastName(),
                        "FirstLine" => $request->getFirstLine(),
                        "CountryCode" => $request->getCountryCode(),
                        "PostCode" => $request->getPostCode(),
                        "Town" => $request->getTown(),
                        "CompanyName1" => $request->getCompanyName1(),
                        "DateOfBirth" => $request->getDateOfBirth(),
                        "Email" => $request->getEmail(),
                        "Fax" => $request->getFax(),
                        "TelephonePrivate" => $request->getTelephonePrivate(),
                        "TelephoneOffice" => $request->getTelephoneOffice(),
                        "Gender" => $request->getGender(),
                        "DELIVERY_FIRSTNAME" => $request->getExtraInfoByKey("DELIVERY_FIRSTNAME"),
                        "DELIVERY_LASTNAME" => $request->getExtraInfoByKey("DELIVERY_LASTNAME"),
                        "DELIVERY_FIRSTLINE" => $request->getExtraInfoByKey("DELIVERY_FIRSTLINE"),
                        "DELIVERY_HOUSENUMBER" => $request->getExtraInfoByKey("DELIVERY_HOUSENUMBER"),
                        "DELIVERY_COUNTRYCODE" => $request->getExtraInfoByKey("DELIVERY_COUNTRYCODE"),
                        "DELIVERY_POSTCODE" => $request->getExtraInfoByKey("DELIVERY_POSTCODE"),
                        "DELIVERY_TOWN" => $request->getExtraInfoByKey("DELIVERY_TOWN"),
                        "DELIVERY_COMPANYNAME" => $request->getExtraInfoByKey("DELIVERY_COMPANYNAME")
                    );
                    $this->_dataHelper->_checkoutSession->setIsTheSame($this->_savedUser);
                    $this->_dataHelper->_checkoutSession->setCDPStatus($status);
                    if ($status != 2) {
                        return false;
                    }
                }
            } catch (\Exception $e) {
            }
        }
        return null;
    }

    /* @var $payment \Magento\Quote\Model\Quote\Payment */
    public function validateCustomFields(\Magento\Payment\Model\InfoInterface $payment)
    {
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

            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $payment->getAdditionalInformation('customer_dob')))
            {
                throw new LocalizedException(
                    __("Birthday is invalid")
                );
            } else {
                $e = explode("-", $payment->getAdditionalInformation('customer_dob'));
                if (!isset($e[0]) || intval($e[0]) < 1800 || intval($e[0]) > date("Y")) {
                    throw new LocalizedException(
                        __("Provided date is not valid")
                    );
                }
            }
        }
        if ($this->_scopeConfig->getValue("byjunocheckoutsettings/byjuno_setup/country_phone_validation",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 1 && $payment->getQuote() != null) {

            $pattern = "/^[0-9]{4}$/";
            if (strtolower($payment->getQuote()->getBillingAddress()->getCountryId()) == 'ch' && !preg_match($pattern, $payment->getQuote()->getBillingAddress()->getPostcode())) {
                throw new LocalizedException(
                    __($this->_scopeConfig->getValue('byjunocheckoutsettings/localization/postal_code_wrong', \Magento\Store\Model\ScopeInterface::SCOPE_STORE).
                        ": " . $payment->getQuote()->getBillingAddress()->getPostcode())
                );
            }
            if (!preg_match("/^[0-9\+\(\)\s]+$/", $payment->getQuote()->getBillingAddress()->getTelephone())) {
                throw new LocalizedException(
                    __($this->_scopeConfig->getValue('byjunocheckoutsettings/localization/telephone_code_wrong', \Magento\Store\Model\ScopeInterface::SCOPE_STORE).
                        ": " . $payment->getQuote()->getBillingAddress()->getTelephone())
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
		$this->_dataHelper->_objectManager->configure($this->_dataHelper->_configLoader->load('adminhtml'));
        /* @var $invoice \Magento\Sales\Model\Order\Invoice */
        $order = $payment->getOrder();
        $invoice = \Byjuno\ByjunoCore\Observer\InvoiceObserver::$Invoice;
        if ($invoice == null) {
            throw new LocalizedException(
                __("Internal invoice (InvoiceObserver) error")
            );
        }
        if ($this->_scopeConfig->getValue("byjunocheckoutsettings/byjuno_setup/byjunos4transacton", \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '0') {
            return $this;
        }
        if ($payment->getAdditionalInformation("s3_ok") == null || $payment->getAdditionalInformation("s3_ok") == 'false') {
            throw new LocalizedException (
                __($this->_scopeConfig->getValue('byjunocheckoutsettings/localization/byjuno_s4_fail', \Magento\Store\Model\ScopeInterface::SCOPE_STORE). " (error code: S3_NOT_CREATED)")
            );
        }
        $webshopProfileId = $payment->getAdditionalInformation("webshop_profile_id");
        $incrementValue =  $this->_eavConfig->getEntityType($invoice->getEntityType())->fetchNewIncrementId($invoice->getStore()->getId());
        if ($invoice->getIncrementId() == null) {
            $invoice->setIncrementId($incrementValue);
        }
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

        $payment->setTransactionId($incrementValue.'-invoice');
        $transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE, null, true);
        $transaction->setIsClosed(true);
        $payment->save();

        $transaction->save();
        return $this;
    }


}