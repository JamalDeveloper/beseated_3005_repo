<?php
/**
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv.models
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Class For The IJoomeradvModelijoomeradv which will extends JModelLegacy
 *
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv.model
 * @since       1.0
 */
class IjoomeradvModelijoomeradv extends JModelLegacy
{
	private $db;

	private $mainframe;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->db = JFactory::getDBO();
		$this->mainframe = JFactory::getApplication();
		$this->jsonarray = array();

	}

	/**
	 * fetches ijoomeradv global config
	 *
	 * @return  it will return loadobjectlist
	 */
	public function getApplicationConfig()
	{
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('name, value')
			->from($this->db->qn('#__ijoomeradv_config'));

		// Set the query and load the result.
		$this->db->setQuery($query);

		try
		{
			$result = $this->db->loadObjectList();

			return $result;
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Fetches All Published Extensions
	 *
	 * @return  it will return the value of components
	 */
	public function getExtensions()
	{
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->qn('#__ijoomeradv_extensions'))
			->where($this->db->qn('published') . ' = ' . $this->db->q('1'));

		// Set the query and load the result.
		$this->db->setQuery($query);

		try
		{
			$components = $this->db->loadObjectList();

			return $components;
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Method to get the available viewnames.
	 *
	 * @return   array    Array of viewnames.
	 *
	 * @since    1.0
	 */
	public function getViewNames()
	{
		jimport('joomla.filesystem.file');

		$components = $this->getExtensions();

		foreach ($components as $component)
		{
			$mainXML = JPATH_SITE . '/components/com_ijoomeradv/extensions/' . $component->classname . '.xml';

			if (is_file($mainXML))
			{
				$options[$component->classname] = $this->getTypeOptionsFromXML($mainXML);
			}
		}

		return $options;
	}

	/**
	 * Get Type Options FromXML
	 *
	 * @param   [type]  $file  contains the value of file
	 *
	 * @return  array  $options
	 */
	private function getTypeOptionsFromXML($file)
	{
		$options = array();

		if ($xml = simplexml_load_file($file))
		{
			$views = $xml->xpath('views');

			if (!empty($views))
			{
				foreach ($views[0]->view as $value)
				{
					$options[] = (string) $value->remoteTask;
				}
			}
		}

		return $options;
	}

	/**
	 * Fetches ijoomeradv Menu items
	 *
	 * @return array it will return Menu Array
	 */
	public function getMenus()
	{
		$menuArray = array();
		$positionScreens = array();
		$user = JFactory::getUser();
		$device = IJReq::getTaskData('device');

		if ($device == 'android')
		{
			$menudevice = 2;
			$device_type = IJReq::getTaskData('type', 'hdpi');
		}
		elseif ($device == 'iphone')
		{
			$menudevice = 3;
			$device_type = IJReq::getTaskData('type', '3');

			if ($device_type == 5)
			{
				$device_type = 4;
			}
		}

		$groups = implode(',', $user->getAuthorisedViewLevels());

		$lang = $_REQUEST['lang'];

        if($lang)
        {
            $query = 'SELECT lang_code
                      FROM #__languages
                      WHERE sef="'.$lang.'"';
            $this->db->setQuery($query);

            $langCode = $this->db->loadResult();
        }
        else
        {
            $lang1 =& JFactory::getLanguage();
            $langCode = $lang1->gettag();
        }

		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->qn('#__ijoomeradv_menu_types'));

		// Set the query and load the result.
		$this->db->setQuery($query);

		$menus = $this->db->loadObjectList();

		$i = 0;

		if (!empty($menus))
		{
			foreach ($menus as $value)
			{
				if ($value->position == 1)
				{
					$screennames = json_decode('[]');
				}
				else
				{
					$screens = json_decode($value->screen);
					$screennames = array();

					if ($screens)
					{
						foreach ($screens as $val)
						{
							foreach ($val as $screen)
							{
								$screenname = (explode('.', $screen));
								$screennames[] = $screenname[2];
								$positionScreens[$value->position][] = $screenname[2];
							}
						}
					}
				}

				if (($screennames && $value->position > 1) || $value->position == 1)
				{
					$menuArray[$i] = array("menuid" => $value->id,
						"menuname" => $value->title,
						"menuposition" => $value->position,
						"screens" => $screennames
					);

					// Add IF condition for if menuitem for specific device avail or not
					// if global selected then check avaibility in menu
					/*$query = $this->db->getQuery(true);

					// Create the base select statement.
					$query->select('*')
						->from($this->db->qn('#__ijoomeradv_menu'))
						->where($this->db->qn('menutype') . ' = ' . $this->db->q($value->id))
						->where($this->db->qn('published') . ' = ' . $this->db->q('1'))
						->where($this->db->qn('access') . ' IN ( ' . $groups . ')')
						->order($this->db->qn('ordering') . ' ASC');*/

					$query="SELECT *
                            FROM #__ijoomeradv_menu
                            WHERE menutype=$value->id
                            AND published=1
                            AND access IN ($groups)
                            AND (IF((menudevice=1),($value->menudevice=$menudevice),(menudevice=$menudevice)) OR menudevice=4
                                OR IF((menudevice=1 AND $value->menudevice=1),true,false))
                            AND (language='$langCode' or  language='*')
                            ORDER BY ordering";

					// Set the query and load the result.
					$this->db->setQuery($query);

					$menuitems = $this->db->loadObjectList();

					$k = 0;
					$menuArray[$i]["menuitem"] = array();

					if (!empty($menuitems))
					{
						foreach ($menuitems as $value1)
						{
							$viewname = explode('.', $value1->views);

							$remotedata = json_decode($value1->menuoptions);

							if ($remotedata)
							{
								$remotedata = $remotedata->remoteUse;
							}
							else
							{
								$remotedata = '';
							}

							$menuArray[$i]["menuitem"][$k] = array("itemid" => $value1->id,
								"itemcaption" => $value1->title,
								"itemview" => $viewname[3],
								"itemdata" => $remotedata
							);

							if (( $value->position == 1 or $value->position == 2) && ( $value1->itemimage))
							{
								$menuArray[$i]["menuitem"][$k]["icon"] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/custom/' . $device . '/' . $device_type . '/' . $value1->itemimage . '_icon.png';
							}
							elseif ($value->position == 3 && $value1->itemimage)
							{
								$menuArray[$i]["menuitem"][$k]["tab"] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/custom/' . $device . '/' . $device_type . '/' . $value1->itemimage . '_tab.png';
								$menuArray[$i]["menuitem"][$k]["tab_active"] = JURI::base() . 'administrator/components/com_ijoomeradv/theme/custom/' . $device . '/' . $device_type . '/' . $value1->itemimage . '_tab_active.png';
							}

							$k++;
						}
					}

					$i++;
				}
			}
		}

		return $menuArray;
	}

	/**
	 * Set request variable from menu id
	 *
	 * @param   [type]  $menuid  contains menu id
	 *
	 * @return boolean it will return a value in true or false
	 */
	public function setMenuRequest($menuid)
	{
		$mainframe = JFactory::getApplication();

		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->qn('#__ijoomeradv_menu'))
			->where($this->db->qn('id') . ' = ' . $this->db->q($menuid));

		// Set the query and load the result.
		$this->db->setQuery($query);

		$menuobject = $this->db->loadObject();

		if ($menuobject)
		{
			// Set reqObject as per menuid request
			$views = explode('.', $menuobject->views);
			$mainframe->IJObject->reqObject->extName = $views[0];
			$mainframe->IJObject->reqObject->extView = $views[1];
			$mainframe->IJObject->reqObject->extTask = $views[2];

			// Set required data for menu request
			$menuoptions = json_decode($menuobject->menuoptions);

			foreach ($menuoptions->remoteUse as $key => $value)
			{
				$mainframe->IJObject->reqObject->taskData->$key = $value;
			}
		}

		return true;
	}

	/**
	 * Fetches IJoomeradv Global Config
	 *
	 * @param   [type]  $extName  contains the ExtName
	 *
	 * @return  it will return loadobjectlist
	 */
	public function getExtensionConfig($extName)
	{
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('name, value')
			->from($this->db->qn('#__ijoomeradv_' . $extName . '_config'));

		// Set the query and load the result.
		$this->db->setQuery($query);

		try
		{
			$result = $this->db->loadObjectList();

			// return config list
			return $result;
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Fetches IJoomeradv Custom Views Detail
	 *
	 * @return  it will return the value of $customeView
	 */
	public function getCustomView()
	{
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->qn('#__ijoomeradv_menu'))
			->where($this->db->qn('published') . ' = ' . $this->db->q('1'))
			->where($this->db->qn('type') . ' = ' . $this->db->q('Custom'));

		// Set the query and load the result.
		$this->db->setQuery($query);

		try
		{
			$customView = $this->db->loadObjectList();

			return $customView;
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Fetches IJoomeradv Default Home Views
	 *
	 * @return  it will return loadobject
	 */
	public function getHomeMenu()
	{
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->qn('#__ijoomeradv_menu'))
			->where($this->db->qn('published') . ' = ' . $this->db->q('1'))
			->where($this->db->qn('home') . ' = ' . $this->db->q('1'));

		// Set the query and load the result.
		$this->db->setQuery($query);

		try
		{
			$result = $this->db->loadObjectList();

			return $result;
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Check Ioomer Extension And Related Joomla Component If Installed And Enabled
	 *
	 * @param   [type]  $extName  contains the value of Extension Name
	 *
	 * @return  boolean it will return the value in true or false
	 */
	public function checkIJExtension($extName)
	{
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select($this->db->qn('option'))
			->from($this->db->qn('#__ijoomeradv_extensions'))
			->where($this->db->qn('classname') . ' = ' . $this->db->q($extName));

		$this->db->setQuery($query);

		$option = $this->db->loadResult(); // get component name from the extension name

		if (!$option)
		{
			IJReq::setResponseCode(404);

			return false;
		}
		else
		{
			// Create hepler object
			$IJHelperObj = new ijoomeradvHelper;

			if (!$IJHelperObj->getComponent($option))
			{
				IJReq::setResponseCode(404);

				return false;
			}
		}

		return true;
	}

	public function getUserAvatar($image)
	{
		if(empty($image)){
			return '';
		}

		$url = parse_url($image);
		if(is_array($url) && isset($url['scheme']) && $url['scheme'] == 'https'){
			return $image;
		}
		else if(is_array($url) && isset($url['scheme']) && $url['scheme'] == 'http'){
			return $image;
		}else{
			return JUri::base().'images/beseated/'.$image;
		}
	}

	/**
	 * The LoginProccess Function
	 *
	 * @return  array it will return jsson array
	 */
	public function loginProccess()
	{
		require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/beseated/helper.php';

		$helper = new beseatedAppHelper();

		$notificationDetail = $helper->getNotificationCount();

		//$allNotificationCount =  $notificationDetail['allNotificationCount']+$notificationDetail['messageCount'];
		$allNotificationCount =  $notificationDetail['allNotificationCount'];

		$jsonarray = array();
		$jsonarray['globalNotifications']['bookingsCount']        = ($notificationDetail['bookingCount']) ? (string)$notificationDetail['bookingCount']:"0";
		$jsonarray['globalNotifications']['requestsCount']        = ($notificationDetail['requestCount']) ?  (string)$notificationDetail['requestCount']:"0";
		$jsonarray['globalNotifications']['messagesCount']        = ($notificationDetail['messageCount']) ?  (string)$notificationDetail['messageCount']:"0";
		$jsonarray['globalNotifications']['notificationsCount']   = ($allNotificationCount) ? (string)$allNotificationCount:"0";

		$data['latitude']     = IJReq::getTaskData('lat');
		$data['longitude']    = IJReq::getTaskData('long');
		$data['device_token'] = IJReq::getTaskData('devicetoken');
		$data['device_type']  = IJReq::getTaskData('type');


		$versionCode  = IJReq::getTaskData('versionCode','');
		$osVersion    = IJReq::getTaskData('osVersion','');
		$deviceModel  = IJReq::getTaskData('deviceModel','');


		$session = JFactory::getSession();
		$session->set('versionCode', $versionCode);
		$session->set('osVersion', $osVersion);
		$session->set('deviceModel', $deviceModel);

		// Get current user
		$my = JFactory::getUser();

		/*if(!empty($fbFriendsIDs)){
			$query = $this->db->getQuery(true);
			$query->update($this->db->quoteName('#__beseated_user_profile'))
				->set($this->db->quoteName('fb_friends_id') . ' = ' . $this->db->quote($fbFriendsIDs))
				->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($my->id));
			$this->db->setQuery($query);
			$this->db->execute();
		}*/

		// @TODO : extension levels default params
		$defaultParams = '{"pushnotif_profile_activity_add_comment":"1","pushnotif_profile_activity_reply_comment":"1","pushnotif_profile_status_update":"1","pushnotif_profile_like":"1","pushnotif_profile_stream_like":"1","pushnotif_friends_request_connection":"1","pushnotif_friends_create_connection":"1","pushnotif_inbox_create_message":"1","pushnotif_groups_invite":"1","pushnotif_groups_discussion_reply":"1","pushnotif_groups_wall_create":"1","pushnotif_groups_create_discussion":"1","pushnotif_groups_create_news":"1","pushnotif_groups_create_album":"1","pushnotif_groups_create_video":"1","pushnotif_groups_create_event":"1","pushnotif_groups_sendmail":"1","pushnotif_groups_member_approved":"1","pushnotif_groups_member_join":"1","pushnotif_groups_notify_creator":"1","pushnotif_groups_discussion_newfile":"1","pushnotif_events_invite":"1","pushnotif_events_invitation_approved":"1","pushnotif_events_sendmail":"1","pushnotif_event_notify_creator":"1","pushnotif_event_join_request":"1","pushnotif_videos_submit_wall":"1","pushnotif_videos_reply_wall":"1","pushnotif_videos_tagging":"1","pushnotif_videos_like":"1","pushnotif_photos_submit_wall":"1","pushnotif_photos_reply_wall":"1","pushnotif_photos_tagging":"1","pushnotif_photos_like":"1"}';

		if(!empty($data['device_token']))
		{
			$query = $this->db->getQuery(true);

			// Create the base delete statement.
			$query->delete()
				->from($this->db->quoteName('#__ijoomeradv_users'))
				->where($this->db->quoteName('device_token') . ' = ' . $this->db->quote($data['device_token']));

			// Set the query and execute the delete.
			$this->db->setQuery($query);
			$this->db->execute();
		}

		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('count(1)')
			->from($this->db->qn('#__ijoomeradv_users'))
			->where($this->db->qn('userid') . ' = ' . $this->db->q($my->id));

		// Set the query and load the result.
		$this->db->setQuery($query);

		$user = $this->db->loadResult();


		$query = $this->db->getQuery(true);

		if ($user)
		{
			// Create the base update statement.
			$query->update($this->db->qn('#__ijoomeradv_users'))
				->set($this->db->qn('device_token') . ' = ' . $this->db->q($data['device_token']))
				->set($this->db->qn('device_type') . ' = ' . $this->db->q($data['device_type']))
				->where($this->db->qn('userid') . ' = ' . $this->db->q($my->id));
		}
		else
		{
			// Create the base insert statement.
			$query->insert($this->db->qn('#__ijoomeradv_users'))
				->columns(
					array(
						$this->db->qn('userid'),
						$this->db->qn('jomsocial_params'),
						$this->db->qn('device_token'),
						$this->db->qn('device_type')
						)
					)
				->values(
					$this->db->q($my->id) . ', ' .
					$this->db->q($defaultParams) . ', ' .
					$this->db->q($data['device_token']) . ', ' .
					$this->db->q($data['device_type'])
					);
		}

		$this->db->setQuery($query);
		$this->db->execute();

		// @TODO : extension levels default params
		$defaultParams = '{"pushnotif_profile_activity_add_comment":"1","pushnotif_profile_activity_reply_comment":"1","pushnotif_profile_status_update":"1","pushnotif_profile_like":"1","pushnotif_profile_stream_like":"1","pushnotif_friends_request_connection":"1","pushnotif_friends_create_connection":"1","pushnotif_inbox_create_message":"1","pushnotif_groups_invite":"1","pushnotif_groups_discussion_reply":"1","pushnotif_groups_wall_create":"1","pushnotif_groups_create_discussion":"1","pushnotif_groups_create_news":"1","pushnotif_groups_create_album":"1","pushnotif_groups_create_video":"1","pushnotif_groups_create_event":"1","pushnotif_groups_sendmail":"1","pushnotif_groups_member_approved":"1","pushnotif_groups_member_join":"1","pushnotif_groups_notify_creator":"1","pushnotif_groups_discussion_newfile":"1","pushnotif_events_invite":"1","pushnotif_events_invitation_approved":"1","pushnotif_events_sendmail":"1","pushnotif_event_notify_creator":"1","pushnotif_event_join_request":"1","pushnotif_videos_submit_wall":"1","pushnotif_videos_reply_wall":"1","pushnotif_videos_tagging":"1","pushnotif_videos_like":"1","pushnotif_photos_submit_wall":"1","pushnotif_photos_reply_wall":"1","pushnotif_photos_tagging":"1","pushnotif_photos_like":"1"}';

		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('count(1)')
			->from($this->db->qn('#__ijoomeradv_users'))
			->where($this->db->qn('userid') . ' = ' . $this->db->q($my->id));

		// Set the query and load the result.
		$this->db->setQuery($query);

		$user = $this->db->loadResult();

		$query = $this->db->getQuery(true);

		if(!empty($data['device_token']))
		{
			if ($user)
			{
				// Create the base update statement.
				$query->update($this->db->qn('#__ijoomeradv_users'))
					->set($this->db->qn('device_token') . ' = ' . $this->db->q($data['device_token']))
					->set($this->db->qn('device_type') . ' = ' . $this->db->q($data['device_type']))
					->where($this->db->qn('userid') . ' = ' . $this->db->q($my->id));
			}
			else
			{
				// Create the base insert statement.
				$query->insert($this->db->qn('#__ijoomeradv_users'))
					->columns(
						array(
							$this->db->qn('userid'),
							$this->db->qn('jomsocial_params'),
							$this->db->qn('device_token'),
							$this->db->qn('device_type')
							)
						)
					->values(
						$this->db->q($my->id) . ', ' .
						$this->db->q($defaultParams) . ', ' .
						$this->db->q($data['device_token']) . ', ' .
						$this->db->q($data['device_type'])
						);
			}

			$this->db->setQuery($query);
		    $this->db->execute();
		}


		$jsonarray['code'] = 200;

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';

		$beseatedParams = BeseatedHelper::getExtensionParam();
		$groups         = $my->get('groups');
		$managerGroups    = array('Chauffeur','Protection','Venue','Yacht');

		$elementDetail['email']            = $my->email;

		if(in_array($beseatedParams->beseated_guest, $groups))
		{
			$userType = "Guest";
			$query = $this->db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($this->db->quoteName('#__beseated_user_profile'))
				->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($my->id));

			// Set the query and load the result.
			$this->db->setQuery($query);
			$guestDetail = $this->db->loadObject();

			$fbID         = IJReq::getTaskData('fbID','');
			$fb           = IJReq::getTaskData('fb',0,'int');
			$friendsFbIds = IJReq::getTaskData('friendsFbIds','','string');
			//$fbFriendsIDs = IJReq::getTaskData('fbFriendsIDs','','string');



			if($fb)
			{
				$this->checkForFacebookUser($my->id,$fbID,$friendsFbIds);

				if(!empty($fbID))
				{
					JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
					$tblBeseatedProfile       = JTable::getInstance('Profile', 'BeseatedTable');
					$tblBeseatedProfile->load($my->id);

					$sqlUpdt = $this->db->getQuery(true);
						$sqlUpdt->update($this->db->quoteName('#__beseated_user_profile'))
							->set($this->db->quoteName('fb_id') . ' = ' . $this->db->quote($fbID))
							->set($this->db->quoteName('is_fb_user') . ' = ' . $this->db->quote('1'))
							->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($my->id));
						$this->db->setQuery($sqlUpdt);
						$this->db->execute();

					if($tblBeseatedProfile->is_use_fb_image)
					{

						$is_fb_user = 1;
						$fb_id      = $fbID;
						$avatar = "http://graph.facebook.com/".$fb_id."/picture?type=large";
						$thumb = "https://graph.facebook.com/".$fb_id."/picture?width=150";

						// Initialiase variables.
						$sqlUpdt = $this->db->getQuery(true);
						$sqlUpdt->update($this->db->quoteName('#__beseated_user_profile'))
							->set($this->db->quoteName('avatar') . ' = ' . $this->db->quote($avatar))
							->set($this->db->quoteName('thumb_avatar') . ' = ' . $this->db->quote($thumb))
							->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($my->id));
						$this->db->setQuery($sqlUpdt);
						$this->db->execute();
					}

					$query = $this->db->getQuery(true);
					$query->select('*')
						->from($this->db->quoteName('#__beseated_user_profile'))
						->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($my->id));
					$this->db->setQuery($query);
					$guestDetail = $this->db->loadObject();
				}

				if(!empty($friendsFbIds))
				{
					// Initialiase variables.
					$sqlUpdt = $this->db->getQuery(true);
					$sqlUpdt->update($this->db->quoteName('#__beseated_user_profile'))
						->set($this->db->quoteName('fb_friends_id') . ' = ' . $this->db->quote($friendsFbIds))
						->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($my->id));
					$this->db->setQuery($sqlUpdt);
					$this->db->execute();
				}
			}

			$elementDetail['userID']           = $guestDetail->user_id;
			$elementDetail['fullName']         = $guestDetail->full_name;

			$elementDetail['phone']            = $guestDetail->phone;
			$elementDetail['birthdate']        = $guestDetail->birthdate;
			$elementDetail['city']             = $guestDetail->city;
			$elementDetail['avatar']           = ($guestDetail->avatar)?$this->getUserAvatar($guestDetail->avatar):'';
			$elementDetail['thumbAvatar']      = ($guestDetail->thumb_avatar)?$this->getUserAvatar($guestDetail->thumb_avatar):'';
			$elementDetail['showInBigSpender'] = $guestDetail->show_in_biggest_spender;
			$elementDetail['notification']     = $guestDetail->notification;
			$elementDetail['showFriendsOnly']     = $guestDetail->show_friends_only;

			$ratedSql = $this->db->getQuery(true);
			$ratedSql->select('element_id,element_type')
				->from($this->db->quoteName('#__beseated_rating'))
				->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($my->id));

			$this->db->setQuery($ratedSql);
			$resRateProtection = $this->db->loadObjectList();

			$ratedProtections = array();
			$ratedVenues      = array();
			$ratedChauffeurs  = array();
			$ratedYachts      = array();
			$ratedPrivateJets = array();
			foreach ($resRateProtection as $key => $rating)
			{
				if(strtolower($rating->element_type) == 'venue')
				{
					$ratedVenues[] = $rating->element_id;
				}
				else if (strtolower($rating->element_type) == 'protection')
				{
					$ratedProtections[] = $rating->element_id;
				}
				else if (strtolower($rating->element_type) == 'yacht')
				{
					$ratedYachts[] = $rating->element_id;
				}
				else if (strtolower($rating->element_type) == 'chauffeur')
				{
					$ratedChauffeurs[] = $rating->element_id;
				}
			}

			$currentDate          = date('Y-m-d H:m:s',strtotime('-1 hours'));
			//$currentDate          = date('Y-m-d H:m:s');

			// Rating for protection

			$protectionBookingSql = $this->db->getQuery(true);
			// Create the base select statement.
			$protectionBookingSql->select('pb.protection_booking_id,pb.protection_id')
				->from($this->db->quoteName('#__beseated_protection_booking','pb'))
				->where($this->db->quoteName('pb.user_id') . ' = ' . $this->db->quote($my->id))
				->where('ADDTIME('.$this->db->quoteName('pb.booking_date') . ','.$this->db->quoteName('pb.booking_time') .') <= ' . $this->db->quote($currentDate));
				//->where($this->db->quoteName('pb.is_rated') . ' = ' . $this->db->quote(0));
			if(count($ratedProtections))
			{
				$protectionBookingSql->where($this->db->quoteName('pb.protection_id') . ' NOT IN ('.implode(',', $ratedProtections).')');
			}

			$protectionBookingSql->select('p.protection_name')
				->join('INNER','#__beseated_protection AS p ON p.protection_id=pb.protection_id');

			// Set the query and load the result.
			$this->db->setQuery($protectionBookingSql);

			$resRateProtection = $this->db->loadObjectList();

			$rateToElements = array();
			$processIDs = array();
			foreach ($resRateProtection as $key => $booking)
			{
				if(in_array($booking->protection_id, $processIDs)){
					continue;
				}

				$processIDs[] = $booking->protection_id;
				$temp                   = array();
				$temp['elementID']   = $booking->protection_id;
				$temp['elementName'] = $booking->protection_name;
				$temp['elementType'] = 'Protection';
				$rateToElements[]     = $temp;
			}

			//$jsonarray['luxuryRating'] = $rateToProtection;

			// Rating for yacht

			$yachtBookingSql = $this->db->getQuery(true);
			// Create the base select statement.
			$yachtBookingSql->select('yb.yacht_booking_id,yb.yacht_id')
				->from($this->db->quoteName('#__beseated_yacht_booking','yb'))
				->where($this->db->quoteName('yb.user_id') . ' = ' . $this->db->quote($my->id))
				->where('ADDTIME('.$this->db->quoteName('yb.booking_date') . ','.$this->db->quoteName('yb.booking_time') .') <= ' . $this->db->quote($currentDate));
				//->where($this->db->quoteName('pb.is_rated') . ' = ' . $this->db->quote(0));
			if(count($ratedYachts))
			{
				$yachtBookingSql->where($this->db->quoteName('yb.yacht_id') . ' NOT IN ('.implode(',', $ratedYachts).')');
			}

			$yachtBookingSql->select('y.yacht_name')
				->join('INNER','#__beseated_yacht AS y ON y.yacht_id=yb.yacht_id');

			// Set the query and load the result.
			$this->db->setQuery($yachtBookingSql);

			$resRateYacht = $this->db->loadObjectList();

			$processIDs = array();
			foreach ($resRateYacht as $key => $booking)
			{
				if(in_array($booking->yacht_id, $processIDs)){
					continue;
				}

				$processIDs[] = $booking->yacht_id;
				$temp                   = array();
				$temp['elementID']   = $booking->yacht_id;
				$temp['elementName'] = $booking->yacht_name;
				$temp['elementType'] = 'Yacht';
				$rateToElements[]     = $temp;
			}

			// Rating for chauffeur

			$chauffeurBookingSql = $this->db->getQuery(true);
			// Create the base select statement.
			$chauffeurBookingSql->select('cb.chauffeur_booking_id,cb.chauffeur_id')
				->from($this->db->quoteName('#__beseated_chauffeur_booking','cb'))
				->where($this->db->quoteName('cb.user_id') . ' = ' . $this->db->quote($my->id))
				->where('ADDTIME('.$this->db->quoteName('cb.booking_date') . ','.$this->db->quoteName('cb.booking_time') .') <= ' . $this->db->quote($currentDate));
				//->where($this->db->quoteName('pb.is_rated') . ' = ' . $this->db->quote(0));
			if(count($ratedChauffeurs))
			{
				$chauffeurBookingSql->where($this->db->quoteName('cb.chauffeur_id') . ' NOT IN ('.implode(',', $ratedChauffeurs).')');
			}

			$chauffeurBookingSql->select('c.chauffeur_name')
				->join('INNER','#__beseated_chauffeur AS c ON c.chauffeur_id=cb.chauffeur_id');

			// Set the query and load the result.
			$this->db->setQuery($chauffeurBookingSql);

			$resRatechauffeur = $this->db->loadObjectList();

			$processIDs = array();
			foreach ($resRatechauffeur as $key => $booking)
			{
				if(in_array($booking->chauffeur_id, $processIDs)){
					continue;
				}

				$processIDs[] = $booking->chauffeur_id;
				$temp                   = array();
				$temp['elementID']   = $booking->chauffeur_id;
				$temp['elementName'] = $booking->chauffeur_name;
				$temp['elementType'] = 'Chauffeur';
				$rateToElements[]     = $temp;
			}

			$jsonarray['luxuryRating'] = $rateToElements;

			//echo "<pre/>";print_r($jsonarray);exit;



			$venueBookingSql = $this->db->getQuery(true);
			// Create the base select statement.
			$venueBookingSql->select('vb.venue_table_booking_id	,vb.venue_id')
				->from($this->db->quoteName('#__beseated_venue_table_booking','vb'))
				->where($this->db->quoteName('vb.user_id') . ' = ' . $this->db->quote($my->id))
				->where('ADDTIME('.$this->db->quoteName('vb.booking_date') . ','.$this->db->quoteName('vb.booking_time') .') <= ' . $this->db->quote($currentDate));
				//->where($this->db->quoteName('pb.is_rated') . ' = ' . $this->db->quote(0));
			if(count($ratedVenues))
			{
				$venueBookingSql->where($this->db->quoteName('vb.venue_id') . ' NOT IN ('.implode(',', $ratedVenues).')');
			}

			$venueBookingSql->select('v.venue_name')
				->join('INNER','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

			/*echo $venueBookingSql->dump();
			exit;*/

			// Set the query and load the result.
			$this->db->setQuery($venueBookingSql);

			$resRateVenue = $this->db->loadObjectList();
			/*echo "<pre>";
			print_r($resRateVenue);
			echo "</pre>";
			exit;*/
			$rateToVenue = array();
			$processIDs = array();
			foreach ($resRateVenue as $key => $booking)
			{
				if(in_array($booking->venue_id, $processIDs)){
					continue;
				}
				$processIDs[] = $booking->venue_id;
				$temp              = array();
				$temp['venueID']   = $booking->venue_id;
				$temp['venueName'] = $booking->venue_name;
				$rateToVenue[]     = $temp;
			}

			$jsonarray['venueRating'] = $rateToVenue;
		}
		else if(in_array($beseatedParams->chauffeur, $groups))
		{
			$userType = "Chauffeur";
			$query = $this->db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($this->db->quoteName('#__beseated_chauffeur'))
				->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($my->id));

			// Set the query and load the result.
			$this->db->setQuery($query);
			$chauffeurDetail = $this->db->loadObject();

			$elementID  = $chauffeurDetail->chauffeur_id;
			$elementDetail['chauffeurID']   = ($chauffeurDetail->chauffeur_id) ? $chauffeurDetail->chauffeur_id :"";
			$elementDetail['userID']        = ($chauffeurDetail->user_id)? $chauffeurDetail->user_id:"";
			$elementDetail['chauffeurName'] = ($chauffeurDetail->chauffeur_name)?:"";
			$elementDetail['location']      = ($chauffeurDetail->location)? $chauffeurDetail->chauffeur_name:"";
			$elementDetail['city']          = ($chauffeurDetail->city)? $chauffeurDetail->city:"";
			$elementDetail['currencyCode']  = ($chauffeurDetail->currency_code)? $chauffeurDetail->currency_code:"";
			$elementDetail['currencySign']  = ($chauffeurDetail->currency_sign)? $chauffeurDetail->currency_sign:"";
			$elementDetail['avgRatting']    = ($chauffeurDetail->avg_ratting)? $chauffeurDetail->avg_ratting:"";
			$elementDetail['latitude']      = ($chauffeurDetail->latitude)? $chauffeurDetail->latitude:"";
			$elementDetail['longitude']     = ($chauffeurDetail->longitude)? $chauffeurDetail->longitude:"";

		}
		else if(in_array($beseatedParams->protection, $groups))
		{
			$userType = "Protection";
			$query = $this->db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($this->db->quoteName('#__beseated_protection'))
				->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($my->id));

			// Set the query and load the result.
			$this->db->setQuery($query);
			$protectionDetail = $this->db->loadObject();

			$elementID  = $protectionDetail->protection_id;
			$elementDetail['protectionID']   = ($protectionDetail->protection_id)? $protectionDetail->protection_id :"";
			$elementDetail['userID']         = ($protectionDetail->user_id)? $protectionDetail->user_id:"";
			$elementDetail['protectionName'] = ($protectionDetail->protection_name)?$protectionDetail->protection_name:"";
			$elementDetail['location']       = ($protectionDetail->location)?$protectionDetail->location:"";
			$elementDetail['city']           = ($protectionDetail->city)?$protectionDetail->city:"";
			$elementDetail['currencyCode']   = ($protectionDetail->currency_code)?$protectionDetail->currency_code:"";
			$elementDetail['currencySign']   = ($protectionDetail->currency_sign)?$protectionDetail->currency_sign:"";
			$elementDetail['avgRatting']     = ($protectionDetail->avg_ratting)?$protectionDetail->avg_ratting:"";
			$elementDetail['latitude']       = ($protectionDetail->latitude)?$protectionDetail->latitude:"";
			$elementDetail['longitude']      = ($protectionDetail->longitude)?$protectionDetail->longitude:"";
		}
		else if(in_array($beseatedParams->venue, $groups))
		{
			$userType = "Venue";
			$query = $this->db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($this->db->quoteName('#__beseated_venue'))
				->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($my->id));

			// Set the query and load the result.
			$this->db->setQuery($query);
			$venueDetail = $this->db->loadObject();

			$elementID  = $venueDetail->venue_id;
			$elementDetail['venueID']      = ($venueDetail->venue_id)? $venueDetail->venue_id :"";
			$elementDetail['userID']       = ($venueDetail->user_id)? $venueDetail->user_id :"";
			$elementDetail['venueName']    = ($venueDetail->venue_name)? $venueDetail->venue_name :"";
			$elementDetail['description']  = ($venueDetail->description)? $venueDetail->description :"";
			$elementDetail['workingDays']  = ($venueDetail->working_days)? $venueDetail->working_days :"";
			$elementDetail['music']        = ($venueDetail->music)? $venueDetail->music :"";
			$elementDetail['atmosphere']   = ($venueDetail->atmosphere)? $venueDetail->atmosphere :"";
			$elementDetail['location']     = ($venueDetail->location)? $venueDetail->location :"";
			$elementDetail['city']         = ($venueDetail->city)? $venueDetail->city :"";
			$elementDetail['currencyCode'] = ($venueDetail->currency_code)? $venueDetail->currency_code :"";
			$elementDetail['currencySign'] = ($venueDetail->currency_sign)? $venueDetail->currency_sign :"";
			$elementDetail['avgRatting']   = ($venueDetail->avg_ratting)? $venueDetail->avg_ratting :"";
			$elementDetail['dayClub']      = ($venueDetail->is_day_club)? $venueDetail->is_day_club :"";
			$elementDetail['latitude']     = ($venueDetail->latitude)? $venueDetail->latitude :"";
			$elementDetail['longitude']    = ($venueDetail->longitude)? $venueDetail->longitude :"";
		}
		else if(in_array($beseatedParams->yacht, $groups))
		{
			$userType = "Yacht";
			$query = $this->db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($this->db->quoteName('#__beseated_yacht'))
				->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($my->id));

			// Set the query and load the result.
			$this->db->setQuery($query);
			$yachtDetail = $this->db->loadObject();

			$elementID  = $yachtDetail->yacht_id;
			$elementDetail['yachtID']      = ($yachtDetail->yacht_id)?$yachtDetail->yacht_id:"";
			$elementDetail['userID']       = ($yachtDetail->user_id)?$yachtDetail->user_id:"";
			$elementDetail['yachtName']    = ($yachtDetail->yacht_name)?$yachtDetail->yacht_name:"";
			$elementDetail['location']     = ($yachtDetail->location)?$yachtDetail->location:"";
			$elementDetail['city']         = ($yachtDetail->city)?$yachtDetail->city:"";
			$elementDetail['currencyCode'] = ($yachtDetail->currency_code)?$yachtDetail->currency_code:"";
			$elementDetail['currencySign'] = ($yachtDetail->currency_sign)?$yachtDetail->currency_sign:"";
			$elementDetail['avgRatting']   = ($yachtDetail->avg_ratting)?$yachtDetail->avg_ratting:"";
			$elementDetail['latitude']     = ($yachtDetail->latitude)?$yachtDetail->latitude:"";
			$elementDetail['longitude']    = ($yachtDetail->longitude)?$yachtDetail->longitude:"";
		}

		if(in_array($userType, $managerGroups))
		{
			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $this->db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($this->db->quoteName('#__beseated_element_images'))
				->where($this->db->quoteName('element_id') . ' = ' . $this->db->quote($elementID))
				->where($this->db->quoteName('element_type') . ' = ' . $this->db->quote($userType));

			// Set the query and load the result.
			$this->db->setQuery($query);

			$resultImages = $this->db->loadObjectList();
			$elementImages = array();
			$corePath = JUri::base().'images/beseated/';
			foreach ($resultImages as $key => $image)
			{
				$temp               = array();
				$temp['imageID']    = ($image->image_id) ? $image->image_id:"";
				$temp['thumbImage'] = ($image->thumb_image)? $corePath.$image->thumb_image:'';
				$temp['image']      = ($image->image)? $corePath.$image->image:'';
				$temp['isVideo']    = ($image->is_video)? $image->is_video:"";
				$temp['isDefault']  = ($image->is_default)? $image->is_default:"";
				$elementImages[]    = ($temp)? $temp:"";
			}

			$elementDetail['elementImages'] = $elementImages;

		}

		if($userType == 'Venue')
		{
			// Initialiase variables.
			$db    = JFactory::getDbo();

			// $db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('music_id,music_name')
				->from($db->quoteName('#__beseated_venue_music_table'))
				->where($db->quoteName('published') . ' = ' . $db->quote('1'))
				->order($db->quoteName('created') . ' DESC');

			// Set the query and load the result.
			$db->setQuery($query);
			$musicTypes = $db->loadObjectList();

			// Initialiase variables.
			$db    = JFactory::getDbo();

			// $db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('bottle_type_id,bottle_type_name')
				->from($db->quoteName('#__beseated_venue_bottle_type'))
				->where($db->quoteName('published') . ' = ' . $db->quote('1'))
				->order($db->quoteName('created') . ' DESC');

			// Set the query and load the result.
			$db->setQuery($query);
			$bottleTypes = $db->loadObjectList();

			// Initialiase variables.
			$db    = JFactory::getDbo();

			// $db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('premium_id as premium_table_id,premium_table_name')
				->from($db->quoteName('#__beseated_venue_premium_table'))
				->where($db->quoteName('published') . ' = ' . $db->quote('1'))
				->order($db->quoteName('created') . ' DESC');

			// Set the query and load the result.
			$db->setQuery($query);
			$premiumTableTypes = $db->loadObjectList();

			$jsonarray['premiumTableType'] = $premiumTableTypes;
			$jsonarray['MusicType']        = $musicTypes;
			$jsonarray['bottleType']       = $bottleTypes;
		}

		$jsonarray['elementType'] = $userType;
		$jsonarray['elementDetail'] = $elementDetail;

		return $jsonarray;
	}

	/**
	 * @uses function is used to forgot password
	 *
	 */
	function forgotPassword()
	{
		$email = IJReq::getTaskData('email' ,'');

		if(empty($email))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_INVALID_EMAIL'));
			return false;
		}

		$query = $this->db->getQuery(true);

		$query->select('id')
			->from($this->db->quoteName('#__users'))
			->where($this->db->quoteName('email') . ' = ' . $this->db->quote($email));

		$this->db->setQuery($query);

		$userID = $this->db->loadResult();

		if($userID>0)
		{
			$password_set	= JUserHelper::genRandomPassword(6);

			$userDetail = JFactory::getUser($userID);

	 		$queryUPDT = $this->db->getQuery(true);

			$queryUPDT->update($this->db->quoteName('#__beseated_user_profile'))
				->set($this->db->quoteName('token') . ' = ' . $this->db->quote($password_set))
				->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($userID));

			$this->db->setQuery($queryUPDT);
			$this->db->execute();

			if(file_exists(JPATH_SITE.'/components/com_beseated/helpers/email.php'))
			{
				require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
				$emailAppHelper = new BeseatedEmailHelper();
				$emailAppHelper->forgotPasswordEmail($userID,$password_set);
				//$emailAppHelper->contactThankYouEmail();
			}

			$jsonarray['code']	  = 200;
			return $jsonarray;
		}

		$jsonarray['code']    = 401;
		$jsonarray['message'] = JText::_('COM_IJOOMERADV_EMAIL_NOT_REGISTERED');
		return $jsonarray;
	}
	/**
	 * This Function is Use to Log Into with facebook
	 *
	 * @return it will return false value otherwise jasson array
	 */
	public function fblogin()
	{
		jimport('joomla.user.helper');

		$data['relname'] = IJReq::getTaskData('name');
		$data['user_nm'] = IJReq::getTaskData('username');
		$data['email']   = IJReq::getTaskData('email');
		$data['pic_big'] = IJReq::getTaskData('bigpic');
		$password_set    = IJReq::getTaskData('password');
		$reg_opt         = IJReq::getTaskData('regopt', 0, 'int');
		$fbid            = IJReq::getTaskData('fbid');
		$time            = time();

		if ($reg_opt === 0)
		{
			// first check if fbuser in db logged in
			$query = $this->db->getQuery(true);

			// Create the base select statement.
			$query->select('u.id,u.username')
				->from($this->db->qn('#__users') .' AS u')
				->where($this->db->qn('cu.connectid') . ' = ' . $this->db->q($password_set))
				->join('INNER', '#__community_connect_users as cu ON cu.userid = u.id');

			// Set the query and load the result.
			$this->db->setQuery($query);

			$userinfo = $this->db->loadObject();

			if (isset($userinfo->id) && $userinfo->id > 0)
			{
				$salt = JUserHelper::genRandomPassword(32);
				$crypt = JUserHelper::getCryptedPassword($password_set . $time, $salt);
				$data['password'] = $crypt . ':' . $salt;

				$query = $this->db->getQuery(true);

				// Create the base update statement.
				$query->update($this->db->qn('#__users'))
					->set($this->db->qn('password') . ' = ' . $this->db->q($data['password']))
					->where($this->db->qn('id') . ' = ' . $this->db->q($userinfo->id));

				// Set the query and execute the update.
				$this->db->setQuery($query);

				$this->db->execute();

				$usersipass['username'] = $userinfo->username;
				$usersipass['password'] = $password_set . $time;

				if ($this->mainframe->login($usersipass) == '1')
				{
					$jsonarray = $this->loginProccess();

					return $jsonarray;
				}
				else
				{
					IJReq::setResponseCode(401);
					IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_UNABLE_TO_AUTHENTICATE'));

					return false;
				}
			}
			else
			{
				// Facebook user not found, need to create new user
				IJReq::setResponseCode(703);

				return false;
			}
		}
		elseif ($reg_opt === 1)
		{
			// Registration option 1 if already user
			$credentials = array();
			$credentials['username'] = $data['user_nm'];
			$credentials['password'] = $password_set;

			if ($this->mainframe->login($credentials) == '1' && $fbid != "")
			{
				// Connect fb user to site user...
				$user = JFactory::getUser();

				if (strtolower(IJOOMER_GC_REGISTRATION) === 'community' && file_exists(JPATH_ROOT . '/components/com_community/libraries/core.php'))
				{
					require_once JPATH_ROOT . '/components/com_community/libraries/core.php';

					$query = $this->db->getQuery(true);

					// Create the base insert statement.
					$query->insert($this->db->qn('#__community_connect_users'))
						->columns(
							array(
								$this->db->qn('userid'),
								$this->db->qn('connectid'),
								$this->db->qn('type'))
							)
						->values(
							$this->db->q($user->id) . ', ' .
							$this->db->q($fbid) . ', ' .
							$this->db->q('facebook')
							);

					// Set the query and execute the insert.
					$this->db->setQuery($query);

					$this->db->execute();

					$salt = JUserHelper::genRandomPassword(32);
					$crypt = JUserHelper::getCryptedPassword($password_set . $time, $salt);
					$data['password'] = $crypt . ':' . $salt;

					$query = $this->db->getQuery(true);

					// Create the base update statement.
					$query->update($this->db->qn('#__users'))
						->set($this->db->qn('password') . ' = ' . $this->db->q($data['password']))
						->where($this->db->qn('id') . ' = ' . $this->db->q($user->id));

					// Set the query and execute the update.
					$this->db->setQuery($query);

					$this->db->execute();

					// Store user image...
					CFactory::load('libraries', 'facebook');
					$facebook = new CFacebook;

					// Edited by Salim (Date: 08-09-2011)
					$data['pic_big'] = str_replace('profile.cc.fbcdn', 'profile.ak.fbcdn', $data['pic_big']);
					$data['pic_big'] = str_replace('hprofile-cc-', 'hprofile-ak-', $data['pic_big']);

					$facebook->mapAvatar($data['pic_big'], $user->id, $config->get('fbwatermark'));
				}

				$jsonarray = $this->loginProccess();
			}
			else
			{
				IJReq::setResponseCode(401);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_UNABLE_TO_AUTHENTICATE'));

				return false;
			}
		}
		else
		{
			$query = $this->db->getQuery(true);

			// Create the base select statement.
			$query->select('u.id')
				->from($this->db->qn('#__users', 'u'))
				->where($this->db->qn('u.email') . ' = ' . $this->db->q($data['email']));

			// Set the query and load the result.
			$this->db->setQuery($query);

			$uid = $this->db->loadResult();

			if ($uid > 0)
			{
				// if user exists with email address send email id already exists
				$query = $this->db->getQuery(true);

				// Create the base select statement.
				$query->select('u.id')
					->from($this->db->qn('#__users AS u, #__community_connect_users AS cu'))
					->where($this->db->qn('u.id') . ' = ' . $this->db->q('cu.userid'))
					->where($this->db->qn('u.email') . ' = ' . $this->db->q($data['email']))
					->where($this->db->qn('cu.connectid') . ' = ' . $this->db->q($password_set));

				$this->db->setQuery($query);
				$uid = $this->db->loadResult();

				if (empty($uid))
				{
					IJReq::setResponseCode(702);
					IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_EMAIL_ALREADY_EXIST'));

					return false;
				}
			}

			$query = $this->db->getQuery(true);

			// Create the base select statement.
			$query->select('id')
				->from($this->db->qn('#__users'))
				->where($this->db->qn('username') . ' = ' . $this->db->q($data['user_nm']));

			$this->db->setQuery($query);
			$uid = $this->db->loadResult();

			if ($uid > 0)
			{
				IJReq::setResponseCode(701);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USERNAME_ALREADY_EXIST'));

				return false;
			}
			else
			{
				jimport('joomla.user.helper');
				$user = new JUser;
				$fbData = IJReq::getTaskData('fb');
				$data['name'] = $data['relname'];
				$data['username'] = trim(str_replace("\n", "", $data['user_nm']));
				$data['password1'] = $data['password2'] = trim(str_replace("\n", "", $password_set . $time));
				$data['email1'] = $data['email2'] = trim(str_replace("\n", "", $data['relname']));
				$data['latitude'] = IJReq::getTaskData('lat');
				$data['longitude'] = IJReq::getTaskData('long');

				$user->bind($data);

				if (!$user->save())
				{
					IJReq::setResponseCode(500);

					return false;
				}

				$aclval = $user->id;

				$query = $this->db->getQuery(true);

				// store usegroup for user...
				$query->insert($this->db->qn('#__user_usergroup_map'))
					->columns(
						array(
							$this->db->qn('group_id'),
							$this->db->qn('user_id')
							)
						)
					->values(
						$this->db->q('2') . ', ' .
						$this->db->q($aclval)
						);

				$this->db->setQuery($query);
				$this->db->execute();

				if (strtolower(IJOOMER_GC_REGISTRATION) === 'jomsocial' && file_exists(JPATH_ROOT . '/components/com_community/libraries/core.php'))
				{
					require_once JPATH_ROOT . '/components/com_community/libraries/core.php';

					$query = $this->db->getQuery(true);

					// Create the base insert statement.
					$query->insert($this->db->qn('#__community_connect_users'))
						->columns(
							array(
								$this->db->qn('userid'),
								$this->db->qn('connectid'),
								$this->db->qn('type'))
							)
						->values(
							$this->db->q($aclval) . ', ' .
							$this->db->q($password_set) . ', ' .
							$this->db->q('facebook')
							);

					$this->db->setQuery($query);
					$this->db->execute();

					$config = CFactory::getConfig();

					// Store user image...
					CFactory::load('libraries', 'facebook');
					$facebook = new CFacebook;

					// Edited by Salim (Date: 08-09-2011)
					$data['pic_big'] = str_replace('profile.cc.fbcdn', 'profile.ak.fbcdn', $data['pic_big']);
					$data['pic_big'] = str_replace('hprofile-cc-', 'hprofile-ak-', $data['pic_big']);

					$facebook->mapAvatar($data['pic_big'], $aclval, $config->get('fbwatermark'));
				}

				// Update password again...
				$salt = JUserHelper::genRandomPassword(32);
				$crypt = JUserHelper::getCryptedPassword($password_set . $time, $salt);
				$data['password'] = $crypt . ':' . $salt;

				$query = $this->db->getQuery(true);

				// Create the base update statement.
				$query->update($this->db->qn('#__users'))
					->set($this->db->qn('password') . ' = ' . $this->db->q($data['password']))
					->where($this->db->qn('id') . ' = ' . $this->db->q($aclval));

				// Set the query and execute the update.
				$this->db->setQuery($query);

				$this->db->execute();

				$usersipass['username'] = trim(str_replace("\n", "", $data['user_nm']));
				$usersipass['password'] = trim(str_replace("\n", "", $password_set . $time));

				if ($this->mainframe->login($usersipass) == '1')
				{
					if ($jsonarray = $this->loginProccess())
					{
						$this->fbFieldSet($aclval);

						return $jsonarray;
					}
					else
					{
						return false;
					}
				}
				else
				{
					IJReq::setResponseCode(401);
					IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_UNABLE_TO_AUTHENTICATE'));

					return false;
				}
			}
		}

		return $jsonarray;
	}

	/**
	 * The Function FB Field Set
	 *
	 * @param   [type]  $userid  contains the userid
	 *
	 * @return  void
	 */
	private function fbFieldSet($userid)
	{
		$fb = IJReq::getTaskData('fb');
		$fieldConnection = array('FB_USERID' => (isset($fb->uid)) ? number_format($fb->uid, 0, '', '') : null,
			'FB_USERNAME' => (isset($fb->username)) ? $fb->username : null,
			'FB_3PARTY_ID' => (isset($fb->third_party_id)) ? $fb->third_party_id : null,
			'FB_FNAME' => (isset($fb->first_name)) ? $fb->first_name : null,
			'FB_MNAME' => (isset($fb->middle_name)) ? $fb->middle_name : null,
			'FB_LNAME' => (isset($fb->last_name)) ? $fb->last_name : null,
			'FB_PIC' => (isset($fb->pic)) ? $fb->pic : null,
			'FB_PIC_SMALL' => (isset($fb->pic_small)) ? $fb->pic_small : null,
			'FB_PIC_COVER' => (isset($fb->pic_cover->source)) ? $fb->pic_cover->source : null,
			'FB_VERIFIED' => (isset($fb->verified)) ? $fb->verified : null,
			'FB_SEX' => (isset($fb->sex)) ? $fb->sex : null,
			'FB_BIRTH_DATE' => (isset($fb->birthday_date)) ? $fb->birthday_date : null,
			'FB_STATUS' => (isset($fb->status->message)) ? $fb->status->message : null,
			'FB_ABOUT_ME' => (isset($fb->about_me)) ? $fb->about_me : null,
			'FB_TIMEZONE' => (isset($fb->timezone)) ? $fb->timezone : null,
			'FB_ISMINOR' => (isset($fb->is_minor)) ? $fb->is_minor : null,
			'FB_POLITICAL' => (isset($fb->political)) ? $fb->political : null,
			'FB_QUOTES' => (isset($fb->quotes)) ? $fb->quotes : null,
			'FB_RELATION_STATUS' => (isset($fb->relationship_status)) ? $fb->relationship_status : null,
			'FB_RELIGION' => (isset($fb->religion)) ? $fb->religion : null,
			'FB_TV_SHOW' => (isset($fb->tv)) ? $fb->tv : null,
			'FB_SPORTS' => (isset($fb->sports[0]->name)) ? $fb->sports[0]->name : null,
			'FB_WORK' => (isset($fb->work[0])) ? $fb->work[0] : null,
			'FB_EDUCATION' => (isset($fb->education[0]->school)) ? $fb->education[0]->school : null,
			'FB_EMAIL' => (isset($fb->email)) ? $fb->email : null,
			'FB_WEBSITE' => (isset($fb->website)) ? $fb->website : null,
			'FB_CURRENT_STREET' => (isset($fb->current_address->street)) ? $fb->current_address->street : null,
			'FB_CURRENT_CITY' => (isset($fb->current_address->city)) ? $fb->current_address->city : null,
			'FB_CURRENT_STATE' => (isset($fb->current_address->state)) ? $fb->current_address->state : null,
			'FB_CURRENT_COUNTRY' => (isset($fb->current_address->country)) ? $fb->current_address->country : null,
			'FB_CURRENT_ZIP' => (isset($fb->current_address->zip)) ? $fb->current_address->zip : null,
			'FB_CURRENT_LATITUDE' => (isset($fb->current_address->latitude)) ? $fb->current_address->latitude : null,
			'FB_CURRENT_LONGITUDE' => (isset($fb->current_address->longitude)) ? $fb->current_address->longitude : null,
			'FB_CURRENT_LOCATION_NAME' => (isset($fb->current_address->name)) ? $fb->current_address->name : null,
			'FB_HOMETOWN_STREET' => (isset($fb->hometown_location->street)) ? $fb->hometown_location->street : null,
			'FB_HOMETOWN_CITY' => (isset($fb->hometown_location->city)) ? $fb->hometown_location->city : null,
			'FB_HOMETOWN_STATE' => (isset($fb->hometown_location->state)) ? $fb->hometown_location->state : null,
			'FB_HOMETOWN_COUNTRY' => (isset($fb->hometown_location->country)) ? $fb->hometown_location->country : null,
			'FB_HOMETOWN_ZIP' => (isset($fb->hometown_location->zip)) ? $fb->hometown_location->zip : null,
			'FB_HOMETOWN_LATITUDE' => (isset($fb->hometown_location->latitude)) ? $fb->hometown_location->latitude : null,
			'FB_HOMETOWN_LONGITUDE' => (isset($fb->hometown_location->longitude)) ? $fb->hometown_location->longitude : null,
			'FB_HOMETOWN_LOCATION_NAME' => (isset($fb->hometown_location->name)) ? $fb->hometown_location->name : null,
		);

		foreach ($fieldConnection as $key => $value)
		{
			$query = $this->db->getQuery(true);

			// Create the base select statement.
			$query->select('value')
				->from($this->db->qn('#__ijoomeradv_jomsocial_config'))
				->where($this->db->qn('name') . ' = ' . $this->db->q($key));

			// Set the query and load the result.
			$this->db->setQuery($query);

			$fieldid = $this->db->loadResult();

			if ($fieldid)
			{
				$query = $this->db->getQuery(true);

				// Create the base select statement.
				$query->select('id')
					->from($this->db->qn('#__community_fields_values'))
					->where($this->db->qn('user_id') . ' = ' . $this->db->q($userid))
					->where($this->db->qn('field_id') . ' = ' . $this->db->q($fieldid));

				$this->db->setQuery($query);
				$field = $this->db->loadResult();

				$query = $this->db->getQuery(true);

				if ($field)
				{
					// Create the base update statement.
					$query->update($this->db->qn('#__community_fields_values'))
						->set($this->db->qn('value') . ' = ' . $this->db->q($value))
						->where($this->db->qn('id') . ' = ' . $this->db->q($field));
				}
				else
				{
					// Create the base insert statement.
					$query->insert($this->db->qn('#__community_fields_values'))
						->columns(
							array(
								$this->db->qn('user_id'),
								$this->db->qn('field_id'),
								$this->db->qn('value'),
								$this->db->qn('access')
								)
							)
						->values(
							$this->db->q($userid) . ', ' .
							$this->db->q($fieldid) . ', ' .
							$this->db->q($value) . ', ' .
							$this->db->q(0)
							);

				}

				$this->db->setQuery($query);
				$this->db->execute();

			}
		}

		$query = $this->db->getQuery(true);

		// Create the base update statement.
		$query->update($this->db->qn('#__community_users'))
			->set($this->db->qn('status') . ' = ' . $this->db->q($fieldConnection['FB_STATUS']))
			->where($this->db->qn('userid') . ' = ' . $this->db->q($userid));

		$this->db->setQuery($query);
		$this->db->execute();
	}

	public function checkForFacebookUser($userID,$fbID,$friendsFbIds)
	{

		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->quoteName('#__facebook_joomla_connect'))
			->where($this->db->quoteName('joomla_userid') . ' = ' . $this->db->quote($userID));

		// Set the query and load the result.
		$this->db->setQuery($query);

		$facebookUser = $this->db->loadObject();

		if($facebookUser)
		{
			$updateFBUser = $this->db->getQuery(true);

			// Create the base update statement.
			$updateFBUser->update($this->db->quoteName('#__facebook_joomla_connect'))
				->set($this->db->quoteName('facebook_userid') . ' = ' . $this->db->quote($fbID))
				->set($this->db->quoteName('facebook_friends') . ' = ' . $this->db->quote($friendsFbIds))
				->set($this->db->quoteName('joined_date') . ' = ' . $this->db->quote(time()))
				->set($this->db->quoteName('linked') . ' = ' . $this->db->quote(1))
				->where($this->db->quoteName('joomla_userid') . ' = ' . $this->db->quote($userID));

			// Set the query and execute the update.
			$this->db->setQuery($updateFBUser);

			/*echo $updateFBUser->dump();
			exit;*/

			$this->db->execute();
		}
		else
		{
			$insertFBUser = $this->db->getQuery(true);

			// Create the base insert statement.
			$insertFBUser->insert($this->db->quoteName('#__facebook_joomla_connect'))
				->columns(
					array(
						$this->db->quoteName('joomla_userid'),
						$this->db->quoteName('facebook_userid'),
						$this->db->quoteName('facebook_friends'),
						$this->db->quoteName('joined_date'),
						$this->db->quoteName('linked')
					)
				)
				->values(
					$this->db->quote($userID) . ', ' .
					$this->db->quote($fbID) . ', ' .
					$this->db->quote($friendsFbIds) . ', ' .
					$this->db->quote(time()) . ', ' .
					$this->db->quote(1)
				);

			// Set the query and execute the insert.
			$this->db->setQuery($insertFBUser);

			$this->db->execute();

		}

	}

	/**
	 * This function is use to register a new user
	 *
	 * @example the json string will be like, :
	 *    {
	 *        "task":"registration",
	 *        "taskData": {
	 *            "name":"name",
	 *            "city":"city",
	 *            "birthdate":"birthdate",
	 *            "mobile":"mobile",
	 *            "password":"password",
	 *            "email":"email",
	 *            "fbID":""
	 *	          "fb":""
	 *
	 *        }
	 *    }
	 *
	 * @return it will return false value otherwise jsson array
	 */
	public function registration()
	{
		$post['relname']    = IJReq::getTaskData('name');
		//$post['username'] = IJReq::getTaskData('username');
		$post['password']   = IJReq::getTaskData('password');
		$post['email']      = IJReq::getTaskData('email');
		$post['username']   = $post['email'];
		//$post['type']       = IJReq::getTaskData('type', 0, 'int');

		$post['only_city'] = IJReq::getTaskData('city', '', 'string');
		$post['location']  = IJReq::getTaskData('city', '', 'string');
		$post['birthdate'] = IJReq::getTaskData('birthdate', '', 'string');
		$post['mobile']    = IJReq::getTaskData('mobile', '', 'string');
		$Full_flag         = IJReq::getTaskData('full', 0, 'int');
		$fbid              = IJReq::getTaskData('fbID','');
		$fb                = IJReq::getTaskData('fb',0,'int');

		$lang              = JFactory::getLanguage();
		$lang->load('com_users');

		if (strtolower(IJOOMER_GC_REGISTRATION) === 'no')
		{
			// If registration not allowed
			IJReq::setResponseCode(401);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_REGISTRATION_NOT_ALLOW'));

			return false;
		}

		$username = str_replace("\n", "", trim($post['username']));
		$query    = $this->db->getQuery(true);
		$emails   = str_replace("\n", "", trim($post['email']));

		// Create the base select statement.
		$query->select('id')
			->from($this->db->qn('#__users'))
			->where($this->db->qn('email') . ' = ' . $this->db->q($emails));

		$this->db->setQuery($query);

		if ($this->db->loadResult() > 0)
		{
			// Check if email id already exist
			IJReq::setResponseCode(702);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_EMAIL_ALREADY_EXIST'));

			return false;
		}

		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('id')
			->from($this->db->qn('#__users'))
			->where($this->db->qn('username') . ' = ' . $this->db->q($username));

		$this->db->setQuery($query);

		if ( $this->db->loadResult() > 0)
		{
			// Check if user already exist
			IJReq::setResponseCode(701);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USERNAME_ALREADY_EXIST'));

			return false;
		}

		$params = JComponentHelper::getParams('com_users');
		$system = $params->get('new_usertype', 2);
		$useractivation = $params->get('useractivation');
		$sendpassword = $params->get('sendpassword', 1);

		// Initialise the table with JUser.
		$user = new JUser;
		$post['name'] = trim(str_replace("\n", "", $post['relname']));
		$post['username'] = trim(str_replace("\n", "", $post['username']));
		$post['password'] = $post['password1'] = $post['password2'] = trim(str_replace("\n", "", $post['password']));
		$post['email'] = $post['email1'] = $post['email2'] = trim(str_replace("\n", "", $post['email']));
		$post['groups'][0] = $system;

		$post['only_city']  = trim(str_replace("\n", "", $post['only_city']));
		$post['city']   = trim(str_replace("\n", "", $post['location']));
		$post['birthdate']  = trim(str_replace("\n", "", $post['birthdate']));
		$post['mobile']      = trim(str_replace("\n", "", $post['mobile']));

		$post['is_fb_user'] = $fb;

		$post['is_use_fb_image'] = ($fb) ? '1' : '0';
		$post['fb_id']      = $fbid;
		$post['latitude']   = '';
		$post['longitude']  = '';

		// Check if the user needs to activate their account.
		if (($useractivation == 1) || ($useractivation == 2))
		{
			$post['activation'] = JApplication::getHash(JUserHelper::genRandomPassword());
			$post['block']      = 1;
		}

		if($fb == 1)
		{
			$post['activation'] = '';
			$post['block']      = 0;
		}

		$user->bind($post);

		if (!$user->save())
		{
			IJReq::setResponseCode(500);

			return false;
		}

		$aclval = $user->id;

		if (!$aclval)
		{
			IJReq::setResponseCode(500);

			return false;
		}

		// Compile the notification mail values.
		$data = $user->getProperties();
		$config = JFactory::getConfig();
		$data['fromname'] = $config->get('fromname');
		$data['mailfrom'] = $config->get('mailfrom');
		$data['sitename'] = $config->get('sitename');
		$data['siteurl'] = JUri::root();

		if($fb == 0)
		{
			// Handle account activation/confirmation emails.
			/*echo $useractivation;
			exit;*/
			if ($useractivation == 2)
			{
				// Set the link to confirm the user email.
				$uri = JURI::getInstance();
				$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
				$data['activate'] = $base . JRoute::_('index.php?option=com_users&task=registration.activate&token=' . $data['activation'], false);

				$emailSubject = JText::sprintf(
					'COM_USERS_EMAIL_ACCOUNT_DETAILS',
					$data['name'],
					$data['sitename']
				);

				if ($sendpassword)
				{
					$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY',
						$data['name'],
						$data['sitename'],
						$data['siteurl'] . 'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
						$data['siteurl'],
						$data['username'],
						$data['password_clear']
					);
				}
				else
				{
					$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY_NOPW',
						$data['name'],
						$data['sitename'],
						$data['siteurl'] . 'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
						$data['siteurl'],
						$data['username']
					);
				}
			}
			elseif ($useractivation == 1)
			{
				// Set the link to activate the user account.
				$uri = JURI::getInstance();
				$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
				$data['activate'] = $base . JRoute::_('index.php?option=com_users&task=registration.activate&token=' . $data['activation'], false);

				$emailSubject = JText::_('COM_USERS_EMAIL_ACCOUNT_DETAILS');

				/*$emailSubject = JText::_(
					'COM_USERS_EMAIL_ACCOUNT_DETAILS',
					$data['name'],
					$data['sitename']


				);*/

				if ($sendpassword)
				{
					/*$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
						$data['name'],
						$data['sitename'],
						$data['siteurl'] . 'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
						$data['siteurl'],
						$data['username'],
						$data['password_clear']
					);
					Dear %s,<br /><br />
					<h3><b>WELCOME TO BESEATED</b></h3><br /><br />
					YOUR STAIRWAY TO THE MOST EXCLUSIVE VENUES, LUXURIES & EVENTS<br /><br />
					Thank you for signing up, you may now Be Seated.<br /><br />
					To activate your account, please click %s.<br /><br />
					Simple clicks will lead you to an Exceptional Journey.<br /><br />
					For further assistance, kindly read our FAQs or Email us.<br /><br /><br />
					Beseated, Be Treated, because when you reserve it, you deserve it!<br /><br />
					Yours,<br /><br />
					BESEATED CUSTOMER SUPPORT<br /><br />
					%s
					*/
					$activationLink =  JUri::base().'index.php?option=com_users&task=registration.activate&token=' . $data['activation'];

					if(file_exists(JPATH_SITE.'/components/com_beseated/helpers/email.php'))
					{
						require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
						$emailAppHelper = new BeseatedEmailHelper();
						$emailAppHelper->registration($post['name'],$post['email'],$activationLink);
					}

					/*$htmlActivationLink='<a href="'.$activationLink.'">here</a>';
					$emailFooterImg = JUri::base().'images/beseated/email-footer.jpg';
					$htmlEmailFooter = '<img alt="The Beseated" src="'.$emailFooterImg.'" />';
					$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
						$data['name'],
						$htmlActivationLink,
						$htmlEmailFooter
					);*/
				}
				else
				{
					$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY_NOPW',
						$data['name'],
						$data['sitename'],
						$data['siteurl'] . 'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
						$data['siteurl'],
						$data['username']
					);
				}
			}
			else
			{
				$emailSubject	= JText::sprintf(
					'COM_USERS_EMAIL_ACCOUNT_DETAILS',
					$data['name'],
					$data['sitename']
				);

				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_BODY',
					$data['name'],
					$data['sitename'],
					$data['siteurl'],
					$data['username'],
					$data['password_clear']
				);
			}

			// Send the registration email.
			// Commented By Nilesh : disable registration email
			//$return = true;
			//$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody,true);

			// Send Notification mail to administrators
			if (($params->get('useractivation') < 2) && ($params->get('mail_to_admin') == 1))
			{
				$emailSubject = JText::_('COM_USERS_EMAIL_ACCOUNT_DETAILS');
				/*$emailSubject = JText::sprintf(
					'COM_USERS_EMAIL_ACCOUNT_DETAILS',
					$data['name'],
					$data['sitename']
				);*/

				$emailBodyAdmin = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY',
					$data['name'],
					$data['username'],
					$data['siteurl']
				);

				// Get all admin users
				$query = 'SELECT name, email, sendEmail
						FROM #__users
						WHERE sendEmail=1';
				$this->db->setQuery($query);
				$rows = $this->db->loadObjectList();

				// Send mail to all superadministrators id
				foreach ($rows as $row)
				{
					// Commented By Nilesh : disable registration email
					$return = true;
					//$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBodyAdmin);

					// Check for an error.
					if ($return !== true)
					{
						IJReq::setResponseCode(500);
						IJReq::setResponseMessage(JText::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));

						return false;
					}
				}
			}

			// Check for an error.
			if ($return !== true)
			{
				$query = $this->db->getQuery(true);

				// Send a system message to administrators receiving system mails
				$query->select('id')
					->from($this->db->qn('#__users'))
					->where($this->db->qn('block') . ' = ' . $this->db->q('0'))
					->where($this->db->qn('sendEmail') . ' = ' . $this->db->q('1'));

				$this->db->setQuery($query);
				$sendEmail = $this->db->loadColumn();

				if (count($sendEmail) > 0)
				{
					$jdate = new JDate;

					$query = $this->db->getQuery(true);

					$messages = array();

					foreach ($sendEmail as $userid)
					{
						$messages[] = "({$userid}, {$userid}, '{$jdate->toSql()}', '" . JText::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT') . "', '" . JText::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $return, $data['username']) . "')";
					}

					// Build the query to add the messages
					$query->insert($this->db->qn('#__messages'))
						->columns(
							array(
								$this->db->qn('user_id_from'),
								$this->db->qn('user_id_to'),
								$this->db->qn('date_time'),
								$this->db->qn('subject'),
								$this->db->qn('message')
								)
							)
						->values(implode(',', $messages));

					//$query .= implode(',', $messages);

					$this->db->setQuery($query);
					$this->db->execute();
				}

				IJReq::setResponseCode(500);
				IJReq::setResponseMessage(JText::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'));

				return false;
			}
		}
		else
		{
			$my			= JFactory::getUser($aclval);
			$userid		= $my->id;

			$credentials = array();
			$credentials['username'] = IJReq::getTaskData('email'); // get username
			$credentials['password'] = IJReq::getTaskData('password'); // get password


			// Initialiase variables.
			$query = $this->db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($this->db->quoteName('#__users'))
				->where($this->db->quoteName('email') . ' = binary ' . $this->db->quote($credentials['username']));

			// Set the query and load the result.
			$this->db->setQuery($query);

			$result = $this->db->loadObject();

			if(empty($result->username))
			{
				$jsonarray['code']=401;
				$jsonarray['message']=JText::_('COM_IJOOMERADV_UNABLE_TO_AUTHENTICATE_INVALID_USERNAME');

				return $jsonarray;
			}

			$credentials['username'] = $result->username;

			$mainframe =  JFactory::getApplication();
			if($mainframe->login($credentials) == '1')
			{
				$user = JFactory::getUser();
				if(!$user->id)
				{
					$jsonarray['code']=401;
					$jsonarray['message']=JText::_('COM_IJOOMERADV_UNABLE_TO_AUTHENTICATE');

					return $jsonarray;
				}

				$deviceType	= IJReq::getTaskData('deviceType');

				$jsonarray = $this->loginProccess();
			}
			else
			{
				$jsonarray = array();
				$jsonarray['code']=402;
				$jsonarray['message']=JText::_('COM_IJOOMERADV_UNABLE_TO_AUTHENTICATE_INVALID_PASSWORD');
			}

			return $jsonarray;
		}



		$jsonarray['code'] = 200;

		return $jsonarray;
	}

	/**
	 * Function To Request Token To Reset Password
	 *
	 * @return  it will return a false value otherwise jsson array
	 */
	public function retriveToken()
	{
		$email = IJReq::getTaskData('email');

		jimport('joomla.mail.helper');
		jimport('joomla.user.helper');

		if (!JMailHelper::isEmailAddress($email))
		{
			// Make sure the e-mail address is valid
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_INVALID_EMAIL'));

			return false;
		}

		// Build a query to find the user
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('id')
			->from($this->db->qn('#__users'))
			->where($this->db->qn('email') . ' = ' . $this->db->q($email))
			->where($this->db->qn('block') . ' = ' . $this->db->q('0'));

		$this->db->setQuery($query);

		if (!($id = $this->db->loadResult()))
		{
			// Check if user exist of given email
			IJReq::setResponseCode(401);

			return false;
		}

		if (IJ_JOOMLA_VERSION === 1.5)
		{
			// Generate a new token
			$token = JApplication::getHash(JUserHelper::genRandomPassword());
			$salt = JUserHelper::getSalt('crypt-md5');
			$hashedToken = md5($token . $salt) . ':' . $salt;
		}
		else
		{
			// Set the confirmation token.
			$token = JApplication::getHash(JUserHelper::genRandomPassword());
			$salt = JUserHelper::getSalt('crypt-md5');
			$hashedToken = md5($token . $salt) . ':' . $salt;
		}

		$query = $this->db->getQuery(true);

		// Create the base update statement.
		$query->update($this->db->qn('#__users'))
			->set($this->db->qn('activation') . ' = ' . $this->db->q($hashedToken))
			->where($this->db->qn('id') . ' = ' . $this->db->q($id))
			->where($this->db->qn('block') . ' = ' . $this->db->q('0'));

		$this->db->setQuery($query);

		if (!$this->db->query())
		{
			// Save the token
			IJReq::setResponseCode(500);

			return false;
		}

		if (!$this->_sendConfirmationMail($email, $token))
		{
			// Send the token to the user via e-mail
			IJReq::setResponseCode(500);

			return false;
		}

		$jsonarray['code'] = 200;

		return $jsonarray;
	}

	/**
	 * Function To Validate Token Against Username
	 *
	 * @return  it will return the false value otherwise Jasson Array
	 */
	public function validateToken()
	{
		$token = IJReq::getTaskData('token');
		$username = IJReq::getTaskData('username');

		jimport('joomla.user.helper');

		if (strlen($token) != 32)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_INVALID_TOKEN'));

			return false;
		}

		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('id, activation')
			->from($this->db->qn('#__users'))
			->where($this->db->qn('username') . ' = ' . $this->db->q($username))
			->where($this->db->qn('block') . ' = ' . $this->db->q('0'));

		$this->db->setQuery($query);

		if (!($row = $this->db->loadObject()))
		{
			// Verify the token
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_INVALID_TOKEN'));

			return false;
		}

		$parts = explode(':', $row->activation);
		$crypt = $parts[0];

		if (!isset($parts[1]))
		{
			IJReq::setResponseCode(401);

			return false;
		}

		$salt = $parts[1];
		$testcrypt = JUserHelper::getCryptedPassword($token, $salt);

		// Verify the token
		if (!($crypt == $testcrypt))
		{
			IJReq::setResponseCode(401);

			return false;
		}

		// Push the token and user id into the session
		$jsonarray['code'] = 200;
		$jsonarray['userid'] = $row->id;
		$jsonarray['crypt'] = $crypt . ':' . $salt;

		return $jsonarray;
	}

	/**
	 * @uses
	 *
	 */
	/**
	 * Function Is Used To Reset Password
	 *
	 * @return  it will return a value in false or JssonArray
	 */
	public function resetPassword()
	{
		$token = IJReq::getTaskData('crypt');
		$userid = IJReq::getTaskData('userid', 0, 'int');
		$password1 = IJReq::getTaskData('password');

		// Make sure that we have a pasword
		if (!$token || !$userid || !$password1)
		{
			IJReq::setResponseCode(400);

			return false;
		}

		jimport('joomla.user.helper');

		// Get the necessary variables
		$salt = JUserHelper::genRandomPassword(32);
		$crypt = JUserHelper::getCryptedPassword($password1, $salt);
		$password = $crypt . ':' . $salt;

		// Get the user object
		$user = new JUser($userid);

		// Fire the onBeforeStoreUser trigger
		JPluginHelper::importPlugin('user');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeStoreUser', array($user->getProperties(), false));

		$query = $this->db->getQuery(true);

		// Create the base update statement.
		$query->update($this->db->qn('#__users'))
			->set($this->db->qn('password') . ' = ' . $this->db->q($password))
			->set($this->db->qn('activation') . ' = ' . $this->db->q(''))
			->where($this->db->qn('id') . ' = ' . $this->db->q($userid))
			->where($this->db->qn('activation') . ' = ' . $this->db->q($token))
			->where($this->db->qn('block') . ' = ' . $this->db->q('0'));


		$this->db->setQuery($query);

		if (!$result = $this->db->execute())
		{
			// Save the password
			IJReq::setResponseCode(500);

			return false;
		}

		// Update the user object with the new values.
		$user->password = $password;
		$user->activation = '';
		$user->password_clear = $password1;

		if (IJ_JOOMLA_VERSION === 1.5)
		{
			// Fire the onAfterStoreUser trigger
			$dispatcher->trigger('onAfterStoreUser', array($user->getProperties(), false, $result, ''));
		}
		else
		{
			$app = JFactory::getApplication();

			if ( !$user->save(true))
			{
				IJReq::setResponseCode(500);

				return false;
			}

			// Flush the user data from the session.
			$app->setUserState('com_users.reset.token', null);
			$app->setUserState('com_users.reset.user', null);
		}

		$jsonarray['code'] = 200;

		return $jsonarray;
	}

	/**
	 * Function Use to Retrive Userid
	 *
	 * @return  it will return a false value or Jsson Array
	 */
	public function retriveUsername()
	{
		$email = IJReq::getTaskData('email');

		jimport('joomla.mail.helper');
		jimport('joomla.user.helper');

		// Make sure the e-mail address is valid
		if (!JMailHelper::isEmailAddress($email))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_INVALID_TOKEN'));

			return false;
		}

		// Build a query to find the user
		$query = $this->db->getQuery(true);

		// Build a query to find the user.
		$query->select('*')
			->from($this->db->qn('#__users'))
			->where($this->db->qn('email') . ' = ' . $this->db->q($email))
			->where($this->db->qn('block') . ' = ' . $this->db->q('0'));

		// Set the query and load the result.
		$this->db->setQuery($query);
		$user = $this->db->loadObject();

		// Set the e-mail parameters
		$lang = JFactory::getLanguage();
		$lang->load('com_users');
		$config = JFactory::getConfig();

		// Assemble the login link.
		include_once JPATH_ROOT . '/components/com_users/helpers/route.php';
		$itemid = UsersHelperRoute::getLoginRoute();
		$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
		$link = 'index.php?option=com_users&view=login' . $itemid;

		$mode = $config->get('force_ssl', 0) == 2 ? 1 : - 1;

		$data = JArrayHelper::fromObject($user);
		$fromname = $config->get('fromname');
		$mailfrom = $config->get('mailfrom');
		$sitename = $config->get('sitename');
		$link_text = JRoute::_($link, false, $mode);
		$username = $data['username'];
		$subject = JText::sprintf('COM_USERS_EMAIL_USERNAME_REMINDER_SUBJECT', $sitename);
		$body = JText::sprintf('COM_USERS_EMAIL_USERNAME_REMINDER_BODY', $sitename, $username, $link_text);

		// Send the token to the user via e-mail
		$return = JFactory::getMailer()->sendMail($mailfrom, $fromname, $email, $subject, $body);

		if ( !$return)
		{
			IJReq::setResponseCode(500);

			return false;
		}

		$jsonarray['code'] = 200;

		return $jsonarray;
	}

	/**
	 * The Send Confirmation Mail Function
	 *
	 * @param   [type]  $email  Contains The Email
	 * @param   [type]  $token  Contains The Token
	 *
	 * @return  boolean Returns The Value in True or False
	 */
	public function _sendConfirmationMail($email, $token)
	{
		$config = JFactory::getConfig();

		if (IJ_JOOMLA_VERSION === 1.5)
		{
			$url = JRoute::_('index.php?option=com_user&view=reset&layout=confirm', true, -1);
			$sitename = $config->getValue('sitename');

			// Set the e-mail parameters
			$lang = JFactory::getLanguage();
			$lang->load('com_user');

			$from = $config->getValue('mailfrom');
			$fromname = $config->getValue('fromname');
			$subject = sprintf(JText::_('PASSWORD_RESET_CONFIRMATION_EMAIL_TITLE'), $sitename);
			$body = sprintf(JText::_('PASSWORD_RESET_CONFIRMATION_EMAIL_TEXT'), $sitename, $token, $url);

			// Send the e-mail
			if (!JUtility::sendMail($from, $fromname, $email, $subject, $body))
			{
				return false;
			}
		}
		else
		{
			// Set the e-mail parameters
			$lang = JFactory::getLanguage();
			$lang->load('com_users');
			include_once JPATH_ROOT . '/components/com_users/helpers/route.php';

			$mode = $config->get('force_ssl', 0) == 2 ? 1 : - 1;
			$itemid = UsersHelperRoute::getLoginRoute();
			$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
			$link = 'index.php?option=com_users&view=reset&layout=confirm' . $itemid;

			$fromname = $config->get('fromname');
			$mailfrom = $config->get('mailfrom');
			$sitename = $config->get('sitename');
			$link_text = JRoute::_($link, false, $mode);

			$subject = JText::sprintf('COM_USERS_EMAIL_PASSWORD_RESET_SUBJECT', $sitename);
			$body = JText::sprintf('COM_USERS_EMAIL_PASSWORD_RESET_BODY', $sitename, $token, $link_text);

			// Send the password reset request email.
			$return = JFactory::getMailer()->sendMail($mailfrom, $fromname, $email, $subject, $body);

			if ( !$return)
			{
				return false;
			}
		}

		return true;
	}
}
