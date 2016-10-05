<?php
/**
 * Created by PhpStorm.
 * User: Sven
 * Date: 07.11.2014
 * Time: 10:35
 */

class FrontendTestcase extends PHPUnit_Framework_TestCase
{

    /**
     * @var JApplicationSite
     */
    protected $app;

    protected function setUp()
    {
        $this->app = JFactory::getApplication('site');
        $this->app->initialise();
    }
}