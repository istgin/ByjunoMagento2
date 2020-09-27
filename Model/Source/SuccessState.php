<?php

namespace Byjuno\ByjunoCore\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class SuccessState implements ArrayInterface
{
    protected $_categoryHelper;

    public function __construct(\Magento\Catalog\Helper\Category $catalogCategory)
    {
        $this->_categoryHelper = $catalogCategory;
    }


    /*
     * Option getter
     * @return array
     */
    public function toOptionArray()
    {


        $arr = $this->toArray();
        $ret = [];

        foreach ($arr as $key => $value)
        {

            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $ret;
    }

    /*
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {
        $catagoryList = array();
        $catagoryList["pending"] = 'Pending (can edit)';
        $catagoryList["processing"] = 'Processing';
        $catagoryList["completed"] = 'Completed';
        return $catagoryList;
    }

}