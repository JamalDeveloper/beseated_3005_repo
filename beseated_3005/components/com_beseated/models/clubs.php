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
 * The Beseated Clubs Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubs extends JModelList
{
	public $db = null;
	public $venuesHasTable = null;
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
		$this->db = JFactory::getDbo();
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array();
		}

		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$app                 = JFactory::getApplication();
		$latitude            = $app->input->cookie->get('latitude', '0.00');
		$longitude           = $app->input->cookie->get('longitude', '0.00');
		$input               = $app->input;
		$start               = $app->input->get('limitstart',0);
		$serachType          = '';
		$clubSearch          = $input->get('club','club','string');
		$serviceSearch       = $input->get('service','','string');
		$countrySearch       = $input->get('country','','RAW');
		$inner_search        = $input->get('inner_search', 0, 'int');
		$myfriends_attending = $input->get('myfriends_attending',0,'int');

		$query = $this->db->getQuery(true);
		if($clubSearch == 'club')
		{
			$caption    = $input->get('caption','','string');
			$is_smart   = $input->get('is_smart', 0, 'int');
			$is_casual  = $input->get('is_casual', 0, 'int');
			$is_food    = $input->get('is_food', 0, 'int');
			$is_drink   = $input->get('is_drink', 0, 'int');
			$is_smoking = $input->get('is_smoking', 0, 'int');
			$near_me    = $input->get('near_me', 0, 'int');
			$is_ratting = $input->get('is_ratting', 0, 'int');
			$is_costly  = $input->get('is_costly', 0, 'int');
			$limitstart = $input->get('limitstart', 0, 'int');

			if(!isset($_GET['limitstart']))
			{
				$app->input->cookie->set('inner_search', $inner_search);
				$app->input->cookie->set('myfriends_attending', $myfriends_attending);
				$app->input->cookie->set('caption', $caption);
				$app->input->cookie->set('is_smart', $is_smart);
				$app->input->cookie->set('is_casual', $is_casual);
				$app->input->cookie->set('is_food', $is_food);
				$app->input->cookie->set('is_drink', $is_drink);
				$app->input->cookie->set('is_smoking', $is_smoking);
				$app->input->cookie->set('near_me', $near_me);
				$app->input->cookie->set('is_ratting', $is_ratting);
				$app->input->cookie->set('is_costly', $is_costly);
			}
			else
			{
				$inner_search        = $app->input->cookie->get('inner_search', '0');
				$myfriends_attending = $app->input->cookie->get('myfriends_attending', '0');
				$caption             = $app->input->cookie->get('caption', '');
				$is_smart            = $app->input->cookie->get('is_smart', '0');
				$is_casual           = $app->input->cookie->get('is_casual', '0');
				$is_food             = $app->input->cookie->get('is_food', '0');
				$is_drink            = $app->input->cookie->get('is_drink', '0');
				$is_smoking          = $app->input->cookie->get('is_smoking', '0');
				$near_me             = $app->input->cookie->get('near_me', '0');
				$is_ratting          = $app->input->cookie->get('is_ratting', '0');
				$is_costly           = $app->input->cookie->get('is_costly', '0');
			}

			$this->getActiveTableVanueIDs();

			$query->select("a.*,i.*,(((acos(sin((".$latitude."*pi()/180)) *
					            sin((a.latitude*pi()/180))+cos((".$latitude."*pi()/180)) *
					            cos((a.latitude*pi()/180)) * cos(((".$longitude."- a.longitude)*
					            pi()/180))))*180/pi())*60*1.1515
					        ) as distance ")
				->from($this->db->quoteName('#__beseated_venue','a'))
				->join('LEFT', $this->db->quoteName('#__beseated_element_images', 'i') . ' ON a.venue_id = i.element_id')
				->where($this->db->quoteName('a.published') . ' =  ' .  $this->db->quote(1));
				$query->group($this->db->quoteName('a.venue_id'));

			if(count($this->venuesHasTable))
			{
				$query->where($this->db->quoteName('a.venue_id') . ' IN (' . implode(",", $this->venuesHasTable) .')');
			}

			$search_in_city = $app->input->cookie->get('search_in_city', '');

			if(!empty($countrySearch))
			{
				$app->input->cookie->set('search_in_city', $countrySearch);
				$search_in_city = $app->input->cookie->get('search_in_city', '', 'RAW');
			}
		/*	else if (empty($countrySearch) && !empty($search_in_city))
			{
				$app->input->cookie->set('search_in_city', '');
				$search_in_city = '';
			}*/

			if(!empty($search_in_city))
			{
				$query->where('('.$this->db->quoteName('a.city') . ' LIKE  ' .  $this->db->quote('%'.$search_in_city.'%').' OR '.$this->db->quoteName('a.location') . ' LIKE  ' .  $this->db->quote('%'.$search_in_city.'%').')');
			}

			if(!empty($countrySearch))
			{
				$app->input->cookie->set('search_in_city', '');
				$search_in_city = '';
			}
			if($inner_search == 1)
			{
				if(!empty($caption))
				{
					$query->where($this->db->quoteName('a.venue_name') . ' LIKE  ' .  $this->db->quote('%'.$caption.'%'));
				}

				/*if($is_drink)
				{
					$query->where($this->db->quoteName('a.is_drink') . ' = ' . $this->db->quote(1));
				}

				if($is_food)
				{
					$query->where($this->db->quoteName('a.is_food') . ' = ' . $this->db->quote(1));
				}

				if($is_smart)
				{
					$query->where($this->db->quoteName('a.is_smart') . ' = ' . $this->db->quote(1));
				}
				if($is_casual)
				{
					$query->where($this->db->quoteName('a.is_casual') . ' = ' . $this->db->quote(1));
				}

				if($is_smoking == 1)
				{
					$query->where($this->db->quoteName('a.is_smoking') . ' = ' . $this->db->quote($is_smoking));
				}
				else if($is_smoking == 2)
				{
					$query->where($this->db->quoteName('a.is_smoking') . ' = ' . $this->db->quote(0));
				}

				if($is_ratting)
				{
					$query->where($this->db->quoteName('a.venue_rating') . ' >= ' . $this->db->quote($is_ratting));
				}

				if($is_costly)
				{
					$query->where($this->db->quoteName('a.venue_signs') . ' >= ' . $this->db->quote($is_costly));
				}*/

				if($myfriends_attending)
				{
					$bookings = $this->getFriendsAttending();
					if(count($bookings)!=0)
					{
						$query->where($this->db->quoteName('a.venue_id') . ' IN (' . implode(",", $bookings) . ')');
					}
					else
					{
						$query = $this->db->getQuery(true);
						$query->select('a.*')
							->from($this->db->quoteName('#__beseated_venue','a'))
							->where($this->db->quoteName('a.venue_id') . ' =  ' .  $this->db->quote(0));

						return $query;
					}
				}

				if($near_me)
				{
					$query = $query . " HAVING distance <= 20 ORDER BY distance ASC";
				}
			}

			if(!$near_me)
			{
				$query->order($this->db->quoteName('a.venue_name') . ' ASC');
			}
		}
		else
		{
			$query->select('a.*,i.*')
				->from($this->db->quoteName('#__beseated_venue','a'))
				->join('LEFT', $this->db->quoteName('#__beseated_element_images', 'i') . ' ON a.venue_id = i.element_id')
				->where($this->db->quoteName('a.published') . ' =  ' .  $this->db->quote(1));
				$query->group($this->db->quoteName('a.venue_id'));
				$query->order($this->db->quoteName('a.venue_name') . ' ASC');
		}

		$this->setState('list.limit', 21);
		$this->setState('list.start', $start);

		return $query;
	}

	public function getFriendsAttending()
	{
		$user = JFactory::getUser();
		if(!$user->id)
		{
			return array();
		}

		// Initialiase variables.
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('facebook_friends')
			->from($this->db->quoteName('#__facebook_joomla_connect'))
			->where($this->db->quoteName('joomla_userid') . ' = ' . $this->db->quote($user->id));

		// Set the query and load the result.
		$this->db->setQuery($query);

		$fbFriends = $this->db->loadResult();

		if(empty($fbFriends))
		{
			return array();
		}

		$query1 = $this->db->getQuery(true);

		// Create the base select statement.
		$query1->select('userid')
			->from($this->db->quoteName('#__beseated_user_profile'))
			->where($this->db->quoteName('fbid') . ' IN (' . $fbFriends . ')');

		// Set the query and load the result.
		$this->db->setQuery($query1);
		$foundArray = $this->db->loadColumn();
		$foundArray = implode(",", $foundArray);
		$app = JFactory::getApplication();
		$input = $app->input;

		// Initialiase variables.
		$query3 = $this->db->getQuery(true);

		// Create the base select statement.
		$query3->select('vb.*')
			->from($this->db->quoteName('#__bcted_venue_booking','vb'))
			->where($this->db->quoteName('vb.status') . ' = ' . $this->db->quote('5'))
			->where($this->db->quoteName('vb.user_status') . ' = ' . $this->db->quote('5'))
			->where($this->db->quoteName('vb.is_deleted') . ' = ' . $this->db->quote('0'))
			->where($this->db->quoteName('vb.venue_booking_datetime') . ' >= ' . $this->db->quote(date('Y-m-d')))
			->where($this->db->quoteName('vb.user_id') . ' IN (' . $foundArray . ')')
			->order($this->db->quoteName('vb.venue_booking_datetime') . ' ASC');

		$query3->select('vt.premium_table_id,vt.venue_table_name,vt.custom_table_name')
			->join('LEFT','#__bcted_venue_table AS vt ON vt.venue_table_id=vb.venue_table_id');

		$query3->select('v.venue_name')
			->join('LEFT','#__bcted_venue AS v ON v.venue_id=vb.venue_id');

		$query3->select('u.name,u.username')
			->join('LEFT','#__users AS u ON u.id=vb.user_id');

		$query3->select('bu.fbid')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.userid=vb.user_id');

		// Set the query and load the result.
		$this->db->setQuery($query3);
		$bookings = $this->db->loadObjectList();

		$venueIDs = array();
		foreach ($bookings as $key => $booking)
		{
			$venueIDs[] = $booking->venue_id;
		}

		$venueIDs = array_unique($venueIDs);

		return $venueIDs;
	}

	public function getActiveTableVanueIDs()
	{
		if(!$this->venuesHasTable)
		{
			$queryVT = $this->db->getQuery(true);
			$queryVT->select('venue_id')
				->from($this->db->quoteName('#__beseated_venue'))
				->where($this->db->quoteName('has_table') . ' = ' . $this->db->quote('1'));
			$this->db->setQuery($queryVT);
			$venuesHasTable = $this->db->loadColumn();
			$this->venuesHasTable = array_unique($venuesHasTable);
		}


		return $this->venuesHasTable;
	}
}
