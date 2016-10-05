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
 * The Beseated Club Tables Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubTables extends JModelList
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

	public function getVenueTables()
	{
		$app        = JFactory::getApplication();
		$input      = $app->input;
		$serachType = '';
		$venueID    = $input->get('club_id',0,'int');
		$db         = JFactory::getDbo();
		$query      = $db->getQuery(true);

		// Set the query and load the result.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_venue_table','a'))
			->where($db->quoteName('a.published') . ' =  ' .  $db->quote(1))
			->where($db->quoteName('a.venue_id') . ' =  ' .  $db->quote($venueID));

		$query->select('b.currency_code,b.currency_sign')
			->join('LEFT','#__beseated_venue AS b ON b.venue_id=a.venue_id ');

		$db->setQuery($query);
		$result = $db->loadObjectList();
		return $result;
	}
}
