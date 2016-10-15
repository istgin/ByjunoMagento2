<?php

namespace Icepay\IcpCore\Model\Icepay;
class Postback {

    protected $sqlModel;
    protected $orderModel;
    protected $order;
    protected $storeID;
    private $_post;

    public function __construct()
    {
        $this->sqlModel = Mage::getModel('icecore/mysql4_iceCore');
        $this->orderModel = Mage::getModel('sales/order');
    }

    public function handle($_vars)
    {
        if (!$_vars) {
            Mage::helper("icecore")->log("No Postback vars");
            die("ICEPAY postback page installed correctly.");
        }
        $this->_post = $_vars;

        if ($_vars['Status'] == Icepay_IceCore_Model_Config::STATUS_VERSION_CHECK) {
            $this->outputVersion($this->validateVersion());
        }

        Mage::helper("icecore")->log(serialize($_vars));

        $this->order = $this->orderModel->loadByIncrementId($_vars['OrderID']);

        $icepayTransaction = $this->sqlModel->loadPaymentByID($this->order->getRealOrderId());

        $this->storeID = $icepayTransaction["store_id"];

        $transActionID = $this->saveTransaction($_vars);

        $doSpecialActions = false;

        if ($_vars['Status'] == Icepay_IceCore_Model_Config::STATUS_AUTH) {
            if (Mage::helper('icecore')->isModuleInstalled('Icepay_AutoCapture')) {
                if (Mage::Helper('icepay_autocapture')->isAutoCaptureActive($this->storeID)) {
                    $_vars['Status'] = Icepay_IceCore_Model_Config::STATUS_SUCCESS;
                }
            }
        }

        if ($this->canUpdateBasedOnIcepayTable($icepayTransaction['status'], $_vars['Status'])) {
            /* creating the invoice causes major overhead! Status should to be updated and saved first */
            if ($_vars['Status'] == Icepay_IceCore_Model_Config::STATUS_SUCCESS)
                $doSpecialActions = true;

            // Update ICEPAY transaction info
            $newData = $icepayTransaction;
            $newData['update_time'] = now();
            $newData['status'] = $_vars['Status'];
            $newData['transaction_id'] = $_vars['PaymentID'];
            $this->sqlModel->changeStatus($newData);

            // Update order status
            if ($_vars['Status'] == Icepay_IceCore_Model_Config::STATUS_ERROR) {
                $this->order->cancel();
            } else {
                $this->order->setState(
                        $this->getMagentoState($_vars['Status']), $this->getMagentoStatus($_vars['Status']), Mage::helper('icecore')->__('Status of order changed'), true
                );
            };
        };

        $this->order->save();

        $this->sendMail($icepayTransaction['status'], $_vars['Status']);

        if ($doSpecialActions) {
            $extraMsg = $this->specialActions($_vars['Status'], $transActionID);
            $this->order->setState(
                    $this->getMagentoState($_vars['Status']), $this->getMagentoStatus($_vars['Status']), $extraMsg, false
            );
            $this->order->save();
        }
    }

    protected function outputVersion($extended = false)
    {
        $dump = array(
            "module" => $this->getVersions(),
            "notice" => "Checksum validation passed!"
        );
        if ($extended) {

            $dump["additional"] = array(
                "magento" => Mage::getVersion(),
                "soap" => Mage::helper('icecore')->hasSOAP() ? "installed" : "not installed",
                "storescope" => Mage::helper('icecore')->getStoreScopeID(),
            );
        } else {
            $dump["notice"] = "Checksum failed! Merchant ID and Secret code probably incorrect.";
        }
        var_dump($dump);
        exit();
    }

    protected function validateVersion()
    {
        if ($this->generateChecksumForVersion() != $this->_post["Checksum"])
            return false;
        return true;
    }

    protected function getVersions()
    {
        $_v = "";
        if (class_exists(Mage::getConfig()->getHelperClassName('icecore')))
            $_v.= sprintf("%s %s. ", Mage::helper('icecore')->title, Mage::helper('icecore')->version);
        if (class_exists(Mage::getConfig()->getHelperClassName('icebasic')))
            $_v.= sprintf("%s %s. ", Mage::helper('icebasic')->title, Mage::helper('icebasic')->version);
        if (class_exists(Mage::getConfig()->getHelperClassName('iceadvanced')))
            $_v.= sprintf("%s %s. ", Mage::helper('iceadvanced')->title, Mage::helper('iceadvanced')->version);
        return $_v;
    }

    protected function generateChecksumForVersion()
    {
        return sha1(
                sprintf("%s|%s|%s|%s", Mage::helper('icecore')->getConfig(Icepay_IceCore_Model_Config::SECRETCODE), Mage::helper('icecore')->getConfig(Icepay_IceCore_Model_Config::MERCHANTID), $this->_post["Status"], substr(strval(time()), 0, 8)
                )
        );
    }

