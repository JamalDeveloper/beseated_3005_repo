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
 * The Beseated Jet Service Booking View
 *
 * @since  0.0.1
 */
class BeseatedViewJetServiceBooking extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $companyID;
	protected $serviceID;
	protected $user;
	protected $Itemid;
	protected $companyDetail;
	protected $serviceDetail;
	protected $showServiceList;
	protected $allServices;

	/**
	 * Display the Beseated Jet Service Booking view
	 *
	 * @param   string  $tpl  The name of the template file to parse;
	 * automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   0.0.1
	 */
	function display($tpl = null)
	{
		$input             = JFactory::getApplication()->input;
		$this->Itemid      = $input->get('Itemid', 0, 'int');
		$this->user        = JFactory::getUser();
		$this->airportList = $this->get('AirportList');

		if(!$this->user->id)
        {
			$app         = JFactory::getApplication();
			$menu        = $app->getMenu();
			$menuItem    = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
			$bctParams   = BeseatedHelper::getExtensionParam();
			$accessLevel = BeseatedHelper::getGroupAccessLevel($bctParams->beseated_guest);
			$access      = array('access','link');
			$property    = array($accessLevel,'index.php?option=com_beseated&view=jetservicebooking');
			$menuItem2   = $menu->getItems( $access, $property, true );
			$link2       = 'index.php?option=com_beseated&view=jetservicebooking&Itemid='.$menuItem2->id;
            JFactory::getApplication()->redirect(JRoute::_(JURI::root().'index.php?option=com_users&view=login&Itemid='.$menuItem->id.'&return='.base64_encode($link2)));
        }

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		// Display the template
		parent::display($tpl);
	}

}
