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
class BeseatedModelEvents extends JModelList
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
		// Initialiase variables.
		$this->db       = JFactory::getDbo();
		$query          = $this->db->getQuery(true);
		$app            = JFactory::getApplication();
		$input          = $app->input;
		$search_in_city = $app->input->cookie->get('search_in_city', '');
		$latitude       = $app->input->cookie->get('latitude', '0.00');
		$longitude      = $app->input->cookie->get('longitude', '0.00');
		$inner_search   = $input->get('inner_search', 0, 'int');
		$caption        = $input->get('caption','','string');
		$near_me        = $input->get('near_me',0,'int');
		$start          = $input->get('limitstart',0);

		//echo "<pre/>";print_r($input);exit;

		if(!isset($_GET['limitstart']))
		{
			$input->cookie->set('inner_search', $inner_search);
			$input->cookie->set('caption', $caption);
			$input->cookie->set('near_me', $near_me);
		}
		else
		{
			$inner_search        = $input->cookie->get('inner_search', '0');
			$caption             = $input->cookie->get('caption', '');
			$near_me             = $input->cookie->get('near_me', '0');
		}

		// Create the base select statement.
		$query->select("e.*,(((acos(sin((".$latitude."*pi()/180)) *
					            sin((e.latitude*pi()/180))+cos((".$latitude."*pi()/180)) *
					            cos((e.latitude*pi()/180)) * cos(((".$longitude."- e.longitude)*
					            pi()/180))))*180/pi())*60*1.1515
					        ) as distance")
			->from($this->db->quoteName('#__beseated_event','e'))
			->where($this->db->quoteName('e.published') . ' = ' . $this->db->quote('1'))
			//->where($this->db->quoteName('e.available_ticket') . ' >= ' . $this->db->quote('1'))
			->where($this->db->quoteName('e.is_deleted') . ' = ' . $this->db->quote('0'))
			->where('CONCAT('.$this->db->quoteName('e.event_date').', " ", '.$this->db->quoteName('e.event_time') . ') >= ' . $this->db->quote(date('Y-m-d H:i:s')))
			->order($this->db->quoteName('e.event_date') . ' ASC');


		if(!empty($search_in_city))
		{
			$query->where('('.
					$this->db->quoteName('e.location') .' LIKE ' . $this->db->quote('%'.$search_in_city.'%'). ' OR '.
					$this->db->quoteName('e.city') .' LIKE ' . $this->db->quote('%'.$search_in_city.'%').
				')');
		}

		if($inner_search == 1)
		{
			if(!empty($caption))
			{
				$query->where($this->db->quoteName('e.event_name') . ' LIKE  ' .  $this->db->quote('%'.$caption.'%'));
			}

			if($near_me)
			{
				$query = $query . " HAVING distance <= 20 ORDER BY distance ASC";
			}
			else
			{
				$query->order($this->db->quoteName('e.time_stamp') . ' ASC');
			}
		}

		$query->select('MIN(i.ticket_price) as ticket_price')
			->join('RIGHT', $this->db->quoteName('#__beseated_event_ticket_type_zone', 'i') . ' ON i.event_id = e.event_id')
			->where($this->db->quoteName('i.is_deleted') . ' =  ' .  $this->db->quote('0'));

		$this->db->setQuery($query);
		$events = $this->db->loadObjectList();

		//echo "<pre>";print_r($events);echo "<pre/>";exit();
	
		$this->setState('list.limit', 21);
		$this->setState('list.start', $start);

		return $query;
	}


}
