<?php
/**
 * Created by PhpStorm.
 * User: Sven
 * Date: 29.10.2014
 * Time: 14:36
 */

class EventgalleryLibraryFolderTest extends PHPUnit_Framework_TestCase {

    public function testLocalFolder() {
        /**
         * @var EventgalleryLibraryFactoryFolder $folderFactory
         */
        $folderFactory = EventgalleryLibraryFactoryFolder::getInstance();
        $folder = $folderFactory->getFolder("test");

        $this->assertNotNull($folder);
        $this->assertEquals("test", $folder->getFolderName());

        $folderType = $folder->getFolderType();
        $this->assertNotNull($folderType);

        $imagetypeset = $folder->getImageTypeSet();
        $this->assertNotNull($imagetypeset);

        $filecount = $folder->getFileCount();
        $this->assertGreaterThan(10, $filecount);

        $files = $folder->getFiles();
        $this->assertGreaterThan(10, count($files));

    }

    public function testPicasaFolder() {
        /**
         * @var EventgalleryLibraryFactoryFolder $folderFactory
         */
        $folderFactory = EventgalleryLibraryFactoryFolder::getInstance();
        $folder = $folderFactory->getFolder("103855497268910100628@Maximilian5Jahr");

        $this->assertNotNull($folder);
        $this->assertEquals("103855497268910100628@Maximilian5Jahr", $folder->getFolderName());

        $folderType = $folder->getFolderType();
        $this->assertNotNull($folderType);

        $imagetypeset = $folder->getImageTypeSet();
        $this->assertNotNull($imagetypeset);

        $filecount = $folder->getFileCount();
        $this->assertGreaterThan(10, $filecount);

        $files = $folder->getFiles();
        $this->assertGreaterThan(10, count($files));

    }

}
 