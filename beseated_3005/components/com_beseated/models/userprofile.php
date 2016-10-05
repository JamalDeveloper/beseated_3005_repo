<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
jimport( 'joomla.application.component.helper' );
jimport('joomla.filesystem.folder');
/**
 * The Beseated User Profile Message Model
 *
 * @since  0.0.1
 */
class BeseatedModelUserProfile extends JModelAdmin
{
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   0.0.1
	 */
	public function getTable($type = 'Profile', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   0.0.1
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_beseated.userprofile',
			'userprofile',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   0.0.1
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState(
			'com_beseated.edit.Userprofile.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$user           = JFactory::getUser();
		$userProfile    = BeseatedHelper::getUserElementID($user->id);
		$obj            = new stdClass;
		$obj            = $data;
		$obj->full_name = $userProfile->full_name;
		$obj->email     = $user->email;
		$obj->phone     = $userProfile->phone;
		$obj->city      = $userProfile->city;
		$obj->latitude  = $userProfile->latitude;
		$obj->longitude = $userProfile->longitude;
		$obj->birthdate = $userProfile->birthdate;

		return $obj;
	}

	public function save($data)
	{
		$input        = JFactory::getApplication()->input;

		$input       = JFactory::getApplication()->input;
		$bigspender  = $input->get('bigspender', 'off', 'string');
		$showfriends = $input->get('showfriends', 'off', 'string');

		$bigspender  = ($bigspender == 'on') ? 1 : 0;
		$showfriends = ($showfriends == 'on') ? 1 : 0;

		$post['username']                = $data['full_name'];
		/*$post['relname']               = $data['first_name'];*/
		$post['password']                = $data['password'];
		$post['password2']               = $data['password2'];
		$post['phone']                   = $data['phone'];
		$post['email']                   = $data['email'];
		$post['location']                = $data['city'];
		

		$only_city = $this->getAddress($data['city']);
			
	    /*	$post['last_name'] = $data['last_name'];*/
		$post['city']      = (isset($only_city['city']))?$only_city['city']:'';
		$post['latitude']  = (isset($only_city['lat']))?$only_city['lat']:'';
		$post['longitude'] = (isset($only_city['long']))?$only_city['long']:'';

		$db                   = JFactory::getDbo();
		$loginUser            = JFactory::getUser();
		$user                 = new JUser;
		$user->load($loginUser->id);
		$userData['username'] = trim($post['email']);
		$userData['name']     = trim($post['username']);
		$userData['email']    = trim($post['email']);

		if(isset($post['password']) && !empty($post['password']) && isset($post['password2']) && !empty($post['password2']))
		{
			$userData['password']  =  $post['password'];
			$userData['password2'] =  $post['password2'];
		}

		$user->bind($userData);
		if (!$user->save())
		{
			return 500;
		}

		$aclval = $user->id;

		if (!$aclval)
		{
			return 500;
		}


		$tblProfile            = $this->getTable();

		$userProfile           = BeseatedHelper::getUserElementID($loginUser->id);
		$tblProfile->load($userProfile->user_id);

		if($tblProfile->city !== $post['location'])
		{
			$tblProfile->latitude  = $post['latitude'];
			$tblProfile->longitude = $post['longitude'];
			$tblProfile->location  = $post['location'];
			$tblProfile->city      = $post['city'];
		}

		$tblProfile->phone                   = $post['phone'];
		$tblProfile->show_in_biggest_spender =  $bigspender;
		$tblProfile->show_friends_only       =  $showfriends;
		$tblProfile->birthdate               =  $data['birthdate'];
		
		$tblProfile->store();

		return 1;
	}

	public function getAddress($address)
	{

		$address = str_replace(" ", "+", $address);
		$json    = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false");
		$json    = json_decode($json);

		$only_city = array();
		$only_city['lat']  = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
		$only_city['long'] = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
		$only_city['city'] = $json->{'results'}[0]->{'address_components'}[0]->{'long_name'};

	    return $only_city;
	}

	

}
