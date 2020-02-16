<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 09.11.2016
 * Time: 15:48
 */
namespace Byjuno\ByjunoCore\Helper;

use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;

/**
 * Class OrderSender
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ByjunoInvoiceSender extends InvoiceSender
{
    private $email;
    protected function checkAndSend(\Magento\Sales\Model\Order $order)
    {
        $this->identityContainer->setStore($order->getStore());
        if (!$this->identityContainer->isEnabled()) {
            return false;
        }
        $this->prepareTemplate($order);
        /** @var \Magento\Sales\Model\Order\Email\SenderBuilder $sender */
        $this->identityContainer->setCustomerName("Byjuno");
        $this->identityContainer->setCustomerEmail($this->email);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectManagerInterface = $objectManager->get('\Magento\Framework\ObjectManagerInterface');
        $this->senderBuilderFactory = new \Magento\Sales\Model\Order\Email\SenderBuilderFactory($objectManagerInterface, '\\Byjuno\\ByjunoCore\\Helper\\ByjunoInvoiceSenderBuilder');

        $sender = $this->getSender();

        try {
            $sender->send();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return true;
    }

    public function sendInvoice(\Magento\Sales\Model\Order\Invoice $invoice, $email, \Byjuno\ByjunoCore\Helper\DataHelper $helper)
    {
        $this->email = $email;
        $pdfcls = $helper->_objectManager->create(\Magento\Sales\Model\Order\Pdf\Invoice::class)->getPdf([$invoice]);
        $pdf = $pdfcls->render();
        $order = $invoice->getOrder();
        $transport = [
            'order' => $order,
            'invoice' => $invoice,
            'comment' => $invoice->getCustomerNoteNotify() ? $invoice->getCustomerNote() : '',
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order)
        ];
        ByjunoInvoiceSenderBuilder::$pdf = $pdf;
        ByjunoInvoiceSenderBuilder::$pdf_id = $invoice->getIncrementId();

        $this->templateContainer->setTemplateVars($transport);

        if ($this->checkAndSend($order)) {
            return true;
        }
        return false;
    }
}
