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
 * The Beseated Profile Model
 *
 * @since  0.0.1
 */
class BeseatedModelProtectionProfile extends JModelList
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

	public function getProfileData()
	{
		$user          = JFactory::getUser();
		$elementType   = BeseatedHelper::getUserType($user->id);
		$elementDetail = BeseatedHelper::getUserElementID($user->id);

		return $elementDetail;
	}

	public function saveProtectionProfile($data,$id)
	{
		$tblProtection = JTable::getInstance('Protection','BeseatedTable',array());
		$tblProtection->load($id);
		$tblProtection->bind($data);

		if($tblProtection->store())
		{
			//$user              = new JUser;
			//$user->load($tblProtection->user_id);

			$tblUser = JTable::getInstance('Users','BeseatedTable',array());
		    $tblUser->load($tblProtection->user_id);
 
			if($tblUser->id)
			{
				$tblUser->name = trim($tblProtection->protection_name);
				$tblUser->store();
			}

			$tblBctUser            = JTable::getInstance('Profile', 'BeseatedTable', array());
			$bctUserProfile        = BeseatedHelper::getBeseatedUserProfile($tblProtection->user_id);
			$tblBctUser->load($bctUserProfile->user_id);
			$tblBctUser->full_name = $tblProtection->protection_name;
			$tblBctUser->store();


			return true;
		}

		return false;
	}

	public function deleteImage($imageId, $elementId)
	{

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->delete()
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('image_id') . ' = ' . $db->quote((int) $imageId))
			->where($db->quoteName('element_id') . ' = ' . $db->quote((int) $elementId));

		// Set the query and execute the delete.
		$db->setQuery($query);

		try
		{
			$result = $db->execute();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return $result;

	}

}
