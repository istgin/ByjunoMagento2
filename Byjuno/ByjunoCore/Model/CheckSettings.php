<?php

/**
 *  ICEPAY Core - Block checking settings
 *  @version 1.0.0
 *  @author Olaf Abbenhuis
 *  @copyright ICEPAY <www.byjuno.com>
 *  
 *  Disclaimer:
 *  The merchant is entitled to change de ICEPAY plug-in code,
 *  any changes will be at merchant's own risk.
 *  Requesting ICEPAY support for a modified plug-in will be
 *  charged in accordance with the standard ICEPAY tariffs.
 * 
 */
namespace Byjuno\ByjunoCore\Model;
class CheckSettings extends \Magento\Config\Block\System\Config\Form\Field
{
	protected $_helper;
    public function __construct(
    \Byjuno\ByjunoCore\Helper\IceHelper $helper,
    \Magento\Backend\Block\Template\Context $context,
    array $data = [])
    {
        parent::__construct($context, $data);
        $this->_helper = $helper;
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        return '<span id="row_byjunocore_settings_manual"><p class="note"><span><a href="http://www.byjuno.com/downloads/pdf/manuals/magento/byjuno-manual-magento-advanced.pdf" target="blank&quot;" class="btn-manual"></a> <a href="http://www.byjuno.nl/webshop-modules/ideal-voor-magento-advanced" target="blank" class="btn-movie"></a><br><br><br><span class="manual-comment">Need help? View our manual or install video!</span></span></p></span>';
    }


	
  
  
}