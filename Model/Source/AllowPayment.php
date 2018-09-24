<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 15.10.2016
 * Time: 15:43
 */

namespace Byjuno\ByjunoCore\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class AllowPayment implements ArrayInterface
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
        $catagoryList["0"] = 'Both (B2C & B2B)';
        $catagoryList["1"] = 'B2C only';
        $catagoryList["2"] = 'B2B only';
        return $catagoryList;
    }

}