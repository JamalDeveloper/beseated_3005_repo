<?php
/**
 * Created by PhpStorm.
 * User: Sven
 * Date: 07.11.2014
 * Time: 09:35
 */

class SingleimageTest extends FrontendTestcase {
    /**
     * @var JControllerLegacy
     */
    private $singleimageController;

    /**
     * Clear the cache
     */
    protected function setUp() {
        parent::setUp();
        JRequest::setVar('option', 'com_eventgallery');
        JRequest::setVar('view', 'singleimage');
        JRequest::setVar('folder', 'test');
        JRequest::setVar('file', 'image.jpg');
        $this->singleimageController = new EventgalleryController();
        $this->app->getParams()->set('use_social_sharing_button', 1);
        $this->app->getParams()->set('use_social_sharing_facebook', 1);
        $this->app->getParams()->set('use_social_sharing_google', 1);
        $this->app->getParams()->set('use_social_sharing_twitter', 1);
        $this->app->getParams()->set('use_social_sharing_pinterest', 1);
        $this->app->getParams()->set('use_social_sharing_email', 1);
        $this->app->getParams()->set('use_social_sharing_download', 1);
        $this->singleimageController->resetViewCache();
    }

    public function testSingleImageDefault() {
        $this->app->input->set('layout', 'default');
        ob_start();
        $this->singleimageController->display();
        $content = ob_get_contents();
        ob_clean();

        $this->assertContains('id="singleimage"', $content);
        $this->assertContains('id="bigimagelink"', $content);
    }

    public function testSingleImageMinipage() {

        $this->app->input->set('layout', 'minipage');
        ob_start();
        $this->singleimageController->display();
        $content = ob_get_contents();
        ob_clean();

        $this->assertContains("<html>", $content);
        $this->assertContains('property="og:url"', $content);
    }

    public function testSingleImageShare() {

        $this->app->input->set('layout', 'share');
        ob_start();
        $this->singleimageController->display();
        $content = ob_get_contents();
        ob_clean();

        $this->assertContains("twitter.png", $content);
        $this->assertContains("facebook-post-image", $content);
    }

}