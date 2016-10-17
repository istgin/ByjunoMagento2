<?php

namespace Byjuno\ByjunoCore\Model\Resource\Logs;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Byjuno\ByjunoCore\Model\Logs',
            'Byjuno\ByjunoCore\Model\Resource\Logs'
        );
    }
}