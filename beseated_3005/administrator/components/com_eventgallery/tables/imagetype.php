<?php
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die('Restricted access');


class TableImagetype extends JTable
{
    public $id;
    public $type;
    public $isdigital;
    public $size;
    public $width;
    public $height;
    public $depth;
    public $weight;
    public $taxrate;
    public $price;
    public $currency;
    public $name;
    public $displayname;
    public $description;
    public $published;
    public $note;
    public $modified;
    public $created;

    /**
     * Constructor
     * @param JDatabaseDriver $db
     */

    function __construct(&$db)
    {
        parent::__construct('#__eventgallery_imagetype', 'id', $db);
    }
}

