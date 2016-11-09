<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 09.11.2016
 * Time: 15:48
 */
namespace Byjuno\ByjunoCore\Helper;

use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;

/**
 * Class OrderSender
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ByjunoOrderSender extends OrderSender
{
    protected function checkAndSend(\Magento\Sales\Model\Order $order)
    {
        $this->identityContainer->setStore($order->getStore());
        if (!$this->identityContainer->isEnabled()) {
            return false;
        }
        $this->prepareTemplate($order);

        /** @var \Magento\Sales\Model\Order\Email\SenderBuilder $sender */
        $this->identityContainer->setCustomerName("Igor");
        $this->identityContainer->setCustomerEmail("igor.sutugin@gmail.com");
        $sender = $this->getSender();

        try {
            $sender->send();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return true;
    }

    public function send(\Magento\Sales\Model\Order $order, $forceSyncMode = false)
    {
        $order->setSendEmail(true);

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            if ($this->checkAndSend($order)) {
                $order->setEmailSent(true);
                $this->orderResource->saveAttribute($order, ['send_email', 'email_sent']);
                return true;
            }
        }

        $this->orderResource->saveAttribute($order, 'send_email');

        return false;
    }
}
