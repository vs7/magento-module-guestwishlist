<?php

class VS7_GuestWishlist_Model_Wishlist_Option extends Mage_Core_Model_Abstract
    implements Mage_Catalog_Model_Product_Configuration_Item_Option_Interface
{
    protected $_item;
    protected $_product;

    protected function _construct()
    {
        $this->_init('vs7_guestwishlist/wishlist_option');
    }

    protected function _hasModelChanged()
    {
        if (!$this->hasDataChanges()) {
            return false;
        }

        return $this->_getResource()->hasDataChanged($this);
    }

    public function setItem($item)
    {
        $this->setWishlistItemId($item->getId());
        $this->_item = $item;
        return $this;
    }

    public function getItem()
    {
        return $this->_item;
    }

    public function setProduct($product)
    {
        $this->setProductId($product->getId());
        $this->_product = $product;
        return $this;
    }

    public function getProduct()
    {
        return $this->_product;
    }

    public function getValue()
    {
        return $this->_getData('value');
    }

    protected function _beforeSave()
    {
        if ($this->getItem()) {
            $this->setGuestWishlistItemId($this->getItem()->getId());
        }
        return parent::_beforeSave();
    }

    public function __clone()
    {
        $this->setId(null);
        $this->_item = null;
        return $this;
    }
}