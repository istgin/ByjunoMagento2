<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 13.11.2016
 * Time: 20:18
 */

namespace Byjuno\ByjunoCore\Observer;
use Magento\Framework\Event\ObserverInterface;

class InvoiceObserver implements ObserverInterface
{
    public static $Invoice;
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $payment \Magento\Sales\Model\Order\Payment\Interceptor */
        $payment = $observer->getData('payment');
        /* @var $invoice \Magento\Sales\Model\Order\Invoice */
        $invoice = $observer->getData('invoice');

        if ($payment->getMethodInstance()->getCode() == \Byjuno\ByjunoCore\Model\Ui\ConfigProvider::CODE_INVOICE
            || $payment->getMethodInstance()->getCode() == \Byjuno\ByjunoCore\Model\Ui\ConfigProvider::CODE_INSTALLMENT  ) {
            //WTF? How to get invoice in other way?? no processInvoice like in magento 1.X
            self::$Invoice = $invoice;
        }

    }
}