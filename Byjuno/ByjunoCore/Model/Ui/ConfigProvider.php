<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Byjuno\ByjunoCore\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Byjuno\ByjunoCore\Gateway\Http\Client\ClientMock;

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

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Locale\Resolver $resolver
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_resolver = $resolver;
    }

    private function getByjunoLogo()
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

        $invoiceDelivery[] = Array(
            "value" => "email",
            "text" => __("Rechnungsversand via E-Mail (ohne Gebühr) an") . ": "
        );

        $invoiceDelivery[] = Array(
            "value" => "postal",
            "text" => __("Rechnungsversand in Papierform via Post (gegen Gebühr von CHF 3.50) an") . ": "
        );

        return [
            'payment' => [
                self::CODE_INVOICE => [
                    'redirectUrl' => 'byjunocore/checkout/startpayment',
                    'methods' => $methodsAvailableInvoice,
                    'delivery' => $invoiceDelivery,
                    'default_payment' => 'invoice_single_enable',
                    'default_delivery' => 'email',
                    'logo' => $this->getByjunoLogo()
                ],
                self::CODE_INSTALLMENT => [
                    'redirectUrl' => 'byjunocore/checkout/startpayment'
                ]
            ]
        ];
    }
}
