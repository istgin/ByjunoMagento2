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

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE_INVOICE => [
                    'redirectUrl' => 'byjunocore/checkout/startpayment'
                ],
                self::CODE_INSTALLMENT => [
                    'redirectUrl' => 'byjunocore/checkout/startpayment'
                ]
            ]
        ];
    }
}
