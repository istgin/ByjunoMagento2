<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 08.12.2016
 * Time: 22:10
 */

namespace  Byjuno\ByjunoCore\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        //handle all possible upgrade versions

        if(!$context->getVersion()) {
            //no previous version found, installation, InstallSchema was just executed
            //be careful, since everything below is true for installation !
        }

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            //code to upgrade to 1.0.1
            //no changes
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            //code to upgrade to 1.0.2
            //no changes
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            //code to upgrade to 1.0.3
            //no changes
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            //code to upgrade to 1.0.4
            //no changes
        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            //code to upgrade to 1.0.5
            //no changes
        }

        if (version_compare($context->getVersion(), '1.0.6') < 0) {
            //code to upgrade to 1.0.6
            //no changes
        }

        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            //code to upgrade to 1.0.7
            //no changes
        }

        if (version_compare($context->getVersion(), '1.0.8') < 0) {
            //code to upgrade to 1.0.8
            //no changes
        }

        if (version_compare($context->getVersion(), '1.0.9') < 0) {
            //code to upgrade to 1.0.9
            //no changes
        }

        if (version_compare($context->getVersion(), '1.1.0') < 0) {
            //code to upgrade to 1.1.0
            //no changes
        }

        if (version_compare($context->getVersion(), '1.1.1') < 0) {
            //code to upgrade to 1.1.1
            //no changes
        }

        if (version_compare($context->getVersion(), '1.1.2') < 0) {
            //code to upgrade to 1.1.2
            //no changes
        }

        if (version_compare($context->getVersion(), '1.2.0') < 0) {
            //code to upgrade to 1.2.0
            //no changes
        }

        if (version_compare($context->getVersion(), '1.2.1') < 0) {
            //code to upgrade to 1.2.1
            //no changes
        }

        $setup->endSetup();
    }
}