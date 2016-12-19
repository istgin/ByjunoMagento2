<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Byjuno\ByjunoCore\Model;
use Byjuno\ByjunoCore\Observer\InvoiceObserver;


class Invoicebyjunooverride extends \Magento\Sales\Model\Order\Invoice
{

    public function capture()
    {
        InvoiceObserver::$Invoice = $this;
        return parent::capture();
    }

}