<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SBluege
 * Date: 28.06.13
 * Time: 05:46
 * To change this template use File | Settings | File Templates.
 */

class CheckoutTest extends FrontendTestcase {

    protected function setUp()
    {
        parent::setUp();
        // this test needs to reset the Joomla session.
        // to avoid any errors we need to supress them here.
        //error_reporting(0);
        //JFactory::getSession()->restart();
        //error_reporting(E_ERROR | E_WARNING | E_PARSE);

    }

    public function testCheckout() {

        /**
         * @var EventgalleryLibraryManagerCart $cartMgr
         */
        $cartMgr = EventgalleryLibraryManagerCart::getInstance();

        // CREATE
        $cart = $cartMgr->getCart();
        $this->assertEmpty($cart->getLineItems());
        $cart->addItem("test","A_001_2013-03-17_IMG_1294.jpg","1");
        $this->assertNotEmpty($cart->getLineItems());

        /**
         * @var EventgalleryLibraryImagelineitem $lineitem
         */
        // add line item
        $lineitems = array_values($cart->getLineItems());
        $lineitem = $lineitems[0];

        $oldPrice = $lineitem->getPrice()->getAmount();
        $lineitem->setQuantity(10);
        $cartMgr->calculateCart();
        $newPrice = $oldPrice*10;
        $this->assertEquals($newPrice, $cart->getSubTotal()->getAmount());

        // clone line item
        $newLineitem = $cart->cloneLineItem($lineitem->getId());
        $this->assertEquals(2, $cart->getLineItemsCount());
        $this->assertEquals(11, $cart->getLineItemsTotalCount());

        // delete item
        $cart->deleteLineItem($newLineitem->getId());
        $this->assertEquals(1, $cart->getLineItemsCount());
        $this->assertEquals(10, $cart->getLineItemsTotalCount());

        /**
         * @var EventgalleryLibraryFactoryShippingmethod $shippingMethodFactory
         */
        $shippingMethodFactory = EventgalleryLibraryFactoryShippingmethod::getInstance();

        /** @noinspection PhpParamsInspection */
        $cart->setShippingMethod($shippingMethodFactory->getDefaultMethod());
        $this->assertNotEmpty($cart->getShippingMethodServiceLineItem());

        /**
         * @var EventgalleryLibraryFactoryPaymentmethod $paymentMethodFactory
         */
        $paymentMethodFactory = EventgalleryLibraryFactoryPaymentmethod::getInstance();
        /** @noinspection PhpParamsInspection */
        $cart->setPaymentMethod($paymentMethodFactory->getDefaultMethod());
        $this->assertNotEmpty($cart->getPaymentMethodServiceLineItem());

        /**
         * @var EventgalleryLibraryFactoryAddress $addressFactory
         */
        $addressFactory = EventgalleryLibraryFactoryAddress::getInstance();
        $data = array (
            "firstname"=>"Peter",
            "lastname"=>"Mustermann",
            "address1"=>"Foostreet",
            "address2"=>"Barstreet",
            "address3"=>"12345678",
            "zip"=>"12345",
            "city"=>"Footown",
            "state"=>"Thüringen",
            "country"=>"Barland"
        );

        $address = $addressFactory->createStaticAddress($data,'');
        $cart->setBillingAddress($address);
        $cart->setShippingAddress($address);

        $message = "foo bar my message ist this. Can you send this?";
        $cart->setMessage($message);

        $email = "svenbluege+eventgallerytest@gmail.com";
        $cart->setEMail($email);

        $phone = "0049 12345 4567";
        $cart->setPhone($phone);

        $cartMgr->calculateCart();

        /**
         * @var EventgalleryLibraryManagerOrder $orderMgr
         */
        $orderMgr = EventgalleryLibraryManagerOrder::getInstance();
        $order = $orderMgr->createOrder($cart);
        $this->assertEquals(1, $order->getLineItemsCount());
        $this->assertEquals(10, $order->getLineItemsTotalCount());
        $this->assertNotEmpty($order->getShippingMethod());
        $this->assertNotEmpty($order->getPaymentMethod());
        $this->assertNotEmpty($order->getShippingAddress());
        $this->assertNotEmpty($order->getBillingAddress());

        $this->assertEquals('Peter', $order->getBillingAddress()->getFirstName());
        $this->assertEquals('Mustermann', $order->getBillingAddress()->getLastName());
        $this->assertEquals('Foostreet', $order->getBillingAddress()->getAddress1());
        $this->assertEquals('Barstreet', $order->getBillingAddress()->getAddress2());
        $this->assertEquals('12345678', $order->getBillingAddress()->getAddress3());
        $this->assertEquals('12345', $order->getBillingAddress()->getZip());
        $this->assertEquals('Footown', $order->getBillingAddress()->getCity());
        $this->assertEquals('Thüringen', $order->getBillingAddress()->getState());
        $this->assertEquals('Barland', $order->getBillingAddress()->getCountry());

        $this->assertEquals($email, $order->getEMail());
        $this->assertEquals($message, $order->getMessage());
        $this->assertEquals($phone, $order->getPhone());

        $total = $cart->getTotal()->getAmount();
        $subtotal = $cart->getSubTotal()->getAmount();
        $this->assertEquals($total, $order->getTotal()->getAmount());
        $this->assertEquals($subtotal, $order->getSubTotal()->getAmount());

        $this->assertEquals($cart->getShippingMethod()->getPrice()->getAmount(), $order->getShippingMethodServiceLineItem()->getPrice()->getAmount());
        $this->assertEquals($cart->getPaymentMethod()->getPrice()->getAmount(), $order->getPaymentMethodServiceLineItem()->getPrice()->getAmount());
        if ($cart->getSurcharge()) {
            $this->assertEquals($cart->getSurcharge()->getPrice()->getAmount(), $order->getSurchargeServiceLineItem()->getPrice()->getAmount());
            $manualTotal = $order->getSubTotal()->getAmount()+$order->getShippingMethodServiceLineItem()->getPrice()->getAmount()+$order->getSurchargeServiceLineItem()->getPrice()->getAmount()+$order->getPaymentMethodServiceLineItem()->getPrice()->getAmount();
        } else {
            $manualTotal = $order->getSubTotal()->getAmount()+$order->getShippingMethodServiceLineItem()->getPrice()->getAmount()+$order->getPaymentMethodServiceLineItem()->getPrice()->getAmount();
        }

        $this->assertEquals($manualTotal, $order->getTotal()->getAmount());


        // move to history
        $cart->setStatus(1);
        $this->assertEquals(1, $cart->getStatus());

        // Load the front end language
        $language = JFactory::getLanguage();
        $language->load('com_eventgallery' , JPATH_SITE.DIRECTORY_SEPARATOR.'components/com_eventgallery', $language->getTag(), true);
        $language->load('com_eventgallery' , JPATH_SITE.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.'overrides', $language->getTag(), true, false);

        /**
         * @var EventgalleryLibraryManagerEmailtemplate $emailtemplateMgr
         */
        $emailtemplateMgr = EventgalleryLibraryManagerEmailtemplate::getInstance();

        $data = Array();
        $data['disclaimer'] = "disclaimer";
        $data['order'] = $emailtemplateMgr->createOrderData($order);

        $data = json_decode(json_encode($data), FALSE);

        $language = JFactory::getLanguage();
        $to = Array("svenbluege-unittest@gmail.com", $order->getBillingAddress()->getFirstName().' '.$order->getBillingAddress()->getLastName());
        $emailtemplateMgr->sendMail('new_order', $language->getTag(), true, $data, $to, true);

        /**
         * @var EventgalleryLibraryFactoryOrderstatus $orderstatusFactory
         */
        $orderstatusFactory = EventgalleryLibraryFactoryOrderstatus::getInstance();
        $order->setPaymentStatus($orderstatusFactory->getOrderStatusById(EventgalleryLibraryOrderstatus::TYPE_PAYMENT_PAYED));

        // some additional tests:

        $status = $order->getPaymentStatus();
        $this->assertNotNull($status);

        $status = $order->getShippingStatus();
        $this->assertNotNull($status);

        $status = $order->getOrderStatus();
        $this->assertNotNull($status);

    }

