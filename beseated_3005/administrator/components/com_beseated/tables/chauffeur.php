<?php
/**
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Beseated Chauffeur Table class
 *
 * @since  0.0.1
 */
class BeseatedTableChauffeur extends JTable
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
		parent::__construct('#__beseated_chauffeur', 'chauffeur_id', $db);
	}
}
