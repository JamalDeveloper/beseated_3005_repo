<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SBluege
 * Date: 25.08.13
 * Time: 13:50
 * To change this template use File | Settings | File Templates.
 */

class EventgalleryHelpersSizecalculatorTest extends PHPUnit_Framework_TestCase {

    public function testAdjustSize() {


        // landscape
        $helper = new EventgalleryHelpersSizecalculator(640, 480, 300, false);
        $this->assertEquals(320, $helper->getWidth());
        $this->assertEquals(240, $helper->getHeight());

        $helper = new EventgalleryHelpersSizecalculator(640, 480, 300, true);
        $this->assertEquals(320, $helper->getWidth());
        $this->assertEquals(320, $helper->getHeight());

        $helper = new EventgalleryHelpersSizecalculator(640, 480, 1, false);
        $this->assertEquals(32, $helper->getWidth());
        $this->assertEquals(24, $helper->getHeight());

        $helper = new EventgalleryHelpersSizecalculator(640, 480, 1280, false);
        $this->assertEquals(1280, $helper->getWidth());
        $this->assertEquals(960, $helper->getHeight());

        //portait

        $helper = new EventgalleryHelpersSizecalculator(480, 640, 300, false);
        $this->assertEquals(240, $helper->getWidth());
        $this->assertEquals(320, $helper->getHeight());

        $helper = new EventgalleryHelpersSizecalculator(480, 640, 300, true);
        $this->assertEquals(320, $helper->getWidth());
        $this->assertEquals(320, $helper->getHeight());

        $helper = new EventgalleryHelpersSizecalculator(480, 640, 1, false);
        $this->assertEquals(24, $helper->getWidth());
        $this->assertEquals(32, $helper->getHeight());

        $helper = new EventgalleryHelpersSizecalculator(480, 640, 1280, false);
        $this->assertEquals(960, $helper->getWidth());
        $this->assertEquals(1280, $helper->getHeight());

    }

}
