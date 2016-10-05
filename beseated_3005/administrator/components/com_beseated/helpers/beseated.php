<?php
	/**
	 * @package     Joomla.Administrator
	 * @subpackage  com_content
	 *
	 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE.txt
	 */

defined('_JEXEC') or die;

	/**
	 * Category component helper.
	 *
	 * @package     Joomla.Administrator
	 * @subpackage  com_content
	 * @since       1.6
	 */
class BeseatedHelper extends JHelperContent
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		$html = '<html><body>';

		JHtmlSidebar::addEntry('<b>Beseated Guests</b>');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Guests'), 'index.php?option=com_beseated&view=guests', $vName == 'guests');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Upcoming Birthdays'), 'index.php?option=com_beseated&view=birthdays', $vName == 'birthdays');

		JHtmlSidebar::addEntry('<b>Chauffeurs</b>');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Chauffeurs'), 'index.php?option=com_beseated&view=chauffeurs', $vName == 'chauffeurs');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Bookings'), 'index.php?option=com_beseated&view=chauffeurbookings', $vName == 'chauffeurbookings');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Requests'), 'index.php?option=com_beseated&view=chauffeurbookingrequests', $vName == 'chauffeurbookingrequests');

		JHtmlSidebar::addEntry('<b>Protections</b>');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Protections'), 'index.php?option=com_beseated&view=protections', $vName == 'protections');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Bookings'), 'index.php?option=com_beseated&view=protectionbookings', $vName == 'protectionbookings');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Requests'), 'index.php?option=com_beseated&view=protectionbookingrequests', $vName == 'protectionbookingrequests');

		JHtmlSidebar::addEntry('<b>Yachts</b>');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Yachts'), 'index.php?option=com_beseated&view=yachts', $vName == 'yachts');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Bookings'), 'index.php?option=com_beseated&view=yachtbookings', $vName == 'yachtbookings');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Requests'), 'index.php?option=com_beseated&view=yachtbookingrequests', $vName == 'yachtbookingrequests');

 		JHtmlSidebar::addEntry('<b>Venues</b>');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Venues'), 'index.php?option=com_beseated&view=venues', $vName == 'venues');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Venue Table Types'), 'index.php?option=com_beseated&view=premiumtables', $vName == 'premiumtables');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Venue Music Types'), 'index.php?option=com_beseated&view=musictables', $vName == 'musictables');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Venue Bottle Types'), 'index.php?option=com_beseated&view=bottletables', $vName == 'bottletables');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Bookings'), 'index.php?option=com_beseated&view=venuebookings', $vName == 'venuebookings');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Requests'), 'index.php?option=com_beseated&view=venuebookingrequests', $vName == 'venuebookingrequests');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;GuestList Requests'), 'index.php?option=com_beseated&view=guestlistRequests', $vName == 'guestlistRequests');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;GuestList Bookings'), 'index.php?option=com_beseated&view=guestlistBookings', $vName == 'guestlistBookings');


		JHtmlSidebar::addEntry('<b>Private Jets</b>');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Private Jets'), 'index.php?option=com_beseated&view=privatejets', $vName == 'privatejets');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Bookings'), 'index.php?option=com_beseated&view=privatejetbookings', $vName == 'privatejetbookings');

		JHtmlSidebar::addEntry('<b>Events</b>');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Events'), 'index.php?option=com_beseated&view=events', $vName == 'events');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Bookings'), 'index.php?option=com_beseated&view=ticketbookings', $vName == 'ticketbookings');

		JHtmlSidebar::addEntry('<b>Rewards</b>');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Rewards'), 'index.php?option=com_beseated&view=rewards', $vName == 'rewards');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Bookings'), 'index.php?option=com_beseated&view=rewardbookings', $vName == 'rewardbookings');

		JHtmlSidebar::addEntry('<b>Messages</b>');
        JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Messages'), 'index.php?option=com_beseated&view=systemmessages', $vName == 'systemmessages');
        JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Promotion Message'), 'index.php?option=com_beseated&view=promotionmessage', $vName == 'promotionmessage');

		//JHtmlSidebar::addEntry('<b>Booking Requests</b>');
		//JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Booking Requests'), 'index.php?option=com_beseated&view=bookingrequests', $vName == 'bookingrequests');

		JHtmlSidebar::addEntry('<b>Concierges</b>');
		JHtmlSidebar::addEntry(JText::_('&nbsp;&nbsp;--&nbsp;Concierges'), 'index.php?option=com_beseated&view=concierges', $vName == 'concierges');



		$html .= '</body></html>';
	}

	function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
	{

		$sort_col = array();

		foreach ($arr as $key=> $row)
		{
			$sort_col[$key] = ucfirst($row->$col);
		}

		//echo "<pre/>";print_r($dir);
		array_multisort($sort_col, $dir, $arr);
	}
}
