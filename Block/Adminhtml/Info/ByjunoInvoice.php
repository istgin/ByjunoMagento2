<?php
namespace Byjuno\ByjunoCore\Block\Adminhtml\Info;

class ByjunoInvoice extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    public function  toHtml() {
        $paymentMehtodName = $this->getMethod()->getTitle();
        $info = $this->getInfo()->getAdditionalInformation("is_b2b");
        $plId = $this->getInfo()->getAdditionalInformation("payment_plan");
        $repayment = "";
        $webshopProfileId = $this->getInfo()->getAdditionalInformation("webshop_profile_id");
        if ($plId == 'invoice_single_enable') {
            $repayment = $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_single_invoice/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $webshopProfileId);
        } else if ($plId == 'invoice_partial_enable') {
            $repayment = $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_partial/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $webshopProfileId);
        }
        $paymentSend = $this->getInfo()->getAdditionalInformation("payment_send");
        $htmlAdd = '';
        if ($paymentSend == 'email')
        {
            $htmlAdd = __("Delivery method by E-Mail");
        }
        else if ($paymentSend == 'postal')
        {
            $htmlAdd = __("Delivery method by Post");
        }
        $out = '(B2C)';
        if ($info == true) {
            $out = '(B2B)';
        }
        return $paymentMehtodName."<br />".$repayment.' '.$out.'<br />'.$htmlAdd;
    }

    /**
     * @return string
     */
    public function toPdf()
    {
        $paymentMehtodName = $this->getMethod()->getTitle();
        $info = $this->getInfo()->getAdditionalInformation("is_b2b");
        $plId = $this->getInfo()->getAdditionalInformation("payment_plan");
        $repayment = "";
        $webshopProfileId = $this->getInfo()->getAdditionalInformation("webshop_profile_id");
        if ($plId == 'invoice_single_enable') {
            $repayment = $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_single_invoice/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $webshopProfileId);
        } else if ($plId == 'invoice_partial_enable') {
            $repayment = $this->_scopeConfig->getValue("byjunoinvoicesettings/byjuno_invoice_partial/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $webshopProfileId);
        }
        $paymentSend = $this->getInfo()->getAdditionalInformation("payment_send");
        $htmlAdd = '';
        if ($paymentSend == 'email')
        {
            $htmlAdd = __("Delivery method by E-Mail");
        }
        else if ($paymentSend == 'postal')
        {
            $htmlAdd = __("Delivery method by Post");
        }
        $out = '(B2C)';
        if ($info == true) {
            $out = '(B2B)';
        }
        return $paymentMehtodName."{{pdf_row_separator}}".$repayment.' '.$out.'{{pdf_row_separator}}'.$htmlAdd;
    }
}