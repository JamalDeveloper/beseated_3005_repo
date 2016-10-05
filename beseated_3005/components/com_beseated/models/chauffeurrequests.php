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
class BeseatedModelChauffeurRequests extends JModelList
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
	public function getTable($type = 'ChauffeurBooking', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getChauffeurRsvp()
	{
		$user      = JFactory::getUser();
		$chauffeur = BeseatedHelper::chauffeurUserDetail($user->id);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$rsvpStatus = array();
		$rsvpStatus[] = BeseatedHelper::getStatusID('request');
		$rsvpStatus[] = BeseatedHelper::getStatusID('awaiting-payment');
		$rsvpStatus[] = BeseatedHelper::getStatusID('decline');

		// Create the base select statement.
		$query->select('cb.*')
			->from($db->quoteName('#__beseated_chauffeur_booking') . ' AS cb')
			->where($db->quoteName('cb.chauffeur_id') . ' = ' . $db->quote($chauffeur->chauffeur_id))
			->where($db->quoteName('cb.chauffeur_status') . ' IN ('.implode(',', $rsvpStatus).')')
			->where($db->quoteName('cb.deleted_by_chauffeur') . ' = ' . $db->quote(0))
			->order($db->quoteName('cb.booking_date') . ' ASC,'.$db->quoteName('cb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar,usr.fb_id')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=cb.user_id');

		/*$query->select('st.status_display AS status_text')
			->join('LEFT','#__beseated_status AS st ON st.status_id=pb.protection_status');*/

		$query->select('c.chauffeur_name')
			->join('LEFT','#__beseated_chauffeur AS c ON c.chauffeur_id=cb.chauffeur_id');

		$query->select('cs.service_name,cs.thumb_image,cs.image')
			->join('LEFT','#__beseated_chauffeur_services AS cs ON cs.service_id=cb.service_id');


		$db->setQuery($query);

		$resBookings = $db->loadObjectList();

		return $resBookings;
	}
}
