<?php
/**
 * Visdata view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

/**
 * visforms View
 *
 * @package    Joomla.Administratoar
 * @subpackage com_visforms
 * @since      Joomla 1.6
 */
class VisformsViewVisdata extends JViewLegacy
{
	protected $item;
	protected $canDo;
	
	/**
	 * visforms view display method
	 * @return void
	 **/
        
	function display($tpl = null)
	{  
        // Get data from the model
        VisformsHelper::addSubmenu('visforms');
        $this->sidebar = JHtmlSidebar::render();
        $this->item		= $this->get('Item');
        $fid = JFactory::getApplication()->input->getInt('fid', -1);
        $this->canDo = VisformsHelper::getActions($fid);
        $user		= JFactory::getUser();
		$userId		= $user->get('id');
		$this->checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
        $this->fields = $this->get('Datafields');
        $this->form = $this->get('Form');
        $this->addTitle();
        $this->addToolbar();         
        parent::display($tpl);
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
        JFactory::getApplication()->input->set('hidemainmenu', true);
        // Can't save the record if it's checked out.
        if (!$this->checkedOut) {
            if ($this->canDo->get('core.edit.data') || $this->canDo->get('core.edit.own.data')) 
            {
                JToolbarHelper::apply('visdata.apply');
                JToolbarHelper::save('visdata.save');
                if ($this->item->ismfd)
                {
                    JToolbarHelper::custom('visdata.reset','undo','undo','COM_VISFORMS_RESET_DATA', false) ;
                }
            }
        }
        JToolbarHelper::cancel('visdata.cancel');
	}
        
        /**
	 * Add the page title.
	 *
	 * @since	1.6
	 */
	protected function addTitle()
	{
		$doc = JFactory::getDocument();
		$css = '.icon-visform {background:url(../administrator/components/com_visforms/images/visforms_logo_32.png) no-repeat;}'.
            ' [class^="icon-visform"] {display: block; float: left; height: 32px; line-height: 32px; width: 32px;}'.
           '  .visformbottom {	text-align: center;	padding-top: 15px;	color: #999;}';
   		$doc->addStyleDeclaration($css);
		$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_visforms/css/visforms_min.css');	
		JToolbarHelper::title(JText::_( 'COM_VISFORMS_VISFORM_DATA' ), 'visform' );
	}
}
