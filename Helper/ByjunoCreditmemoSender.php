<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 09.11.2016
 * Time: 15:48
 */
namespace Byjuno\ByjunoCore\Helper;

use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;

/**
 * Class OrderSender
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ByjunoCreditmemoSender extends CreditmemoSender
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
        $sender = $this->getSender();

        try {
            $sender->send();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return true;
    }

    public function sendCreditMemo(\Magento\Sales\Model\Order\Creditmemo $creditmemo, $email, $forceSyncMode = false)
    {
        $this->email = $email;
        $order = $creditmemo->getOrder();
        $transport = [
            'order' => $order,
            'creditmemo' => $creditmemo,
            'comment' => $creditmemo->getCustomerNoteNotify() ? $creditmemo->getCustomerNote() : '',
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
        ];

        $this->templateContainer->setTemplateVars($transport);
        if ($this->checkAndSend($order)) {
            return true;
        }

        return false;
    }
}
