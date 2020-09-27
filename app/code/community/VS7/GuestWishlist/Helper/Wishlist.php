<?php

class VS7_GuestWishlist_Helper_Wishlist extends Mage_Core_Helper_Abstract
{
    public function getWishlistModel()
    {
        return Mage::registry('guest_wishlist');
    }

    public function getWishlistSession()
    {
        $session = Mage::getModel('core/session');
        return unserialize($session->getWishlist());
    }
}