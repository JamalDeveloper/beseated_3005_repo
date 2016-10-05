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
 * The Beseated Message Detail Model
 *
 * @since  0.0.1
 */
class BeseatedModelProtectionMessageDetail extends JModelList
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
		parent::__construct($config);
	}

	protected function getListQuery()
	{
		// Following code is unsable so plz ignore : 11-09-16 Jamal
		
		$app          = JFactory::getApplication();
		$input        = $app->input;
		$connectionID = $input->get('connection_id',0,'int');
		$user         = JFactory::getUser();

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')
			->from($db->quoteName('#__beseated_message'))
			->where($db->quoteName('connection_id') . ' = ' . $db->quote($connectionID))
			->where('(('.$db->quoteName('from_user_id') . ' = '. $db->quote($user->id) .' AND '. $db->quoteName('deleted_by_from_user') .' = ' . $db->quote('0').')' . ' OR ' .'('.$db->qn('to_user_id') . ' = '. $db->quote($user->id) . ' AND ' . $db->quoteName('deleted_by_to_user') .' = ' . $db->quote('0').'))')
			->order($db->quoteName('time_stamp') . ' ASC');

		return $query;
	}
}
