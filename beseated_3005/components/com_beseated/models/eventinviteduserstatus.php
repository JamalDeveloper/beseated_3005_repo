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
 * The Beseated Messages Model
 *
 * @since  0.0.1
 */
class BeseatedModelEventinviteduserstatus extends JModelList
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

	function getInvitationsOnBooking($bookingID,$bookingType)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$statusID = BeseatedHelper::getStatusID('decline');

		$query->select('invt.email,invt.invited_user_status as user_action,invt.invite_id as invitation_id')
					->from($db->quoteName('#__beseated_event_ticket_booking_invite').' AS invt')
					->where($db->quoteName('invt.invited_user_status') . ' != ' . $db->quote($statusID))
					->where($db->quoteName('ticket_booking_id') . ' = ' . $db->quote($bookingID));

			$query->select('usr.full_name,usr.avatar,usr.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS usr ON invt.invited_user_id=usr.user_id');
		

		// Set the query and load the result.
		$db->setQuery($query);
		$resInvitations = $db->loadObjectList();

		$resultInvitations = array();

		foreach ($resInvitations as $key => $invitation)
		{
			$temp                 = array();
			$temp['invitationID'] = $invitation->invitation_id;
			$temp['email']        = $invitation->email;
			$temp['statusCode']   = $invitation->user_action;
			$temp['fullName']     = ($invitation->full_name)?$invitation->full_name:$invitation->email;
			$temp['avatar']       = BeseatedHelper::getUserAvatar($invitation->avatar);
			$temp['thumbAvatar']  = BeseatedHelper::getUserAvatar($invitation->thumb_avatar);

			$resultInvitations[] = $temp;
		}

		return $resultInvitations;
	}
}
