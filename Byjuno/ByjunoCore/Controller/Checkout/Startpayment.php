<?php
/**
 * Copyright © 2015 Pay.nl All rights reserved.
 */
namespace Byjuno\ByjunoCore\Controller\Checkout;
use Magento\Checkout\Model\Session;

class Startpayment extends \Magento\Framework\App\Action\Action
{
    protected $_config;
    /**
     * @var Session
     */
    protected $_checkoutSession;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_logger = $logger;
        parent::__construct($context);
    }
    public function execute()
    {

        try {
            $order = $this->_getCheckoutSession()->getLastRealOrder();
            $method = $order->getPayment()->getMethod();
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/onepage/success');
        } catch (\Exception $e) {
            $order = $this->_getCheckoutSession()->getLastRealOrder();
            $order->registerCancellation("errorXXX")->save();
            $this->_getCheckoutSession()->restoreQuote();
            $this->messageManager->addExceptionMessage(new \Exception("ex"), "Error - ".$order->getId());
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/cart');

        }
        return $resultRedirect;

    }
    /**
     * Return checkout session object
     *
     * @return Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }
}