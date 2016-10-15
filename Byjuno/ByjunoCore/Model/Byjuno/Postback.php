<?php

namespace Byjuno\ByjunoCore\Model\Byjuno;
class Postback {

    protected $sqlModel;
    protected $orderModel;
    protected $order;
    protected $storeID;
    private $_post;

    public function __construct()
    {
        $this->sqlModel = Mage::getModel('byjunocore/mysql4_iceCore');
        $this->orderModel = Mage::getModel('sales/order');
    }

    public function handle($_vars)
    {
        if (!$_vars) {
            Mage::helper("byjunocore")->log("No Postback vars");
            die("ICEPAY postback page installed correctly.");
        }
        $this->_post = $_vars;

        if ($_vars['Status'] == Byjuno_ByjunoCore_Model_Config::STATUS_VERSION_CHECK) {
            $this->outputVersion($this->validateVersion());
        }

        Mage::helper("byjunocore")->log(serialize($_vars));

        $this->order = $this->orderModel->loadByIncrementId($_vars['OrderID']);

        $byjunoTransaction = $this->sqlModel->loadPaymentByID($this->order->getRealOrderId());

        $this->storeID = $byjunoTransaction["store_id"];

        $transActionID = $this->saveTransaction($_vars);

        $doSpecialActions = false;

        if ($_vars['Status'] == Byjuno_ByjunoCore_Model_Config::STATUS_AUTH) {
            if (Mage::helper('byjunocore')->isModuleInstalled('Byjuno_AutoCapture')) {
                if (Mage::Helper('byjuno_autocapture')->isAutoCaptureActive($this->storeID)) {
                    $_vars['Status'] = Byjuno_ByjunoCore_Model_Config::STATUS_SUCCESS;
                }
            }
        }

        if ($this->canUpdateBasedOnByjunoTable($byjunoTransaction['status'], $_vars['Status'])) {
            /* creating the invoice causes major overhead! Status should to be updated and saved first */
            if ($_vars['Status'] == Byjuno_ByjunoCore_Model_Config::STATUS_SUCCESS)
                $doSpecialActions = true;

            // Update ICEPAY transaction info
            $newData = $byjunoTransaction;
            $newData['update_time'] = now();
            $newData['status'] = $_vars['Status'];
            $newData['transaction_id'] = $_vars['PaymentID'];
            $this->sqlModel->changeStatus($newData);

            // Update order status
            if ($_vars['Status'] == Byjuno_ByjunoCore_Model_Config::STATUS_ERROR) {
                $this->order->cancel();
            } else {
                $this->order->setState(
                        $this->getMagentoState($_vars['Status']), $this->getMagentoStatus($_vars['Status']), Mage::helper('byjunocore')->__('Status of order changed'), true
                );
            };
        };

        $this->order->save();

        $this->sendMail($byjunoTransaction['status'], $_vars['Status']);

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
                "soap" => Mage::helper('byjunocore')->hasSOAP() ? "installed" : "not installed",
                "storescope" => Mage::helper('byjunocore')->getStoreScopeID(),
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
        if (class_exists(Mage::getConfig()->getHelperClassName('byjunocore')))
            $_v.= sprintf("%s %s. ", Mage::helper('byjunocore')->title, Mage::helper('byjunocore')->version);
        if (class_exists(Mage::getConfig()->getHelperClassName('icebasic')))
            $_v.= sprintf("%s %s. ", Mage::helper('icebasic')->title, Mage::helper('icebasic')->version);
        if (class_exists(Mage::getConfig()->getHelperClassName('iceadvanced')))
            $_v.= sprintf("%s %s. ", Mage::helper('iceadvanced')->title, Mage::helper('iceadvanced')->version);
        return $_v;
    }

    protected function generateChecksumForVersion()
    {
        return sha1(
                sprintf("%s|%s|%s|%s", Mage::helper('byjunocore')->getConfig(Byjuno_ByjunoCore_Model_Config::SECRETCODE), Mage::helper('byjunocore')->getConfig(Byjuno_ByjunoCore_Model_Config::MERCHANTID), $this->_post["Status"], substr(strval(time()), 0, 8)
                )
        );
    }

