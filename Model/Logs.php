<?php

namespace Byjuno\ByjunoCore\Model;

use Magento\Framework\Model\AbstractModel;

class Logs extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Byjuno\ByjunoCore\Model\Resource\Logs');
    }
}