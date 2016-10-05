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
class BeseatedModelChauffeurshareuserpay extends JModelList
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


	public function getChauffeurBookingDetail()
	{
		$input = JFactory::getApplication()->input;
		$app   = JFactory::getApplication();

		$chauffeurBookingID = $input->getInt('chauffeur_booking_id',0);

		$db    = JFactory::getDbo();

		$query = $db->getQuery(true);
			// Create the base select statement.
			$query->select('yb.chauffeur_booking_id,yb.chauffeur_id,yb.service_id,yb.user_id,yb.booking_date,yb.booking_time,yb.pickup_location,dropoff_location,yb.capacity,yb.total_price,yb.user_status,yb.is_splitted,yb.each_person_pay,yb.splitted_count,yb.remaining_amount,yb.response_date_time,yb.total_split_count,yb.booking_currency_sign')
				->from($db->quoteName('#__beseated_chauffeur_booking') . ' AS yb')
				->where($db->quoteName('yb.chauffeur_booking_id') .'='.$db->quote($chauffeurBookingID));

			$query->select('y.chauffeur_name,y.location,y.city,y.currency_code,y.deposit_per,y.refund_policy')
				->join('INNER','#__beseated_chauffeur AS y ON y.chauffeur_id=yb.chauffeur_id');

			$query->select('ys.service_name,ys.thumb_image,ys.image')
				->join('INNER','#__beseated_chauffeur_services AS ys ON ys.service_id=yb.service_id');

			$query->select('bu.full_name')
				->join('INNER','#__beseated_user_profile AS bu ON bu.user_id=yb.user_id');

			$db->setQuery($query);	
			$chauffeurBookingDetail = $db->loadObject();

			return $chauffeurBookingDetail;
				
	}

	public function getChauffeurSharedUserDetail()
	{
		$input = JFactory::getApplication()->input;
		
		$chauffeurBookingID = $input->getInt('chauffeur_booking_id',0);

		$db = JFactory::getDbo();

		$querySplit = $db->getQuery(true);
		$querySplit->select('split.chauffeur_booking_split_id,split.user_id,split.email,split.splitted_amount,split.split_payment_status')
			->from($db->quoteName('#__beseated_chauffeur_booking_split','split'))
			//->where($db->quoteName('split.is_owner') . ' = ' . $db->quote(0))
			->where($db->quoteName('split.chauffeur_booking_id') . ' = ' . $db->quote($chauffeurBookingID));
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
		
		$chauffeurBookingID = $input->getInt('chauffeur_booking_id',0);

		$db = JFactory::getDbo();

		$querySplit = $db->getQuery(true);
			$querySplit->select('split.chauffeur_booking_split_id,split.user_id,split.email,split.splitted_amount,split.split_payment_status')
				->from($db->quoteName('#__beseated_chauffeur_booking_split','split'))
				->where($db->quoteName('split.chauffeur_booking_id') . ' = ' . $db->quote($chauffeurBookingID));
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
