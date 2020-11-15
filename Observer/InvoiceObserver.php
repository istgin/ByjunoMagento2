<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 13.11.2016
 * Time: 20:18
 */

namespace Byjuno\ByjunoCore\Observer;


use Magento\Framework\Event\ObserverInterface;

class InvoiceObserver implements ObserverInterface {
    public static $Invoice;
    public function execute(\Magento\Framework\Event\Observer $observer ) {
        self::$Invoice  = $observer->getInvoice();
    }
}