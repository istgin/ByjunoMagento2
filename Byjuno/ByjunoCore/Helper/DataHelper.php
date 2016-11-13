<?php
namespace Byjuno\ByjunoCore\Helper;

class DataHelper extends \Magento\Framework\App\Helper\AbstractHelper {


    protected $_storeManager;
    protected $_iteratorFactory;
    protected $_blockMenu;
    protected $_url;
    /* @var $_scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface */
    public $_scopeConfig;
    protected $_checkoutSession;
    protected $_countryHelper;
    protected $_resolver;
    public $_byjunoOrderSender;
    public $_byjunoLogger;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    public $_loggerPsr;

    /**
     * @var \Byjuno\ByjunoCore\Helper\Api\ByjunoCommunicator
     */
    public $_communicator;
    /**
     * @var \Byjuno\ByjunoCore\Helper\Api\ByjunoResponse
     */
    public $_response;
    /**
     * @var \Byjuno\ByjunoCore\Helper\Api\ByjunoS4Response
     */
    public $_responseS4;


    function saveLog(\Byjuno\ByjunoCore\Helper\Api\ByjunoRequest $request, $xml_request, $xml_response, $status, $type) {
        $data = array( 'firstname'  => $request->getFirstName(),
            'lastname'   => $request->getLastName(),
            'postcode'   => $request->getPostCode(),
            'town'       => $request->getTown(),
            'country'    => $request->getCountryCode(),
            'street1'    => $request->getFirstLine(),
            'request_id' => $request->getRequestId(),
            'status'     => ($status != 0) ? $status : 'Error',
            'error'      => '',
            'request'    => $xml_request,
            'response'   => $xml_response,
            'type'       => $type,
            'ip'         => $_SERVER['REMOTE_ADDR']);

        $this->_byjunoLogger->log($data);
    }

    function saveS4Log(\Magento\Sales\Model\Order $order, \Byjuno\ByjunoCore\Helper\Api\ByjunoS4Request $request, $xml_request, $xml_response, $status, $type) {

        $data = array( 'firstname'  => $order->getCustomerFirstname(),
            'lastname'   => $order->getCustomerLastname(),
            'postcode'   => '-',
            'town'       => '-',
            'country'    => '-',
            'street1'    => '-',
            'request_id' => $request->getRequestId(),
            'status'     => $status,
            'error'      => '',
            'request'    => $xml_request,
            'response'   => $xml_response,
            'type'       => $type,
            'ip'         => $_SERVER['REMOTE_ADDR']);

        $this->_byjunoLogger->log($data);
    }

/*
    function saveS4Log(Mage_Sales_Model_Order $order, Byjuno_Cdp_Helper_Api_Classes_ByjunoS4Request $request, $xml_request, $xml_response, $status, $type) {

        $data = array( 'firstname'  => $order->getCustomerFirstname(),
            'lastname'   => $order->getCustomerLastname(),
            'postcode'   => '-',
            'town'       => '-',
            'country'    => '-',
            'street1'    => '-',
            'request_id' => $request->getRequestId(),
            'status'     => $status,
            'error'      => '',
            'request'    => $xml_request,
            'response'   => $xml_response,
            'type'       => $type,
            'ip'         => $_SERVER['REMOTE_ADDR']);

        $byjuno_model = Mage::getModel('byjuno/byjuno');
        $byjuno_model->setData($data);
        $byjuno_model->save();
    }
*/
    public function valueToStatus($val) {
        $status[0] = 'Fail to connect (status Error)';
        $status[1] = 'There are serious negative indicators (status 1)';
        $status[2] = 'All payment methods allowed (status 2)';
        $status[3] = 'Manual post-processing (currently not yet in use) (status 3)';
        $status[4] = 'Postal address is incorrect (status 4)';
        $status[5] = 'Enquiry exceeds the credit limit (the credit limit is specified in the cooperation agreement) (status 5)';
        $status[6] = 'Customer specifications not met (optional) (status 6)';
        $status[7] = 'Enquiry exceeds the net credit limit (enquiry amount plus open items exceeds credit limit) (status 7)';
        $status[8] = 'Person queried is not of creditworthy age (status 8)';
        $status[9] = 'Delivery address does not match invoice address (for payment guarantee only) (status 9)';
        $status[10] = 'Household cannot be identified at this address (status 10)';
        $status[11] = 'Country is not supported (status 11)';
        $status[12] = 'Party queried is not a natural person (status 12)';
        $status[13] = 'System is in maintenance mode (status 13)';
        $status[14] = 'Address with high fraud risk (status 14)';
        $status[15] = 'Allowance is too low (status 15)';
        if (isset($status[$val])) {
            return $status[$val];
        }
        return $status[0];
    }

