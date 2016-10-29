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

    public function isAvailable(CartInterface $quote = null)
    {

    }
}