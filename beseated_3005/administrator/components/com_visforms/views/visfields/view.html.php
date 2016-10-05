<?php
/**
 * Visfields view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

defined('_JEXEC') or die( 'Restricted access' );

/**
 * visfields View
 *
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 *
 * @since        Joomla 1.6 
 */
class VisformsViewVisfields extends JViewLegacy
{
	protected $form;
	protected $items;
	protected $state;
	protected $canDo;
	
	/**
	 * visfields view display method
	 * @return void
	 * @since Joomla 1.6
	 **/
	public function display($tpl = null)
	{
		$fid = JFactory::getApplication()->input->getInt('fid', -1);
		// Get data from the model
		$this->form	= $this->get('Form');
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$pagination = $this->get('Pagination');
		$this->canDo = VisformsHelper::getActions($fid);
        $this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
        $user=JFactory::getUser();
        $formtitle = ($this->get('Formtitle')) ? JText::_('COM_VISFORMS_OF_FORM') . $this->get('Formtitle') : '';
		
		$doc = JFactory::getDocument();
		JToolbarHelper::title(JText::_( 'COM_VISFORMS_VISFORM_FIELDS' ) . $formtitle, 'visform' );
        	$css = '.icon-visform {background:url(../administrator/components/com_visforms/images/visforms_logo_32.png) no-repeat;}'.
            ' [class^="icon-visform"] {display: block; float: left; height: 32px; line-height: 32px; width: 32px;}'.
           '  .visformbottom {	text-align: center;	padding-top: 15px;	color: #999;}';
   		$doc->addStyleDeclaration($css);
        $doc->addStyleSheet(JURI::root(true).'/administrator/components/com_visforms/css/visforms_min.css');	

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
        
        VisformsHelper::addSubmenu('visforms');
        $this->sidebar = JHtmlSidebar::render();
        
        if ($this->canDo->get('core.create')) 
        {
			JToolbarHelper::addNew('visfield.add');
		}
        
		if ($this->canDo->get('core.edit.state'))  
        {
			JToolbarHelper::publishList('visfields.publish');
			JToolbarHelper::unpublishList('visfields.unpublish');
			JToolbarHelper::checkin('visfields.checkin');
		}
		
		if ($this->canDo->get('core.delete')) 
        {
			JToolbarHelper::deleteList('COM_VISFORMS_DELETE_FIELD_TRUE', 'visfields.delete', 'COM_VISFORMS_DELETE');
		}
		
		if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))  
        {
			JToolbarHelper::editList('visfield.edit');
		}
         // Add a batch button
		if ($user->authorise('core.create', 'com_visforms') && $user->authorise('core.edit', 'com_visforms') && $user->authorise('core.edit.state', 'com_visforms'))
		{
			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}
		JToolbarHelper::custom('visfields.forms','forms','forms',JText::_('COM_VISFORMS_SUBMENU_FORMS'), false) ;
        JToolbarHelper::custom('visfields.form','file-2','file2-',JText::_('COM_VISFORMS_BACK_TO_FORM'), false) ;


		$this->assignRef('pagination', $pagination);

		parent::display($tpl);
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
            'a.ordering'    => JText::_('JGRID_HEADING_ORDERING'),
			'a.published'   => JText::_('COM_VISFORMS_PUBLISHED'),
			'a.label'        => JText::_('COM_VISFORMS_TITLE'),
			'a.id'           => JText::_('COM_VISFORMS_ID')
		);
	}
}