    protected function sendMail($currentStatus, $newStatus)
    {
        switch ($currentStatus) {
            case Icepay_IceCore_Model_Config::STATUS_NEW:
                if ($newStatus == Icepay_IceCore_Model_Config::STATUS_ERROR) {
                    $this->order->sendOrderUpdateEmail();
                } else {
                    $this->order->sendNewOrderEmail();
                };
                break;
            default:
                $this->order->sendOrderUpdateEmail();
        }
    }

    protected function saveTransaction($_vars)
    {
        $payment = $this->order->getPayment();

        $transaction = $payment->getTransaction($_vars['PaymentID']);

        $i = 0;
        do {
            $id = $_vars['PaymentID'] . (($i > 0) ? "-{$i}" : "");
            $transaction = $payment->getTransaction($id);
            $i++;
        } while ($transaction);
        $i--;

        $id = $_vars['PaymentID'] . (($i > 0) ? "-{$i}" : "");

        $payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $_vars);


        $payment->setTransactionId($id)
                ->setIsTransactionClosed($this->isClosedStatus($_vars['Status']));

        if ($this->isRefund($_vars['Status'])) {
            $payment->setParentTransactionId($this->getParentPaymentID($_vars['StatusCode']));
            //Creditmemo currently not supported
        };

        $payment->addTransaction(
                $this->getTransactionStatus($_vars['Status']), null, false);

        $payment->save();

