<?php

class VS7_GuestWishlist_Model_Resource_Wishlist_Option_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_optionsByItem    = array();
    protected $_optionsByProduct = array();

    protected function _construct()
    {
        parent::_construct();
        $this->_init('vs7_guestwishlist/wishlist_option');
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();

        foreach ($this as $option) {
            $optionId = $option->getId();
            $itemId = $option->getGuestWishlistItemId();
            $productId = $option->getProductId();
            if (isset($this->_optionsByItem[$itemId])) {
                $this->_optionsByItem[$itemId][] = $optionId;
            } else {
                $this->_optionsByItem[$itemId] = array($optionId);
            }
            if (isset($this->_optionsByProduct[$productId])) {
                $this->_optionsByProduct[$productId][] = $optionId;
            } else {
                $this->_optionsByProduct[$productId] = array($optionId);
            }
        }
    }

    public function addItemFilter($item)
    {
        if (empty($item)) {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
        } else if (is_array($item)) {
            $this->addFieldToFilter('guest_wishlist_item_id', array('in' => $item));
        } else if ($item instanceof VS7_GuestWishlist_Model_Wishlist) {
            $this->addFieldToFilter('guest_wishlist_item_id', $item->getId());
        } else {
            $this->addFieldToFilter('guest_wishlist_item_id', $item);
        }

        return $this;
    }

    public function getProductIds()
    {
        $this->load();

        return array_keys($this->_optionsByProduct);
    }

    public function getOptionsByItem($item)
    {
        if ($item instanceof VS7_GuestWishlist_Model_Wishlist) {
            $itemId = $item->getId();
        } else {
            $itemId = $item;
        }

        $this->load();

        $options = array();
        if (isset($this->_optionsByItem[$itemId])) {
            foreach ($this->_optionsByItem[$itemId] as $optionId) {
                $options[] = $this->_items[$optionId];
            }
        }

        return $options;
    }
}