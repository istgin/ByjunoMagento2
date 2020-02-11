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
class Installment extends \Magento\Payment\Block\Form
{
    /**
     * @var string
     */
    protected $_template = 'Byjuno_ByjunoCore::form/installment-child.phtml';

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


        $methodsAvailableInstallment = Array();

        $byjuno_installment_3installment_allow = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_3installment/byjuno_installment_3installment_allow", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_3installment/active", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            && ($byjuno_installment_3installment_allow == '0' || ($byjuno_installment_3installment_allow == '1' && !$isCompany) || ($byjuno_installment_3installment_allow == '2' && $isCompany))) {
            $methodsAvailableInstallment[] = Array(
                "value" => 'installment_3installment_enable',
                "name" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_3installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_3installment/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        $byjuno_installment_10installment_allow = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_10installment/byjuno_installment_10installment_allow", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_10installment/active", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            && ($byjuno_installment_10installment_allow == '0' || ($byjuno_installment_10installment_allow == '1' && !$isCompany) || ($byjuno_installment_10installment_allow == '2' && $isCompany))) {
            $methodsAvailableInstallment[] = Array(
                "value" => 'installment_10installment_enable',
                "name" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_10installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_10installment/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        $byjuno_installment_12installment_allow = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_12installment/byjuno_installment_12installment_allow", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_12installment/active", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            && ($byjuno_installment_12installment_allow == '0' || ($byjuno_installment_12installment_allow == '1' && !$isCompany) || ($byjuno_installment_12installment_allow == '2' && $isCompany))) {
            $methodsAvailableInstallment[] = Array(
                "value" => 'installment_12installment_enable',
                "name" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_12installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_12installment/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        $byjuno_installment_24installment_allow = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_24installment/byjuno_installment_24installment_allow", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_24installment/active", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            && ($byjuno_installment_24installment_allow == '0' || ($byjuno_installment_24installment_allow == '1' && !$isCompany) || ($byjuno_installment_24installment_allow == '2' && $isCompany))) {
            $methodsAvailableInstallment[] = Array(
                "value" => 'installment_24installment_enable',
                "name" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_24installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_24installment/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        $byjuno_installment_4x12installment_allow = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_4x12installment/byjuno_installment_4x12installment_allow", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_4x12installment/active", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            && ($byjuno_installment_4x12installment_allow == '0' || ($byjuno_installment_4x12installment_allow == '1' && !$isCompany) || ($byjuno_installment_4x12installment_allow == '2' && $isCompany))) {
            $methodsAvailableInstallment[] = Array(
                "value" => 'installment_4x12installment_enable',
                "name" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_4x12installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_4x12installment/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        return $methodsAvailableInstallment;
    }

    public function getDeliveryMethods()
    {
        $installmentDelivery = Array();
        $installmentDelivery[] = Array(
            "value" => "email",
            "text" => __($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_localization/byjuno_installment_email_text",
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) . ": "
        );

        $installmentDelivery[] = Array(
            "value" => "postal",
            "text" => __($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_localization/byjuno_installment_postal_text",
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) . ": "
        );

        return $installmentDelivery;
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }
}