        return $id;
    }

    protected function createInvoice($id)
    {
        $invoice = $this->order->prepareInvoice()
                ->setTransactionId($id)
                ->addComment(Mage::helper('icecore')->__('Auto-generated by ICEPAY'))
                ->register()
                ->pay();

        $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

        $transactionSave->save();

        $invoice->sendEmail();

        return $invoice;
    }

    protected function specialActions($newStatus, $transActionID)
    {
        $msg = "";
        switch ($newStatus) {
            case Icepay_IceCore_Model_Config::STATUS_SUCCESS:
                if (!$this->order->hasInvoices() && Mage::app()->getStore($this->storeID)->getConfig(Icepay_IceCore_Model_Config::AUTOINVOICE) == 1) {
                    $invoice = $this->createInvoice($transActionID);
                    $msg = Mage::helper("icecore")->__('Invoice Auto-Created: %s', '<strong>' . $invoice->getIncrementId() . '</strong>');
                };
                break;
        }
        return $msg;
    }

    protected function canUpdate($currentStatus, $newStatus)
    {
        switch ($newStatus) {
            case Icepay_IceCore_Model_Config::STATUS_SUCCESS: return ($currentStatus == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT || $currentStatus == Mage_Sales_Model_Order::STATE_NEW);
            case Icepay_IceCore_Model_Config::STATUS_OPEN: return ($currentStatus == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT || $currentStatus == Mage_Sales_Model_Order::STATE_NEW);
            case Icepay_IceCore_Model_Config::STATUS_ERROR: return ($currentStatus == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT || $currentStatus == Mage_Sales_Model_Order::STATE_NEW);
            case Icepay_IceCore_Model_Config::STATUS_AUTH: return ($currentStatus == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT || $currentStatus == Mage_Sales_Model_Order::STATE_NEW);
            case Icepay_IceCore_Model_Config::STATUS_CHARGEBACK: return ($currentStatus == Mage_Sales_Model_Order::STATE_PROCESSING || $currentStatus == Mage_Sales_Model_Order::STATE_COMPLETE);
            case Icepay_IceCore_Model_Config::STATUS_REFUND: return ($currentStatus == Mage_Sales_Model_Order::STATE_PROCESSING || $currentStatus == Mage_Sales_Model_Order::STATE_COMPLETE);
            default:
                return false;
        };
    }

    protected function canUpdateBasedOnIcepayTable($currentStatus, $newStatus)
    {
        switch ($currentStatus) {
            case Icepay_IceCore_Model_Config::STATUS_NEW:
            case Icepay_IceCore_Model_Config::STATUS_OPEN:
                return (
                        $newStatus == Icepay_IceCore_Model_Config::STATUS_SUCCESS ||
                        $newStatus == Icepay_IceCore_Model_Config::STATUS_ERROR ||
                        $newStatus == Icepay_IceCore_Model_Config::STATUS_AUTH ||
                        $newStatus == Icepay_IceCore_Model_Config::STATUS_OPEN
                        );
                break;
            case Icepay_IceCore_Model_Config::STATUS_AUTH:
                return (
                        $newStatus == Icepay_IceCore_Model_Config::STATUS_SUCCESS ||
                        $newStatus == Icepay_IceCore_Model_Config::STATUS_ERROR
                        );
                break;
            case Icepay_IceCore_Model_Config::STATUS_ERROR:
                return (
                        $newStatus == Icepay_IceCore_Model_Config::STATUS_SUCCESS
                        );
                break;
            case Icepay_IceCore_Model_Config::STATUS_SUCCESS:
                return (
                        $newStatus == Icepay_IceCore_Model_Config::STATUS_CHARGEBACK ||
                        $newStatus == Icepay_IceCore_Model_Config::STATUS_REFUND
                        );
                break;
            default:
                return false;
                break;
        }
    }

    protected function getMagentoStatus($icepayStatus)
    {
        switch ($icepayStatus) {
            case Icepay_IceCore_Model_Config::STATUS_SUCCESS: return Icepay_IceCore_Model_Config::STATUS_MAGENTO_SUCCESS;
            case Icepay_IceCore_Model_Config::STATUS_OPEN: return Icepay_IceCore_Model_Config::STATUS_MAGENTO_OPEN;
            case Icepay_IceCore_Model_Config::STATUS_ERROR: return Icepay_IceCore_Model_Config::STATUS_MAGENTO_ERROR;
            case Icepay_IceCore_Model_Config::STATUS_CHARGEBACK: return Icepay_IceCore_Model_Config::STATUS_MAGENTO_CHARGEBACK;
            case Icepay_IceCore_Model_Config::STATUS_REFUND: return Icepay_IceCore_Model_Config::STATUS_MAGENTO_REFUND;
            case Icepay_IceCore_Model_Config::STATUS_AUTH: return Icepay_IceCore_Model_Config::STATUS_MAGENTO_AUTHORIZED;
            default:
                return false;
        };
    }

    protected function getMagentoState($icepayStatus)
    {
        switch ($icepayStatus) {
            case Icepay_IceCore_Model_Config::STATUS_SUCCESS:
                return Mage_Sales_Model_Order::STATE_PROCESSING;
                break;
            case Icepay_IceCore_Model_Config::STATUS_OPEN:
            case Icepay_IceCore_Model_config::STATUS_AUTH:
                return Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
                break;
            case Icepay_IceCore_Model_Config::STATUS_ERROR:
                return Mage_Sales_Model_Order::STATE_CANCELED;
                break;
            case Icepay_IceCore_Model_Config::STATUS_CHARGEBACK:
            case Icepay_IceCore_Model_Config::STATUS_REFUND:
                return Mage_Sales_Model_Order::STATE_HOLDED;
                //return Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                break;
            default:
                return false;
        };
    }

    protected function getTransactionStatus($icepayStatus)
    {
        switch ($icepayStatus) {
            case Icepay_IceCore_Model_Config::STATUS_SUCCESS: return Mage_Sales_Model_Order_Payment_Transaction::TYPE_PAYMENT;
            case Icepay_IceCore_Model_Config::STATUS_OPEN: return Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID;
            case Icepay_IceCore_Model_config::STATUS_AUTH: return Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID;
            case Icepay_IceCore_Model_Config::STATUS_ERROR: return Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID;
            case Icepay_IceCore_Model_Config::STATUS_CHARGEBACK: return Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND;
            case Icepay_IceCore_Model_Config::STATUS_REFUND: return Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND;
            default: return false;
        };
    }

    protected function isClosedStatus($icepayStatus)
    {
        switch ($icepayStatus) {
            case Icepay_IceCore_Model_Config::STATUS_SUCCESS: return true;
            case Icepay_IceCore_Model_Config::STATUS_OPEN: return false;
            case Icepay_IceCore_Model_config::STATUS_AUTH: return false;
            case Icepay_IceCore_Model_Config::STATUS_ERROR: return true;
            case Icepay_IceCore_Model_Config::STATUS_CHARGEBACK: return true;
            case Icepay_IceCore_Model_Config::STATUS_REFUND: return true;
            default: return false;
        };
    }

    protected function isRefund($icepayStatus)
    {
        switch ($icepayStatus) {
            case Icepay_IceCore_Model_Config::STATUS_CHARGEBACK: return true;
            case Icepay_IceCore_Model_Config::STATUS_REFUND: return true;
            default:
                return false;
        };
    }

    protected function getParentPaymentID($statusString)
    {
        $arr = explode("PaymentID:", $statusString);
        return intval($arr[1]);
    }

    protected function getTransactionString($_vars)
    {
        $str = "";
        foreach ($_vars as $key => $value) {
            $str .= "{$key}: {$value}<BR>";
        }
        return $str;
    }

}