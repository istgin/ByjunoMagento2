<?php

namespace Byjuno\ByjunoCore\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Logs extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_logs';
        $this->_blockGroup = 'Byjuno_ByjunoCore';
        $this->_headerText = __('Manage Logs');
        $this->_addButtonLabel = __('Add Logs');
        parent::_construct();
    }
}