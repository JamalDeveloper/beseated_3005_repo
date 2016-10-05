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
jimport( 'joomla.application.component.controller' );

class AdminfbconnctController extends JControllerLegacy
{
	protected $default_view = 'fbconnct';
	
	function display($cachable = false, $urlparams = false) {
	
			require_once JPATH_COMPONENT.'/helpers/fbconnct.php';
			fbconnctHelper::addSubmenu(JRequest::getCmd('view', 'fbconnct'));
			
			$view   = $this->input->get('view', 'fbconnct');
			$layout = $this->input->get('layout', 'default');
			$id     = $this->input->getInt('id');

			parent::display();
			return $this;
	}

}
?>
