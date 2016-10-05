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
 * The Beseated Club Account History Messages Model
 *
 * @since  0.0.1
 */
class BeseatedModelYachtAccountHistory extends JModelList
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
		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$user = JFactory::getUser();
		$elementType   = BeseatedHelper::getUserType($user->id);
		$elementDetail = BeseatedHelper::getUserElementID($user->id);

		$bookingStatus   = array();
		$bookingStatus[] = BeseatedHelper::getStatusID('booked');
	    //$bookingStatus[] = BeseatedHelper::getStatusID('confirmed');
		$bookingStatus[] = BeseatedHelper::getStatusID('canceled');

		if($elementType != 'Yacht')
		{
			return array();
		}

		if(!$elementDetail->yacht_id)
		{
			return array();
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('yb.*')
			->from($db->quoteName('#__beseated_yacht_booking','yb'))
			->where($db->quoteName('yb.yacht_id') . ' = ' . $db->quote($elementDetail->yacht_id))
			->where($db->quoteName('yb.yacht_status') . ' IN ('.implode(',', $bookingStatus).')')
			->where($db->quoteName('yb.booking_date')  . ' < ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('yb.deleted_by_yacht') . ' = ' . $db->quote(0))
			->where($db->quoteName('up.is_deleted') . ' = ' . $db->quote(0))
			->order($db->quoteName('yb.booking_date') . ' ASC,'.$db->quoteName('yb.booking_time') . ' ASC');

		/*$query->select('ps.payment_id')
			->join('LEFT','#__beseated_payment_status AS ps ON ps.booking_id=vb.venue_table_booking_id AND ps.booking_type="venue"');*/

		$query->select('up.full_name,up.phone,up.avatar')
			->join('LEFT','#__beseated_user_profile AS up ON up.user_id=yb.user_id');

		$query->select('ys.service_name,ys.thumb_image,ys.image')
			->join('LEFT','#__beseated_yacht_services AS ys ON ys.service_id=yb.service_id');

		return $query;
	}

	public function addUserToBlackList($userID,$yachtID,$elementType = 'Yacht')
	{
		$isBlackListed = BeseatedHelper::checkBlackList($userID,$yachtID,$elementType);
		if($isBlackListed)
		{
			return "706";
		}

		$isAdded = BeseatedHelper::addUserToBlackList($userID,$yachtID,$elementType);
		if($isAdded)
		{
			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base delete statement.
			$query->delete()
				->from($db->quoteName('#__beseated_favourite'))
				->where($db->quoteName('user_id') . ' = ' . $db->quote((int) $userID))
				->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
				->where($db->quoteName('element_id') . ' = ' . $db->quote((int) $yachtID));

			// Set the query and execute the delete.
			$db->setQuery($query);

			$db->execute();

			return "200";
		}

		return "500";
	}

	public function removeUserFromBlackList($userID,$yachtID,$elementType = 'Yacht')
	{
		BeseatedHelper::removeUserFromBlackList($userID,$yachtID,$elementType);

		return "200";
	}
}
