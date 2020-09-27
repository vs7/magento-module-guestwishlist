<?php

class VS7_GuestWishlist_Model_Resource_Wishlist extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('vs7_guestwishlist/wishlist', 'guest_wishlist_item_id');
    }
}