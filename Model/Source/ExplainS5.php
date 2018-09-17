<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 15.10.2016
 * Time: 18:38
 */
namespace Byjuno\ByjunoCore\Model\Source;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ExplainS5 extends Field
{
    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $byjuno_s5_explain = $this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/byjunos5transacton', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $message = 'S5 Transactions (Cancel and/or Refund) must be delivered to Byjuno manually or from ERP System';
        $color = 'FFE5E6';
        if ($byjuno_s5_explain == 1) {
            $message = 'S5 Transactions will be sent to Byjuno:<br/>
Cancel - for not invoiced amount<br/>
Refund - per Credit Memo';
            $color = 'ddffdf';
        }
        return '<div style="white-space: nowrap; background-color: #'.$color.'; padding: 10px 5px 10px 5px">'.$message.'</div>';
    }

}