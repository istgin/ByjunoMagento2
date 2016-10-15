<?php

/**
 *  ICEPAY Core - Block generating store URLs
 *  @version 1.0.0
 *  @author Olaf Abbenhuis
 *  @copyright ICEPAY <www.icepay.com>
 *  
 *  Disclaimer:
 *  The merchant is entitled to change de ICEPAY plug-in code,
 *  any changes will be at merchant's own risk.
 *  Requesting ICEPAY support for a modified plug-in will be
 *  charged in accordance with the standard ICEPAY tariffs.
 * 
 */
namespace Icepay\IcpCore\Model;
class GenerateURL extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_helper;
    public function __construct(
    \Icepay\IcpCore\Helper\IceHelper $helper,
    \Magento\Backend\Block\Template\Context $context,
    array $data = [])
    {
        parent::__construct($context, $data);
        $this->_helper = $helper;
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->enCase($this->_helper->getStoreFrontURL($this->setAction($element->getName())));
    }


    protected function setAction($elementName)
    {
        switch ($elementName) {
            case "groups[icepay_setup][fields][merchant_url_ok][value]": return "result";
            case "groups[icepay_setup][fields][merchant_url_err][value]": return "result";
            case "groups[icepay_setup][fields][merchant_url_notify][value]": return "notify";
        }
    }

    protected function enCase($str)
    {
        return '<input type="text" name="" class="icepay_url_form" value="' . $str . '"/>';
    }

}