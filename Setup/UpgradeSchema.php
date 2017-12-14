<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var ProductMetadataInterface $productMetadata
     */
    public $productMetadata;

    public function __construct(
        ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        // update ptoduct type
        $installer->run("UPDATE IGNORE `{$installer->getTable('catalog_product_entity')}` SET `type_id` = 'mageworx_giftcards' WHERE `type_id` LIKE 'giftcards'");

        // update config path
        $installer->run("UPDATE IGNORE `{$installer->getTable('core_config_data')}` SET `path` = REPLACE(`path`,'mageworx_giftcards/main/','mageworx_giftcards/mageworx_default/') WHERE `path` LIKE 'mageworx_giftcards/main/%'");
        $installer->run("UPDATE IGNORE `{$installer->getTable('core_config_data')}` SET `path` = REPLACE(`path`,'mageworx_giftcards/email/','mageworx_giftcards/mageworx_email/') WHERE `path` LIKE 'mageworx_giftcards/email/%'");

        $installer->getConnection()
            ->addColumn(
                $installer->getTable('quote'),
                'mageworx_giftcards_description',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'MageWorx Gift Card Description',
                ]
            );

        $installer->getConnection()
            ->addColumn(
                $installer->getTable('quote'),
                'mageworx_giftcards_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => "12,4",
                    'comment' => 'MageWorx Gift Card Discount Amount',
                ]
            );

        $installer->getConnection()
            ->addColumn(
                $installer->getTable('quote'),
                'base_mageworx_giftcards_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => "12,4",
                    'comment' => 'MageWorx Gift Card Base Discount Amount',
                ]
            );

        $installer->getConnection()
            ->addColumn(
                $installer->getTable('sales_order'),
                'mageworx_giftcards_description',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'MageWorx Gift Card Description',
                ]
            );

        $installer->getConnection()
            ->addColumn(
                $installer->getTable('sales_order'),
                'mageworx_giftcards_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => "12,4",
                    'comment' => 'MageWorx Gift Card Discount Amount',
                ]
            );

        $installer->getConnection()
            ->addColumn(
                $installer->getTable('sales_order'),
                'base_mageworx_giftcards_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => "12,4",
                    'comment' => 'MageWorx Gift Card Base Discount Amount',
                ]
            );

        if (version_compare($context->getVersion(), '2.1.1') < 0) {
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('mageworx_giftcards_card'),
                    'storeview_ids',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' => 'Store views to allow using the gift card code on',
                    ]
                );
        }

        if (version_compare($context->getVersion(), '2.1.3') < 0) {
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('mageworx_giftcards_card'),
                    'lifetime_days',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => 5,
                        'comment' => 'Card lifetime in days',
                    ]
                );
        }

        if (version_compare($context->getVersion(), '2.1.6') < 0) {
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('mageworx_giftcards_card'),
                    'ignore_default_lifetime',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                        'comment' => 'Ignore default card lifetime',
                    ]
                );
        }

        if (version_compare($context->getVersion(), '2.1.7') < 0) {
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('mageworx_giftcards_card'),
                    'mageworx_gc_customer_groups',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'comment' => 'Available for Customer Groups',
                    ]
                );
        }

        if (version_compare($context->getVersion(), '2.1.8') < 0) {
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('mageworx_giftcards_card'),
                    'lifetime_days',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => 5,
                        'comment' => 'Card lifetime in days',
                    ]
                );

            $installer->getConnection()
                ->dropColumn(
                    $installer->getTable('mageworx_giftcards_card'),
                    'ignore_default_lifetime'
                );

            $installer->getConnection()
                ->dropColumn(
                    $installer->getTable('mageworx_giftcards_card'),
                    'lifetime_days'
                );

            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('mageworx_giftcards_card'),
                    'expire_date',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                        'comment' => 'Expire Date',
                    ]
                );

            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('mageworx_giftcards_card'),
                    'expired_email_send',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                        'comment' => 'Is Expired Email Send',
                        'default' => '0'
                    ]
                );

            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('mageworx_giftcards_card'),
                    'expiration_alert_email_send',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                        'comment' => 'Is Expiration Alert Email Send',
                        'default' => '0'
                    ]
                );

            $installer->getConnection()
                ->dropColumn(
                    $installer->getTable('mageworx_giftcards_card'),
                    'storeview_ids'
                );

            $installer->getConnection()
                ->dropColumn(
                    $installer->getTable('mageworx_giftcards_card'),
                    'mageworx_gc_customer_groups'
                );

            $storeTable = $installer->getTable('store');
            $customerGroupsTable = $installer->getTable('customer_group');
            $giftcardsStoresTable = $installer->getTable('mageworx_giftcards_store');
            $giftcardsCustomerGroupsTable = $installer->getTable('mageworx_giftcards_customer_group');

            /**
             * Create table 'mageworx_giftcards_store' if not exists. This table will be used instead of
             * column storeview_ids of main giftcards table
             */
            $table = $installer->getConnection()->newTable(
                $giftcardsStoresTable
            )->addColumn(
                'card_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Card ID'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'Store ID'
            )->addForeignKey(
                $installer->getFkName(
                    'mageworx_giftcards_store',
                    'card_id',
                    'mageworx_giftcards_card',
                    'card_id'
                ),
                'card_id',
                $installer->getTable('mageworx_giftcards_card'),
                'card_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'mageworx_giftcards_store',
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $storeTable,
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'MageWorx Giftcards To Stores Relations'
            );

            $installer->getConnection()->createTable($table);

            /**
             * Create table 'mageworx_giftcards_customer_group' if not exists. This table will be used instead of
             * column mageworx_gc_customer_groups of main giftcards table
             */

            $type = version_compare($this->getMagentoVersion(), '2.2.0-dev') < 0 ?
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT : \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER;

            $table = $installer->getConnection()->newTable(
                $giftcardsCustomerGroupsTable
            )->addColumn(
                'card_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Card Id'
            )->addColumn(
                'customer_group_id',
                $type,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )->addIndex(
                $installer->getIdxName('mageworx_giftcards_customer_group', ['customer_group_id']),
                ['customer_group_id']
            )->addForeignKey(
                $installer->getFkName(
                    'mageworx_giftcards_customer_group',
                    'card_id',
                    'mageworx_giftcards',
                    'card_id'
                ),
                'card_id',
                $installer->getTable('mageworx_giftcards_card'),
                'card_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'mageworx_giftcards_customer_group',
                    'customer_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'customer_group_id',
                $customerGroupsTable,
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'MageWorx Giftcards To Customer Groups Relations'
            );

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }

    /**
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
}
