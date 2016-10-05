<?php
/**
 * @package     BCT.Plugin
 * @subpackage  com_bct
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * PlgUserBCT plugin
 *
 * @since  0.0.1
 */
class PlgUserBctRegistration extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  0.0.1
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  0.0.1
	 */
	protected $db;

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * This method sends a registration email to new users created in the backend.
	 *
	 * @param   array    $user     Holds the new user data.
	 * @param   boolean  $isnew    True if a new user is stored.
	 * @param   boolean  $success  True if user was succesfully stored in the database.
	 * @param   string   $msg      Message.
	 *
	 * @return  void
	 *
	 * @since   0.0.1
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$task  = $input->get('task','','string');
		$cid   = $input->get('cid',array(),'array');

		if(!empty($task) && count($cid)>0)
		{
			$this->disableUserElement($task,$cid);
		}

		// Initialiase variables.
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblBctUserProfile       = JTable::getInstance('Profile', 'BctedTable',array());

		$userType = "guest";

		if($success)
		{
			$newEmail = $user['email'];
			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base update statement.
			$query->update($db->quoteName('#__bcted_package_invite'))
				->set($db->quoteName('invited_user_id') . ' = ' . $db->quote($user['id']))
				->where($db->quoteName('invited_email') . ' = ' . $db->quote($newEmail));

			// Set the query and execute the update.
			$db->setQuery($query);

			$db->execute();
		}

		if($isnew && $success)
		{
			$newEmail = $user['email'];

			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($db->quoteName('#__bcted_user_refer'))
				->where($db->quoteName('refer_email') . ' = ' . $db->quote($newEmail))
				->order($db->quoteName('time_stamp') . ' ASC');

			// Set the query and load the result.
			$db->setQuery($query);

			$result = $db->loadObject();

			if($result)
			{
				$tblRefer       = JTable::getInstance('Refer', 'BctedTable',array());
				$tblRefer->load($result->refer_id);
				$tblRefer->ref_user_id = $user['id'];
				$tblRefer->is_registered = 1;
				$tblRefer->store();

				/*$refByUser = JFactory::getUser($result->userid);
				$lang = JFactory::getLanguage();
				$extension = 'com_bcted';
				$base_dir = JPATH_SITE;
				$language_tag = 'en-GB';
				$reload = true;
				$lang->load($extension, $base_dir, $language_tag, $reload);

				$bctedConfig = $this->getExtensionParam();
				$earnPoint = $bctedConfig->friends_referral;*/

				/*$app     = JFactory::getApplication();
				$config  = JFactory::getConfig();

				$site    = $config->get('sitename');
				$from    = $config->get('mailfrom');
				$sender  = $config->get('fromname');
				$email   = $refByUser->email;

				$imgPath = JUri::base().'images/email-footer-logo.png';
				$imageLink = '<img title="Beseated" alt="Beseated" src="'.$imgPath.'"/>';

				$subject = JText::_('COM_BESEATED_REFERRAL_POINT_USER_FIRST_PURCHASE_EMAIL_SUBJECT');

				$body     = JText::sprintf('COM_BESEATED_REFERRAL_POINT_USER_FIRST_PURCHASE_EMAIL_BODY',$refByUser->name, $earnPoint, $user['name'], $imageLink);

				$sender  = JMailHelper::cleanAddress($sender);
				$subject = JMailHelper::cleanSubject($subject);
				$body    = JMailHelper::cleanBody($body);

				$return = JFactory::getMailer()->sendMail($from, $sender, $email, $subject, $body, true);*/

				/*if ($return !== true)
				{
					return new JException(JText::_('COM__SEND_MAIL_FAILED'), 500);
				}*/
			}
		}

		if($isnew && $success)
		{
			$tmpUser = JFactory::getUser($user['id']);

			$profileData = array();
			$profileData['userid']     = $user['id'];
			$profileData['phoneno']    = $user['phoneno'];
			$profileData['fbid']       = $user['fbid'];
			$profileData['user_email'] = $user['email'];
			$profileData['first_name'] =  (isset($user['first_name']))?$user['first_name']:$user['name'];
			$profileData['last_name']  = $user['last_name'];
			$profileData['city']       = (isset($user['only_city']))?$user['only_city']:$user['city'];
			$profileData['latitude']   = $user['latitude'];
			$profileData['longitude']  = $user['longitude'];

			$bctedConfig = $this->getExtensionParam();
			$groups = $tmpUser->get('groups');

			if(in_array($bctedConfig->club, $groups))
			{
				$userType = "venue";
				$params['settings']['pushNotification']['receiveRequest'] = "1";
			}
			else if(in_array($bctedConfig->service_provider, $groups))
			{
				$userType = "service";
				$params['settings']['pushNotification']['receiveRequest'] = "1";
			}
			else if(in_array($bctedConfig->guest, $groups))
			{
				$userType = "guest";
				$params['settings']['social']['connectToFacebook']    = "1";
			}

			$profileData['user_type_for_push'] = $userType;

			if(isset($users['avatar']))
			{
				$profileData['avatar'] = ($user['avatar'])?$user['avatar']:'';
			}
			else
			{
				$profileData['avatar'] = "";
			}

			$profileData['last_update_status'] = time();
			$params['settings']['pushNotification']['receiveMessage'] = "1";
			$params['settings']['pushNotification']['updateMyBookingStatus']   = "1";
			$profileData['params'] = json_encode($params);

			$tblBctUserProfile->bind($profileData);

			if($userType == 'venue')
			{
				$tblVenue = JTable::getInstance('Venue', 'BctedTable',array());
				$venuePost['venue_name']      = $user['name'];
				if(isset($user['only_city']) && !empty($user['only_city']))
				{
					$venuePost['city']            = $user['only_city'];
				}
				$venuePost['venue_address']   = $user['city'];
				$venuePost['userid']          = $user['id'];
				$venuePost['latitude']        = $user['latitude'];
				$venuePost['longitude']       = $user['longitude'];
				$venuePost['venue_created']   = date('Y-m-d h:i:s');
				$venuePost['venue_modified']  = date('Y-m-d h:i:s');
				$venuePost['time_stamp']      = time();
				$venuePost['licence_type']    = 'basic';
				$venuePost['currency_code']   = 'GBP';
				$venuePost['currency_sign']   = '£';
				$venuePost['venue_active']    = 1;
				$venuePost['commission_rate'] = 10;
				$venuePost['is_casual']       = 1;
				$venuePost['is_drink']        = 1;
				$venuePost['is_smoking']      = 0;

				$tblVenue->load(0);
				$tblVenue->bind($venuePost);
				$tblVenue->store();
			}
			else if($userType == 'service')
			{
				$tblCompany = JTable::getInstance('Company', 'BctedTable',array());

				$companyPost['company_name']     = $user['name'];
				if(isset($user['only_city']) && !empty($user['only_city']))
				{
					$companyPost['city']             = $user['only_city'];
				}
				$companyPost['company_address']  = $user['city'];
				$companyPost['userid']           = $user['id'];
				$companyPost['latitude']         = $user['latitude'];
				$companyPost['longitude']        = $user['longitude'];
				$companyPost['company_created']  = date('Y-m-d h:i:s');
				$companyPost['company_modified'] = date('Y-m-d h:i:s');
				$companyPost['time_stamp']       = time();
				$companyPost['licence_type']     = 'basic';
				$companyPost['currency_code']    = 'GBP';
				$companyPost['currency_sign']    = '£';
				$companyPost['company_active']   = 1;
				$companyPost['commission_rate']  = 10;

				$tblCompany->load(0);
				$tblCompany->bind($companyPost);
				$tblCompany->store();
			}

			if($tblBctUserProfile->store())
			{
				$this->syncUsers();
			}
			else
			{
				$this->syncUsers();
			}
		}

		if($success)
		{
			if(isset($user['city']) && !empty($user['city']))
			{
				$queryPC = $db->getQuery(true);
				$queryPC->update($db->quoteName('#__beseated_user_profile'))
					->set($db->quoteName('city') . ' = ' . $db->quote($user['city']))
					->where($db->quoteName('userid') . ' = ' . $db->quote($user['id']));

				$db->setQuery($queryPC);
				$db->execute();

				$queryCC = $db->getQuery(true);
				$queryCC->update($db->quoteName('#__bcted_company'))
					->set($db->quoteName('city') . ' = ' . $db->quote($user['city']))
					->where($db->quoteName('userid') . ' = ' . $db->quote($user['id']));

				$db->setQuery($queryCC);
				$db->execute();

				$queryVC = $db->getQuery(true);
				$queryVC->update($db->quoteName('#__bcted_venue'))
					->set($db->quoteName('city') . ' = ' . $db->quote($user['city']))
					->where($db->quoteName('userid') . ' = ' . $db->quote($user['id']));

				$db->setQuery($queryVC);
				$db->execute();
			}

			$bctedConfig = $this->getExtensionParam();
			$tmpUser = JFactory::getUser($user['id']);
			$groups = $tmpUser->get('groups');
			if(in_array($bctedConfig->club, $groups))
			{
				$userType = "venue";
			}
			else if(in_array($bctedConfig->service_provider, $groups))
			{
				$userType = "service";
			}
			else if(in_array($bctedConfig->guest, $groups))
			{
				$userType = "guest";
			}

			if($userType == 'venue')
			{
				$tblVenue = JTable::getInstance('Venue', 'BctedTable',array());

				if(isset($user['only_city']) && !empty($user['only_city']))
				{
					$venuePost['city'] = $user['only_city'];
				}
				if(isset($user['only_country']) && !empty($user['only_country']))
				{
					$venuePost['country'] = $user['only_country'];
				}

				if(isset($user['latitude']) && !empty($user['latitude']))
				{
					$venuePost['latitude'] = $user['latitude'];
				}

				if(isset($user['longitude']) && !empty($user['longitude']))
				{
					$venuePost['longitude'] = $user['longitude'];
				}

				$venuePost['venue_address']   = $user['city'];

				/*echo "<pre>";
				print_r($venuePost);
				echo "</pre>";
				exit;*/

				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('*')
					->from($db->quoteName('#__bcted_venue'))
					->where($db->quoteName('userid') . ' = ' . $db->quote($user['id']));

				// Set the query and load the result.
				$db->setQuery($query);

				$venue = $db->loadObject();


				$tblVenue->load($venue->venue_id);
				$tblVenue->bind($venuePost);
				$tblVenue->store();
			}
			else if($userType == "service")
			{
				$tblCompany = JTable::getInstance('Company', 'BctedTable',array());

				if(isset($user['only_city']) && !empty($user['only_city']))
				{
					$companyPost['city']            = $user['only_city'];
				}
				if(isset($user['only_country']) && !empty($user['only_country']))
				{
					$companyPost['country']            = $user['only_country'];
				}

				if(isset($user['latitude']) && !empty($user['latitude']))
				{
					$companyPost['latitude'] = $user['latitude'];
				}

				if(isset($user['longitude']) && !empty($user['longitude']))
				{
					$companyPost['longitude'] = $user['longitude'];
				}

				$companyPost['company_address']   = $user['city'];

				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('*')
					->from($db->quoteName('#__bcted_company'))
					->where($db->quoteName('userid') . ' = ' . $db->quote($user['id']));

				// Set the query and load the result.
				$db->setQuery($query);

				$company = $db->loadObject();
				$tblCompany->load($company->company_id);
				$tblCompany->bind($companyPost);
				$tblCompany->store();
			}
		}

		return true;
	}

	public static function getAddressDetail($address)
	{
		$address = str_replace(" ", "+", $address);
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.trim($address).'&sensor=false';
		$json = @file_get_contents($url);
		$data=json_decode($json);
		$status = $data->status;
		if($status=="OK")
		{
			foreach ($data->results as $key => $results)
			{
				$address_components = $results->address_components;

				foreach ($address_components as $key => $single_components)
				{
					$types = $single_components->types;
					$cityName = '';

					if(in_array('locality', $types))
					{
						$cityName = $single_components->long_name;
					}
					elseif(in_array('administrative_area_level_2',$types))
					{
						$cityName = $single_components->long_name;
					}
					elseif(in_array('administrative_area_level_1',$types))
					{
						$cityName = $single_components->long_name;
					}

					if(!empty($cityName))
					{
						return $cityName;
					}
				}
			}
		}
	}

	private function disableUserElement($task,$users)
	{
		$bctedConfig = $this->getExtensionParam();

		foreach ($users as $key => $user)
		{
			if($task == 'block')
			{
				$tmpUser = JFactory::getUser($user);
				$groups = $tmpUser->get('groups');

				if(in_array($bctedConfig->club, $groups))
				{
					$userType = "venue";
					// Initialiase variables.
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->quoteName('#__bcted_venue'))
						->set($db->quoteName('venue_active') . ' = ' . $db->quote(0))
						->where($db->quoteName('userid') . ' = ' . $db->quote($user));

					// Set the query and execute the update.
					$db->setQuery($query);
					$db->execute();

					$query2 = $db->getQuery(true);

					// Create the base update statement.
					$query2->update($db->quoteName('#__bcted_venue_table'))
						->set($db->quoteName('venue_table_active') . ' = ' . $db->quote(0))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($user));

					// Set the query and execute the update.
					$db->setQuery($query2);
					$db->execute();

				}
				else if(in_array($bctedConfig->service_provider, $groups))
				{
					$userType = "service";

					// Initialiase variables.
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->quoteName('#__bcted_company'))
						->set($db->quoteName('company_active') . ' = ' . $db->quote(0))
						->where($db->quoteName('userid') . ' = ' . $db->quote($user));

					// Set the query and execute the update.
					$db->setQuery($query);
					$db->execute();

					$query2 = $db->getQuery(true);

					// Create the base update statement.
					$query2->update($db->quoteName('#__bcted_company_services'))
						->set($db->quoteName('service_active') . ' = ' . $db->quote(0))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($user));

					// Set the query and execute the update.
					$db->setQuery($query2);
					$db->execute();

				}
				else if(in_array($bctedConfig->guest, $groups))
				{
					$userType = "guest";

				}

			}
			else if($task == 'unblock')
			{
				$tmpUser = JFactory::getUser($user);
				$groups = $tmpUser->get('groups');

				if(in_array($bctedConfig->club, $groups))
				{
					$userType = "venue";
					// Initialiase variables.
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->quoteName('#__bcted_venue'))
						->set($db->quoteName('venue_active') . ' = ' . $db->quote(1))
						->where($db->quoteName('userid') . ' = ' . $db->quote($user));

					// Set the query and execute the update.
					$db->setQuery($query);
					$db->execute();

					$query2 = $db->getQuery(true);

					// Create the base update statement.
					$query2->update($db->quoteName('#__bcted_venue_table'))
						->set($db->quoteName('venue_table_active') . ' = ' . $db->quote(1))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($user));

					// Set the query and execute the update.
					$db->setQuery($query2);
					$db->execute();

				}
				else if(in_array($bctedConfig->service_provider, $groups))
				{
					$userType = "service";

					// Initialiase variables.
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->quoteName('#__bcted_company'))
						->set($db->quoteName('company_active') . ' = ' . $db->quote(1))
						->where($db->quoteName('userid') . ' = ' . $db->quote($user));

					// Set the query and execute the update.
					$db->setQuery($query);
					$db->execute();

					$query2 = $db->getQuery(true);

					// Create the base update statement.
					$query2->update($db->quoteName('#__bcted_company_services'))
						->set($db->quoteName('service_active') . ' = ' . $db->quote(1))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($user));

					// Set the query and execute the update.
					$db->setQuery($query2);
					$db->execute();

				}
				else if(in_array($bctedConfig->guest, $groups))
				{
					$userType = "guest";

				}
			}
		}
	}

	/**
	 * * get Params For Extension
	 *
	 * @return  object  $params   Global parameters for component
	 */
	private function getExtensionParam()
	{
		$app    = JFactory::getApplication();
		//$option = $app->input->get('option');
		$option = "com_bcted";
		$db     = JFactory::getDbo();

		$option = '%' . $db->escape($option, true) . '%';

		// Initialiase variables.
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->qn('#__extensions'))
			->where($db->qn('name') . ' LIKE ' . $db->q($option))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->order($db->qn('ordering') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();

			$params = json_decode($result->params);
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), 500);
		}

		return $params;
	}

	public function getUserGroups($userID)
	{
		$user = JFactory::getUser($userID);
		$groups = $user->get('groups');
		$groupIDs = implode(",", $groups);

		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('title')
			->from($this->db->qn('#__usergroups'))
			->where($this->db->qn('id') . ' IN ('.$groupIDs.')');

		// Set the query and load the result.
		$this->db->setQuery($query);

		$resultGroup = $this->db->loadColumn();

		return $resultGroup;


	}

	public function syncUsers()
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblBctUserProfile       = JTable::getInstance('Profile', 'BctedTable');

		$db    = JFactory::getDbo();
		$syncSql = "SELECT id FROM `#__users` WHERE id NOT IN (SELECT userid FROM `#__beseated_user_profile`)";
		$db->setQuery($syncSql);
		$syncUsers = $db->loadColumn();

		foreach ($syncUsers as $key => $value)
		{
			$tblBctUserProfile       = JTable::getInstance('Profile', 'BctedTable');
			$profileData = array();
			$profileData['userid'] = $value;
			$profileData['avatar'] = "";
			$profileData['last_update_status'] = time();

			$tmpUser = JFactory::getUser($value);

			$bctedConfig = $this->getExtensionParam();

			$groups = $tmpUser->get('groups');

			if(in_array($bctedConfig->club, $groups))
			{

				$params['settings']['pushNotification']['receiveRequest'] = "1";
			}
			else if(in_array($bctedConfig->service_provider, $groups))
			{

				$params['settings']['pushNotification']['receiveRequest'] = "1";
			}
			else if(in_array($bctedConfig->guest, $groups))
			{
				$params['settings']['social']['connectToFacebook']    = "0";
			}

			//$params['settings']['social']['connectToFacebook']    = "0";
			$params['settings']['pushNotification']['receiveMessage'] = "1";
			$params['settings']['pushNotification']['updateMyBookingStatus']   = "1";

			$profileData['params'] = json_encode($params);

			$tblBctUserProfile->bind($profileData);

			$tblBctUserProfile->store();
		}
	}

	/**
	 * Remove all sessions for the user name
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array    $user     Holds the user data
	 * @param   boolean  $success  True if user was succesfully stored in the database
	 * @param   string   $msg      Message
	 *
	 * @return  boolean
	 *
	 * @since   0.0.1
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{

		/*echo "<pre>";
		print_r($user);
		echo "</pre>";
		exit;

		Array
		(
		    [isRoot] =>
		    [id] => 97
		    [name] => todelete
		    [username] => todelete
		    [email] => todelete@gmail.com
		    [password] => $2y$10$lVsYbISXYqxOR5c3a7QZjOGsyIgIdN8NfyrYrVNExCyStyQjSyoya
		    [password_clear] =>
		    [block] => 0
		    [sendEmail] => 0
		    [registerDate] => 2015-02-05 05:27:20
		    [lastvisitDate] => 0000-00-00 00:00:00
		    [activation] =>
		    [params] => {"admin_style":"","admin_language":"","language":"","editor":"","helpsite":"","timezone":""}
		    [groups] => Array
		        (
		            [11] => 11
		        )

		    [guest] => 0
		    [lastResetTime] => 0000-00-00 00:00:00
		    [resetCount] => 0
		    [requireReset] => 0
		    [otpKey] =>
		    [otep] =>
		)
		*/
		$bctedConfig = $this->getExtensionParam();

		$groups = $user['groups'];

		if(in_array($bctedConfig->club, $groups))
		{
			$userType = "venue";
		}
		else if(in_array($bctedConfig->service_provider, $groups))
		{
			$userType = "service";
		}
		else if(in_array($bctedConfig->guest, $groups))
		{
			$userType = "guest";
		}


		// Initialiase variables.
		$db    = JFactory::getDbo();

		// Delete Heartdart User Profile
		/*$queryProfile   = $db->getQuery(true);
		$queryProfile->delete()
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('userid') . ' = ' . $db->quote((int) $user['id']));
		$db->setQuery($queryProfile);
		$db->execute();*/

		$queryUpdtProfile   = $db->getQuery(true);
			$queryUpdtProfile->update($db->quoteName('#__beseated_user_profile'))
				->set($db->quoteName('user_email') . ' = ' . $db->quote($user['email']))
				->set($db->quoteName('first_name') . ' = ' . $db->quote($user['name']))
				->where($db->quoteName('userid') . ' = ' . $db->quote($user['id']));
			$db->setQuery($queryUpdtProfile);
			$db->execute();

		if($userType == 'venue')
		{
			/*$querySelVenue = $db->getQuery(true);

			// Create the base select statement.
			$querySelVenue->select('venue_id')
				->from($db->quoteName('#__bcted_venue'))
				->where($db->quoteName('userid') . ' = ' . $db->quote($user['id']));

			// Set the query and load the result.
			$db->setQuery($querySelVenue);

			$venueID = $db->loadResult();*/

			$queryUpdtVenue   = $db->getQuery(true);
			$queryUpdtVenue->update($db->quoteName('#__bcted_venue'))
				->set($db->quoteName('venue_active') . ' = ' . $db->quote(0))
				->where($db->quoteName('userid') . ' = ' . $db->quote($user['id']));
			$db->setQuery($queryUpdtVenue);
			$db->execute();

			$queryUpdtVenuetable = $db->getQuery(true);
			$queryUpdtVenuetable->update($db->quoteName('#__bcted_venue_table'))
				->set($db->quoteName('venue_table_active') . ' = ' . $db->quote(0))
				->where($db->quoteName('user_id') . ' = ' . $db->quote($user['id']));
			$db->setQuery($queryUpdtVenuetable);
			$db->execute();
			$queryVenService = $db->getQuery(true);
			$queryVenService->select('venue_id')
				->from($db->quoteName('#__bcted_venue'))
				->where($db->quoteName('userid') . ' = ' . $db->quote($user['id']));

			// Set the query and load the result.
			$db->setQuery($queryVenService);
			$venueArray = $db->loadColumn();

			if(count($venueArray)!=0)
			{
				$strString = implode(",", $venueArray);
				$queryDelFav   = $db->getQuery(true);
				$queryDelFav->delete()
					->from($db->quoteName('#__bcted_favourites'))
					->where($db->quoteName('favourite_type') . ' = ' . $db->quote('Venue'))
					->where($db->quoteName('favourited_id') . ' IN (' . $strString .')');
				$db->setQuery($queryDelFav);
				$db->execute();
			}

			/*$queryVenue   = $db->getQuery(true);
			$queryVenue->delete()
				->from($db->quoteName('#__bcted_venue'))
				->where($db->quoteName('venue_id') . ' = ' . $db->quote((int) $venueID));
			$db->setQuery($queryVenue);
			$db->execute();

			$queryVenueTable   = $db->getQuery(true);
			$queryVenueTable->delete()
				->from($db->quoteName('#__bcted_venue_table'))
				->where($db->quoteName('venue_id') . ' = ' . $db->quote((int) $venueID));
			$db->setQuery($queryVenueTable);
			$db->execute();*/
		}
		else if($userType == 'service')
		{
			$queryUpdtCompany = $db->getQuery(true);
			$queryUpdtCompany->update($db->quoteName('#__bcted_company'))
				->set($db->quoteName('company_active') . ' = ' . $db->quote(0))
				->where($db->quoteName('userid') . ' = ' . $db->quote($user['id']));

			// Set the query and execute the update.
			$db->setQuery($queryUpdtCompany);
			$db->execute();

			$queryUpdtCompanyService = $db->getQuery(true);
			$queryUpdtCompanyService->update($db->quoteName('#__bcted_company_services'))
				->set($db->quoteName('service_active') . ' = ' . $db->quote(0))
				->where($db->quoteName('user_id') . ' = ' . $db->quote($user['id']));
			$db->setQuery($queryUpdtCompanyService);
			$db->execute();
			$querySelService = $db->getQuery(true);
			$querySelService->select('service_id')
				->from($db->quoteName('#__bcted_company_services'))
				->where($db->quoteName('user_id') . ' = ' . $db->quote($user['id']));

			// Set the query and load the result.
			$db->setQuery($querySelService);
			$servicesArray = $db->loadColumn();

			if(count($servicesArray)!=0)
			{
				$strString = implode(",", $servicesArray);
				$queryDelFav   = $db->getQuery(true);
				$queryDelFav->delete()
					->from($db->quoteName('#__bcted_favourites'))
					->where($db->quoteName('favourite_type') . ' = ' . $db->quote('Service'))
					->where($db->quoteName('favourited_id') . ' IN (' . $strString .')');
				$db->setQuery($queryDelFav);
				$db->execute();
			}





			// Create the base select statement.
			/*$querySelCompany->select('company_id')
				->from($db->quoteName('#__bcted_company'))
				->where($db->quoteName('userid') . ' = ' . $db->quote($user['id']));

			// Set the query and load the result.
			$db->setQuery($querySelCompany);

			$companyID = $db->loadResult();*/

			/*$queryCompany   = $db->getQuery(true);
			$queryCompany->delete()
				->from($db->quoteName('#__bcted_company'))
				->where($db->quoteName('company_id') . ' = ' . $db->quote((int) $companyID));
			$db->setQuery($queryCompany);
			$db->execute();

			$queryCompanyService   = $db->getQuery(true);
			$queryCompanyService->delete()
				->from($db->quoteName('#__bcted_company_services'))
				->where($db->quoteName('company_id') . ' = ' . $db->quote((int) $companyID));
			$db->setQuery($queryCompanyService);
			$db->execute();*/
		}
		else if($userType == "guest")
		{

		}
		//$this->syncData($user['id'],'user'); //Record Delete User in Sync Data

		/*// Delete User Friends
		$queryFriends   = $db->getQuery(true);
		$queryFriends->delete()
			->from($db->quoteName('#__heartdart_friend'))
			->where($db->quoteName('from_user') . ' = ' . $db->quote((int) $user['id']));
		$db->setQuery($queryFriends);
		$db->execute();

		//Delete User Friends
		$queryFriends   = $db->getQuery(true);
		$queryFriends->delete()
			->from($db->quoteName('#__heartdart_friend'))
			->where($db->quoteName('to_user') . ' = ' . $db->quote((int) $user['id']));
		$db->setQuery($queryFriends);
		$db->execute();

		//SELECT user Message.
		$querySELmsg = $db->getQuery(true);
		$querySELmsg->select('msg_id')
			->from($db->quoteName('#__heartdart_message'))
			->where($db->quoteName('userid') . ' = ' . $db->quote($user['id']));
		$db->setQuery($querySELmsg);
		$userMessages = $db->loadColumn();

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_heartdart/tables');
		$tblMessage       = JTable::getInstance('Message', 'HeartdartTable');

		foreach ($userMessages as $key => $message)
		{
			$tblMessage->load($message);

			if($tblMessage->delete())
			{
				$this->syncData($message, 'message');
			}
		}

		// Delete following record which is added by delete user
		$queryFollowing = $db->getQuery(true);
		$queryFollowing->delete()
			->from($db->quoteName('#__heartdart_following'))
			->where($db->quoteName('userid') . ' = ' . $db->quote((int) $user['id']));
		$db->setQuery($queryFollowing);
		$db->execute();*/

		// Delete following record if user connected with facebook
		$queryFBUser = $db->getQuery(true);
		$queryFBUser->delete()
			->from($db->quoteName('#__facebook_joomla_connect'))
			->where($db->quoteName('joomla_userid') . ' = ' . $db->quote((int) $user['id']));
		$db->setQuery($queryFBUser);
		$db->execute();

		return true;
	}


	/**
	 * This method should handle any logout logic and report back to the subject
	 *
	 * @param   array  $user     Holds the user data.
	 * @param   array  $options  Array holding options (client, ...).
	 *
	 * @return  object  True on success
	 *
	 * @since   0.0.1
	 */
	public function onUserLogout($user, $options = array())
	{
		//$userID = $user['id'];

		// Initialiase variables.
		/*$db    = JFactory::getDbo();
		$query = $db->getQuery(true);*/

		// Create the base delete statement.
		/*$query->delete()
			->from($db->quoteName('#__ijoomeradv_users'))
			->where($db->quoteName('userid') . ' = ' . $db->quote((int) $userID));

		// Set the query and execute the delete.
		$db->setQuery($query);
		$db->execute();*/

		/*try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}*/
		return true;
	}

	/*public function syncData($elemtnID,$elementType)
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblSyncdata       = JTable::getInstance('Syncdata', 'BctedTable');

		$tblSyncdata->load(0);

		$postData['element_id']   = $elemtnID;
		$postData['element_type'] = $elementType;
		$postData['timestamp']    = time();

		$tblSyncdata->bind($postData);
		$tblSyncdata->store();

	}*/
}
