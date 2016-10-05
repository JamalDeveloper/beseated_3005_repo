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
 * The Beseated Club Information Model
 *
 * @since  0.0.1
 */
class BeseatedModelRewardDetail extends JModelList
{
	public $db = null;
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
		$this->db = JFactory::getDbo();
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array();
		}

		parent::__construct($config);
	}

	public function getRewardDetail()
	{
		$app      = JFactory::getApplication();
		$rewardID = $app->input->getInt('reward_id');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__beseated_rewards'))
			->where($db->quoteName('published') . ' = ' . $db->quote('1'))
			->where($db->quoteName('reward_id') . ' = ' . $db->quote($rewardID))
			->order($db->quoteName('reward_coin') . ' ASC');
		$db->setQuery($query);
	    $resRewards = $db->loadObject();

	   return $resRewards;
	}

	public function bookreward($rewardID, $userID)
	{
		$userDetail = JFactory::getUser();
		$tblReward  = JTable::getInstance('Rewards', 'BeseatedTable');
		$tblReward->load($rewardID);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('SUM(earn_point)')
			->from($db->quoteName('#__beseated_loyalty_point'))
			->where($db->quoteName('is_valid') . ' = ' . $db->quote('1'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);
		$total_earn_point = $db->loadResult();

		if(!$total_earn_point || $tblReward->reward_coin > $total_earn_point)
		{
			return 400;
		}

		// $db = $this->getDbo();
		$queryInsert = $db->getQuery(true);

		// Create the base insert statement.
		$queryInsert->insert($db->quoteName('#__beseated_rewards_bookings'))
			->columns(array($db->quoteName('reward_id'), $db->quoteName('user_id'), $db->quoteName('reward_coin'), $db->quoteName('time_stamp'), $db->quoteName('booking_date')))
			->values($db->quote($rewardID) . ', ' . $db->quote($userID). ', ' . $db->quote($tblReward->reward_coin). ', ' . $db->quote(time()). ', ' . $db->quote(date('Y-m-d')));

		// Set the query and execute the insert.
		$db->setQuery($queryInsert);
		$db->execute();
		$reward_bookingID = $db->insertid();

		$tblLoyaltyPoint = JTable::getInstance('LoyaltyPoint', 'BeseatedTable');
		$tblLoyaltyPoint->load(0);

		$data['user_id']    = $userID;
		$data['earn_point'] = '-'.$tblReward->reward_coin;
		$data['point_app']  = 'purchase.reward';
		$data['cid']        = $rewardID;
		$data['is_valid']   = '1';
		$data['time_stamp'] = time();

		$tblLoyaltyPoint->bind($data);

		if($tblLoyaltyPoint->store())
		{
			require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
			$emailAppHelper = new BeseatedEmailHelper();
			$emailAppHelper->rewardBookingMail($userDetail->name,$userDetail->email);
			return 200;
		}

	}

}
