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
class BeseatedModelProtectionrequestpay extends JModelList
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
	public function getTable($type = 'MessageConnection', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}


	public function getProtectionBookingDetail()
	{
		$input = JFactory::getApplication()->input;
		$app   = JFactory::getApplication();

		$protectionBookingID = $input->getInt('protection_booking_id',0);

		$db    = JFactory::getDbo();

		$query = $db->getQuery(true);
			// Create the base select statement.
			$query->select('pb.protection_booking_id,pb.protection_id,pb.service_id,pb.user_id,pb.booking_date,pb.booking_time,pb.total_hours,pb.price_per_hours,pb.total_price,pb.user_status,pb.is_splitted,pb.each_person_pay,pb.splitted_count,pb.remaining_amount,pb.response_date_time,pb.total_split_count,pb.total_guard,pb.booking_currency_sign')
				->from($db->quoteName('#__beseated_protection_booking') . ' AS pb')
				->where($db->quoteName('protection_booking_id') .'='.$db->quote($protectionBookingID));

			$query->select('p.protection_name,p.location,p.city,p.currency_code,p.deposit_per,p.refund_policy')
				->join('INNER','#__beseated_protection AS p ON p.protection_id=pb.protection_id');

			$query->select('ps.service_name,ps.thumb_image,ps.image')
				->join('INNER','#__beseated_protection_services AS ps ON ps.service_id=pb.service_id');

			$db->setQuery($query);	
			$protectionBookingDetail = $db->loadObject();

			return $protectionBookingDetail;
				
	}

	public function getProtectionSharedUserDetail()
	{
		$input = JFactory::getApplication()->input;
		
		$protectionBookingID = $input->getInt('protection_booking_id',0);

		$db = JFactory::getDbo();

		$querySplit = $db->getQuery(true);
		$querySplit->select('split.protection_booking_split_id,split.user_id,split.email,split.splitted_amount,split.split_payment_status')
			->from($db->quoteName('#__beseated_protection_booking_split','split'))
			->where($db->quoteName('split.is_owner') . ' = ' . $db->quote(0))
			->where($db->quoteName('split.protection_booking_id') . ' = ' . $db->quote($protectionBookingID));
		$querySplit->select('bu.full_name,bu.avatar,bu.thumb_avatar,bu.is_fb_user')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=split.user_id')
			->order($db->quoteName('bu.full_name') . ' ASC');

		$db->setQuery($querySplit);
		$resSplitUsers = $db->loadObjectList();

		foreach ($resSplitUsers as $key => $user)
		{
			$user->full_name    = ($user->full_name)?$user->full_name:$user->email;
			$user->thumb_avatar = BeseatedHelper::getUserAvatar($user->thumb_avatar);
		}

		return $resSplitUsers;
	}

	public function getPercentagePaidSharedUser()
	{
		$input = JFactory::getApplication()->input;
		
		$protectionBookingID = $input->getInt('protection_booking_id',0);

		$db = JFactory::getDbo();

		$querySplit = $db->getQuery(true);
			$querySplit->select('split.protection_booking_split_id,split.user_id,split.email,split.splitted_amount,split.split_payment_status')
				->from($db->quoteName('#__beseated_protection_booking_split','split'))
				->where($db->quoteName('split.protection_booking_id') . ' = ' . $db->quote($protectionBookingID));
			$querySplit->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=split.user_id')
				->order($db->quoteName('bu.full_name') . ' ASC');

			$db->setQuery($querySplit);
			$resSplitUsers = $db->loadObjectList();

			$shareUserDiv = 0;
			$paymentPer = 0;
			
			if(count($resSplitUsers))
			{
				$shareUserDiv =     100 / count($resSplitUsers);
			}
			
			foreach ($resSplitUsers as $key => $splitUserdetail) 
			{
			  if($splitUserdetail->split_payment_status == 7)
			  {
			      $paymentPer += $shareUserDiv;
			  }
			}

			return number_format($paymentPer);
	}


}
