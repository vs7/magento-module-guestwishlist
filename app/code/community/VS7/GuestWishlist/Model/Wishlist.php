<?php

class VS7_GuestWishlist_Model_Wishlist extends Mage_Core_Model_Abstract
    implements Mage_Catalog_Model_Product_Configuration_Item_Interface
{

    protected $_store = null;
    protected $_options             = array();
    protected $_optionsByCode       = array();
    protected $_notRepresentOptions = array('info_buyRequest');
    protected $_flagOptionsSaved = null;

    protected function _construct()
    {
        $this->_init('vs7_guestwishlist/wishlist');
    }

    public function addNewItem($product, $buyRequest)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $productId = $product->getId();
            // Maybe force some store by wishlist internal properties
            $storeId = $product->getStoreId();
        } else {
            $productId = (int) $product;
            if ($buyRequest->getStoreId()) {
                $storeId = $buyRequest->getStoreId();
            } else {
                $storeId = Mage::app()->getStore()->getId();
            }
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId($storeId)
            ->load($productId);

        if ($buyRequest instanceof Varien_Object) {
            $_buyRequest = $buyRequest;
        } elseif (is_string($buyRequest)) {
            $_buyRequest = new Varien_Object(unserialize($buyRequest));
        } elseif (is_array($buyRequest)) {
            $_buyRequest = new Varien_Object($buyRequest);
        } else {
            $_buyRequest = new Varien_Object();
        }

        $cartCandidates = $product->getTypeInstance(true)
            ->processConfiguration($_buyRequest, $product);

        if (is_string($cartCandidates)) {
            return $cartCandidates;
        }

        if (!is_array($cartCandidates)) {
            $cartCandidates = array($cartCandidates);
        }

        $errors = array();
        $items = array();

        foreach ($cartCandidates as $candidate) {
            if ($candidate->getParentProductId()) {
                continue;
            }
            $qty = $candidate->getQty() ? $candidate->getQty() : 1;
            $item = $this->_addCatalogProduct($candidate, $qty);
            $items[] = $item;

            if ($item->getHasError()) {
                $errors[] = $item->getMessage();
            }
        }

        Mage::dispatchEvent('wishlist_product_add_after', array('items' => $items));

        return $item;
    }

    protected function _addCatalogProduct($product, $qty = 1)
    {
        $item = null;
        foreach ($this->getItemCollection() as $_item) {
            if ($_item->representProduct($product)) {
                $item = $_item;
                break;
            }
        }

        if ($item === null) {
            $storeId = $this->getStore()->getId();
            $visitorId = Mage::getSingleton('log/visitor')->getId();
            $this->setProductId($product->getId())
                ->setStoreId($storeId)
                ->setOptions($product->getCustomOptions())
                ->setVisitorId($visitorId)
                ->setQty($qty);
            $this->save();

            Mage::dispatchEvent('wishlist_item_add_after', array('wishlist' => $this));
        } else {
            $qty = $item->getQty() + $qty;
            $item->setQty($qty)
                ->save();
        }

        return $item ? $item : $this;
    }

    public function getItemCollection()
    {
        $visitorId = Mage::getSingleton('log/visitor')->getId();
        $collection = $this->getCollection()
            ->addFieldToFilter('visitor_id', $visitorId);
        return $collection;
    }

    public function getStore()
    {
        if (is_null($this->_store)) {
            $this->setStore(Mage::app()->getStore());
        }
        return $this->_store;
    }

    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    public function setOptions($options)
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
        return $this;
    }

    /**
     * Add option to item
     *
     * @param   VS7_GuestWishlist_Model_Wishlist_Option $option
     * @return  VS7_GuestWishlist_Model_Wishlist
     */
    public function addOption($option)
    {
        if (is_array($option)) {
            $option = Mage::getModel('vs7_guestwishlist/wishlist_option')->setData($option)
                ->setItem($this);
        } else if ($option instanceof VS7_GuestWishlist_Model_Wishlist_Option) {
            $option->setItem($this);
        } else if ($option instanceof Varien_Object) {
            $option = Mage::getModel('vs7_guestwishlist/wishlist_option')->setData($option->getData())
                ->setProduct($option->getProduct())
                ->setItem($this);
        } else {
            Mage::throwException(Mage::helper('sales')->__('Invalid item option format.'));
        }

        $exOption = $this->getOptionByCode($option->getCode());
        if ($exOption) {
            $exOption->addData($option->getData());
        } else {
            $this->_addOptionCode($option);
            $this->_options[] = $option;
        }
        return $this;
    }

    public function getOptionByCode($code)
    {
        if (isset($this->_optionsByCode[$code]) && !$this->_optionsByCode[$code]->isDeleted()) {
            return $this->_optionsByCode[$code];
        }
        return null;
    }

    protected function _addOptionCode($option)
    {
        if (!isset($this->_optionsByCode[$option->getCode()])) {
            $this->_optionsByCode[$option->getCode()] = $option;
        }
        else {
            Mage::throwException(Mage::helper('sales')->__('An item option with code %s already exists.', $option->getCode()));
        }
        return $this;
    }

    protected function _afterSave()
    {
        $this->_saveItemOptions();
        return parent::_afterSave();
    }

    public function save()
    {
        $hasDataChanges = $this->hasDataChanges();
        $this->_flagOptionsSaved = false;

        parent::save();

        if ($hasDataChanges && !$this->_flagOptionsSaved) {
            $this->_saveItemOptions();
        }
    }

    protected function _saveItemOptions()
    {
        foreach ($this->_options as $index => $option) {
            if ($option->isDeleted()) {
                $option->delete();
                unset($this->_options[$index]);
                unset($this->_optionsByCode[$option->getCode()]);
            } else {
                $option->save();
            }
        }

        $this->_flagOptionsSaved = true;

        return $this;
    }

    public function representProduct($product)
    {
        $itemProduct = $this->getProduct();
        if ($itemProduct->getId() != $product->getId()) {
            return false;
        }

        $itemOptions    = $this->getOptionsByCode();
        $productOptions = $product->getCustomOptions();

        if(!$this->compareOptions($itemOptions, $productOptions)){
            return false;
        }
        if(!$this->compareOptions($productOptions, $itemOptions)){
            return false;
        }
        return true;
    }

    public function getProduct()
    {
        $product = $this->_getData('product');
        if (is_null($product)) {
            if (!$this->getProductId()) {
                throw new Mage_Core_Exception(Mage::helper('wishlist')->__('Cannot specify product.'),
                    self::EXCEPTION_CODE_NOT_SPECIFIED_PRODUCT);
            }

            $product = Mage::getModel('catalog/product')
                ->setStoreId($this->getStoreId())
                ->load($this->getProductId());

            $this->setData('product', $product);
        }

        $product->setFinalPrice(null);
        $product->setCustomOptions($this->_optionsByCode);
        return $product;
    }

    public function getOptionsByCode()
    {
        return $this->_optionsByCode;
    }

    public function compareOptions($options1, $options2)
    {
        foreach ($options1 as $option) {
            $code = $option->getCode();
            if (in_array($code, $this->_notRepresentOptions )) {
                continue;
            }
            if ( !isset($options2[$code])
                || ($options2[$code]->getValue() === null)
                || $options2[$code]->getValue() != $option->getValue()) {
                return false;
            }
        }
        return true;
    }

    public function getFileDownloadParams() {}
}