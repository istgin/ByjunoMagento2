<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 29.10.2016
 * Time: 15:44
 */

namespace Byjuno\ByjunoCore\Model;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Symfony\Component\Config\Definition\Exception\Exception;


/**
 * Pay In Store payment method model
 */
class Invoice extends \Magento\Payment\Model\Method\Adapter
{

    /* @var $_scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface */
    private $_scopeConfig;
    private $eventManager;

    /* @var $_scopeConfig \Magento\Checkout\Model\Session */
    private $_checkoutSession;


    /**
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param CommandPoolInterface $commandPool
     * @param ValidatorPoolInterface $validatorPool
     * @param CommandManagerInterface $commandExecutor
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null
    ) {

        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $commandPool,
            $validatorPool,
            $commandExecutor
        );
        $this->eventManager = $eventManager;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');

    }
    public function isAvailable(CartInterface $quote = null)
    {
        $isAvaliable =  $this->_scopeConfig->getValue("byjunocheckoutsettings/byjuno_setup/active");
        $methodsAvailable =
            $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_partial/active") ||
            $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_single_invoice/active");
        return $isAvaliable && $methodsAvailable;
    }

    public function getTitle()
    {
        return $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_setup/title_invoice", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $dataKey = $data->getDataByKey('additional_data');
        $payment = $this->getInfoInstance();
        $payment->setAdditionalInformation('payment_plan', null);
        $payment->setAdditionalInformation('invoice_send', null);
        $payment->setAdditionalInformation('payment_send_to', null);
        $payment->setAdditionalInformation('s3_ok', null);
        $payment->setAdditionalInformation('webshop_profile_id', null);
        if (isset($dataKey['payment_plan'])) {
            $payment->setAdditionalInformation('payment_plan', $dataKey['payment_plan']);
        }
        if (isset($dataKey['invoice_send'])) {
            $sentTo = '';
            if ($dataKey['invoice_send'] == 'postal') {
                $sentTo = (String)$this->_checkoutSession->getQuote()->getBillingAddress()->getStreetFull().', '.
                    (String)$this->_checkoutSession->getQuote()->getBillingAddress()->getCity().', '.
                    (String)$this->_checkoutSession->getQuote()->getBillingAddress()->getPostcode();
            } else if ($dataKey['invoice_send'] == 'email') {
                $sentTo = (String)$this->_checkoutSession->getQuote()->getBillingAddress()->getEmail();
            }
            $payment->setAdditionalInformation('invoice_send', $dataKey['invoice_send']);
            $payment->setAdditionalInformation('payment_send_to', $sentTo);
        }
        $payment->setAdditionalInformation('s3_ok', 'false');
        $payment->setAdditionalInformation("webshop_profile_id", $this->getStore());
        return $this;
    }

    public function validate()
    {
        $payment = $this->getInfoInstance();
        if ($payment->getAdditionalInformation('payment_plan') == null || ($payment->getAdditionalInformation('payment_plan') != 'invoice_single_enable' && $payment->getAdditionalInformation('payment_plan') != 'invoice_partial_enable')) {
            throw new LocalizedException(
                __("Invalid payment plan")
            );
        }

        if ($payment->getAdditionalInformation('invoice_send') == null || ($payment->getAdditionalInformation('invoice_send') != 'email' && $payment->getAdditionalInformation('invoice_send') != 'postal')) {
            throw new LocalizedException(
                __("Please select invoice send way")
            );
        }

        if ($payment->getAdditionalInformation('payment_send_to') == null) {
            throw new LocalizedException(
                __("Invoice send way invalid address")
            );
        }
        /*
        throw new LocalizedException(
            __("OK")
        );
        */
        return $this;
    }

    public function order(InfoInterface $payment, $amount)
    {
        throw new LocalizedException(
            __("XXXXX")
        );

        return $this;
    }


    public function authorize(InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        throw new LocalizedException(
            __("XXXXX-" . $order->getId())
        );
        return $this;
    }

}