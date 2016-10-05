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
 * The Beseated Club Ratings Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubRatings extends JModelList
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

	public function getVenueRatings()
	{
		$app        = JFactory::getApplication();
		$input      = $app->input;
		$serachType = '';
		$venueID    = $input->get('club_id',0,'int');
		$db         = JFactory::getDbo();
	
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('r.rating_id,r.user_id,r.avg_rating,r.food_rating,r.service_rating,r.atmosphere_rating,r.value_rating,r.rating_count,r.rating_comment,r.created')
			->from($db->quoteName('#__beseated_rating','r'))
			->where($db->quoteName('r.element_id') . ' = '.$db->quote($venueID))
			->where($db->quoteName('r.element_type') . ' = ' . $db->quote('venue'))
			->order($db->quoteName('r.time_stamp') . ' ASC');

		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar,bu.fb_id')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=r.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resRatings = $db->loadObjectList();

		return $resRatings;
	}
}
