<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SBluege
 * Date: 25.08.13
 * Time: 13:25
 * To change this template use File | Settings | File Templates.
 */

class EventgalleryHelpersSizesetTest extends PHPUnit_Framework_TestCase {

    public function testGetMatchingSize() {

        $helper = new EventgalleryHelpersSizeset();


        // min
        $this->assertEquals(32, $helper->getMatchingSize(1));
        $this->assertEquals(32, $helper->getMatchingSize(32));

        //max
        $this->assertEquals(1440, $helper->getMatchingSize(3200));

        //random values
        $this->assertEquals(1280, $helper->getMatchingSize(1200));
        $this->assertEquals(110, $helper->getMatchingSize(105));
        $this->assertEquals(220, $helper->getMatchingSize(219));


    }

}
