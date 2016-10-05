<?php
/**
 * Created by PhpStorm.
 * User: Sven
 * Date: 07.11.2014
 * Time: 09:35
 */

class TrackorderTest extends FrontendTestcase {

    /**
     * @var EventgalleryControllerEvents
     */
    private $controller;

    /**
     * Clear the cache
     */
    protected function setUp() {
        parent::setUp();
        JRequest::setVar('option', 'com_eventgallery');
        JRequest::setVar('view', 'trackorder');
        $this->controller = new EventgalleryControllerTrackorder();
        $this->controller->resetViewCache();
    }

    public function testDefaultPage() {
        ob_start();
        $this->controller->display();
        $content = ob_get_contents();
        ob_clean();

        $this->assertContains('class="eventgallery-track-my-order"', $content);

    }

    public function testAnonymousOrderPage() {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        $query->select('*')->from('#__eventgallery_order')->order('documentno desc');

        $db->setQuery($query);
        $orderRow = $db->loadObject();

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->app->input->set('orderid', $orderRow->documentno);
        $this->app->input->set('email', $orderRow->email);
        $this->app->input->set('task', 'order');
        JFactory::getApplication()->input->post->set(JSession::getFormToken(),'1');

        ob_start();
        $this->controller->order();
        $content = ob_get_contents();
        ob_clean();

        $this->assertContains('class="eventgallery-checkout"', $content);
        $this->assertContains($orderRow->email, $content);
    }

}