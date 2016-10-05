<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SBluege
 * Date: 28.06.13
 * Time: 05:26
 * To change this template use File | Settings | File Templates.
 */

class Test extends FrontendTestcase {

    public function testAdd2Cart() {

        $controller = new EventgalleryControllerRest();
        $_SERVER['REQUEST_METHOD']='POST';
        $_SERVER['HTTP_HOST']='localhost';
        JRequest::setVar('folder', 'test');
        JRequest::setVar('file', 'A_001_2013-03-17_IMG_1294.jpg');
        JRequest::setVar('quantity', '1');
        // commented out since the types are not longer fixed
        #JRequest::setVar('imagetypeid', '4');
        JRequest::setVar('methode', 'POST');


        /**
         * @var EventgalleryLibraryManagerCart $cartMgr
         */
        $cartMgr = EventgalleryLibraryManagerCart::getInstance();

        // Create new Cart
        $cart = $cartMgr->getCart();
        $cart->setStatus(1);
        $cart = $cartMgr->getCart();

        $this->assertEmpty($cart->getLineItems());

        ob_start();
        $controller->add2cart();
        ob_clean();

        $this->assertNotEmpty($cart->getLineItems());
    }

    public function testDeleteItem() {

        $controller = new EventgalleryControllerRest();
        $_SERVER['REQUEST_METHOD']='POST';
        $_SERVER['HTTP_HOST']='localhost';

        /**
         * @var EventgalleryLibraryManagerCart $cartMgr
         */
        $cartMgr = EventgalleryLibraryManagerCart::getInstance();

        // CREATE
        $cart = $cartMgr->getCart();

        $this->assertNotEmpty($cart->getLineItems());
        $lineitems = $cart->getLineItems();

        /**
         * @var EventgalleryLibraryLineitem $lineitem
         */
        $lineitem = $lineitems[0];
        JRequest::setVar('lineitemid', $lineitem->getId());
        ob_start();
        $controller->removeFromCart();
        ob_clean();

        $this->assertEmpty($cart->getLineItems());
    }

    public function testGetCart() {
        $controller = new EventgalleryControllerRest();
        ob_start();
        $controller->getCart();
        ob_clean();
    }

}
