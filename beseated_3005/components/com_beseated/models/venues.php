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
 * The Beseated Venues Model
 *
 * @since  0.0.1
 */
class BctedModelVenues extends JModelList
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
				'venue_id','a.venue_id'
			);
		}

		parent::__construct($config);
	}

	protected function getListQuery()
	{
		// Initialiase variables.
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$search = $this->getState('filter.search');

		// Create the base select statement.
		$query->select('a.*,b.media,b.media_type,c.lib_name')
			->from($db->quoteName('#__heartdart_message','a'));

		$query->join('LEFT','#__heartdart_message_media as b on a.media_id = b.media_id');
		$query->join('LEFT','#__heartdart_library as c on a.library_id = c.library_id');
		$query->order($db->quoteName('a.msg_id') . ' DESC');

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published IN (0, 1))');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.msg_id');
		$orderDirn 	= $this->state->get('list.direction', 'asc');
		$query->group('a.msg_id');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}
}
