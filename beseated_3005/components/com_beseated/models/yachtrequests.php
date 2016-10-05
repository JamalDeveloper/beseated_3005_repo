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
 * The Beseated Club Requests Model
 *
 * @since  0.0.1
 */
class BeseatedModelYachtRequests extends JModelList
{
	protected $liveUsers;
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
		$this->liveUsers = BeseatedHelper::getLiveBeseatedGuests();
		parent::__construct($config);
	}

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
	public function getTable($type = 'YachtBooking', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getYachtRsvp()
	{
		$user  = JFactory::getUser();

		$yacht = BeseatedHelper::yachtUserDetail($user->id);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$rsvpStatus = array();
		$rsvpStatus[] = BeseatedHelper::getStatusID('request');
		$rsvpStatus[] = BeseatedHelper::getStatusID('awaiting-payment');
		$rsvpStatus[] = BeseatedHelper::getStatusID('decline');

		// Create the base select statement.
		$query->select('yb.*')
			->from($db->quoteName('#__beseated_yacht_booking') . ' AS yb')
			->where($db->quoteName('yb.yacht_id') . ' = ' . $db->quote($yacht->yacht_id))
			->where($db->quoteName('yb.yacht_status') . ' IN ('.implode(',', $rsvpStatus).')')
			//->where($db->quoteName('yb.yacht_status') . ' IN ('.implode(',', $rsvpStatus).')')
			->where($db->quoteName('yb.deleted_by_yacht') . ' = ' . $db->quote(0))
			->where($db->quoteName('usr.is_deleted') . ' = ' . $db->quote(0))
			->order($db->quoteName('yb.booking_date') . ' ASC,'.$db->quoteName('yb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=yb.user_id');

		/*$query->select('st.status_display AS status_text')
			->join('LEFT','#__beseated_status AS st ON st.status_id=pb.protection_status');*/

		$query->select('y.yacht_name')
			->join('LEFT','#__beseated_yacht AS y ON y.yacht_id=yb.yacht_id');

		$query->select('ys.service_name,ys.thumb_image,ys.image,ys.capacity')
			->join('LEFT','#__beseated_yacht_services AS ys ON ys.service_id=yb.service_id');


		// Set the query and load the result.
		//$db->setQuery($query,$startFrom,$pageLimit);
		$db->setQuery($query);

		$resBookings = $db->loadObjectList();

		$resultRsvpBookings = array();
		
		foreach ($resBookings as $key => $booking)
		{
			if(!BeseatedHelper::isPastDate($booking->booking_date))
			{
				$resultRsvpBookings[] = $booking;
			}

		}

		$resultRsvpBookings = json_decode(json_encode($resultRsvpBookings));

		return $resultRsvpBookings;

		
	}

	
}
