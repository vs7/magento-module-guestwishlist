<?php

class VS7_GuestWishlist_IndexController extends Mage_Core_Controller_Front_Action
{
    protected function _initWishlist()
    {
        if ($gWishlist = Mage::registry('guest_wishlist')) {
            return $gWishlist;
        }
        $gWishlist = Mage::getModel('vs7_guestwishlist/wishlist');
        Mage::register('guest_wishlist', $gWishlist);
        return $gWishlist;
    }

    public function addAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirectReferer();
        }
        $this->_addItemToWishList();
        $this->_redirectReferer();
    }

    protected function _addItemToWishList()
    {
        $session = Mage::getModel('core/session');
        $postData = $this->getRequest()->getPost();

        if (empty($postData)) {
            $this->_redirectReferer();
        }

        $qty = $postData['qty'];
        $productId = $postData['product'];
        $product = Mage::getModel('catalog/product')->load($productId);
        $storeId = Mage::app()->getStore(true)->getId();
        $visitorId = Mage::getSingleton('log/visitor')->getId();

        if ($qty == '') {
            $qty = $product->getStockItem()->getMinSaleQty();
        }

        $wishlist = $this->_initWishlist();

        $wishlistItem['product_id'] = $productId;
        $wishlistItem['qty'] = $qty;
        $wishlistItem['options'] = serialize($postData);
        $wishlistItem['store_id'] = $storeId;
        $wishlistItem['visitor_id'] = $visitorId;

        try {
            $buyRequest = new Varien_Object($postData);

            $result = $wishlist->addNewItem($product, $buyRequest);

            if (is_string($result)) {
                Mage::throwException($result);
            }

            $wishlistItemId = $wishlist->getId();

            $product_info = $session->hasWishlist() ? unserialize($session->getWishlist()) : array();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist' => $wishlist,
                    'product' => $product,
                    'item' => $result
                )
            );

            $referer = $this->_getRefererUrl();

            $session->setAddActionReferer($referer);

            $message = $this->__('%1$s has been added to your guest wishlist. Click <a href="%2$s">here</a> to continue shopping.',
                $product->getName(), Mage::helper('core')->escapeUrl($referer));
            $session->addSuccess($message);

        } catch (Mage_Core_Exception $e) {
            $session->addError($this->__('An error occurred while adding item to guest wishlist: %s', $e->getMessage()));
        }
        catch (Exception $e) {
            $session->addError($this->__('An error occurred while adding item to guest wishlist.'));
        }


        $this->_redirect('*');
    }
}