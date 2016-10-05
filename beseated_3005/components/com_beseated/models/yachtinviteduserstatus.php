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
class BeseatedModelYachtinviteduserstatus extends JModelList
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

	function getInvitationsOnBooking()
	{
		$app       = JFactory::getApplication();
		$bookingID = $app->input->getInt('booking_id',0);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('invt.invitation_id,invt.email,invt.user_action')
			->from($db->quoteName('#__beseated_invitation') . ' AS invt')
			->where($db->quoteName('invt.element_booking_id') . ' = ' . $db->quote($bookingID))
			->where($db->quoteName('invt.element_type') . ' = ' . $db->quote('yacht'))
			->order($db->quoteName('invt.email') . ' ASC');

		$query->select('usr.full_name,usr.avatar,usr.thumb_avatar')
			    ->join('LEFT','#__beseated_user_profile AS usr ON invt.user_id=usr.user_id');
		
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
