<?php

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * provides a base class for factories implementing the Singleton pattern.
 *
 * Class EventgalleryLibraryFactoryFactory
 */
class EventgalleryLibraryFactoryFactory extends EventgalleryLibraryDatabaseObject
{

    public static $_instances = array();

    public function __construct() {
        $this->db = JFactory::getDbo();
    }

    final public static function getInstance() {


        $calledClassName = get_called_class();

        if (! isset (self::$_instances[$calledClassName])) {
            self::$_instances[$calledClassName] = new $calledClassName();
        }

        return self::$_instances[$calledClassName];
    }

}
