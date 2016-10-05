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
class BeseatedModelProtectionInformation extends JModelList
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

	public function getProtectionDetail()
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		$protectionID = $input->get('protection_id',0,'int');

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
		$query->select('p.*')
			->from($db->quoteName('#__beseated_protection', 'p'))
			->where($db->quoteName('p.published') . ' = ' . $db->quote('1'))
			->where($db->quoteName('p.protection_id') . ' = ' . $db->quote($protectionID));
			if(count($liveUser) > 0){
    			$liveUserStr = implode(",", $liveUser);
    			$query->where($db->quoteName('p.user_id') . ' IN (' . $liveUserStr . ')');
     		}

     	$query->select('img.thumb_image,img.image')
   				->join('LEFT','#__beseated_element_images AS img ON img.element_id=p.protection_id')
   				->where($db->quoteName('img.element_type') . ' = ' . $db->quote('Protection'))

		->order($db->quoteName('p.protection_id') . ' ASC')
		->group('protection_id');

    	$db->setQuery($query);

     	$result = $db->loadObject();

		return $result;
	}

	public function getProtectionServices()
	{

		$app          = JFactory::getApplication();
		$input        = $app->input;
		$protectionID = $input->get('protection_id',0,'int');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('s.*')
			->from($db->quoteName('#__beseated_protection_services','s'))
			->where($db->quoteName('s.protection_id') . ' = ' . $db->quote($protectionID))
			->where($db->quoteName('s.published') . ' = ' . $db->quote('1'))
			->order($db->quoteName('s.service_id') . ' ASC');

		$query->select('p.currency_code,p.currency_sign')
   				->join('LEFT','#__beseated_protection AS p ON s.protection_id=p.protection_id');

		// Set the query and load the result.
		$db->setQuery($query);

		$serviceResult = $db->loadObjectList();

		return $serviceResult;
	}

	public function getProtectionImages()
	{
		$app          = JFactory::getApplication();
		$input        = $app->input;
		$serachType   = '';
		$protectionID = $input->get('protection_id',0,'int');
		$query        = $this->db->getQuery(true);


		// Set the query and load the result.
		$query->select('i.*')
			->from($this->db->quoteName('#__beseated_element_images','i'))
			->where($this->db->quoteName('i.element_id') . ' =  ' .  $this->db->quote($protectionID))
			->where($this->db->quoteName('i.element_type') . ' =  ' .  $this->db->quote('Protection'))
			->order('i.is_default DESC');

		$this->db->setQuery($query);
		$imageResult = $this->db->loadObjectList();

		return $imageResult;
	}

}
