<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 08.12.2016
 * Time: 22:12
 */

namespace Byjuno\ByjunoCore\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Vendor setup factory
     *
     * @var VendorSetupFactory
     */
    private $vendorSetupFactory;
    /**
     * Init
     *
     * @param VendorSetupFactory $taxSetupFactory
     */
    public function __construct(VendorSetupFactory $vendorSetupFactory)
    {
        $this->vendorSetupFactory = $vendorSetupFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if(!$context->getVersion()) {
            //no previous version found, installation, InstallSchema was just executed
            //be careful, since everything below is true for installation !
        }

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            //code to upgrade to 1.0.1
            //no changes
        }

        $setup->endSetup();
    }
}