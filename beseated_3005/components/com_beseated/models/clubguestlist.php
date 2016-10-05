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
 * The Beseated Club Guest List Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubGuestList extends JModelList
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
	public function getTable($type = 'Venuebooking', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function sendGuestListRequest($data = array())
	{
		$tblVenueguestlist = $this->getTable('GuestBooking','BeseatedTable');

		$tblVenueguestlist->load(0);
		$tblVenueguestlist->bind($data);

		if(!$tblVenueguestlist->store())
		{
			return 0;
		}

		

		require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/beseated/helper.php';
		$appHelper                       = new beseatedAppHelper;

		$tblVenue                        = $this->getTable('Venue','BeseatedTable');
		$tblVenue->load($tblVenueguestlist->venue_id);
		$loginUser                       = JFactory::getUser();
		$extraParam                      = array();

		$extraParam['guestListRequstID'] = $tblVenueguestlist->guest_booking_id;
		$extraParam['requestedDate']     = date('d-m-Y',strtotime($tblVenueguestlist->booking_date));
		$extraParam['maleCount']         = $tblVenueguestlist->male_guest;
		$extraParam['femaleCount']       = $tblVenueguestlist->female_guest;
		//$extraParam['additionalInfo']    = $tblVenueguestlist->additional_info;



		$guestsCount    = $tblVenueguestlist->male_guest + $tblVenueguestlist->female_guest;
		$maleCount      = $tblVenueguestlist->male_guest;
		$femaleCount    = $tblVenueguestlist->female_guest;
		$maleFemaleText = $guestsCount . '('.$male_guest.'M / '.$female_guest.'F)';
		$message        = JText::sprintf('COM_BESEATED_VENUE_GUESTLIST_REQUEST_MESSAGE',$loginUser->name,$maleFemaleText,date('d-m-Y',strtotime($tblVenueguestlist->booking_date)));
		BeseatedHelper::sendMessage($tblVenue->venue_id,0,0,0,$tblVenue->user_id,$message,$extraParam,$messageType = 'venueguestlistrequest');
		
		return 1;
	}
}
