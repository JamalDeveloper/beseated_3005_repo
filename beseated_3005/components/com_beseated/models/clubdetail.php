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
 * The Beseated ClubDetail Model
 *
 * @since  0.0.1
 */
class BctedModelClubDetail extends JModelList
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

	public function getVenueDetail()
	{
		$app           = JFactory::getApplication();
		$input         = $app->input;
		$serachType    = '';
		$venueID       = $input->get('venue_id',0,'int');
		$serviceSearch = $input->get('service','','string');
		$countrySearch = $input->get('country','','string');
		$db            = JFactory::getDbo();
		$query         = $db->getQuery(true);

		// Set the query and load the result.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_venue','a'))
			->where($db->quoteName('a.published') . ' =  ' .  $db->quote(1))
			->where($db->quoteName('a.venue_id') . ' =  ' .  $db->quote($venueID));

		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}
}
