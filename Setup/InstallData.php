<?php
/**
 * Created by PhpStorm.
 * User: Igor
 * Date: 15.10.2016
 * Time: 19:59
*/

namespace  Byjuno\ByjunoCore\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $setup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Sales\Model\Order::ENTITY,
            'byjuno_status',
            [
                'type' => 'string',
                'backend' => '',
                'frontend' => '',
                'label' => 'Byjuno status',
                'input' => '',
                'class' => '',
                'source' => '',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Sales\Model\Order::ENTITY,
            'byjuno_credit_rating',
            [
                'type' => 'string',
                'backend' => '',
                'frontend' => '',
                'label' => 'Byjuno credit rating',
                'input' => '',
                'class' => '',
                'source' => '',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Sales\Model\Order::ENTITY,
            'byjuno_credit_level',
            [
                'type' => 'string',
                'backend' => '',
                'frontend' => '',
                'label' => 'Byjuno credit level',
                'input' => '',
                'class' => '',
                'source' => '',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Sales\Model\Order::ENTITY,
            'byjuno_payment_method',
            [
                'type' => 'string',
                'backend' => '',
                'frontend' => '',
                'label' => 'Byjuno payment method',
                'input' => '',
                'class' => '',
                'source' => '',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        $data = [];
        $dataSate = [];
        $statuses = [
            'pending_byjuno' => Array( "name" => __('Byjuno wait for payment'), 'is_default' => 1, "visible_on_front" => 1, "state" => "new"),
            'pending_byjuno_payment' => Array( "name" =>__('Byjuno S2 confirmed'), 'is_default' => 0, "visible_on_front" => 1, "state" => "pending_payment"),
            'byjuno_confirmed'  => Array( "name" =>__('Byjuno S3 confirmed'), 'is_default' => 0, "visible_on_front" => 1, "state" => "processing"),
        ];
        foreach ($statuses as $code => $info) {
            $data[] = ['status' => $code, 'label' => $info["name"]];
            $dataSate[] = ['status' => $code, 'state' => $info["state"], 'is_default' => $info["is_default"], 'visible_on_front' => $info["visible_on_front"]];
        }
        $setup->getConnection()
            ->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $data);

        $setup->getConnection()
            ->insertArray($setup->getTable('sales_order_status_state'), ['status', 'state', 'is_default', 'visible_on_front'], $dataSate);

        $setup->endSetup();
    }
}