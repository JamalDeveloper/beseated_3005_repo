<?php
/**
 * @package     Beseated.Plugin
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * PlgUserBeseated plugin
 *
 * @since  0.0.1
 */
class PlgUserBeseatedUser extends JPlugin
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
		
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblBeseatedProfile       = JTable::getInstance('Profile', 'BeseatedTable');

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$beseatedParams = BeseatedHelper::getExtensionParam();

		$tmpUser = JFactory::getUser($user['id']);
		$groups  = $tmpUser->get('groups');

		if(in_array($beseatedParams->beseated_guest, $groups))
		{
			$userType = "beseated_guest";
		}
		else if(in_array($beseatedParams->chauffeur, $groups))
		{
			$userType = "chauffeur";
		}
		else if(in_array($beseatedParams->protection, $groups))
		{
			$userType = "protection";
		}
		else if(in_array($beseatedParams->venue, $groups))
		{
			$userType = "venue";
		}
		else if(in_array($beseatedParams->yacht, $groups))
		{
			$userType = "yacht";
		}


		if($isnew && $success)
		{
			$notifType = array('bookings','notifications','requests','messages');
			// Initialiase variables.
			$db    = JFactory::getDbo();
			// $db = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base insert statement.
			$query->insert($db->quoteName('#__beseated_notification_show_detail'))
				->columns(array($db->quoteName('notification_type'), $db->quoteName('user_id'), $db->quoteName('time_stamp')));

			for ($i = 0; $i < count($notifType); $i++)
			{
				$query->values($db->quote($notifType[$i]) . ', ' . $db->quote($user['id']). ', ' . $db->quote(time()));
			}

			// Set the query and execute the insert.
			$db->setQuery($query);

			$db->execute();

			// Initialiase variables.
			$query = $db->getQuery(true);

			if($userType == 'beseated_guest')
			{
				$is_fb_user = $user['is_fb_user'];
				$fb_id      = $user['fb_id'];

				if($is_fb_user)
				{
					$thumb = "http://graph.facebook.com/".$fb_id."/picture?type=large";
					$avatar = "https://graph.facebook.com/".$fb_id."/picture?width=150";
				}else{
					$thumb = "";
					$avatar = "";
				}

				$only_city = $this->getAddress($user['city']);
				$lat       = $only_city['lat'];
				$long      = $only_city['long'];
				$city      = $only_city['city'];

				// Create the base insert statement.
				$query->insert($db->quoteName('#__beseated_user_profile'))
				->columns(
					array(
						$db->quoteName('user_id'),
						$db->quoteName('user_type'),
						$db->quoteName('full_name'),
						$db->quoteName('email'),
						$db->quoteName('phone'),
						$db->quoteName('birthdate'),
						$db->quoteName('avatar'),
						$db->quoteName('thumb_avatar'),
						$db->quoteName('location'),
						$db->quoteName('city'),
						$db->quoteName('latitude'),
						$db->quoteName('longitude'),
						$db->quoteName('is_fb_user'),
						$db->quoteName('fb_id'),
						$db->quoteName('notification'),
						$db->quoteName('show_in_biggest_spender'),
						$db->quoteName('time_stamp')
					)
				)
				->values(
					$db->quote($user['id']) . ', ' .
					$db->quote($userType) . ', ' .
					$db->quote($user['name']) . ', ' .
					$db->quote($user['email']) . ', ' .
					$db->quote($user['mobile']) . ', ' .
					$db->quote($user['birthdate']) . ', ' .
					$db->quote($avatar) . ', ' .
					$db->quote($thumb) . ', ' .
					$db->quote($user['city']) . ', ' .
					$db->quote($city) . ', ' .
					$db->quote($lat) . ', ' .
					$db->quote($long) . ', ' .
					$db->quote($user['is_fb_user']) . ', ' .
					$db->quote($user['fb_id']) . ', ' .
					$db->quote(1) . ', ' .
					$db->quote(1) . ', ' .
					$db->quote(time())
				);

				$db->setQuery($query);
				$db->execute();

				$email      = $user['email'];
				$fb_id      = $user['fb_id'];
				$is_fb_user = $user['is_fb_user'];
				$user_id    = $user['id'];
				// Replace Split user for Chauffeur
				$sqlSplitChauffeur = $db->getQuery(true);
				$sqlSplitChauffeur->update($db->quoteName('#__beseated_chauffeur_booking_split'))
					->set($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
					->where($db->quoteName('email') . ' = ' . $db->quote($email))
					->where($db->quoteName('fbid') . ' = ' . $db->quote($fb_id));
				$db->setQuery($sqlSplitChauffeur);
				$db->execute();

				// Replace Split user for Protection
				$sqlSplitProtection = $db->getQuery(true);
				$sqlSplitProtection->update($db->quoteName('#__beseated_protection_booking_split'))
					->set($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
					->where($db->quoteName('email') . ' = ' . $db->quote($email))
					->where($db->quoteName('fbid') . ' = ' . $db->quote($fb_id));
				$db->setQuery($sqlSplitProtection);
				$db->execute();

				// Replace Split user for Venue
				$sqlSplitVenue = $db->getQuery(true);
				$sqlSplitVenue->update($db->quoteName('#__beseated_venue_table_booking_split'))
					->set($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
					->where($db->quoteName('email') . ' = ' . $db->quote($email))
					->where($db->quoteName('fbid') . ' = ' . $db->quote($fb_id));
				$db->setQuery($sqlSplitVenue);
				$db->execute();

				// Replace Split user for Yacht
				$sqlSplitYacht = $db->getQuery(true);
				$sqlSplitYacht->update($db->quoteName('#__beseated_yacht_booking_split'))
					->set($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
					->where($db->quoteName('email') . ' = ' . $db->quote($email))
					->where($db->quoteName('fbid') . ' = ' . $db->quote($fb_id));
				$db->setQuery($sqlSplitYacht);
				$db->execute();

				// Replace Invitation user for ALL
				$sqlInvitation = $db->getQuery(true);
				$sqlInvitation->update($db->quoteName('#__beseated_invitation'))
					->set($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
					->where($db->quoteName('email') . ' = ' . $db->quote($email))
					->where($db->quoteName('fbid') . ' = ' . $db->quote($fb_id));
				$db->setQuery($sqlInvitation);
				$db->execute();

				// Replace Invitation user for Event ticket booking
				$sqlTicketInvitation = $db->getQuery(true);
				$sqlTicketInvitation->update($db->quoteName('#__beseated_event_ticket_booking_invite'))
					->set($db->quoteName('invited_user_id') . ' = ' . $db->quote($user_id))
					->where($db->quoteName('email') . ' = ' . $db->quote($email))
					->where($db->quoteName('fbid') . ' = ' . $db->quote($fb_id));
				$db->setQuery($sqlTicketInvitation);
				$db->execute();

				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('ticket_booking_detail_id')
					->from($db->quoteName('#__beseated_event_ticket_booking_invite'))
					->where($db->quoteName('email') . ' = ' . $db->quote($email))
					->where($db->quoteName('fbid') . ' = ' . $db->quote($fb_id));

				// Set the query and load the result.
				$db->setQuery($query);
				$ticket_booking_detail_id = $db->loadResult();

				if($ticket_booking_detail_id)
				{
					$tblTicketBookingDetail       = JTable::getInstance('TicketBookingDetail', 'BeseatedTable');

					$tblTicketBookingDetail->load($ticket_booking_detail_id);
					$tblTicketBookingDetail->user_id = $user_id;
					$tblTicketBookingDetail->store();

					// Initialiase variables.
					$db    = JFactory::getDbo();

					// $db    = $this->getDbo();
					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->quoteName('#__beseated_notification'))
						->set($db->quoteName('target') . ' = ' . $db->quote($user['id']))
						->where($db->quoteName('notification_type') . ' = ' . $db->quote('Event'))
						->where($db->quoteName('cid') . ' = ' . $db->quote($ticket_booking_detail_id));

					// Set the query and execute the update.
					$db->setQuery($query);

					$db->execute();

				}

				$query = $db->getQuery(true);
				// Create the base select statement.
				$query->select('*')
					->from($db->quoteName('#__beseated_user_refer'))
					->where($db->quoteName('refer_email') . ' = ' . $db->quote($email))
					->order($db->quoteName('time_stamp') . ' ASC');

				// Set the query and load the result.
				$db->setQuery($query);

				$result = $db->loadObject();

				if($result)
				{
					$tblRefer  = JTable::getInstance('Refer', 'BeseatedTable',array());
					$tblRefer->load($result->refer_id);
					$tblRefer->ref_user_id = $user['id'];
					$tblRefer->is_registered = 1;
					$tblRefer->store();

				}
			}
			else if ($userType == 'chauffeur' || $userType == 'protection' || $userType == 'venue' || $userType == 'yacht')
			{
				// Create the base insert statement.
				$query->insert($db->quoteName('#__beseated_user_profile'))
					->columns(
					array(
						$db->quoteName('user_id'),
						$db->quoteName('user_type'),
						$db->quoteName('full_name'),
						$db->quoteName('email'),
						$db->quoteName('location'),
						$db->quoteName('city'),
						$db->quoteName('latitude'),
						$db->quoteName('longitude'),
						$db->quoteName('time_stamp')
					)
					)
					->values(
					$db->quote($user['id']) . ', ' .
					$db->quote($userType) . ', ' .
					$db->quote($user['name']) . ', ' .
					$db->quote($user['email']) . ', ' .
					$db->quote($user['city']) . ', ' .
					$db->quote($user['only_city']) . ', ' .
					$db->quote($user['latitude']) . ', ' .
					$db->quote($user['longitude']) . ', ' .
					$db->quote(time())
					);

				if($userType == 'chauffeur')
				{
					$tblChauffeur                 = JTable::getInstance('Chauffeur', 'BeseatedTable');
					$tblChauffeur->load(0);
					$tblChauffeur->user_id        = $user['id'];
					$tblChauffeur->chauffeur_name = $user['name'];
					$tblChauffeur->location       = $user['city'];
					$tblChauffeur->city           = $user['only_city'];
					$tblChauffeur->latitude       = $user['latitude'];
					$tblChauffeur->longitude      = $user['longitude'];
					$tblChauffeur->currency_code  = "AED";
					$tblChauffeur->currency_sign  = "AED";
					$tblChauffeur->published      = 1;
					$tblChauffeur->time_stamp     = time();
					$tblChauffeur->store();
				}
				else if($userType == 'protection')
				{
					$tblProtection                  = JTable::getInstance('Protection', 'BeseatedTable');
					$tblProtection->load(0);
					$tblProtection->user_id         = $user['id'];
					$tblProtection->protection_name = $user['name'];
					$tblProtection->location        = $user['city'];
					$tblProtection->city            = $user['only_city'];
					$tblProtection->latitude        = $user['latitude'];
					$tblProtection->longitude       = $user['longitude'];
					$tblProtection->currency_code   = "AED";
					$tblProtection->currency_sign   = "AED";
					$tblProtection->published       = 1;
					$tblProtection->time_stamp      = time();
					$tblProtection->store();
				}
				else if($userType == 'venue')
				{
					if(!isset($user['is_day_club'])){
						$user['is_day_club'] = 0;
					}

					$tblVenue             = JTable::getInstance('Venue', 'BeseatedTable');
					$tblVenue->load(0);
					$tblVenue->user_id       = $user['id'];
					$tblVenue->venue_name    = $user['name'];
					$tblVenue->location      = $user['city'];
					$tblVenue->city          = $user['only_city'];
					$tblVenue->latitude      = $user['latitude'];
					$tblVenue->longitude     = $user['longitude'];
					$tblVenue->is_day_club   = $user['is_day_club'];
					$tblVenue->currency_code = "AED";
					$tblVenue->currency_sign = "AED";
					$tblVenue->published     = 1;
					$tblVenue->time_stamp    = time();
					$tblVenue->store();
				}
				else if($userType == 'yacht')
				{
					$tblYacht             = JTable::getInstance('Yacht', 'BeseatedTable');
					$tblYacht->load(0);
					$tblYacht->user_id       = $user['id'];
					$tblYacht->yacht_name    = $user['name'];
					$tblYacht->location      = $user['city'];
					$tblYacht->city          = $user['only_city'];
					$tblYacht->latitude      = $user['latitude'];
					$tblYacht->longitude     = $user['longitude'];
					$tblYacht->currency_code = "AED";
					$tblYacht->currency_sign = "AED";
					$tblYacht->published     = 1;
					$tblYacht->time_stamp    = time();
					$tblYacht->store();
				}
			}

			// Set the query and execute the insert.
			$db->setQuery($query);

			try
			{
				$db->execute();

			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException($e->getMessage(), $e->getCode());
			}

			if($isnew){
				$sqlAllJUser = $db->getQuery(true);

				$sqlAllJUser = "SELECT * FROM `#__users` WHERE `id` NOT IN (SELECT `user_id` FROM `#__beseated_user_profile`)";
				$db->setQuery($sqlAllJUser);
				$resNotBeseatedUsers = $db->loadObjectList();
				foreach ($resNotBeseatedUsers as $key => $newBeseated)
				{
					$NJUser = JFactory::getUser($newBeseated->id);
					$isroot = $NJUser->authorise('core.admin');
					$nUserType = ($isroot)?'administrator':'beseated_guest';
					$sqlNewUser = $db->getQuery(true);
					$sqlNewUser->insert($db->quoteName('#__beseated_user_profile'))
					->columns(
						array(
							$db->quoteName('user_id'),
							$db->quoteName('user_type'),
							$db->quoteName('full_name'),
							$db->quoteName('email'),
							$db->quoteName('time_stamp')
						)
					)
					->values(
						$db->quote($newBeseated->id) . ', ' .
						$db->quote($nUserType) . ', ' .
						$db->quote($newBeseated->name) . ', ' .
						$db->quote($newBeseated->email) . ', ' .
						$db->quote(time())
					);

					$db->setQuery($sqlNewUser);
					$db->execute();
				}
			}
		}
		else if ($success)
		{
			$tblBeseatedProfile            = JTable::getInstance('Profile', 'BeseatedTable');
			$tblBeseatedProfile->load($user['id']);
			$tblBeseatedProfile->user_type = $userType;
			$tblBeseatedProfile->full_name = $user['name'];
			$tblBeseatedProfile->email     = $user['email'];

			if(isset($user['mobile']) && !empty($user['mobile']))
				$tblBeseatedProfile->phone      = $user['mobile'];

			if(isset($user['birthdate']) && !empty($user['birthdate']))
				$tblBeseatedProfile->birthdate  = $user['birthdate'];

			if(isset($user['city']) && !empty($user['city']))
				$tblBeseatedProfile->location   = $user['city'];

			if(isset($user['only_city']) && !empty($user['only_city']))
				$tblBeseatedProfile->city       = $user['only_city'];

			if(isset($user['latitude']) && !empty($user['latitude']))
				$tblBeseatedProfile->latitude   = $user['latitude'];

			if(isset($user['longitude']) && !empty($user['longitude']))
				$tblBeseatedProfile->longitude  = $user['longitude'];

			if(isset($user['is_fb_user']) && !empty($user['is_fb_user']))
				$tblBeseatedProfile->is_fb_user = $user['is_fb_user'];

			if(isset($user['fb_id']) && !empty($user['fb_id']))
				$tblBeseatedProfile->fb_id      = $user['fb_id'];

			$tblBeseatedProfile->time_stamp = time();
			$tblBeseatedProfile->store();

			$db = JFactory::getDbo();

			if($userType == 'chauffeur')
			{
				$query = $db->getQuery(true);
				$query->select('*')
					->from($db->quoteName('#__beseated_chauffeur'))
					->where($db->quoteName('user_id') . ' = ' . $db->quote($user['id']));
				$db->setQuery($query);
				$elementDetail = $db->loadObject();
				$tblChauffeur                 = JTable::getInstance('Chauffeur', 'BeseatedTable');
				$tblChauffeur->load($elementDetail->chauffeur_id);
				$only_city = $this->getAddress($elementDetail->location);
				$tblChauffeur->user_id        = $user['id'];
				$tblChauffeur->chauffeur_name = $user['name'];
				$tblChauffeur->location       = $elementDetail->location;
				$tblChauffeur->city           = $only_city['city'];
				$tblChauffeur->latitude       = $only_city['lat'];
				$tblChauffeur->longitude      = $only_city['long'];
				$tblChauffeur->currency_code  = "AED";
				$tblChauffeur->currency_sign  = "AED";
				$tblChauffeur->published      = 1;
				$tblChauffeur->time_stamp     = time();
				$tblChauffeur->store();
			}
			else if($userType == 'protection')
			{
				$query = $db->getQuery(true);
				$query->select('*')
					->from($db->quoteName('#__beseated_protection'))
					->where($db->quoteName('user_id') . ' = ' . $db->quote($user['id']));
				$db->setQuery($query);
				$elementDetail = $db->loadObject();
				$tblProtection                  = JTable::getInstance('Protection', 'BeseatedTable');
				$tblProtection->load($elementDetail->protection_id);
				$only_city = $this->getAddress($elementDetail->location);
				$tblProtection->user_id         = $user['id'];
				$tblProtection->protection_name = $user['name'];
				$tblProtection->location        = $elementDetail->location;
				$tblProtection->city            = $only_city['city'];
				$tblProtection->latitude        = $only_city['lat'];
				$tblProtection->longitude       = $only_city['long'];
				$tblProtection->currency_code   = "AED";
				$tblProtection->currency_sign   = "AED";
				$tblProtection->published       = 1;
				$tblProtection->time_stamp      = time();
				$tblProtection->store();
			}
			else if($userType == 'venue')
			{
				$input = JFactory::getApplication()->input;
				if(!isset($user['is_day_club'])){
					$user['is_day_club'] = 0;
				}

				$query = $db->getQuery(true);
				$query->select('*')
					->from($db->quoteName('#__beseated_venue'))
					->where($db->quoteName('user_id') . ' = ' . $db->quote($user['id']));
				$db->setQuery($query);
				$elementDetail = $db->loadObject();


				if (isset($user['city']))
					$location = $user['city'];
				else
					$location = $input->get('location','','string');

				if (isset($user['latitude']))
					$latitude = $user['latitude'];
				else
					$latitude = $input->get('latitude');

				if (isset($user['longitude']))
					$longitude = $user['longitude'];
				else
					$longitude = $input->get('longitude');

				if (isset($user['only_city']))
					$only_city = $user['only_city'];
				else
					$only_city = $input->get('city');

				$tblVenue             = JTable::getInstance('Venue', 'BeseatedTable');
				$tblVenue->load($elementDetail->venue_id);
				$tblVenue->user_id       = $user['id'];
				$tblVenue->venue_name    = $user['name'];
				$tblVenue->location      = $location;
				$tblVenue->city          = $only_city;
				$tblVenue->latitude      = $latitude;
				$tblVenue->longitude     = $longitude;
				$tblVenue->is_day_club   = $user['is_day_club'];
				//$tblVenue->currency_code = "AED";
				//$tblVenue->currency_sign = "AED";
				$tblVenue->published     = 1;
				$tblVenue->time_stamp    = time();
				$tblVenue->store();
			}
			else if($userType == 'yacht')
			{
				$query = $db->getQuery(true);
				$query->select('*')
					->from($db->quoteName('#__beseated_yacht'))
					->where($db->quoteName('user_id') . ' = ' . $db->quote($user['id']));
				$db->setQuery($query);
				$elementDetail = $db->loadObject();
				$tblYacht                = JTable::getInstance('Yacht', 'BeseatedTable');
				$tblYacht->load($elementDetail->yacht_id);
				$only_city               = $this->getAddress($elementDetail->location);
				$tblYacht->user_id       = $user['id'];
				$tblYacht->yacht_name    = $user['name'];
				$tblYacht->location      = $elementDetail->location;
				$tblYacht->city          = $only_city['city'];
				$tblYacht->latitude      = $only_city['lat'];
				$tblYacht->longitude     = $only_city['long'];
				$tblYacht->currency_code = "AED";
				$tblYacht->currency_sign = "AED";
				$tblYacht->published     = 1;
				$tblYacht->time_stamp    = time();
				$tblYacht->store();
			}
		}

		return true;
	}

	public function syncUsers()
	{

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
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblBeseatedProfile       = JTable::getInstance('Profile', 'BeseatedTable');
		$tblBeseatedProfile->load($user['id']);

		if($tblBeseatedProfile->user_id)
		{
			$tblBeseatedProfile->is_deleted = 1;
			if($tblBeseatedProfile->store())
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				
				// Create the base delete statement.
				$query->delete()
					->from($db->quoteName('#__facebook_joomla_connect'))
					->where($db->quoteName('joomla_userid') . ' = ' . $db->quote($tblBeseatedProfile->user_id));
				
				// Set the query and execute the delete.
				$db->setQuery($query);
				
				$db->execute();
			}
		}

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$beseatedParams = BeseatedHelper::getExtensionParam();

		$tmpUser = JFactory::getUser($user['id']);
		$groups = $tmpUser->get('groups');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$executeQuery = 0;

		if(in_array($beseatedParams->chauffeur, $groups))
		{
			$executeQuery = 1;
			$userType = "chauffeur";
			// Create the base update statement.
			$query->update($db->quoteName('#__beseated_chauffeur'))
				->set($db->quoteName('published') . ' = ' . $db->quote(0))
				->where($db->quoteName('user_id') . ' = ' . $db->quote($user['id']));

		}
		else if(in_array($beseatedParams->protection, $groups))
		{
			$executeQuery = 1;
			$userType = "protection";
			// Create the base update statement.
			$query->update($db->quoteName('#__beseated_protection'))
				->set($db->quoteName('published') . ' = ' . $db->quote(0))
				->where($db->quoteName('user_id') . ' = ' . $db->quote($user['id']));
		}
		else if(in_array($beseatedParams->venue, $groups))
		{
			$executeQuery = 1;
			$userType = "venue";
			// Create the base update statement.
			$query->update($db->quoteName('#__beseated_venue'))
				->set($db->quoteName('published') . ' = ' . $db->quote(0))
				->where($db->quoteName('user_id') . ' = ' . $db->quote($user['id']));

		}
		else if(in_array($beseatedParams->yacht, $groups))
		{
			$executeQuery = 1;
			$userType = "yacht";
			// Create the base update statement.
			$query->update($db->quoteName('#__beseated_yacht'))
				->set($db->quoteName('published') . ' = ' . $db->quote(0))
				->where($db->quoteName('user_id') . ' = ' . $db->quote($user['id']));
		}

		if($executeQuery)
		{
			// Set the query and execute the update.
			$db->setQuery($query);
			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException($e->getMessage(), $e->getCode());
			}
		}

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
		return true;
	}

	public function getAddress($address)
	{

		$address = str_replace(" ", "+", $address);
		$json    = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&key=AIzaSyAKf4wfPXxaajH5eB3mH4Bsc7G-jSAhKKI");
		$json    = json_decode($json);

		$only_city = array();
		$only_city['lat']  = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
		$only_city['long'] = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
		$only_city['city'] = $json->{'results'}[0]->{'address_components'}[0]->{'long_name'};

	    return $only_city;
	}

}
