<?php
/**
 * Created by PhpStorm.
 * User: Sven
 * Date: 07.11.2014
 * Time: 09:35
 */

class EventTest extends FrontendTestcase {

    /**
     * @var EventgalleryControllerEvent
     */
    private $eventController;

    /**
     * Clear the cache
     */
    protected function setUp() {
        parent::setUp();
        JRequest::setVar('option', 'com_eventgallery');
        JRequest::setVar('view', 'event');
        JRequest::setVar('folder', 'test');
        $this->eventController = new EventgalleryControllerEvent();
        $this->eventController->resetViewCache();


    }

    public function testEventsAjax() {
        $this->app->getParams()->set('event_layout', 'ajaxpaging');
        ob_start();
        $this->eventController->display();
        $content = ob_get_contents();
        ob_clean();

        $this->assertContains('class="ajaxpaging"', $content);
    }

    public function testEventsImagelist() {

        $this->app->getParams()->set('event_layout', 'imagelist');
        ob_start();
        $this->eventController->display();
        $content = ob_get_contents();
        ob_clean();

        $this->assertContains('class="event"', $content);
        $this->assertContains('class="eventgallery-thumbnails eventgallery-imagelist', $content);
    }

    public function testEventsPageableList() {

        $this->app->getParams()->set('event_layout', 'pageable');
        ob_start();
        $this->eventController->display();
        $content = ob_get_contents();
        ob_clean();

        $this->assertContains('class="event"', $content);
        $this->assertContains('class="eventgallery-thumbnails eventgallery-imagelist', $content);
    }

    public function testEventsSimple() {

        $this->app->getParams()->set('event_layout', 'simple');
        ob_start();
        $this->eventController->display();
        $content = ob_get_contents();
        ob_clean();

        $this->assertContains('id="event"', $content);
        $this->assertContains('class="eventgallery-simplelist', $content);
    }

    public function testEventsTiles() {

        $this->app->getParams()->set('event_layout', 'tiles');
        ob_start();
        $this->eventController->display();
        $content = ob_get_contents();
        ob_clean();

        $this->assertContains('id="event"', $content);
        $this->assertContains('class="eventgallery-tiles-list eventgallery-event-tiles-list"', $content);
    }

}