    protected function sendMail($currentStatus, $newStatus)
    {
        switch ($currentStatus) {
            case Byjuno_ByjunoCore_Model_Config::STATUS_NEW:
                if ($newStatus == Byjuno_ByjunoCore_Model_Config::STATUS_ERROR) {
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
                ->addComment(Mage::helper('byjunocore')->__('Auto-generated by ICEPAY'))
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
            case Byjuno_ByjunoCore_Model_Config::STATUS_SUCCESS:
                if (!$this->order->hasInvoices() && Mage::app()->getStore($this->storeID)->getConfig(Byjuno_ByjunoCore_Model_Config::AUTOINVOICE) == 1) {
                    $invoice = $this->createInvoice($transActionID);
                    $msg = Mage::helper("byjunocore")->__('Invoice Auto-Created: %s', '<strong>' . $invoice->getIncrementId() . '</strong>');
                };
                break;
        }
        return $msg;
    }

    protected function canUpdate($currentStatus, $newStatus)
    {
        switch ($newStatus) {
            case Byjuno_ByjunoCore_Model_Config::STATUS_SUCCESS: return ($currentStatus == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT || $currentStatus == Mage_Sales_Model_Order::STATE_NEW);
            case Byjuno_ByjunoCore_Model_Config::STATUS_OPEN: return ($currentStatus == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT || $currentStatus == Mage_Sales_Model_Order::STATE_NEW);
            case Byjuno_ByjunoCore_Model_Config::STATUS_ERROR: return ($currentStatus == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT || $currentStatus == Mage_Sales_Model_Order::STATE_NEW);
            case Byjuno_ByjunoCore_Model_Config::STATUS_AUTH: return ($currentStatus == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT || $currentStatus == Mage_Sales_Model_Order::STATE_NEW);
            case Byjuno_ByjunoCore_Model_Config::STATUS_CHARGEBACK: return ($currentStatus == Mage_Sales_Model_Order::STATE_PROCESSING || $currentStatus == Mage_Sales_Model_Order::STATE_COMPLETE);
            case Byjuno_ByjunoCore_Model_Config::STATUS_REFUND: return ($currentStatus == Mage_Sales_Model_Order::STATE_PROCESSING || $currentStatus == Mage_Sales_Model_Order::STATE_COMPLETE);
            default:
                return false;
        };
    }

    protected function canUpdateBasedOnByjunoTable($currentStatus, $newStatus)
    {
        switch ($currentStatus) {
            case Byjuno_ByjunoCore_Model_Config::STATUS_NEW:
            case Byjuno_ByjunoCore_Model_Config::STATUS_OPEN:
                return (
                        $newStatus == Byjuno_ByjunoCore_Model_Config::STATUS_SUCCESS ||
                        $newStatus == Byjuno_ByjunoCore_Model_Config::STATUS_ERROR ||
                        $newStatus == Byjuno_ByjunoCore_Model_Config::STATUS_AUTH ||
                        $newStatus == Byjuno_ByjunoCore_Model_Config::STATUS_OPEN
                        );
                break;
            case Byjuno_ByjunoCore_Model_Config::STATUS_AUTH:
                return (
                        $newStatus == Byjuno_ByjunoCore_Model_Config::STATUS_SUCCESS ||
                        $newStatus == Byjuno_ByjunoCore_Model_Config::STATUS_ERROR
                        );
                break;
            case Byjuno_ByjunoCore_Model_Config::STATUS_ERROR:
                return (
                        $newStatus == Byjuno_ByjunoCore_Model_Config::STATUS_SUCCESS
                        );
                break;
            case Byjuno_ByjunoCore_Model_Config::STATUS_SUCCESS:
                return (
                        $newStatus == Byjuno_ByjunoCore_Model_Config::STATUS_CHARGEBACK ||
                        $newStatus == Byjuno_ByjunoCore_Model_Config::STATUS_REFUND
                        );
                break;
            default:
                return false;
                break;
        }
    }

    protected function getMagentoStatus($byjunoStatus)
    {
        switch ($byjunoStatus) {
            case Byjuno_ByjunoCore_Model_Config::STATUS_SUCCESS: return Byjuno_ByjunoCore_Model_Config::STATUS_MAGENTO_SUCCESS;
            case Byjuno_ByjunoCore_Model_Config::STATUS_OPEN: return Byjuno_ByjunoCore_Model_Config::STATUS_MAGENTO_OPEN;
            case Byjuno_ByjunoCore_Model_Config::STATUS_ERROR: return Byjuno_ByjunoCore_Model_Config::STATUS_MAGENTO_ERROR;
            case Byjuno_ByjunoCore_Model_Config::STATUS_CHARGEBACK: return Byjuno_ByjunoCore_Model_Config::STATUS_MAGENTO_CHARGEBACK;
            case Byjuno_ByjunoCore_Model_Config::STATUS_REFUND: return Byjuno_ByjunoCore_Model_Config::STATUS_MAGENTO_REFUND;
            case Byjuno_ByjunoCore_Model_Config::STATUS_AUTH: return Byjuno_ByjunoCore_Model_Config::STATUS_MAGENTO_AUTHORIZED;
            default:
                return false;
        };
    }

    protected function getMagentoState($byjunoStatus)
    {
        switch ($byjunoStatus) {
            case Byjuno_ByjunoCore_Model_Config::STATUS_SUCCESS:
                return Mage_Sales_Model_Order::STATE_PROCESSING;
                break;
            case Byjuno_ByjunoCore_Model_Config::STATUS_OPEN:
            case Byjuno_ByjunoCore_Model_config::STATUS_AUTH:
                return Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
                break;
            case Byjuno_ByjunoCore_Model_Config::STATUS_ERROR:
                return Mage_Sales_Model_Order::STATE_CANCELED;
                break;
            case Byjuno_ByjunoCore_Model_Config::STATUS_CHARGEBACK:
            case Byjuno_ByjunoCore_Model_Config::STATUS_REFUND:
                return Mage_Sales_Model_Order::STATE_HOLDED;
                //return Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                break;
            default:
                return false;
        };
    }

    protected function getTransactionStatus($byjunoStatus)
    {
        switch ($byjunoStatus) {
            case Byjuno_ByjunoCore_Model_Config::STATUS_SUCCESS: return Mage_Sales_Model_Order_Payment_Transaction::TYPE_PAYMENT;
            case Byjuno_ByjunoCore_Model_Config::STATUS_OPEN: return Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID;
            case Byjuno_ByjunoCore_Model_config::STATUS_AUTH: return Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID;
            case Byjuno_ByjunoCore_Model_Config::STATUS_ERROR: return Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID;
            case Byjuno_ByjunoCore_Model_Config::STATUS_CHARGEBACK: return Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND;
            case Byjuno_ByjunoCore_Model_Config::STATUS_REFUND: return Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND;
            default: return false;
        };
    }

    protected function isClosedStatus($byjunoStatus)
    {
        switch ($byjunoStatus) {
            case Byjuno_ByjunoCore_Model_Config::STATUS_SUCCESS: return true;
            case Byjuno_ByjunoCore_Model_Config::STATUS_OPEN: return false;
            case Byjuno_ByjunoCore_Model_config::STATUS_AUTH: return false;
            case Byjuno_ByjunoCore_Model_Config::STATUS_ERROR: return true;
            case Byjuno_ByjunoCore_Model_Config::STATUS_CHARGEBACK: return true;
            case Byjuno_ByjunoCore_Model_Config::STATUS_REFUND: return true;
            default: return false;
        };
    }

    protected function isRefund($byjunoStatus)
    {
        switch ($byjunoStatus) {
            case Byjuno_ByjunoCore_Model_Config::STATUS_CHARGEBACK: return true;
            case Byjuno_ByjunoCore_Model_Config::STATUS_REFUND: return true;
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