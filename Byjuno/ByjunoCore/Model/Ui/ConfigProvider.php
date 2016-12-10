<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Byjuno\ByjunoCore\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    protected $_resolver;
    const CODE_INVOICE = 'byjuno_invoice';
    const CODE_INSTALLMENT = 'byjuno_installment';
    /* @var $_scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface */
    private $_scopeConfig;

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */


    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $methodInstanceInvoice;

    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $methodInstanceInstallment;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        PaymentHelper $paymentHelper,
        \Magento\Framework\Locale\Resolver $resolver
    )
    {
        $this->methodInstanceInvoice = $paymentHelper->getMethodInstance(self::CODE_INVOICE);
        $this->methodInstanceInstallment = $paymentHelper->getMethodInstance(self::CODE_INSTALLMENT);
        $this->_scopeConfig = $scopeConfig;
        $this->_resolver = $resolver;
    }

    private function getByjunoLogoInstallment()
    {
        $logo = 'https://byjuno.ch/Content/logo/de/6639/BJ_Ratenzahlung_BLK.gif';
        if (substr($this->_resolver->getLocale(), 0, 2) == 'en') {
            $logo = 'https://byjuno.ch/Content/logo/en/6639/BJ_Installments_BLK.gif';
        } else if (substr($this->_resolver->getLocale(), 0, 2) == 'fr') {
            $logo = 'https://byjuno.ch/Content/logo/fr/6639/BJ_Paiement_echelonne_BLK.gif';
        } else if (substr($this->_resolver->getLocale(), 0, 2) == 'it') {
            $logo = 'https://byjuno.ch/Content/logo/it/6639/BJ_Pagemento_Rateale_BLK.gif';
        } else {
            $logo = 'https://byjuno.ch/Content/logo/de/6639/BJ_Ratenzahlung_BLK.gif';
        }
        return $logo;
    }

    private function getByjunoLogoInvoice()
    {
        $logo = '';
        if (substr($this->_resolver->getLocale(), 0, 2) == 'en') {
            $logo = 'https://byjuno.ch/Content/logo/en/6639/BJ_Invoice_BLK.gif';
        } else if (substr($this->_resolver->getLocale(), 0, 2) == 'fr') {
            $logo = 'https://byjuno.ch/Content/logo/fr/6639/BJ_Facture_BLK.gif';
        } else if (substr($this->_resolver->getLocale(), 0, 2) == 'it') {
            $logo = 'https://byjuno.ch/Content/logo/it/6639/BJ_Fattura_BLK.gif';
        } else {
            $logo = 'https://byjuno.ch/Content/logo/de/6639/BJ_Rechnung_BLK.gif';
        }
        return $logo;
    }

    public function getConfig()
    {
        $methodsAvailableInvoice = Array();

        if ($this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_single_invoice/active")) {
            $methodsAvailableInvoice[] = Array(
                "value" => 'invoice_single_enable',
                "name" => $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_single_invoice/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_single_invoice/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        if ($this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_partial/active")) {
            $methodsAvailableInvoice[] = Array(
                "value" => 'invoice_partial_enable',
                "name" => $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_partial/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_partial/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }
        $defaultInvoicePlan = 'invoice_single_enable';
        if (count($methodsAvailableInvoice) > 0) {
            $defaultInvoicePlan = $methodsAvailableInvoice[0]["value"];
        }

        $methodsAvailableInstallment = Array();

        if ($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_3installment/active")) {
            $methodsAvailableInstallment[] = Array(
                "value" => 'installment_3installment_enable',
                "name" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_3installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_3installment/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        if ($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_10installment/active")) {
            $methodsAvailableInstallment[] = Array(
                "value" => 'installment_10installment_enable',
                "name" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_10installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_10installment/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        if ($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_12installment/active")) {
            $methodsAvailableInstallment[] = Array(
                "value" => 'installment_12installment_enable',
                "name" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_12installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_12installment/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        if ($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_24installment/active")) {
            $methodsAvailableInstallment[] = Array(
                "value" => 'installment_24installment_enable',
                "name" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_24installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_24installment/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        if ($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_4x12installment/active")) {
            $methodsAvailableInstallment[] = Array(
                "value" => 'installment_4x12installment_enable',
                "name" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_4x12installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_4x12installment/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        $defaultInstallmentPlan = 'installment_3installment_enable';
        if (count($methodsAvailableInstallment) > 0) {
            $defaultInstallmentPlan = $methodsAvailableInstallment[0]["value"];
        }

        $invoiceDelivery[] = Array(
            "value" => "email",
            "text" => __($this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_localization/byjuno_invoice_email_text",
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) . ": "
        );

        $invoiceDelivery[] = Array(
            "value" => "postal",
            "text" => __($this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_localization/byjuno_invoice_postal_text",
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) . ": "
        );

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

        $genders[] = Array(
            "value" => "Herr",
            "text" => "Herr"
        );

        $genders[] = Array(
            "value" => "Frau",
            "text" => "Frau",
        );

        return [
            'payment' => [
                self::CODE_INVOICE => [
                    'redirectUrl' => $this->methodInstanceInvoice->getConfigData('order_place_redirect_url'),
                    'methods' => $methodsAvailableInvoice,
                    'delivery' => $invoiceDelivery,
                    'default_payment' => $defaultInvoicePlan,
                    'default_delivery' => 'email',
                    'logo' => $this->getByjunoLogoInvoice(),
                    'default_customgender' => $genders[0]["value"],
                    'custom_genders' => $genders,
                    'enable_fields' => true
                ],
                self::CODE_INSTALLMENT => [
                    'redirectUrl' => $this->methodInstanceInvoice->getConfigData('order_place_redirect_url'),
                    'methods' => $methodsAvailableInstallment,
                    'delivery' => $invoiceDelivery,
                    'default_payment' => $defaultInstallmentPlan,
                    'default_delivery' => 'email',
                    'logo' => $this->getByjunoLogoInstallment(),
                    'default_customgender' => $genders[0]["value"],
                    'custom_genders' => $genders,
                    'enable_fields' => false
                ]
            ]
        ];
    }
}
