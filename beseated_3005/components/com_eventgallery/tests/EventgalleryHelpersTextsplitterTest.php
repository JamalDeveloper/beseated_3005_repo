<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SBluege
 * Date: 25.08.13
 * Time: 08:13
 * To change this template use File | Settings | File Templates.
 */

class EventgalleryHelpersTextsplitterTest extends PHPUnit_Framework_TestCase {

    public function testSplit() {
        $string ='foo<hr id="system-readmore">bar';
        $result = EventgalleryHelpersTextsplitter::split($string);

        $this->assertEquals("foo", $result->introtext, 'Intro text does not match');
        $this->assertEquals("bar", $result->fulltext, 'Full text does not match');


        $string ='<hr id="system-readmore">bar';
        $result = EventgalleryHelpersTextsplitter::split($string);

        $this->assertEquals("", $result->introtext, 'Intro text does not match');
        $this->assertEquals("bar", $result->fulltext, 'Full text does not match');

        $string ='foo<hr id="system-readmore">';
        $result = EventgalleryHelpersTextsplitter::split($string);

        $this->assertEquals("foo", $result->introtext, 'Intro text does not match');
        $this->assertEquals("", $result->fulltext, 'Full text does not match');

        $string ='foo bar';
        $result = EventgalleryHelpersTextsplitter::split($string);

        $this->assertEquals("foo bar", $result->introtext, 'Intro text does not match');
        $this->assertEquals("foo bar", $result->fulltext, 'Full text does not match');
    }


}
