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
 * The Beseated Club Information Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubInformation extends JModelList
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

	public function getVenueDetail()
	{
		$app        = JFactory::getApplication();
		$input      = $app->input;
		$serachType = '';
		$venueID    = $input->get('club_id',0,'int');
		$query      = $this->db->getQuery(true);


		// Set the query and load the result.
		$query->select('a.*')
			->from($this->db->quoteName('#__beseated_venue','a'))
			->where($this->db->quoteName('a.published') . ' =  ' .  $this->db->quote(1))
			->where($this->db->quoteName('a.venue_id') . ' =  ' .  $this->db->quote($venueID));

		$this->db->setQuery($query);
		$result = $this->db->loadObject();

		return $result;
	}

	public function getVenueImages()
	{
		$app        = JFactory::getApplication();
		$input      = $app->input;
		$serachType = '';
		$venueID    = $input->get('club_id',0,'int');
		$query      = $this->db->getQuery(true);


		// Set the query and load the result.
		$query->select('i.*')
			->from($this->db->quoteName('#__beseated_element_images','i'))
			->where($this->db->quoteName('i.element_id') . ' =  ' .  $this->db->quote($venueID))
			->order('i.is_default DESC');

		$this->db->setQuery($query);
		$imageResult = $this->db->loadObjectList();

		return $imageResult;
	}

}
