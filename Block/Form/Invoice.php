<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Byjuno\ByjunoCore\Block\Form;

/**
 * @api
 * @since 100.0.2
 */
class Invoice extends \Magento\Payment\Block\Form
{
    /**
     * @var string
     */
    protected $_template = 'Byjuno_ByjunoCore::form/invoice-child.phtml';

    /**
     * Payment config model
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;
    protected $_adminSession;


    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_paymentConfig = $paymentConfig;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $authSession = $objectManager->get('\Magento\Backend\Model\Session\Quote');
        $this->_adminSession = $authSession;
    }

    public function getGenders()
    {
        $gender_prefix = trim($this->_scopeConfig->getValue("byjunocheckoutsettings/byjuno_setup/gender_prefix", \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $gendersArray = explode(";", $gender_prefix);
        $genders = Array();
        foreach($gendersArray as $g) {
            if ($g != '') {
                $genders[] = Array(
                    "value" => trim($g),
                    "text" => trim($g)
                );
            }
        }
        return $genders;
    }

    public function getGendersEnable()
    {
        $gender_enable = false;
        if ($this->_scopeConfig->getValue("byjunocheckoutsettings/byjuno_setup/gender_enable",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 1) {
            $gender_enable = true;
        }
        return $gender_enable;
    }

    public function getBirthdayEnable()
    {
        $birthday_enable = false;
        if ($this->_scopeConfig->getValue("byjunocheckoutsettings/byjuno_setup/birthday_enable",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 1) {
            $birthday_enable = true;
        }
        return $birthday_enable;
    }

    public function getPaymentPlans()
    {
        $isCompany = false;
        if (!empty($this->_adminSession->getQuote()->getBillingAddress()->getCompany()) &&
            $this->_scopeConfig->getValue("byjunocheckoutsettings/byjuno_setup/businesstobusiness", \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '1'
        )
        {
            $isCompany = true;
        }

        $methodsAvailableInvoice = Array();

        $byjuno_single_invoice_allow = $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_single_invoice/byjuno_single_invoice_allow", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_single_invoice/active", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            && ($byjuno_single_invoice_allow == '0' || ($byjuno_single_invoice_allow == '1' && !$isCompany) || ($byjuno_single_invoice_allow == '2' && $isCompany))
        ) {
            $methodsAvailableInvoice[] = Array(
                "value" => 'invoice_single_enable',
                "name" => $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_single_invoice/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_single_invoice/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        $byjuno_invoice_partial_allow = $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_partial/byjuno_invoice_partial_allow", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_partial/active", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            && ($byjuno_invoice_partial_allow == '0' || ($byjuno_invoice_partial_allow == '1' && !$isCompany) || ($byjuno_invoice_partial_allow == '2' && $isCompany))) {
            $methodsAvailableInvoice[] = Array(
                "value" => 'invoice_partial_enable',
                "name" => $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_partial/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_partial/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        return $methodsAvailableInvoice;
    }

    public function getDeliveryMethods()
    {
        $invoiceDelivery = Array();
        $invoiceDelivery[] = Array(
            "value" => "email",
            "text" => __($this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_localization/byjuno_invoice_email_text",
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
        );

        $invoiceDelivery[] = Array(
            "value" => "postal",
            "text" => __($this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_localization/byjuno_invoice_postal_text",
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
        );

        return $invoiceDelivery;
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }
}
