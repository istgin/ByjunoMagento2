<?php
/**
 * Created by Byjuno.
 * User: i.sutugins
 * Date: 14.2.9
 * Time: 10:28
 */
namespace Byjuno\ByjunoCore\Helper\Api;

class ByjunoLogger
{
    public function log($array) {
        $sql = '
                INSERT INTO `'._DB_PREFIX_.'byjuno_logs` (
                  `firstname`,
                  `lastname`,
                  `town`,
                  `postcode`,
                  `street`,
                  `country`,
                  `ip`,
                  `status`,
                  `request_id`,
                  `type`,
                  `error`,
                  `response`,
                  `request`
                )
                VALUES
                (
                    \''.pSQL($array['firstname']).'\',
                    \''.pSQL($array['lastname']).'\',
                    \''.pSQL($array['town']).'\',
                    \''.pSQL($array['postcode']).'\',
                    \''.pSQL($array['street']).'\',
                    \''.pSQL($array['country']).'\',
                    \''.pSQL($array['ip']).'\',
                    \''.pSQL($array['status']).'\',
                    \''.pSQL($array['request_id']).'\',
                    \''.pSQL($array['type']).'\',
                    \''.pSQL($array['error'], true).'\',
                    \''.pSQL($array['response'], true).'\',
                    \''.pSQL($array['request'], true).'\'
                )
        ';
        Db::getInstance()->Execute($sql);
    }
};