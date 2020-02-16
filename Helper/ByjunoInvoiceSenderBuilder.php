<?php
namespace Byjuno\ByjunoCore\Helper;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Model\Order\Email\SenderBuilder;

class ByjunoInvoiceSenderBuilder extends SenderBuilder
{
    public static $pdf = "";
    public static $pdf_id = "";

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
        $this->transportBuilder->addAttachment(self::$pdf, "invoice_".self::$pdf_id.".pdf", "application/pdf");
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }
}
