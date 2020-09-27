<?php

class VS7_GuestWishlist_Block_Link extends Mage_Core_Block_Template
{
    public function addGuestWishlistLink()
    {
        /* @var $parentBlock Mage_Page_Block_Template_Links */
        $parentBlock = $this->getParentBlock();

        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $parentBlock->removeLinkBlock('wishlist_link');
            $parentBlock->addLink('My Wishlist', 'gwishlist', 'My Wishlist', true);
        }
        return $this;
    }
}