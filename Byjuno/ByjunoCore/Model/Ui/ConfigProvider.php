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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }

    public function getConfig()
    {
        $methodsAvailableInvoice = Array();
        if ($this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_partial/active")) {
            $methodsAvailableInvoice[] = Array(
                "value" => '1',
                "name" => $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_partial/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_partial/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }
        if ($this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_single_invoice/active")) {
            $methodsAvailableInvoice[] = Array(
                "value" => '2',
                "name" => $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_single_invoice/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                "link" => $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_single_invoice/link", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        $invoiceDelivery[] = Array(
            "value" => "email",
            "text" => __("Rechnungsversand via E-Mail (ohne Gebühr) an").": "
        );

        $invoiceDelivery[] = Array(
            "value" => "postal",
            "text" => __("Rechnungsversand in Papierform via Post (gegen Gebühr von CHF 3.50) an").": "
        );

        return [
            'payment' => [
                self::CODE_INVOICE => [
                    'redirectUrl' => 'byjunocore/checkout/startpayment',
                    'methods' => $methodsAvailableInvoice,
                    'delivery' => $invoiceDelivery,
                    'default_payment' => '1',
                    'default_delivery' => 'email'
                ],
                self::CODE_INSTALLMENT => [
                    'redirectUrl' => 'byjunocore/checkout/startpayment'
                ]
            ]
        ];
    }
}
