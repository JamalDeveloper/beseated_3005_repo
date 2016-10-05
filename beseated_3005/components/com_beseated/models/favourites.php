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
 * The Beseated Favourites Model
 *
 * @since  0.0.1
 */
class BeseatedModelFavourites extends JModelList
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

		$query->select('a.favourite_id,a.element_type,a.element_id')
			->from($db->quoteName('#__beseated_favourite','a'))
			->where($db->quoteName('a.user_id') . ' =  ' .  $db->quote($user->id))
			->where($db->quoteName('a.element_type') . ' =  ' .  $db->quote('Venue'));

		$query->select('v.venue_name,v.city,v.avg_ratting')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=a.element_id AND a.element_type="Venue"');

		$query->select('img.thumb_image,img.image')
				->join('LEFT','#__beseated_element_images AS img ON img.element_id=v.venue_id');

		$query->order('img.is_default DESC');
		$query->group('v.venue_id');

		return $query;
	}

	public function getProtectionFavourite()
	{
		$user  = JFactory::getUser();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.favourite_id,a.element_type,a.element_id')
			->from($db->quoteName('#__beseated_favourite','a'))
			->where($db->quoteName('a.user_id') . ' =  ' .  $db->quote($user->id))
			->where($db->quoteName('a.element_type') . ' =  ' .  $db->quote('Protection'));

		$query->select('p.protection_name,p.city,p.avg_ratting')
			->join('LEFT','#__beseated_protection AS p ON p.protection_id=a.element_id AND a.element_type="Protection"');

		$query->select('img.thumb_image,img.image')
				->join('LEFT','#__beseated_element_images AS img ON img.element_id=p.protection_id');
		$query->order('img.is_default DESC');
		$query->group('p.protection_id');
		$db->setQuery($query);
		$favouriteList = $db->loadObjectList();

		return $favouriteList;
	}

	public function getChauffeurFavourite()
	{
		$user  = JFactory::getUser();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.favourite_id,a.element_type,a.element_id')
			->from($db->quoteName('#__beseated_favourite','a'))
			->where($db->quoteName('a.user_id') . ' =  ' .  $db->quote($user->id))
			->where($db->quoteName('a.element_type') . ' =  ' .  $db->quote('Chauffeur'));

		$query->select('c.chauffeur_name,c.city,c.avg_ratting')
			->join('LEFT','#__beseated_chauffeur AS c ON c.chauffeur_id=a.element_id AND a.element_type="Chauffeur"');

		$query->select('img.thumb_image,img.image')
				->join('LEFT','#__beseated_element_images AS img ON img.element_id=c.chauffeur_id');
		$query->order('img.is_default DESC');
		$query->group('c.chauffeur_id');
		$db->setQuery($query);
		$favouriteList = $db->loadObjectList();

		return $favouriteList;
	}

	public function getYachtFavourite()
	{
		$user  = JFactory::getUser();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.favourite_id,a.element_type,a.element_id')
			->from($db->quoteName('#__beseated_favourite','a'))
			->where($db->quoteName('a.user_id') . ' =  ' .  $db->quote($user->id))
			->where($db->quoteName('a.element_type') . ' =  ' .  $db->quote('Yacht'));

		$query->select('y.yacht_name,y.city,y.avg_ratting')
			->join('LEFT','#__beseated_yacht AS y ON y.yacht_id=a.element_id AND a.element_type="Yacht"');

		$query->select('img.thumb_image,img.image')
				->join('LEFT','#__beseated_element_images AS img ON img.element_id=y.yacht_id');
		$query->order('img.is_default DESC');
		$query->group('y.yacht_id');
		$db->setQuery($query);
		$favouriteList = $db->loadObjectList();

		return $favouriteList;
	}
}
