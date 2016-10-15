<?php

/**
 *  ICEPAY Core - Block modules grid
 */
namespace Icepay\IcpCore\Model\Grid;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Modules extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{

    public $_ajaxLoadPaymentMethodURL;
    public $_ajaxSavePaymentMethodURL;
    public $_ajaxGetPaymentMethodsURL;

    protected $_element = null;

    protected $_template = 'Icepay_IcpCore::renderer/grid_modules.phtml';
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $_sourceCountry;
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Directory\Model\Config\Source\Country $sourceCountry,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_sourceCountry = $sourceCountry;
        $this->_directoryHelper = $directoryHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }

    public function getJS($uri)
    {
        return "";//Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS, true) . $uri;
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getPaymentmethods()
    {
        return Array();
       // $core_sql = Mage::getSingleton('icecore/mysql4_iceCore');
       // return $core_sql->getModulesConfiguration();
    }

    protected function _prepareLayout()
    {

        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(
                [
                    'label' => __('Get paymentmethods'),
                    'onclick' => 'return ICEPAY.retrieveFromICEPAY()',
                    'class' => 'add',
                ]
            );

        $this->setChild('add_button', $button);
/*
        if (version_compare(Mage::getVersion(), '1.7.0.0', '<')) {
            $this->getLayout()->getBlock('head')->addItem('js_css', 'prototype/windows/themes/magento.css');
        } else {
            $this->getLayout()->getBlock('head')->addItem('skin_css', 'lib/prototype/windows/themes/magento.css');
        }

        $this->getLayout()
                ->getBlock('head')
                ->addItem('js_css', 'prototype/windows/themes/default.css');
*/
        return parent::_prepareLayout();
    }

}