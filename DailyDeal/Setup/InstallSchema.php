<?php
namespace MW\DailyDeal\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'mw_dailydeal'
         */
        if (!$installer->getConnection()->isTableExists($installer->getTable('mw_dailydeal'))) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mw_dailydeal'))
                ->addColumn(
                    'dailydeal_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'Product Id'
                )
                ->addColumn(
                    'cur_product',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Product Name'
                )
                ->addColumn(
                    'product_sku',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'product sku'
                )
                ->addColumn(
                    'product_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    [],
                    'product price'
                )
                ->addColumn(
                    'discount',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true, 'default' => '0'],
                    'discount'
                )
                ->addColumn(
                    'discount_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'discount_type'
                )
                ->addColumn(
                    'start_date_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    [],
                    'start_date_time'
                )
                ->addColumn(
                    'end_date_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    [],
                    'To'
                )
                ->addColumn(
                    'dailydeal_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true, 'default' => '0'],
                    'dailydeal_price'
                )
                ->addColumn(
                    'deal_qty',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['default' => '0'],
                    'deal_qty'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['default' => '0'],
                    'status'
                )
                ->addColumn(
                    'description',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Description'
                )
                ->addColumn(
                    'website_ids',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'website_ids'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'website_id'
                )
                ->addColumn(
                    'store_ids',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'store_ids'
                )
                ->addColumn(
                    'customer_group_ids',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'customer_group_ids'
                )
                ->addColumn(
                    'promo',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'promo'
                )
                ->addColumn(
                    'featured',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['default' => '0'],
                    'featured'
                )
                ->addColumn(
                    'disableproduct',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['default' => '0'],
                    'disableproduct'
                )
                ->addColumn(
                    'requiredlogin',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['default' => '0'],
                    'requiredlogin'
                )
                ->addColumn(
                    'impression',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['default' => '0'],
                    'impression'
                )
                ->addColumn(
                    'sold_qty',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['default' => '0'],
                    'sold_qty'
                )
                ->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'order_id'
                )
                ->addColumn(
                    'store_view',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'store_view'
                )
                ->addColumn(
                    'limit_customer',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'limit_customer'
                )
                ->addColumn(
                    'disable_product_after_finish',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'disable_product_after_finish'
                )
                ->addColumn(
                    'expire',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['default' => '1'],
                    'expire'
                )
                ->addColumn(
                    'active',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Active'
                )
                ->addColumn(
                    'deal_scheduler_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['default' => 0],
                    'deal_scheduler_id'
                )
                ->addColumn(
                    'thread',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'thread'
                )
                ->setComment('dailydeal');

            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'mw_deal_scheduler'
         */
        if (!$installer->getConnection()->isTableExists($installer->getTable('mw_deal_scheduler'))) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mw_deal_scheduler'))
                ->addColumn(
                    'deal_scheduler_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'deal_scheduler_id'
                )
                ->addColumn(
                    'title',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'title'
                )
                ->addColumn(
                    'deal_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'deal_time'
                )
                ->addColumn(
                    'deal_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'deal_price'
                )
                ->addColumn(
                    'deal_qty',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'deal_qty'
                )
                ->addColumn(
                    'number_deal',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'number_deal'
                )
                ->addColumn(
                    'number_day',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'number_day'
                )
                ->addColumn(
                    'generate_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'generate_type'
                )
                ->addColumn(
                    'start_date_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    [],
                    'start_date_time'
                )
                ->addColumn(
                    'end_date_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    [],
                    'end_date_time'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    [],
                    'status'
                )
                ->setComment('mw_deal_scheduler');

            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'mw_deal_scheduler_product'
         */
        if (!$installer->getConnection()->isTableExists($installer->getTable('mw_deal_scheduler_product'))) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mw_deal_scheduler_product'))
                ->addColumn(
                    'dealschedulerproduct_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'dealschedulerproduct_id'
                )
                ->addColumn(
                    'deal_scheduler_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true],
                    'deal_scheduler_id'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true],
                    'product_id'
                )
                ->addColumn(
                    'deal_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'deal_time'
                )
                ->addColumn(
                    'deal_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    [],
                    'deal_price'
                )
                ->addColumn(
                    'product_deal_qty',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'product_deal_qty'
                )
                ->addColumn(
                    'deal_position',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'deal_position'
                )
                ->addIndex(
                    $installer->getIdxName('mw_deal_scheduler_product', ['deal_scheduler_id']),
                    ['deal_scheduler_id']
                )
                ->addIndex(
                    $installer->getIdxName('mw_deal_scheduler_product', ['product_id']),
                    ['product_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mw_deal_scheduler_product',
                        'deal_scheduler_id',
                        'mw_deal_scheduler',
                        'deal_scheduler_id'
                    ),
                    'deal_scheduler_id',
                    $installer->getTable('mw_deal_scheduler'),
                    'deal_scheduler_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mw_deal_scheduler_product',
                        'product_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('mw_deal_scheduler_product');

            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
