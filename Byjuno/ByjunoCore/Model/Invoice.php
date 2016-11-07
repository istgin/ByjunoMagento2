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


/**
 * Pay In Store payment method model
 */
class Invoice extends \Magento\Payment\Model\Method\Adapter
{

    /* @var $_scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface */
    private $_scopeConfig;

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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');

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

    public function validate()
    {
        //$payment = $this->getInfoInstance();
       /* throw new LocalizedException(
            __("XXX")
        );
*/
        return $this;
    }


    public function authorize(InfoInterface $payment, $amount)
    {
        /** @var $order \Magento\Sales\Model\Order */
        $order = $payment->getOrder();
        /*throw new LocalizedException(
            __("XXXXX-" . $order->getId())
        );*/
        return $this;
    }

}