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
class BeseatedModelChauffeurInformation extends JModelList
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

	public function getChaufferDetail()
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		$chauffeurID = $input->get('chauffeur_id',0,'int');

		$db             = JFactory::getDbo();
		$queryLiveUsers = $db->getQuery(true);

		$queryLiveUsers->select('id')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('block') . ' = ' . $db->quote('0'));

		// Set the query and load the result.
		$db->setQuery($queryLiveUsers);
		$liveUser = $db->loadColumn();


		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('c.*')
			->from($db->quoteName('#__beseated_chauffeur', 'c'))
			->where($db->quoteName('c.published') . ' = ' . $db->quote('1'))
			->where($db->quoteName('c.chauffeur_id') . ' = ' . $db->quote($chauffeurID));
			if(count($liveUser) > 0){
    			$liveUserStr = implode(",", $liveUser);
    			$query->where($db->quoteName('c.user_id') . ' IN (' . $liveUserStr . ')');
     		}

     	$query->select('img.thumb_image,img.image')
   				->join('LEFT','#__beseated_element_images AS img ON img.element_id=c.chauffeur_id')
   				->where($db->quoteName('img.element_type') . ' = ' . $db->quote('Chauffeur'))

		->order($db->quoteName('c.chauffeur_id') . ' ASC')
		->group('chauffeur_id');

    	$db->setQuery($query);

     	$result = $db->loadObject();

		return $result;
	}

	public function getChaufferServices()
	{

		$app         = JFactory::getApplication();
		$input       = $app->input;
		$chauffeurID = $input->get('chauffeur_id',0,'int');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_chauffeur_services'))
			->where($db->quoteName('chauffeur_id') . ' = ' . $db->quote($chauffeurID))
			->where($db->quoteName('published') . ' = ' . $db->quote('1'))
			->order($db->quoteName('service_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		$serviceResult = $db->loadObjectList();

		return $serviceResult;
	}

	public function getChaufferImages()
	{
		$app        = JFactory::getApplication();
		$input      = $app->input;
		$serachType = '';
		$venueID    = $input->get('chauffeur_id',0,'int');
		$query      = $this->db->getQuery(true);


		// Set the query and load the result.
		$query->select('i.*')
			->from($this->db->quoteName('#__beseated_element_images','i'))
			->where($this->db->quoteName('i.element_id') . ' =  ' .  $this->db->quote($venueID))
			->where($this->db->quoteName('i.element_type') . ' =  ' .  $this->db->quote('Chauffeur'))
			->order('i.is_default DESC');

		$this->db->setQuery($query);
		$imageResult = $this->db->loadObjectList();

		return $imageResult;
	}

}
