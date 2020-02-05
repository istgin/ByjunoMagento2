<?php
namespace Byjuno\ByjunoCore\Block\Adminhtml\Info;

class ByjunoInstallment extends \Magento\Payment\Block\Info
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
        if ($plId == 'installment_3installment_enable') {
            $repayment = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_3installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $webshopProfileId);
        } else if ($plId == 'installment_10installment_enable') {
            $repayment = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_10installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $webshopProfileId);
        } else if ($plId == 'installment_12installment_enable') {
            $repayment = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_12installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $webshopProfileId);
        } else if ($plId == 'installment_24installment_enable') {
            $repayment = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_24installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $webshopProfileId);
        } else if ($plId == 'installment_4x12installment_enable') {
            $repayment = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_4x12installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $webshopProfileId);
        }
        $paymentSend = $this->getInfo()->getAdditionalInformation("payment_send");
        $htmlAdd = '';
        if ($paymentSend == 'email')
        {
            $htmlAdd = __($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_localization/byjuno_installment_email_text",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        }
        else if ($paymentSend == 'postal')
        {
            $htmlAdd = __($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_localization/byjuno_installment_postal_text",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
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
        if ($plId == 'installment_3installment_enable') {
            $repayment = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_3installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $webshopProfileId);
        } else if ($plId == 'installment_10installment_enable') {
            $repayment = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_10installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $webshopProfileId);
        } else if ($plId == 'installment_12installment_enable') {
            $repayment = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_12installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $webshopProfileId);
        } else if ($plId == 'installment_24installment_enable') {
            $repayment = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_24installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $webshopProfileId);
        } else if ($plId == 'installment_4x12installment_enable') {
            $repayment = $this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_4x12installment/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $webshopProfileId);
        }
        $paymentSend = $this->getInfo()->getAdditionalInformation("payment_send");
        $htmlAdd = '';
        if ($paymentSend == 'email')
        {
            $htmlAdd = __($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_localization/byjuno_installment_email_text",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        }
        else if ($paymentSend == 'postal')
        {
            $htmlAdd = __($this->_scopeConfig->getValue("byjunoinstallmentsettings/byjuno_installment_localization/byjuno_installment_postal_text",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        }
        $out = '(B2C)';
        if ($info == true) {
            $out = '(B2B)';
        }
        return $paymentMehtodName."{{pdf_row_separator}}".$repayment.' '.$out.'{{pdf_row_separator}}'.$htmlAdd;
    }
}