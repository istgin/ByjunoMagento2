<?php
/**
 * Created by CembraPay.
 * User: i.sutugins
 * Date: 14.2.9
 * Time: 10:28
 */
namespace Byjuno\ByjunoCore\Helper\CembraApi;

use Byjuno\ByjunoCore\Helper\DataHelper;
use Magento\Framework\Exception\LocalizedException;

class CembraPayLogger
{
    public function log($array) {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->create('\Magento\Framework\App\ResourceConnection');

        $conn = $connection->getConnection();
        $conn->insert('cembrapaycheckout_log',
            array(
                'firstname' => $array['firstname'],
                'lastname' => $array['lastname'],
                'town' => $array['town'],
                'postcode' => $array['postcode'],
                'street' => $array['street1'],
                'country' => $array['country'],
                'ip' => $array['ip'],
                'status' => $array['status'],
                'request_id' => $array['request_id'],
                'order_id' => $array['order_id'],
                'transaction_id' => $array['transaction_id'],
                'type' => $array['type'],
                'error' => $array['error'],
                'response' => $array['response'],
                'request' => $array['request'],
                'creation_date' => date ("Y-m-d H:i:s")
            )
        );
    }

    public function getAuthTransaction($orderId) {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->create('\Magento\Framework\App\ResourceConnection');
        $tableName = $connection->getTableName("cembrapaycheckout_log");
        $conn = $connection->getConnection();

        $select = $conn->select()->from($tableName)
            ->where('order_id = ?', $orderId)
            ->where('type = ?',  DataHelper::$MESSAGE_AUTH);
        $result = $conn->fetchRow($select);
        return $result;
    }

    public function getChkTransaction($orderId) {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->create('\Magento\Framework\App\ResourceConnection');
        $tableName = $connection->getTableName("cembrapaycheckout_log");
        $conn = $connection->getConnection();

        $select = $conn->select()->from($tableName)
            ->where('order_id = ?', $orderId)
            ->where('type = ?',  DataHelper::$MESSAGE_CHK);
        $result = $conn->fetchRow($select);
        return $result;
    }
};
