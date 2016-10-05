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
 * The Beseated Club Friends Attending Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerClubFriendsAttending extends JControllerAdmin
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
	public function getModel($name = 'ClubFriendsAttending', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function addMeForVenueTable()
	{
		$app             = JFactory::getApplication();
		$input           = $app->input;
		$user            = JFactory::getUser();
		$bookingID       = $input->get('booking_id',0,'int');
		$message         = $input->get('message','','string');
		$tblVenue        = JTable::getInstance('Venue', 'BeseatedTable');
		$tblTable        = JTable::getInstance('Table', 'BeseatedTable');
		$tblVenuebooking = JTable::getInstance('Venuebooking', 'BeseatedTable');
		$tblVenuebooking->load($bookingID);
		$venueID         = $tblVenuebooking->venue_id;
		$tableID         = $tblVenuebooking->table_id;
		$userID          = $tblVenuebooking->user_id;

		$tblUserprofile = JTable::getInstance('Profile', 'BeseatedTable');
		$tblUserprofile->load($userID);

		$tblVenue->load($venueID);
		$tblTable->load($tableID);

		if(empty($message) || !$userID)
		{
			echo "400";
			exit;
		}

		$checkForAlreadyAttending = $this->getSingleFriendsAttendingStatus($bookingID);
		if($checkForAlreadyAttending)
		{
			echo "501";
			exit;
		}

		$tblFriendsAttending = JTable::getInstance('FriendsAttending', 'BeseatedTable');

		$postData                           = array();
		$postData['venue_table_booking_id'] = $bookingID;
		$postData['user_id']                = $user->id;
		$postData['booking_user_id']        = $tblVenuebooking->user_id;
		$postData['venue_id']               = $venueID;
		$postData['table_id']               = $tblTable->table_id;
		$postData['user_status']            = 1;
		$postData['booking_user_status']    = 1;

		$tblFriendsAttending->load(0);
		$tblFriendsAttending->bind($postData);

		if(!$tblFriendsAttending->store())
		{
			echo "500";
			exit;
		}


		echo "200";
		exit;
	}

	function getSingleFriendsAttendingStatus($bookingID)
	{
		$user = JFactory::getUser();

		$this->db    = JFactory::getDbo();
		//$query = $db->getQuery(true);

		$query = $this->db->getQuery(true);
		$query->select('venue_table_booking_id,user_status,booking_user_status')
			->from($this->db->quoteName('#__beseated_venue_friends_attending'))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user->id))
			->where($this->db->quoteName('venue_table_booking_id') . ' = ' . $this->db->quote($bookingID));
		$this->db->setQuery($query);
		$friendAttending = $this->db->loadObject();

		return $friendAttending;
	}

}
