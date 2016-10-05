<?php
/**
* @package 		Facebook Connect Extension (joomla 3.x)
* @copyright	Copyright (C) Computer - http://www.sanwebe.com. All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* @author		Saran Chamling
* @download URL	http://www.sanwebe.com
*/ 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * HTML View class for the HelloWorld Component
 *
 * @package    HelloWorld
 */
 
class AdminFbconnctViewusers extends JViewLegacy
{
    function display($tpl = null)
    { 
		JToolBarHelper::preferences( 'com_fbconnct',400,570 );
		JToolBarHelper::title( JText::_( 'Facebook Connected Users' ),'users_c' );

		$lists['search']=  JRequest::getVar( "search");
		// Get data from the model
		$items = $this->get('Data');	
		$pagination = $this->get('Pagination');
		$totalcusers = $this->get('Total');
		$this->state = $this->get('State');
 		// push data into the template
		$this->assignRef('items', $items);	
		$this->assignRef('pagination', $pagination);
		$this->assignRef('delete', $pagination);
		$this->assignRef('lists',	$lists);
		$this->assignRef('totalcusers', $totalcusers);
		parent::display($tpl);
    }
}
