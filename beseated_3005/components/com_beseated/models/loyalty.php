<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * The Beseated Loyalty Model
 *
 * @since  0.0.1
 */
class BeseatedModelLoyalty extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelList
	 * @since   0.0.1
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array();
		}

		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$user  = JFactory::getUser();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_loyalty_point'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
			->order($db->quoteName('time_stamp') . ' DESC');

		return $query;
	}

	function getRewards()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__beseated_rewards'))
			->where($db->quoteName('published') . ' = ' . $db->quote('1'))
			->order($db->quoteName('reward_coin') . ' ASC');
		$db->setQuery($query);
	    $resRewards = $db->loadObjectList();

	   return $resRewards;

	}
}
