<?php
/**
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Besaeted Premium Tables Model
 *
 * @since  0.0.1
 */
class BeseatedModelMusicTables extends JModelList
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
			$config['filter_fields'] = array(
				'music_id','a.music_id',
				'music_name','a.music_name'
			);
		}

		parent::__construct($config);
	}

	protected function getListQuery()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Filter: like / search
		$search = $this->getState('filter.search');

		// Create the base select statement.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_venue_music_table','a'));


		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('a.music_name LIKE ' . $like);
		}

		$fullordering = $this->state->get('list.fullordering', '');

		if(empty($fullordering))
		{
			$fullordering = "a.music_name ASC";
		}

		$orderArray = explode(" ", $fullordering);
		$ordering = $orderArray[0];
		$direction = $orderArray[1];

		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));



		return $query;
	}

	/*
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState($ordering = 'id', $direction = 'ASC')
	{
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$value = $app->input->get('limit', $app->getCfg('list_limit', 0), 'uint');
		$this->setState('list.limit', 10);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$value = $app->input->get('listdirection', 'ASC', 'string');
		$this->setState('list.direction', $value);

		$orderCol = $app->input->get('filter_order', 'a.music_id');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.music_id';
		}

		$this->setState('list.ordering', $orderCol);

		// List state information.
		parent::populateState();

	}
}
