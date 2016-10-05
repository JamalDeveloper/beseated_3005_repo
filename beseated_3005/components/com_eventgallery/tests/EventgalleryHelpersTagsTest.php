<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SBluege
 * Date: 25.08.13
 * Time: 08:16
 * To change this template use File | Settings | File Templates.
 */

class EventgalleryHelpersTagsTest extends PHPUnit_Framework_TestCase {

    public function testSplitTags() {

        $tagsString = "foo, bar     test1, test2";
        $tags = EventgalleryHelpersTags::splitTags($tagsString);
        $this->assertCount(4, $tags, 'Number of tags does not match');

    }

    public function testCheckTags() {

        $eventTags = "foo";
        $needsTags = "foo";

        $this->assertTrue(EventgalleryHelpersTags::checkTags($eventTags, $needsTags));

        $eventTags = "foo,bar,test1,test3";
        $needsTags = "test5,test10";

        $this->assertFalse(EventgalleryHelpersTags::checkTags($eventTags, $needsTags));

        $needsTags = "test1, bar";

        $this->assertTrue(EventgalleryHelpersTags::checkTags($eventTags, $needsTags));

        $needsTags = "";

        $this->assertFalse(EventgalleryHelpersTags::checkTags($eventTags, $needsTags));


        $eventTags = "landscapes, landscapes_sunset";
        $needsTags = "landscapes_autumn";

        $this->assertFalse(EventgalleryHelpersTags::checkTags($eventTags, $needsTags));

        $eventTags = "landscapes_autumn";
        $needsTags = "landscapes, landscapes_sunset";

        $this->assertFalse(EventgalleryHelpersTags::checkTags($eventTags, $needsTags));

        $eventTags = "landscapes,   ,  ,    landscapes_autumn";
        $needsTags = "land,  , ,  land";

        $this->assertFalse(EventgalleryHelpersTags::checkTags($eventTags, $needsTags));
    }

}
