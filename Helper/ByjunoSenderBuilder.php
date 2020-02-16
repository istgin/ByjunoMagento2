<?php
namespace Byjuno\ByjunoCore\Helper;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\SenderBuilder;

class ByjunoSenderBuilder extends SenderBuilder
{
    public function send()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /* @var $transportBuilder \Byjuno\ByjunoCore\Mail\Template\ByjunoTransportBuilder */
        $transportBuilder = $objectManager->get('\Byjuno\ByjunoCore\Mail\Template\ByjunoTransportBuilder');
        $this->transportBuilder = $transportBuilder;
        $this->configureEmailTemplate();
        $this->transportBuilder->addTo(
            $this->identityContainer->getCustomerEmail(),
            $this->identityContainer->getCustomerName()
        );
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }
}
