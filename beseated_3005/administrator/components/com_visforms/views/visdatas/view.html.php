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
 * Dataview to show data of a single form
 *
 * @package		Joomla.Administrator
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsViewVisdatas extends JViewLegacy
{
	protected $items;
	protected $state;
    protected $canDo;
	
	
	/**
	 * Visdata view display method
	 *
	 * @return void
	 **/
	public function display($tpl = null)
	{
        if ($this->getLayout() !== 'modal')
		{
			VisformsHelper::addSubmenu('visforms');
            $this->sidebar = JHtmlSidebar::render();
        }
        
        $fid = JFactory::getApplication()->input->getInt('fid', 0);
        $this->canDo = VisformsHelper::getActions($fid);
          
        // We don't need toolbar and title in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addTitle();
            $this->addToolbar();
        }

        // Get data from the model
        $this->items = $this->get('Items');
        $this->state = $this->get('State');		
        $this->fields = $this->get('Datafields');
        $this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
        $this->pagination = $this->get('Pagination');
        

        parent::display($tpl);
	}
	
	/**
	 * Add the page toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{	
            if ($this->canDo->get('core.edit.state')) 
            {
                JToolbarHelper::publishList('visdatas.publish');
                JToolbarHelper::unpublishList('visdatas.unpublish');
                JToolbarHelper::checkin('visdatas.checkin');
            }
            if ($this->canDo->get('core.export.data')) 
            {
                JToolbarHelper::custom('visdatas.export','export.png','export.png','Export', false) ;
            }
            if ($this->canDo->get('core.delete.data')) 
            {
                JToolbarHelper::deleteList('COM_VISFORMS_DELETE_DATASET_TRUE','visdatas.delete', 'COM_VISFORMS_DELETE');
            }
            if ($this->canDo->get('core.edit.data') || $this->canDo->get('core.edit.own.data')) 
            {
                JToolbarHelper::editList('visdata.edit');
                JToolbarHelper::custom('visdatas.reset','undo','undo','COM_VISFORMS_RESET_DATA', true) ;
            }
            JToolbarHelper::custom('visdatas.forms','forms','forms',JText::_('COM_VISFORMS_SUBMENU_FORMS'), false) ;
            JToolbarHelper::custom('visfields.form','file-2','file-2',JText::_('COM_VISFORMS_BACK_TO_FORM'), false) ;
	}
	
	/**
	 * Add the page title.
	 *
	 * @since	1.6
	 */
	protected function addTitle()
	{
		$doc = JFactory::getDocument();
        $fid = JFactory::getApplication()->input->getInt('fid', 0);
        $fieldsmodel = JModelLegacy::getInstance('Visfields', 'VisformsModel');
        $formtitle = ($fieldsmodel->getFormtitle()) ? JText::_('COM_VISFORMS_OF_FORM') . $fieldsmodel->getFormtitle() : '';
		$css = '.icon-visform {background:url(../administrator/components/com_visforms/images/visforms_logo_32.png) no-repeat;}'.
            ' [class^="icon-visform"] {display: block; float: left; height: 32px; line-height: 32px; width: 32px;}'.
           '  .visformbottom {	text-align: center;	padding-top: 15px;	color: #999;}';
   		$doc->addStyleDeclaration($css);
		$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_visforms/css/visforms_min.css');	
		JToolbarHelper::title(JText::_( 'COM_VISFORMS_VISFORM_DATA' ) . $formtitle, 'visform' );
	}
    
     /**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
            'a.created'    => JText::_('COM_VISFORMS_DATE'),
			'a.published'   => JText::_('COM_VISFORMS_PUBLISHED'),
			'a.ipaddress'    => JText::_('COM_VISFORMS_IP'),
			'a.id'           => JText::_('COM_VISFORMS_ID'),
		);
	}
}
