<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 15.10.2016
 * Time: 18:38
 */
namespace Byjuno\ByjunoCore\Model\Source;

use Byjuno\ByjunoCore\Helper\CembraApi\CembraPayAzure;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class AccessToken extends Field
{
    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $access_token = $this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/access_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $color = 'ddffdf';
        if (!CembraPayAzure::validToken($access_token)) {
            $color = 'FFE5E6';
        }
        return '<div style="word-break: break-all; background-color: #'.$color.'; padding: 10px 5px 10px 5px">'.$access_token.'</div>';
    }

}
