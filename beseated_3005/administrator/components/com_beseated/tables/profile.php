<?php
/**
 * @package     BC Ted.Administrator
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * BC Ted Message Table class
 *
 * @since  0.0.1
 */
class BeseatedTableProfile extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   Object  Database_connector  object
	 *
	 * @since   0.0.1
	 */
	function __construct(&$db)
	{
		parent::__construct('#__beseated_user_profile', 'user_id', $db);
	}
}
