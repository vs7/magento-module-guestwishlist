<?php

class VS7_GuestWishlist_Model_Resource_Wishlist_Collection extends Mage_Wishlist_Model_Resource_Item_Collection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('vs7_guestwishlist/wishlist');
    }

    protected function _assignOptions()
    {
        $itemIds = array_keys($this->_items);
        /* @var $optionCollection VS7_GuestWishlist_Model_Resource_Wishlist_Option_Collection */
        $optionCollection = Mage::getModel('vs7_guestwishlist/wishlist_option')->getCollection();
        $optionCollection->addItemFilter($itemIds);

        /* @var $item VS7_GuestWishlist_Model_Wishlist */
        foreach ($this as $item) {
            $item->setOptions($optionCollection->getOptionsByItem($item));
        }
        $productIds = $optionCollection->getProductIds();
        $this->_productIds = array_merge($this->_productIds, $productIds);

        return $this;
    }

}