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
class BeseatedModelProtections extends JModelList
{
	public $db = null;
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
		$app           = JFactory::getApplication();
		$input         = $app->input;
		$countrySearch = $input->get('country','','RAW');
		$latitude      = $app->input->cookie->get('latitude', '0.00');
		$longitude     = $app->input->cookie->get('longitude', '0.00');
		$inner_search  = $input->get('inner_search', 0, 'int');
		$caption       = $input->get('caption','','string');
		$near_me       = $input->get('near_me',0,'int');
		$start         = $input->get('limitstart',0);

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

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select("p.*,img.thumb_image,img.image,(((acos(sin((".$latitude."*pi()/180)) *
					            sin((p.latitude*pi()/180))+cos((".$latitude."*pi()/180)) *
					            cos((p.latitude*pi()/180)) * cos(((".$longitude."- p.longitude)*
					            pi()/180))))*180/pi())*60*1.1515
					        ) as distance")
			->from($db->quoteName('#__beseated_protection', 'p'))
			->where($db->quoteName('img.element_type') . ' = ' . $db->quote('Protection'))
			->where($db->quoteName('img.is_default') . ' = ' . $db->quote('1'))
			->where($db->quoteName('p.published') . ' = ' . $db->quote('1'))
			->where($db->quoteName('p.has_service') . ' = ' . $db->quote('1'))
			->where($db->quoteName('u.block') . ' = ' . $db->quote('0'))
			->join('LEFT','#__beseated_element_images AS img ON img.element_id=p.protection_id')
			->join('INNER','#__users AS u ON u.id=p.user_id')
			->group($db->quoteName('p.protection_id'));

     	$search_in_city = $app->input->cookie->get('search_in_city', '');

		if(!empty($countrySearch))
		{
			$app->input->cookie->set('search_in_city', $countrySearch);
			$search_in_city = $app->input->cookie->get('search_in_city', '', 'RAW');
		}

		if(!empty($search_in_city))
		{
			$query->where('('.$db->quoteName('p.city') . ' LIKE  ' .  $db->quote('%'.$search_in_city.'%').' OR '.$db->quoteName('p.location') . ' LIKE  ' .  $db->quote('%'.$search_in_city.'%').')');
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
				$query->where($db->quoteName('p.protection_name') . ' LIKE  ' .  $db->quote('%'.$caption.'%'));
			}

			if($near_me)
			{
				$query = $query . " HAVING distance <= 20 ORDER BY distance ASC";
			}
			else
			{
				$query->order($db->quoteName('p.protection_name') . ' ASC');
			}
		}

		$this->setState('list.limit', 21);
		$this->setState('list.start', $start);

		return $query;
	}

	/**
	 * Method to get a list of articles.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function addtofavourite($protectionID, $userID)
	{
		if($this->checkForAlreadyFavourite($protectionID,'Protection',$userID))
		{
			return 2;
		}

		$tblFavourite         = JTable::getInstance('Favourite', 'BeseatedTable', array());
		$tblFavourite->load(0);
		$data                 = array();
		$data['element_type'] = 'Protection';
		$data['element_id']   = $protectionID;
		$data['user_id']      = $userID;
		$data['created']      = date('Y-m-d h:i:s');
		$data['time_stamp']   = time();

		$tblFavourite->bind($data);
		if(!$tblFavourite->store())
		{
			return 0;
		}

		return 1;
	}

	public function removefromfavourite($protectionID, $userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->delete()
			->from($db->quoteName('#__beseated_favourite'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($protectionID))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote('Protection'));

		// Set the query and execute the delete.
		$db->setQuery($query);
		$db->execute();

		return 1;
	}

	public function checkForAlreadyFavourite($elementID,$elementType,$userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('favourite_id')
			->from($db->quoteName('#__beseated_favourite'))
			->where($db->quoteName('favourite_id') . ' = ' . $db->quote($elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		return $db->loadResult();
	}
}
