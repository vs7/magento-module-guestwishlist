<?php

/* @var $this VS7_GuestWishlist_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table=$installer->getConnection()
    ->newTable($installer->getTable('vs7_guestwishlist'))
    ->addColumn('guest_wishlist_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'nullable' => false,
        'identity' => true,
        'primary' => true
    ), 'Guest Wishlist Item ID')
    ->addColumn('visitor_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'default' => 0,
        'nullable' => false
    ), 'Visitor ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => 0
    ), 'Product ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
        'unsigned' => true
    ), 'Store ID')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, null, array(
        'precision' => 12,
        'scale' => 4,
        'nullable' => false
    ), 'Qty')
    ->addColumn('options', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(), 'Options value')
    ->addForeignKey($installer->getFkName('vs7_wishlist_guest', 'product_id', 'catalog/product', 'entity_id'),
        'product_id', $installer->getTable('catalog/product'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('vs7_wishlist_guest', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addIndex($installer->getIdxName('vs7_wishlist_guest', 'visitor_id'), 'visitor_id')
    ->addIndex($installer->getIdxName('vs7_wishlist_guest', 'product_id'), 'product_id')
    ->addIndex($installer->getIdxName('vs7_wishlist_guest', 'store_id'), 'store_id');

$installer->getConnection()->createTable($table);
$installer->endSetup();