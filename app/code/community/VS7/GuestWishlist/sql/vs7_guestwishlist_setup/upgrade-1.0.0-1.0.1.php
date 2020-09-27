<?php

/* @var $this VS7_GuestWishlist_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->dropColumn($installer->getTable('vs7_guestwishlist'), 'options');

$table = $installer->getConnection()
    ->newTable($installer->getTable('vs7_guestwishlist/wishlist_option'))
    ->addColumn('option_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'nullable' => false,
        'primary' => true,
        'identity' => true
    ), 'Option ID')
    ->addColumn('guest_wishlist_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'nullable' => false
    ), 'Guest Wishlist Item ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
    ), 'Product ID')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false
    ), 'Code')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(), 'Value')
    ->addForeignKey($installer->getFkName('vs7_guestwishlist/wishlist_option', 'guest_wishlist_item_id', 'vs7_guestwishlist', 'guest_wishlist_option_id'),
        'guest_wishlist_item_id', $installer->getTable('vs7_guestwishlist'), 'guest_wishlist_item_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->getConnection()->createTable($table);
$installer->endSetup();