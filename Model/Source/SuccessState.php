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
        $catagoryList["pending"] = 'Pending - requires single query request disabled (can edit) and auto invoice is disabled.';
        $catagoryList["processing"] = 'Processing - requires auto invoice is disabled.';
        $catagoryList["completed"] = 'Completed';
        return $catagoryList;
    }

}
