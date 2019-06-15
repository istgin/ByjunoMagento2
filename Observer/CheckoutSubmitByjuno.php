<?php
namespace Byjuno\ByjunoCore\Observer;
use Magento\Framework\Event\ObserverInterface;
use PHPUnit\Framework\Exception;

class CheckoutSubmitByjuno implements ObserverInterface {
    protected $helper;
    protected $categoryRepository;
    /**
     * @param \Byjuno\ByjunoCore\Helper\DataHelper $helper
     */
    public function __construct( \Byjuno\ByjunoCore\Helper\DataHelper $helper ) {
        $this->helper = $helper;
    }
    public function execute( \Magento\Framework\Event\Observer $observer ) {
        $event   = $observer->getEvent();
        $order   = $observer->getOrder();

        $addressDelivery = $order->getShippingAddress();
        /**
         * @var \Magento\Sales\Model\Order      $order
         * @var \Magento\Sales\Model\Order\Item $orderProduct
         * @var \Magento\Catalog\Model\Product  $product
         */
        $method = $order->getPayment()->getMethod();
        if ($method != "byjuno_invoice" && $method != "byjuno_installment")
        {
            return;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $state =  $objectManager->get('Magento\Framework\App\State');
        if ($state->getAreaCode() == "adminhtml") {
            \Byjuno\ByjunoCore\Controller\Checkout\Startpayment::executeBackendOrder($this->helper, $order);
        }

    }
}