    public function testMethodes() {
        /**
         * @var EventgalleryLibraryManagerCart $cartMgr
         */
        $cartMgr = EventgalleryLibraryManagerCart::getInstance();

        // CREATE
        $cart = $cartMgr->getCart();
        $this->assertEmpty($cart->getLineItems());
        $cart->addItem("test","A_001_2013-03-17_IMG_1294.jpg","1");
        $this->assertNotEmpty($cart->getLineItems());

        /**
         * @var EventgalleryLibraryFactoryShippingmethod $shippingMethodFactory
         */
        $shippingMethodFactory = EventgalleryLibraryFactoryShippingmethod::getInstance();
        $methods = $shippingMethodFactory->getMethods();
        foreach ($methods as $method) {
            /**
             * @var EventgalleryLibraryInterfaceMethod $method
             */
            $method->isEligible($cart);
        }

        /**
         * @var EventgalleryLibraryFactoryPaymentmethod $paymentMethodFactory
         */
        $paymentMethodFactory = EventgalleryLibraryFactoryPaymentmethod::getInstance();
        $methods = $paymentMethodFactory->getMethods();
        foreach ($methods as $method) {
            /**
             * @var EventgalleryLibraryInterfaceMethod $method
             */
            $method->isEligible($cart);
        }

        /**
         * @var EventgalleryLibraryFactorySurcharge $surchargeFactory
         */
        $surchargeFactory = EventgalleryLibraryFactorySurcharge::getInstance();
        $methods = $surchargeFactory->getMethods();
        foreach ($methods as $method) {
            /**
             * @var EventgalleryLibraryInterfaceMethod $method
             */
            $method->isEligible($cart);
        }


    }
}
