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

echo  $this->loadSnippet('cart');
echo  $this->loadSnippet('social');

$this->params->set('event_thumb_link_mode','singleimagepage');
echo $this->loadSnippet('event/imagelist');

echo $this->loadSnippet('footer_disclaimer');