    public function getClientIp() {
        $ipaddress = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if(!empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if(!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if(!empty($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if(!empty($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    public function mapMethod($method) {
        if ($method == 'cdp_installment') {
            return "INSTALLMENT";
        } else {
            return "INVOICE";
        }
    }

    private function mapRepayment($type)
    {
        if ($type == 'installment_3_enable') {
            return "10";
        } else if ($type == 'installment_10_enable') {
            return "5";
        } else if ($type == 'installment_12_enable') {
            return "8";
        } else if ($type == 'installment_24_enable') {
            return "9";
        } else if ($type == 'installment_4x12_enable') {
            return "1";
        } else if ($type == 'installment_4x10_enable') {
            return "2";
        } else if ($type == 'invoice_single_enable') {
            return "3";
        } else if ($type == 'invoice_partial_enable') {
            return "3";
        }
        return "0";
    }

    function getByjunoErrorMessage($status, $paymentType = 'b2c') {
        $message = '';
        if ($status == 10 && $paymentType == 'b2b') {
            if (substr($this->_resolver->getLocale(), 0, 2) == 'en') {
                $message = 'Company is not found in Register of Commerce';
            } else if (substr($this->_resolver->getLocale(), 0, 2) == 'fr') {
                $message = 'La société n‘est pas inscrit au registre du commerce';
            } else if (substr($this->_resolver->getLocale(), 0, 2) == 'it') {
                $message = 'L‘azienda non é registrata nel registro di commercio';
            } else {
                $message = 'Die Firma ist nicht im Handelsregister eingetragen';
            }
        } else {
            $message = $this->_scopeConfig->getValue('byjunocheckoutsettings/localization/byjuno_fail_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return $message;
    }

    public function saveStatusToOrder(\Magento\Sales\Model\Order $order, \Byjuno\ByjunoCore\Helper\Api\ByjunoResponse $byjunoS2Response) {
        $order->addStatusHistoryComment('<b>Byjuno status: '.$this->valueToStatus($byjunoS2Response->getCustomerRequestStatus()).'</b>
        <br/>Credit rating: '.$this->_response->getCustomerCreditRating().'
        <br/>Credit rating level: '.$this->_response->getCustomerCreditRatingLevel().'<br/>Status code: '. $byjunoS2Response->getCustomerRequestStatus().'</b>');
        $order->setByjunoStatus($byjunoS2Response->getCustomerRequestStatus());
        $order->setByjunoCreditRating($byjunoS2Response->getCustomerCreditRating());
        $order->setByjunoCreditLevel($byjunoS2Response->getCustomerCreditRatingLevel());
        $order->save();
    }

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory,
        \Magento\Backend\Block\Menu $blockMenu,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Model\Config\Source\Country $countryHelper,
        \Magento\Framework\Locale\Resolver $resolver,
        \Byjuno\ByjunoCore\Helper\Api\ByjunoCommunicator $communicator,
        \Byjuno\ByjunoCore\Helper\Api\ByjunoResponse $response,
        \Byjuno\ByjunoCore\Helper\Api\ByjunoS4Response $responseS4,
        \Byjuno\ByjunoCore\Helper\ByjunoOrderSender $byjunoOrderSender,
        \Psr\Log\LoggerInterface $loggerPsr,
        \Byjuno\ByjunoCore\Helper\Api\ByjunoLogger $byjunoLogger
    )
    {

        parent::__construct($context);
        $this->_byjunoLogger = $byjunoLogger;
        $this->_byjunoOrderSender = $byjunoOrderSender;
        $this->_response = $response;
        $this->_responseS4 = $responseS4;
        $this->_communicator = $communicator;
        $this->_resolver = $resolver;
        $this->_countryHelper = $countryHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_iteratorFactory = $iteratorFactory;
        $this->_blockMenu = $blockMenu;
        $this->_url = $url;
    }

    function CreateMagentoShopRequestOrder(\Magento\Sales\Model\Order $order,
                                           \Magento\Sales\Model\Order\Payment $paymentmethod,
                                           $gender_custom, $dob_custom) {

        $request = new \Byjuno\ByjunoCore\Helper\Api\ByjunoRequest();
        $request->setClientId($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/clientid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $request->setUserID($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/userid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $request->setPassword($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $request->setVersion("1.00");
        try {
            $request->setRequestEmail($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/mail', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        } catch (\Exception $e) {

        }
        $b = $order->getCustomerDob();
        if (!empty($b)) {
            try {
                $dobObject = new \DateTime($b);
                if ($dobObject != null) {
                    $request->setDateOfBirth($dobObject->format('Y-m-d'));
                }
            } catch (\Exception $e) {

            }
        }

        if (!empty($dob_custom)) {
            try {
                $dobObject = new \DateTime($dob_custom);
                if ($dobObject != null) {
                    $request->setDateOfBirth($dobObject->format('Y-m-d'));
                }
            } catch (\Exception $e) {

            }
        }


        $g = $order->getCustomerGender();
        if (!empty($g)) {
            if ($g == '1') {
                $request->setGender('1');
            } else if ($g == '2') {
                $request->setGender('2');
            } else {
                $request->setGender('0');
            }
        } else {
            if (strtolower($order->getCustomerPrefix()) == 'herr') {
                $request->setGender('1');
            } else if (strtolower($order->getCustomerPrefix()) == 'frau') {
                $request->setGender('2');
            } else {
                $request->setGender('0');
            }
        }

        if (!empty($gender_custom)) {
            if ($gender_custom == '1') {
                $request->setGender('1');
            } else if ($gender_custom == '2') {
                $request->setGender('2');
            }
        }
        $billingStreet = $order->getBillingAddress()->getStreet();
        $billingStreet = implode("", $billingStreet);
        $requestId = uniqid((String)$order->getBillingAddress()->getEntityId()."_");
        $request->setRequestId($requestId);
        $reference = $order->getCustomerId();
        if (empty($reference)) {
            $request->setCustomerReference("guest_".$order->getId());
        } else {
            $request->setCustomerReference($order->getCustomerId());
        }
        $request->setFirstName((String)$order->getBillingAddress()->getFirstname());
        $request->setLastName((String)$order->getBillingAddress()->getLastname());
        //quote.billingAddress().street[0] + ", " + quote.billingAddress().city + ", " + quote.billingAddress().postcode
        $request->setFirstLine(trim((String)$billingStreet));
        $request->setCountryCode(strtoupper($order->getBillingAddress()->getCountryId()));
        $request->setPostCode((String)$order->getBillingAddress()->getPostcode());
        $request->setTown((String)$order->getBillingAddress()->getCity());
        $request->setFax((String)trim($order->getBillingAddress()->getFax(), '-'));
        $request->setLanguage((String)substr($this->_resolver->getLocale(), 0, 2));

        if ($order->getBillingAddress()->getCompany()) {
            $request->setCompanyName1($order->getBillingAddress()->getCompany());
        }

        $request->setTelephonePrivate((String)trim($order->getBillingAddress()->getTelephone(), '-'));
        $request->setEmail((String)$order->getBillingAddress()->getEmail());

        $extraInfo["Name"] = 'ORDERCLOSED';
        $extraInfo["Value"] = 'NO';
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERAMOUNT';
        $extraInfo["Value"] = number_format($order->getGrandTotal(), 2, '.', '');
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERCURRENCY';
        $extraInfo["Value"] = $order->getBaseCurrencyCode();
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'IP';
        $extraInfo["Value"] = $this->getClientIp();
        $request->setExtraInfo($extraInfo);

        $sedId = $this->_checkoutSession->getData("byjuno_session_id");
        if ($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/tmxenabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '1' && !empty($sedId)) {
            $extraInfo["Name"] = 'DEVICE_FINGERPRINT_ID';
            $extraInfo["Value"] = $this->_checkoutSession->getData("byjuno_session_id");
            $request->setExtraInfo($extraInfo);
        }

        if ($paymentmethod->getAdditionalInformation('invoice_send') == 'postal') {
            $extraInfo["Name"] = 'PAPER_INVOICE';
            $extraInfo["Value"] = 'YES';
            $request->setExtraInfo($extraInfo);
        }

        if ($order->canShip()) {
            $shippingStreet = $order->getShippingAddress()->getStreet();
            $shippingStreet = implode("", $shippingStreet);

            $extraInfo["Name"] = 'DELIVERY_FIRSTLINE';
            $extraInfo["Value"] = trim((String)$shippingStreet);
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_HOUSENUMBER';
            $extraInfo["Value"] = '';
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_COUNTRYCODE';
            $extraInfo["Value"] = strtoupper($order->getShippingAddress()->getCountryId());
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_POSTCODE';
            $extraInfo["Value"] = $order->getShippingAddress()->getPostcode();
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_TOWN';
            $extraInfo["Value"] = $order->getShippingAddress()->getCity();
            $request->setExtraInfo($extraInfo);

            if ($order->getShippingAddress()->getCompany() != '' && $this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/businesstobusiness', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'enable') {

                $extraInfo["Name"] = 'DELIVERY_COMPANYNAME';
                $extraInfo["Value"] = $order->getShippingAddress()->getCompany();
                $request->setExtraInfo($extraInfo);

                $extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
                $extraInfo["Value"] = '';
                $request->setExtraInfo($extraInfo);

                $extraInfo["Name"] = 'DELIVERY_LASTNAME';
                $extraInfo["Value"] = $order->getShippingAddress()->getCompany();
                $request->setExtraInfo($extraInfo);

            } else {

                $extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
                $extraInfo["Value"] = $order->getShippingAddress()->getFirstname();
                $request->setExtraInfo($extraInfo);

                $extraInfo["Name"] = 'DELIVERY_LASTNAME';
                $extraInfo["Value"] = $order->getShippingAddress()->getLastname();
                $request->setExtraInfo($extraInfo);
            }
        }

        $extraInfo["Name"] = 'PP_TRANSACTION_NUMBER';
        $extraInfo["Value"] = $requestId;
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERID';
        $extraInfo["Value"] = $order->getIncrementId();
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'PAYMENTMETHOD';
        $extraInfo["Value"] = $this->mapMethod($paymentmethod->getMethod());
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'REPAYMENTTYPE';
        $extraInfo["Value"] = $this->mapRepayment($paymentmethod->getAdditionalInformation('payment_plan'));
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'RISKOWNER';
        $extraInfo["Value"] = 'IJ';
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'CONNECTIVTY_MODULE';
        $extraInfo["Value"] = 'Byjuno Magento 2.1 module 1.0.0';
        $request->setExtraInfo($extraInfo);
        return $request;
    }

    function CreateMagentoShopRequestPaid(\Magento\Sales\Model\Order $order,
                                          \Magento\Sales\Model\Order\Payment $paymentmethod,
                                          $gender_custom, $dob_custom, $transaction) {

        $request = new \Byjuno\ByjunoCore\Helper\Api\ByjunoRequest();
        $request->setClientId($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/clientid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $request->setUserID($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/userid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $request->setPassword($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $request->setVersion("1.00");
        try {
            $request->setRequestEmail($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/mail', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        } catch (\Exception $e) {

        }
        $b = $order->getCustomerDob();
        if (!empty($b)) {
            try {
                $dobObject = new \DateTime($b);
                if ($dobObject != null) {
                    $request->setDateOfBirth($dobObject->format('Y-m-d'));
                }
            } catch (\Exception $e) {

            }
        }

        if (!empty($dob_custom)) {
            try {
                $dobObject = new \DateTime($dob_custom);
                if ($dobObject != null) {
                    $request->setDateOfBirth($dobObject->format('Y-m-d'));
                }
            } catch (\Exception $e) {

            }
        }


        $g = $order->getCustomerGender();
        if (!empty($g)) {
            if ($g == '1') {
                $request->setGender('1');
            } else if ($g == '2') {
                $request->setGender('2');
            } else {
                $request->setGender('0');
            }
        } else {
            if (strtolower($order->getCustomerPrefix()) == 'herr') {
                $request->setGender('1');
            } else if (strtolower($order->getCustomerPrefix()) == 'frau') {
                $request->setGender('2');
            } else {
                $request->setGender('0');
            }
        }

        if (!empty($gender_custom)) {
            if ($gender_custom == '1') {
                $request->setGender('1');
            } else if ($gender_custom == '2') {
                $request->setGender('2');
            }
        }
        $billingStreet = $order->getBillingAddress()->getStreet();
        $billingStreet = implode("", $billingStreet);
        $requestId = uniqid((String)$order->getBillingAddress()->getEntityId()."_");
        $request->setRequestId($requestId);
        $reference = $order->getCustomerId();
        if (empty($reference)) {
            $request->setCustomerReference("guest_".$order->getId());
        } else {
            $request->setCustomerReference($order->getCustomerId());
        }
        $request->setFirstName((String)$order->getBillingAddress()->getFirstname());
        $request->setLastName((String)$order->getBillingAddress()->getLastname());
        //quote.billingAddress().street[0] + ", " + quote.billingAddress().city + ", " + quote.billingAddress().postcode
        $request->setFirstLine(trim((String)$billingStreet));
        $request->setCountryCode(strtoupper($order->getBillingAddress()->getCountryId()));
        $request->setPostCode((String)$order->getBillingAddress()->getPostcode());
        $request->setTown((String)$order->getBillingAddress()->getCity());
        $request->setFax((String)trim($order->getBillingAddress()->getFax(), '-'));
        $request->setLanguage((String)substr($this->_resolver->getLocale(), 0, 2));

        if ($order->getBillingAddress()->getCompany()) {
            $request->setCompanyName1($order->getBillingAddress()->getCompany());
        }

        $request->setTelephonePrivate((String)trim($order->getBillingAddress()->getTelephone(), '-'));
        $request->setEmail((String)$order->getBillingAddress()->getEmail());

        $extraInfo["Name"] = 'TRANSACTIONNUMBER';
        $extraInfo["Value"] = $transaction;
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERCLOSED';
        $extraInfo["Value"] = 'YES';
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERAMOUNT';
        $extraInfo["Value"] = number_format($order->getGrandTotal(), 2, '.', '');
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERCURRENCY';
        $extraInfo["Value"] = $order->getBaseCurrencyCode();
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'IP';
        $extraInfo["Value"] = $this->getClientIp();
        $request->setExtraInfo($extraInfo);

        if ($paymentmethod->getAdditionalInformation('invoice_send') == 'postal') {
            $extraInfo["Name"] = 'PAPER_INVOICE';
            $extraInfo["Value"] = 'YES';
            $request->setExtraInfo($extraInfo);
        }

        if ($order->canShip()) {

            $shippingStreet = $order->getShippingAddress()->getStreet();
            $shippingStreet = implode("", $shippingStreet);

            $extraInfo["Name"] = 'DELIVERY_FIRSTLINE';
            $extraInfo["Value"] = trim($shippingStreet);
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_HOUSENUMBER';
            $extraInfo["Value"] = '';
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_COUNTRYCODE';
            $extraInfo["Value"] = strtoupper($order->getShippingAddress()->getCountryId());
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_POSTCODE';
            $extraInfo["Value"] = $order->getShippingAddress()->getPostcode();
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_TOWN';
            $extraInfo["Value"] = $order->getShippingAddress()->getCity();
            $request->setExtraInfo($extraInfo);

            if ($order->getShippingAddress()->getCompany() != '' && $this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/businesstobusiness', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'enable') {

                $extraInfo["Name"] = 'DELIVERY_COMPANYNAME';
                $extraInfo["Value"] = $order->getShippingAddress()->getCompany();
                $request->setExtraInfo($extraInfo);

                $extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
                $extraInfo["Value"] = '';
                $request->setExtraInfo($extraInfo);

                $extraInfo["Name"] = 'DELIVERY_LASTNAME';
                $extraInfo["Value"] = $order->getShippingAddress()->getCompany();
                $request->setExtraInfo($extraInfo);

            } else {

                $extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
                $extraInfo["Value"] = $order->getShippingAddress()->getFirstname();
                $request->setExtraInfo($extraInfo);

                $extraInfo["Name"] = 'DELIVERY_LASTNAME';
                $extraInfo["Value"] = $order->getShippingAddress()->getLastname();
                $request->setExtraInfo($extraInfo);

            }
        }

        $extraInfo["Name"] = 'ORDERID';
        $extraInfo["Value"] = $order->getIncrementId();
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'PAYMENTMETHOD';
        $extraInfo["Value"] = $this->mapMethod($paymentmethod->getMethod());
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'REPAYMENTTYPE';
        $extraInfo["Value"] = $this->mapRepayment($paymentmethod->getAdditionalInformation('payment_plan'));
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'RISKOWNER';
        $extraInfo["Value"] = 'IJ';
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'CONNECTIVTY_MODULE';
        $extraInfo["Value"] = 'Byjuno Magento 2.1 module 1.0.0';
        $request->setExtraInfo($extraInfo);

        return $request;

    }

    function CreateMagentoShopRequestS4Paid(\Magento\Sales\Model\Order $order, \Magento\Sales\Model\Order\Invoice $invoice, $webshopProfile) {


        $request = new \Byjuno\ByjunoCore\Helper\Api\ByjunoS4Request();
        $request->setClientId($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/clientid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $request->setUserID($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/userid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $request->setPassword($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $request->setVersion("1.00");
        try {
            $request->setRequestEmail($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/mail', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        } catch (\Exception $e) {

        }

        $request->setRequestId(uniqid((String)$order->getIncrementId()."_"));

        $request->setOrderId($order->getIncrementId());
        $reference = $order->getCustomerId();
        if (empty($reference)) {
            $request->setClientRef("guest_".$order->getId());
        } else {
            $request->setClientRef($order->getCustomerId());
        }
        $orderDateString = \Zend_Locale_Format::getDate(
            $order->getCreatedAt(),
            array(
                'date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
            )
        );
        $request->setTransactionDate($orderDateString["year"]."-".$orderDateString["month"].'-'.$orderDateString["day"]);
        $request->setTransactionAmount(number_format($invoice->getGrandTotal(), 2, '.', ''));
        $request->setTransactionCurrency($order->getBaseCurrencyCode());
        $request->setAdditional1("INVOICE");
        $request->setAdditional2($invoice->getIncrementId());
        $request->setOpenBalance(number_format($invoice->getGrandTotal(), 2, '.', ''));

        return $request;

    }

/*


    function saveLog(Mage_Sales_Model_Quote $quote, Byjuno_Cdp_Helper_Api_Classes_ByjunoRequest $request, $xml_request, $xml_response, $status, $type) {
        $data = array( 'firstname'  => $request->getFirstName(),
            'lastname'   => $request->getLastName(),
            'postcode'   => $request->getPostCode(),
            'town'       => $request->getTown(),
            'country'    => $request->getCountryCode(),
            'street1'    => $request->getFirstLine(),
            'request_id' => $request->getRequestId(),
            'status'     => ($status != 0) ? $status : 'Error',
            'error'      => '',
            'request'    => $xml_request,
            'response'   => $xml_response,
            'type'       => $type,
            'ip'         => $_SERVER['REMOTE_ADDR']);

        $byjuno_model = Mage::getModel('byjuno/byjuno');
        $byjuno_model->setData($data);
        $byjuno_model->save();
    }

    function saveS4Log(Mage_Sales_Model_Order $order, Byjuno_Cdp_Helper_Api_Classes_ByjunoS4Request $request, $xml_request, $xml_response, $status, $type) {

        $data = array( 'firstname'  => $order->getCustomerFirstname(),
            'lastname'   => $order->getCustomerLastname(),
            'postcode'   => '-',
            'town'       => '-',
            'country'    => '-',
            'street1'    => '-',
            'request_id' => $request->getRequestId(),
            'status'     => $status,
            'error'      => '',
            'request'    => $xml_request,
            'response'   => $xml_response,
            'type'       => $type,
            'ip'         => $_SERVER['REMOTE_ADDR']);

        $byjuno_model = Mage::getModel('byjuno/byjuno');
        $byjuno_model->setData($data);
        $byjuno_model->save();
    }

    public function getClientIp() {
        $ipaddress = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if(!empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if(!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if(!empty($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if(!empty($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }



    public function valueToStatus($val) {
        $status[0] = 'Fail to connect (status Error)';
        $status[1] = 'There are serious negative indicators (status 1)';
        $status[2] = 'All payment methods allowed (status 2)';
        $status[3] = 'Manual post-processing (currently not yet in use) (status 3)';
        $status[4] = 'Postal address is incorrect (status 4)';
        $status[5] = 'Enquiry exceeds the credit limit (the credit limit is specified in the cooperation agreement) (status 5)';
        $status[6] = 'Customer specifications not met (optional) (status 6)';
        $status[7] = 'Enquiry exceeds the net credit limit (enquiry amount plus open items exceeds credit limit) (status 7)';
        $status[8] = 'Person queried is not of creditworthy age (status 8)';
        $status[9] = 'Delivery address does not match invoice address (for payment guarantee only) (status 9)';
        $status[10] = 'Household cannot be identified at this address (status 10)';
        $status[11] = 'Country is not supported (status 11)';
        $status[12] = 'Party queried is not a natural person (status 12)';
        $status[13] = 'System is in maintenance mode (status 13)';
        $status[14] = 'Address with high fraud risk (status 14)';
        $status[15] = 'Allowance is too low (status 15)';
        if (isset($status[$val])) {
            return $status[$val];
        }
        return $status[0];
    }




    function CreateMagentoShopRequestPaid(Mage_Sales_Model_Order $order, $paymentmethod, $repayment, $transaction, $invoiceDelivery, $gender_custom, $dob_custom) {

        $request = new Byjuno_Cdp_Helper_Api_Classes_ByjunoRequest();
        $request->setClientId(Mage::getStoreConfig('payment/cdp/clientid',Mage::app()->getStore()));
        $request->setUserID(Mage::getStoreConfig('payment/cdp/userid',Mage::app()->getStore()));
        $request->setPassword(Mage::getStoreConfig('payment/cdp/password',Mage::app()->getStore()));
        $request->setVersion("1.00");
        try {
            $request->setRequestEmail(Mage::getStoreConfig('payment/cdp/mail',Mage::app()->getStore()));
        } catch (Exception $e) {

        }
        $b = $order->getCustomerDob();
        if (!empty($b)) {
            try {
                $dobObject = new DateTime($b);
                if ($dobObject != null) {
                    $request->setDateOfBirth($dobObject->format('Y-m-d'));
                }
            } catch (Exception $e) {

            }
        }

        $g = $order->getCustomerGender();
        if (!empty($g)) {
            if ($g == '1') {
                $request->setGender('1');
            } else if ($g == '2') {
                $request->setGender('2');
            } else {			
                $request->setGender('0');
			}
        } else {
            if (strtolower($order->getCustomerPrefix()) == 'herr') {
                $request->setGender('1');
            } else if (strtolower($order->getCustomerPrefix()) == 'frau') {
                $request->setGender('2');
            } else {			
                $request->setGender('0');
			}
        }

        if (!empty($dob_custom)) {
            try {
                $dobObject = new DateTime($dob_custom);
                if ($dobObject != null) {
                    $request->setDateOfBirth($dobObject->format('Y-m-d'));
                }
            } catch (Exception $e) {

            }
        }

        if (!empty($gender_custom)) {
            if ($gender_custom == '1') {
                $request->setGender('1');
            } else if ($gender_custom == '2') {
                $request->setGender('2');
            }
        }

        $request->setRequestId(uniqid((String)$order->getBillingAddress()->getId()."_"));
        $reference = $order->getCustomerId();
        if (empty($reference)) {
            $request->setCustomerReference("guest_".$order->getId());
        } else {
            $request->setCustomerReference($order->getCustomerId());
        }
        $request->setFirstName((String)$order->getBillingAddress()->getFirstname());
        $request->setLastName((String)$order->getBillingAddress()->getLastname());
        $request->setFirstLine(trim((String)$order->getBillingAddress()->getStreetFull()));
        $request->setCountryCode(strtoupper((String)$order->getBillingAddress()->getCountry()));
        $request->setPostCode((String)$order->getBillingAddress()->getPostcode());
        $request->setTown((String)$order->getBillingAddress()->getCity());
        $request->setFax((String)trim($order->getBillingAddress()->getFax(), '-'));
        $request->setLanguage((String)substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2));

        if ($order->getBillingAddress()->getCompany()) {
            $request->setCompanyName1($order->getBillingAddress()->getCompany());
        }

        $request->setTelephonePrivate((String)trim($order->getBillingAddress()->getTelephone(), '-'));
        $request->setEmail((String)$order->getBillingAddress()->getEmail());

        $extraInfo["Name"] = 'TRANSACTIONNUMBER';
        $extraInfo["Value"] = $transaction;
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERCLOSED';
        $extraInfo["Value"] = 'YES';
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERAMOUNT';
        $extraInfo["Value"] = number_format($order->getGrandTotal(), 2, '.', '');
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERCURRENCY';
        $extraInfo["Value"] = $order->getBaseCurrencyCode();
        $request->setExtraInfo($extraInfo);

        if ($invoiceDelivery == 'postal') {
            $extraInfo["Name"] = 'PAPER_INVOICE';
            $extraInfo["Value"] = 'YES';
            $request->setExtraInfo($extraInfo);
        }

        $extraInfo["Name"] = 'IP';
        $extraInfo["Value"] = $this->getClientIp();
        $request->setExtraInfo($extraInfo);

        $sesId = Mage::getSingleton('checkout/session')->getData("byjuno_session_id");
        if (Mage::getStoreConfig('payment/cdp/tmxenabled', Mage::app()->getStore()) == '1' && !empty($sesId)) {
            $extraInfo["Name"] = 'DEVICE_FINGERPRINT_ID';
            $extraInfo["Value"] = Mage::getSingleton('checkout/session')->getData("byjuno_session_id");
            $request->setExtraInfo($extraInfo);
        }

        if ($order->canShip()) {

            $extraInfo["Name"] = 'DELIVERY_FIRSTLINE';
            $extraInfo["Value"] = trim($order->getShippingAddress()->getStreetFull());
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_HOUSENUMBER';
            $extraInfo["Value"] = '';
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_COUNTRYCODE';
            $extraInfo["Value"] = strtoupper($order->getShippingAddress()->getCountry());
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_POSTCODE';
            $extraInfo["Value"] = $order->getShippingAddress()->getPostcode();
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_TOWN';
            $extraInfo["Value"] = $order->getShippingAddress()->getCity();
            $request->setExtraInfo($extraInfo);

            if ($order->getShippingAddress()->getCompany() != '' && Mage::getStoreConfig('payment/cdp/businesstobusiness', Mage::app()->getStore()) == 'enable') {
                $extraInfo["Name"] = 'DELIVERY_COMPANYNAME';
                $extraInfo["Value"] = $order->getShippingAddress()->getCompany();
                $request->setExtraInfo($extraInfo);
			
				$extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
				$extraInfo["Value"] = '';
				$request->setExtraInfo($extraInfo);

				$extraInfo["Name"] = 'DELIVERY_LASTNAME';
				$extraInfo["Value"] = $order->getShippingAddress()->getCompany();
				$request->setExtraInfo($extraInfo);
				
            } else {
			
				$extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
				$extraInfo["Value"] = $order->getShippingAddress()->getFirstname();
				$request->setExtraInfo($extraInfo);

				$extraInfo["Name"] = 'DELIVERY_LASTNAME';
				$extraInfo["Value"] = $order->getShippingAddress()->getLastname();
				$request->setExtraInfo($extraInfo);
			
			}
        }

        $extraInfo["Name"] = 'ORDERID';
        $extraInfo["Value"] = $order->getIncrementId();
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'PAYMENTMETHOD';
        $extraInfo["Value"] = $this->mapMethod($paymentmethod);
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'REPAYMENTTYPE';
        $extraInfo["Value"] = $this->mapRepayment($repayment);
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'RISKOWNER';
        $extraInfo["Value"] = 'IJ';
        $request->setExtraInfo($extraInfo);

		$extraInfo["Name"] = 'CONNECTIVTY_MODULE';
		$extraInfo["Value"] = 'Byjuno magento payment module 1.3.1';
		$request->setExtraInfo($extraInfo);	

        return $request;

    }



    function CreateMagentoShopRequestS5Paid(Mage_Sales_Model_Order $order, $amount, $transactionType, $webshopProfile, $invoiceId = '') {

        $request = new Byjuno_Cdp_Helper_Api_Classes_ByjunoS5Request();
        $request->setClientId(Mage::getStoreConfig('payment/cdp/clientid',$webshopProfile));
        $request->setUserID(Mage::getStoreConfig('payment/cdp/userid',$webshopProfile));
        $request->setPassword(Mage::getStoreConfig('payment/cdp/password',$webshopProfile));
        $request->setVersion("1.3");
        try {
            $request->setRequestEmail(Mage::getStoreConfig('payment/cdp/mail',$webshopProfile));
        } catch (Exception $e) {

        }
        $request->setRequestId(uniqid((String)$order->getCustomerId()."_"));

        $request->setOrderId($order->getIncrementId());
        $reference = $order->getCustomerId();
        if (empty($reference)) {
            $request->setClientRef("guest_".$order->getId());
        } else {
            $request->setClientRef($order->getCustomerId());
        }
        $request->setTransactionDate($order->getCreatedAtStoreDate()->toString(Varien_Date::DATE_INTERNAL_FORMAT));
        $request->setTransactionAmount(number_format($amount, 2, '.', ''));
        $request->setTransactionCurrency($order->getBaseCurrencyCode());
        $request->setTransactionType($transactionType);
        $request->setAdditional2($invoiceId);
        if ($transactionType == "EXPIRED") {
            $request->setOpenBalance("0");
        }

        return $request;

    }

    function CreateMagentoShopRequestCreditCheck(Mage_Sales_Model_Quote $quote)
    {
        $request = new Byjuno_Cdp_Helper_Api_Classes_ByjunoRequest();
        $request->setClientId(Mage::getStoreConfig('payment/cdp/clientid',Mage::app()->getStore()));
        $request->setUserID(Mage::getStoreConfig('payment/cdp/userid',Mage::app()->getStore()));
        $request->setPassword(Mage::getStoreConfig('payment/cdp/password',Mage::app()->getStore()));
        $request->setVersion("1.00");
        try {
            $request->setRequestEmail(Mage::getStoreConfig('payment/cdp/mail',Mage::app()->getStore()));
        } catch (Exception $e) {

        }
        $b = $quote->getCustomerDob();
        if (!empty($b)) {
            $request->setDateOfBirth(Mage::getModel('core/date')->date('Y-m-d', strtotime($b)));
        }

        $g = $quote->getCustomerGender();
        if (!empty($g)) {
            if ($g == '1') {
                $request->setGender('1');
            } else if ($g == '2') {
                $request->setGender('2');
            }
        }
        if (!$request->getGender()) {
            $p = $quote->getCustomerPrefix();
            if (strtolower($p) == 'herr') {
                $request->setGender('1');
            } else if (strtolower($p) == 'frau') {
                $request->setGender('2');
            }
        }

        $request->setRequestId(uniqid((String)$quote->getBillingAddress()->getId()."_"));
        $reference = $quote->getCustomer()->getId();
        if (empty($reference)) {
            $request->setCustomerReference("guest_".$quote->getBillingAddress()->getId());
        } else {
            $request->setCustomerReference($quote->getCustomer()->getId());
        }

        $request->setFirstName((String)$quote->getBillingAddress()->getFirstname());

        $request->setLastName((String)$quote->getBillingAddress()->getLastname());

        $request->setFirstLine(trim((String)$quote->getBillingAddress()->getStreetFull()));

        $request->setCountryCode(strtoupper((String)$quote->getBillingAddress()->getCountry()));
        $request->setPostCode((String)$quote->getBillingAddress()->getPostcode());
        $request->setTown((String)$quote->getBillingAddress()->getCity());
        $request->setFax((String)trim($quote->getBillingAddress()->getFax(), '-'));
        $request->setLanguage((String)substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2));
        if ($quote->getBillingAddress()->getCompany()) {
            $request->setCompanyName1($quote->getBillingAddress()->getCompany());
        }
        $request->setTelephonePrivate((String)trim($quote->getBillingAddress()->getTelephone(), '-'));

        $request->setEmail((String)$quote->getBillingAddress()->getEmail());

        $extraInfo["Name"] = 'ORDERCLOSED';
        $extraInfo["Value"] = 'NO';
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERAMOUNT';
        $extraInfo["Value"] = number_format($quote->getGrandTotal(), 2, '.', '');
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERCURRENCY';
        $extraInfo["Value"] = $quote->getBaseCurrencyCode();
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'IP';
        $extraInfo["Value"] = $this->getClientIp();
        $request->setExtraInfo($extraInfo);
		
        $sesId = Mage::getSingleton('checkout/session')->getData("byjuno_session_id");
        if (Mage::getStoreConfig('payment/cdp/tmxenabled', Mage::app()->getStore()) == 'enable' && !empty($sesId)) {
            $extraInfo["Name"] = 'DEVICE_FINGERPRINT_ID';
            $extraInfo["Value"] = Mage::getSingleton('checkout/session')->getData("intrum_session_id");
            $request->setExtraInfo($extraInfo);
        }

        if (!$quote->isVirtual()) {
            $extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
            $extraInfo["Value"] = $quote->getShippingAddress()->getFirstname();
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_LASTNAME';
            $extraInfo["Value"] = $quote->getShippingAddress()->getLastname();
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_FIRSTLINE';
            $extraInfo["Value"] = trim($quote->getShippingAddress()->getStreetFull());
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_HOUSENUMBER';
            $extraInfo["Value"] = '';
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_COUNTRYCODE';
            $extraInfo["Value"] = strtoupper($quote->getShippingAddress()->getCountry());
            $request->setExtraInfo($extraInfo);
            $extraInfo["Name"] = 'DELIVERY_POSTCODE';
            $extraInfo["Value"] = $quote->getShippingAddress()->getPostcode();
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_TOWN';
            $extraInfo["Value"] = $quote->getShippingAddress()->getCity();
            $request->setExtraInfo($extraInfo);

            if ($quote->getShippingAddress()->getCompany() != '' && Mage::getStoreConfig('payment/cdp/businesstobusiness', Mage::app()->getStore()) == 'enable') {
                $extraInfo["Name"] = 'DELIVERY_COMPANYNAME';
                $extraInfo["Value"] = $quote->getShippingAddress()->getCompany();
                $request->setExtraInfo($extraInfo);
            }
        }

        $extraInfo["Name"] = 'CONNECTIVTY_MODULE';
        $extraInfo["Value"] = 'Byjuno Magento module 1.3.1';
        $request->setExtraInfo($extraInfo);
        return $request;
    }

    function CreateMagentoShopRequestOrder(Mage_Sales_Model_Order $order, $paymentmethod, $repayment, $invoiceDelivery, $gender_custom, $dob_custom) {

        $request = new Byjuno_Cdp_Helper_Api_Classes_ByjunoRequest();
        $request->setClientId(Mage::getStoreConfig('payment/cdp/clientid',Mage::app()->getStore()));
        $request->setUserID(Mage::getStoreConfig('payment/cdp/userid',Mage::app()->getStore()));
        $request->setPassword(Mage::getStoreConfig('payment/cdp/password',Mage::app()->getStore()));
        $request->setVersion("1.00");
        try {
            $request->setRequestEmail(Mage::getStoreConfig('payment/cdp/mail',Mage::app()->getStore()));
        } catch (Exception $e) {

        }
        $b = $order->getCustomerDob();
        if (!empty($b)) {
            try {
                $dobObject = new DateTime($b);
                if ($dobObject != null) {
                    $request->setDateOfBirth($dobObject->format('Y-m-d'));
                }
            } catch (Exception $e) {

            }
        }

        if (!empty($dob_custom)) {
            try {
                $dobObject = new DateTime($dob_custom);
                if ($dobObject != null) {
                    $request->setDateOfBirth($dobObject->format('Y-m-d'));
                }
            } catch (Exception $e) {

            }
        }


        $g = $order->getCustomerGender();
        if (!empty($g)) {
            if ($g == '1') {
                $request->setGender('1');
            } else if ($g == '2') {
                $request->setGender('2');
            } else {			
                $request->setGender('0');
			}
        } else {
            if (strtolower($order->getCustomerPrefix()) == 'herr') {
                $request->setGender('1');
            } else if (strtolower($order->getCustomerPrefix()) == 'frau') {
                $request->setGender('2');
            } else {			
                $request->setGender('0');
			}
        }

        if (!empty($gender_custom)) {
            if ($gender_custom == '1') {
                $request->setGender('1');
            } else if ($gender_custom == '2') {
                $request->setGender('2');
            }
        }

        $requestId = uniqid((String)$order->getBillingAddress()->getId()."_");
        $request->setRequestId($requestId);
        $reference = $order->getCustomerId();
        if (empty($reference)) {
            $request->setCustomerReference("guest_".$order->getId());
        } else {
            $request->setCustomerReference($order->getCustomerId());
        }
        $request->setFirstName((String)$order->getBillingAddress()->getFirstname());
        $request->setLastName((String)$order->getBillingAddress()->getLastname());
        $request->setFirstLine(trim((String)$order->getBillingAddress()->getStreetFull()));
        $request->setCountryCode(strtoupper((String)$order->getBillingAddress()->getCountry()));
        $request->setPostCode((String)$order->getBillingAddress()->getPostcode());
        $request->setTown((String)$order->getBillingAddress()->getCity());
        $request->setFax((String)trim($order->getBillingAddress()->getFax(), '-'));
        $request->setLanguage((String)substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2));

        if ($order->getBillingAddress()->getCompany()) {
            $request->setCompanyName1($order->getBillingAddress()->getCompany());
        }

        $request->setTelephonePrivate((String)trim($order->getBillingAddress()->getTelephone(), '-'));
        $request->setEmail((String)$order->getBillingAddress()->getEmail());

        $extraInfo["Name"] = 'ORDERCLOSED';
        $extraInfo["Value"] = 'NO';
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERAMOUNT';
        $extraInfo["Value"] = number_format($order->getGrandTotal(), 2, '.', '');
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERCURRENCY';
        $extraInfo["Value"] = $order->getBaseCurrencyCode();
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'IP';
        $extraInfo["Value"] = $this->getClientIp();
        $request->setExtraInfo($extraInfo);

        $sesId = Mage::getSingleton('checkout/session')->getData("byjuno_session_id");
        if (Mage::getStoreConfig('payment/cdp/tmxenabled', Mage::app()->getStore()) == '1' && !empty($sesId)) {
            $extraInfo["Name"] = 'DEVICE_FINGERPRINT_ID';
            $extraInfo["Value"] = Mage::getSingleton('checkout/session')->getData("byjuno_session_id");
            $request->setExtraInfo($extraInfo);
        }

        if ($invoiceDelivery == 'postal') {
            $extraInfo["Name"] = 'PAPER_INVOICE';
            $extraInfo["Value"] = 'YES';
            $request->setExtraInfo($extraInfo);
        }

        if ($order->canShip()) {            

            $extraInfo["Name"] = 'DELIVERY_FIRSTLINE';
            $extraInfo["Value"] = trim($order->getShippingAddress()->getStreetFull());
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_HOUSENUMBER';
            $extraInfo["Value"] = '';
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_COUNTRYCODE';
            $extraInfo["Value"] = strtoupper($order->getShippingAddress()->getCountry());
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_POSTCODE';
            $extraInfo["Value"] = $order->getShippingAddress()->getPostcode();
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_TOWN';
            $extraInfo["Value"] = $order->getShippingAddress()->getCity();
            $request->setExtraInfo($extraInfo);

            if ($order->getShippingAddress()->getCompany() != '' && Mage::getStoreConfig('payment/cdp/businesstobusiness', Mage::app()->getStore()) == 'enable') {
                $extraInfo["Name"] = 'DELIVERY_COMPANYNAME';
                $extraInfo["Value"] = $order->getShippingAddress()->getCompany();
                $request->setExtraInfo($extraInfo);
			
				$extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
				$extraInfo["Value"] = '';
				$request->setExtraInfo($extraInfo);

				$extraInfo["Name"] = 'DELIVERY_LASTNAME';
				$extraInfo["Value"] = $order->getShippingAddress()->getCompany();
				$request->setExtraInfo($extraInfo);
				
            } else {
			
				$extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
				$extraInfo["Value"] = $order->getShippingAddress()->getFirstname();
				$request->setExtraInfo($extraInfo);

				$extraInfo["Name"] = 'DELIVERY_LASTNAME';
				$extraInfo["Value"] = $order->getShippingAddress()->getLastname();
				$request->setExtraInfo($extraInfo);
			}
        }

        $extraInfo["Name"] = 'PP_TRANSACTION_NUMBER';
        $extraInfo["Value"] = $requestId;
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERID';
        $extraInfo["Value"] = $order->getIncrementId();
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'PAYMENTMETHOD';
        $extraInfo["Value"] = $this->mapMethod($paymentmethod);
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'REPAYMENTTYPE';
        $extraInfo["Value"] = $this->mapRepayment($repayment);
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'RISKOWNER';
        $extraInfo["Value"] = 'IJ';
        $request->setExtraInfo($extraInfo);

		$extraInfo["Name"] = 'CONNECTIVTY_MODULE';
		$extraInfo["Value"] = 'Byjuno Magento module 1.3.1';
		$request->setExtraInfo($extraInfo);
        return $request;
    }


    public function queueNewOrderEmail(Mage_Sales_Model_Order $order, $forceMode = false)
    {
        $storeId = Mage::app()->getStore()->getId();
        // Get the destination email addresses to send copies to
        $mode = Mage::getStoreConfig('payment/cdp/currentmode', Mage::app()->getStore());
        if ($mode == 'production') {
            $copyTo = Mage::getStoreConfig('payment/cdp/byjuno_prod_email', Mage::app()->getStore());
        } else {
            $copyTo = Mage::getStoreConfig('payment/cdp/byjuno_test_email', Mage::app()->getStore());
        }

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
        } else {
            $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE, $storeId);
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($copyTo);
        $mailer->addEmailInfo($emailInfo);
        $mailer->setSender(Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
            'order'        => $order,
            'billing'      => $order->getBillingAddress(),
            'payment_html' => $paymentBlockHtml
        ));

        $emailQueue = Mage::getModel('core/email_queue');
        $emailQueue->setEntityId($order->getId())
            ->setEntityType(Mage_Sales_Model_Order::ENTITY)
            ->setEventType(Mage_Sales_Model_Order::EMAIL_EVENT_NAME_NEW_ORDER)
            ->setIsForceCheck(!$forceMode);

        $mailer->setQueue($emailQueue)->send();
        $order->setEmailSent(true);
        $order->getResource()->saveAttribute($order, 'email_sent');
    }

    public function sendEmailInvoice(Mage_Sales_Model_Order_Invoice $invoice, $comment = '')
    {
        $order = $invoice->getOrder();
        $storeId = $order->getStore()->getId();

        // Get the destination email addresses to send copies to
        $mode = Mage::getStoreConfig('payment/cdp/currentmode', Mage::app()->getStore());
        if ($mode == 'production') {
            $copyTo = Mage::getStoreConfig('payment/cdp/byjuno_prod_email', Mage::app()->getStore());
        } else {
            $copyTo = Mage::getStoreConfig('payment/cdp/byjuno_test_email', Mage::app()->getStore());
        }

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
        } else {
            $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_TEMPLATE, $storeId);
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($copyTo);
        $mailer->addEmailInfo($emailInfo);

        $mailer->setSender(Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order' => $order,
                'invoice' => $invoice,
                'comment' => $comment,
                'billing' => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();
        $invoice->setEmailSent(true);
        $invoice->getResource()->saveAttribute($invoice, 'email_sent');
    }

    public function sendEmailCreditMemo(Mage_Sales_Model_Order_Creditmemo $creditMemo, $comment = '')
    {
        $order = $creditMemo->getOrder();
        $storeId = $order->getStore()->getId();

        // Get the destination email addresses to send copies to
        $mode = Mage::getStoreConfig('payment/cdp/currentmode', Mage::app()->getStore());
        if ($mode == 'production') {
            $copyTo = Mage::getStoreConfig('payment/cdp/byjuno_prod_email', Mage::app()->getStore());
        } else {
            $copyTo = Mage::getStoreConfig('payment/cdp/byjuno_test_email', Mage::app()->getStore());
        }

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
        } else {
            $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_TEMPLATE, $storeId);
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($copyTo);
        $mailer->addEmailInfo($emailInfo);
        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'        => $order,
                'creditmemo'   => $creditMemo,
                'comment'      => $comment,
                'billing'      => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();
        $creditMemo->setEmailSent(true);
        $creditMemo->getResource()->saveAttribute($creditMemo, 'email_sent');
    }
*/
}