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
 * The Beseated Profile Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerChauffeurshareamount extends JControllerAdmin
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

	public function shareGuestCountUpdate()
	{
		$input                = JFactory::getApplication()->input;
		$app                  = JFactory::getApplication();
		$menu                 = $app->getMenu();
		$menuItem             = $menu->getItems( 'link', 'index.php?option=com_beseated&view=guestrequests', true );
		$Itemid               = $menuItem->id;
		$link                 = $menuItem->link.'&Itemid='.$Itemid;

		$sharedPeopleCount    = $input->getInt('sharedPeopleCount',0);
		$eachPersonPayAmount  = $input->getInt('eachPersonPayAmount',0);
		$bookingID            = $input->getInt('booking_id',0);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblChauffeurBooking = JTable::getInstance('ChauffeurBooking', 'BeseatedTable');
		$tblChauffeurBooking->load($bookingID);

		$tblChauffeurBooking->total_split_count = $sharedPeopleCount;
		$tblChauffeurBooking->each_person_pay   = $eachPersonPayAmount;

		$tblChauffeurBooking->store();

		if(!$tblChauffeurBooking->store())
		{
			$msg = JText::_('COM_BESEATED_ERROR_WHILE_SHARE_USER');
			$app->redirect($link,$msg);
		}
		else
		{
			$link = "index.php?option=com_beseated&view=chauffeurrequestpay&firstTimeShare=1&chauffeur_booking_id=".$bookingID."&Itemid=".$Itemid;
			$app->redirect($link);
		}


	}
	

	
}
