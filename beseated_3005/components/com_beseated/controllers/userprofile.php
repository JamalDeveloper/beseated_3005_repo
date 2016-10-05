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
 * The Beseated User Profile Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerUserProfile extends JControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config	An optional associative array of configuration settings.
	 * @return  ContentControllerArticles
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   0.0.1
	 */
	public function getModel($name = 'UserProfile', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function save()
	{
		$app       = JFactory::getApplication();
		$user       = JFactory::getUser();

		$menu      = $app->getMenu();
		$input     = $app->input;
		$data      = $input->get('jform',array(),'array');

		$email       = trim($data['email']);
		$password    = trim($data['password']);
		$password2   = trim($data['password2']);
		$oldpassword = trim($data['oldpassword']);
		$correctpwd  = 0;

		$flg       = 0;

		if(!empty($password) && !empty($password2) && empty($oldpassword))
		{
			$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userprofile', true );

			$profileLink = $menuItem->link.'&Itemid='.$menuItem->id;
			$app->redirect($profileLink,JText::_('COM_BCTED_USER_PROFILE_NOT_UPDATED'));
		}
		elseif (empty($password) && empty($password2) && !empty($oldpassword)) 
		{
			$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userprofile', true );

			$profileLink = $menuItem->link.'&Itemid='.$menuItem->id;
			$app->redirect($profileLink,JText::_('COM_BCTED_USER_PROFILE_NOT_UPDATED'));
		}
		elseif (!empty($password) && empty($password2) && !empty($oldpassword)) 
		{
			$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userprofile', true );

			$profileLink = $menuItem->link.'&Itemid='.$menuItem->id;
			$app->redirect($profileLink,JText::_('COM_BCTED_USER_PROFILE_NOT_UPDATED'));
		}
		elseif (empty($password) && !empty($password2) && !empty($oldpassword)) 
		{
			$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userprofile', true );

			$profileLink = $menuItem->link.'&Itemid='.$menuItem->id;
			$app->redirect($profileLink,JText::_('COM_BCTED_USER_PROFILE_NOT_UPDATED'));
		}
		
		$passwordMatch = JUserHelper::verifyPassword($oldpassword, $user->password, $user->id);

		if(!empty($password) && !empty($password2) && !empty($oldpassword))
		{
			if(!$passwordMatch)
			{
				$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userprofile', true );

				$profileLink = $menuItem->link.'&Itemid='.$menuItem->id;
				$app->redirect($profileLink,JText::_('COM_BCTED_USER_PROFILE_OLD_PASSWORD_INCORRERCT'));
			}

			if($password === $password2 && $passwordMatch == 1)
			{
				$flg = 1;
			}

			if($flg == 0)
			{
				$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userprofile', true );

				$profileLink = $menuItem->link.'&Itemid='.$menuItem->id;
				$app->redirect($profileLink,JText::_('COM_BCTED_USER_PROFILE_NOT_UPDATED'));
			}
		}

		$model       = $this->getModel();
		$response    = $model->save($data);
		$profileMenu = BeseatedHelper::getBeseatedMenuItem('user-profile');
		$menuItem    = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userprofile', true );
		$profileLink = $menuItem->link.'&Itemid='.$menuItem->id;

		if($response ==1)
		{
			$app->redirect($profileLink,JText::_('COM_BCTED_USER_PROFILE_UPDATED_SUCCESSFULLY'));
		}
		else if($response == 709)
		{
			$app->redirect($profileLink,JText::_('COM_BCTED_REGISTRATION_PHONE_NUMBER_ALREADY_REGISTERED'));
		}
		else if($response == 701)
		{
			$app->redirect($profileLink,JText::_('COM_BCTED_REGISTRATION_USERNAME_ALREADY_REGISTERED'));
		}
		else if($response == 702)
		{
			$app->redirect($profileLink,JText::_('COM_BCTED_REGISTRATION_EMAIL_ALREADY_REGISTERED'));
		}
		else
		{
			$app->redirect($profileLink,JText::_('COM_BCTED_USER_PROFILE_NOT_UPDATED'));
		}
	}

	public function updateAverageRatingOfVenue($venueID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('avg_rating')
			->from($db->quoteName('#__beseated_ratings'))
			->where($db->quoteName('element_type') . ' = ' . $db->quote('venue'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($venueID));

		// Set the query and load the result.
		$db->setQuery($query);

		$rating     = $db->loadColumn();
		$sum        = array_sum($rating);
		$totalEntry = count($rating);
		$avg        = $sum / $totalEntry;
		$avg        = number_format($avg , 2);
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblVenue = JTable::getInstance('Venue', 'BeseatedTable');
		$tblVenue->load($venueID);
		$tblVenue->venue_rating = $avg;
		$tblVenue->store();
	}

	public function updateAverageRatingOfCompany($companyID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('rating')
			->from($db->quoteName('#__bcted_ratings'))
			->where($db->quoteName('rating_type') . ' = ' . $db->quote('service'))
			->where($db->quoteName('rated_id') . ' = ' . $db->quote($companyID));

		// Set the query and load the result.
		$db->setQuery($query);

		$rating                     = $db->loadColumn();
		$sum                        = array_sum($rating);
		$totalEntry                 = count($rating);
		$avg                        = $sum / $totalEntry;
		$avg                        = number_format($avg , 2);
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblCompany                 = JTable::getInstance('Company', 'BctedTable');
		$tblCompany->load($companyID);
		$tblCompany->company_rating = $avg;
		$tblCompany->store();
	}

	public function giverating()
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		$rating      = $input->get('rate_value',0,'int');
		$bookingID   = $input->get('booking_id',0,'int');
		$elementID   = $input->get('element_id',0,'int');
		$userID      = $input->get('rated_user_id',0,'int');
		$elementType = $input->get('element_type','','string');
		$comment     = $input->get('user_message','','string');

		if(!$elementID || empty($comment) || !$rating || $rating > 5 || empty($elementType))
		{
			echo "400";
			exit;
		}

		$tblRating = JTable::getInstance('Rating', 'BeseatedTable',array());
		$tblRating->load(0);

		$data = array();
		$data['element_id']      = $elementID;
		$data['element_type']    = $elementType;
		$data['user_id']         = $userID;
		$data['avg_rating']      = $rating;
		$data['rating_comment']  = $comment;
		$data['published']       = '1';
		$data['time_stamp']      = time();

		$tblRating->bind($data);
		if(!$tblRating->store())
		{
			echo "500";
			exit;
		}

		if($elementType == "venue")
		{
			//$this->updateAverageRatingOfVenue($elementID);
			$tblVenuebooking = JTable::getInstance('Venuebooking', 'BeseatedTable',array());
			$tblVenuebooking->load($bookingID);
			if($tblVenuebooking->venue_booking_id)
			{
				$tblVenuebooking->is_rated = 1;
				$tblVenuebooking->store();
			}
		}
		
		echo "200";
		exit;
	}

	public function checkUserBigspender()
	{
		$input        = JFactory::getApplication()->input;
        $user_id       = $input->get('user_id', 0, 'int');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		// Create the base select statement.
		$query->select('show_in_biggest_spender')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id));
			
		
		// Set the query and load the result.
		$db->setQuery($query);
	
		$result = $db->loadResult();

		echo $result;
		exit();
	}

	public function checkShowFriendsOnly()
	{
		$input        = JFactory::getApplication()->input;
        $user_id       = $input->get('user_id', 0, 'int');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		// Create the base select statement.
		$query->select('show_friends_only')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id));
			
		
		// Set the query and load the result.
		$db->setQuery($query);
	
		$result = $db->loadResult();

		echo $result;
		exit();
	}
}
