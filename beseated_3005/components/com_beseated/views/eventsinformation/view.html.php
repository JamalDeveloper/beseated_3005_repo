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
 * The Beseated Club Information View
 *
 * @since  0.0.1
 */
class BeseatedViewEventsInformation extends JViewLegacy
{
	protected $club;
	protected $isFavourite;
	protected $user;

	/**
	 * Display the Beseated Club Information view
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

		// Get data from the model
		$app          = JFactory::getApplication();
		$eventID      = $app->input->getInt('event_id');
		$this->event  = $this->get('EventDetail');
		$this->images = $this->get('EventsImages');
		$this->ticketTypeDetail = $this->get('TicketTypeDetail');
		$this->user   = JFactory::getUser();

		if(!$this->user->id)
        {
			$app         = JFactory::getApplication();
			$menu        = $app->getMenu();
			$menuItem    = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
			$bctParams   = BeseatedHelper::getExtensionParam();
			$accessLevel = BeseatedHelper::getGroupAccessLevel($bctParams->beseated_guest);
			$access      = array('access','link');
			$property    = array($accessLevel,'index.php?option=com_beseated&view=eventsinformation');
			$menuItem2   = $menu->getItems( $access, $property, true );
			$link2       = 'index.php?option=com_beseated&view=eventsinformation&event_id='.$eventID.'&Itemid='.$menuItem2->id;
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
