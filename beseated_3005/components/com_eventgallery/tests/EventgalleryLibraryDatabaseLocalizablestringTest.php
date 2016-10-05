<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SBluege
 * Date: 26.06.13
 * Time: 19:39
 * To change this template use File | Settings | File Templates.
 */

class EventgalleryLibraryDatabaseLocalizablestringTest extends PHPUnit_Framework_TestCase {

    protected $testdata = array("de_DE"=>'deutsch', "en_US"=>"englisch", "en_GB"=>"britisch");

    protected function setUp()
    {
        parent::setUp();

    }

    public function testJson() {

        $ls = new EventgalleryLibraryDatabaseLocalizablestring(json_encode($this->testdata));

        $this->assertEquals('deutsch', $ls->get("de_DE"));
        $this->assertEquals('englisch', $ls->get("en_US"));
        $this->assertEquals('britisch', $ls->get("en_GB"));
        #$this->assertEquals($this->testdata, $ls->get(null));
    }

    public function testPlainText() {

        $testdata = "foobar is foobar.";

        $ls = new EventgalleryLibraryDatabaseLocalizablestring($testdata);

        $this->assertEquals($testdata, $ls->get("de_DE"));
        $this->assertEquals($testdata, $ls->get("en_US"));
        $this->assertEquals($testdata, $ls->get("en_GB"));
        $this->assertEquals($testdata, $ls->get(null));
    }

    public function testGetEncodedString() {


        $ls = new EventgalleryLibraryDatabaseLocalizablestring(json_encode($this->testdata));

        $this->assertEquals($ls->getEncodedString(), json_encode($this->testdata));
    }

    public function testSetGet() {

        $ls = new EventgalleryLibraryDatabaseLocalizablestring(json_encode($this->testdata));

        $ls->set("fe_DE", "foobar");
        $this->assertEquals('foobar', $ls->get("fe_DE"));

        $ls->set("fe_DE", null);
        $ls->set(null, "foobar");

    }






}
