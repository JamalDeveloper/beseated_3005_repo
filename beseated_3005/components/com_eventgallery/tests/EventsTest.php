<?php
/**
 * Created by PhpStorm.
 * User: Sven
 * Date: 07.11.2014
 * Time: 09:35
 */

class EventsTest extends FrontendTestcase {

    /**
     * @var EventgalleryControllerEvents
     */
    private $eventsController;

    /**
     * Clear the cache
     */
    protected function setUp() {
        parent::setUp();
        JRequest::setVar('option', 'com_eventgallery');
        JRequest::setVar('view', 'events');
        $this->eventsController = new EventgalleryControllerEvents();
    }

    public function testEventsGrid() {

        $this->app->getParams()->set('events_layout', 'default');
        ob_start();
        $this->eventsController->display();
        $content = ob_get_contents();
        ob_clean();

        $this->assertContains('id="events"', $content);
        $this->assertContains('class="eventgallery-events-gridlist"', $content);
    }

    public function testEventsTiles() {

        $this->app->getParams()->set('events_layout', 'tiles');
        ob_start();
        $this->eventsController->display();
        $content = ob_get_contents();
        ob_clean();

        $this->assertContains('class="eventgallery-tiles-list eventgallery-events-tiles-list"', $content);
    }


    public function testEventsList() {

        $this->app->getParams()->set('events_layout', 'list');
        ob_start();
        $this->eventsController->display();
        $content = ob_get_contents();
        ob_clean();

        $this->assertContains('id="events"', $content);
        $this->assertContains('class="displayname"', $content);
    }

}