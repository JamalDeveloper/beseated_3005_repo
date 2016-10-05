<?php
/**
 * Visform view for Visforms
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
 * visform view to show a singl field
 *
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @since        Joomla 1.6 
 */
class VisformsViewVisform extends JViewLegacy
{
	protected $canDo;
	protected $form;
	protected $item;
	
	/**
	 * Visform view display method
	 *
	 * @return void
	 **/
	public function display($tpl = null)
	{	
        VisformsHelper::addSubmenu('visforms');
        $this->sidebar = JHtmlSidebar::render();
        
		$doc = JFactory::getDocument();
		$css = '.icon-visform {background:url(../administrator/components/com_visforms/images/visforms_logo_32.png) no-repeat;}'.
            ' [class^="icon-visform"] {display: block; float: left; height: 32px; line-height: 32px; width: 32px;}'.
           '  .visformbottom {	text-align: center;	padding-top: 15px;	color: #999;}';
   		$doc->addStyleDeclaration($css);
        $doc->addStyleSheet(JURI::root(true).'/administrator/components/com_visforms/css/visforms_min.css');		               
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');	
        // What Access Permissions does this user have? What can (s)he do? 
		$this->canDo = VisformsHelper::getActions($this->item->id);		
		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
                $isNew		= ($this->item->id == 0);
		$text = $isNew ? JText::_( 'COM_VISFORMS_NEW' ) : JText::_( 'COM_VISFORMS_EDIT' );
                JToolbarHelper::title(JText::_( 'COM_VISFORMS' ).': <small><small>[ ' . $text.' ]</small></small>' , 'visform' );
		
		if ($isNew)  
        {
			if ($this->canDo->get('core.create')) {
				JToolbarHelper::apply('visform.apply');
				JToolbarHelper::save('visform.save');
				JToolbarHelper::save2new('visform.save2new');
			}
			JToolbarHelper::cancel('visform.cancel');
		} 
        else 
        {
			// Can't save the record if it's checked out.
			if (!$checkedOut) {
				if ($this->canDo->get('core.edit') || ($this->canDo->get('core.edit.own') && $this->item->created_by == $userId)) {
					JToolbarHelper::apply('visform.apply');
					JToolbarHelper::save('visform.save');
					if ($this->canDo->get('core.create')) {
						JToolbarHelper::save2new('visform.save2new');
					}
				}
			}

			if ($this->canDo->get('core.create')) {
				JToolbarHelper::save2copy('visform.save2copy');
			}
			
			if (!$checkedOut) {
				if ($this->canDo->get('core.edit')) {
					JToolbarHelper::custom('visform.fields','forms','forms','COM_VISFORMS_FIELDS',false) ;
				}
			}
            if ($this->form->getValue('saveresult') == '1')
            {
                JToolbarHelper::custom('visform.datas','archive','archive','COM_VISFORMS_DATAS',false) ;
            }
			// for existing items the button is renamed `close`
			JToolbarHelper::cancel( 'visform.cancel', 'COM_VISFORMS_CLOSE' );
		}

		
		JFactory::getApplication()->input->set( 'hidemainmenu', 1 );
		$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_visforms/css/visforms.css');
		

		
		parent::display($tpl);
	}
}
