<?php
/**
 * Visforms view for Visforms
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
class VisformsViewVisforms extends JViewLegacy
{
	protected $items;
	protected $state;
	protected $canDo;
	
	/**
	 * visforms view display method
	 * @return void
	 **/
	public function display($tpl = null)
	{
        if ($this->getLayout() !== 'modal')
		{
			VisformsHelper::addSubmenu('visforms');
            $this->sidebar = JHtmlSidebar::render();
		}
		$app = JFactory::getApplication();
		$this->update_message = $app->getUserState('com_visforms.update_message');
		//only show update message once
		if (isset($this->update_message))
		{
			$app->setUserState('com_visforms.update_message', null);
		}
		
		// Get data from the model
		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->canDo = VisformsHelper::getActions();
        $this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$pagination = $this->get('Pagination');

		$this->assignRef('pagination', $pagination);
		
		// We don't need toolbar in the modal window.
		if (($this->getLayout() !== 'modal') && ($this->getLayout() !== 'modal_data')) {
			$this->addToolbar();
		}
		
		parent::display($tpl);
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$doc = JFactory::getDocument();
		$css = '.icon-visform {background:url(../administrator/components/com_visforms/images/visforms_logo_32.png) no-repeat;}'.
            ' [class^="icon-visform"] {display: block; float: left; height: 32px; line-height: 32px; width: 32px;}'.
           '  .visformbottom {	text-align: center;	padding-top: 15px;	color: #999;}';
   		$doc->addStyleDeclaration($css);
        $doc->addStyleSheet(JURI::root(true).'/administrator/components/com_visforms/css/visforms_min.css');	
        $user  = JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		

		JToolbarHelper::title(JText::_( 'COM_VISFORMS' ), 'visform' );
        if ($this->canDo->get('core.create')) 
        {
            JToolbarHelper::addNew('visform.add');
		}
		if ($this->canDo->get('core.edit.state'))  
        {
			JToolbarHelper::publishList('visforms.publish');
			JToolbarHelper::unpublishList('visforms.unpublish');
			JToolbarHelper::checkin('visforms.checkin');
		}
		if ($this->canDo->get('core.delete')) 
        {
			JToolbarHelper::deleteList('COM_VISFORMS_DELETE_FORM_TRUE', 'visforms.delete', 'COM_VISFORMS_DELETE');
		}
		if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))  
        {
            JToolbarHelper::editList('visform.edit');
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
        if ($this->canDo->get('core.edit.css'))  
        {
			JToolbarHelper::custom( 'visforms.edit_css', 'editcss', 'editcss', JText::_('COM_VISFORMS_EDIT_CSS'), false, false );
		}
		 // Options button.
		if (JFactory::getUser()->authorise('core.admin', 'com_visforms'))     
		{
			JToolbarHelper::preferences('com_visforms');
		}
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
			'a.published'        => JText::_('COM_VISFORMS_PUBLISHED'),
			'a.title'        => JText::_('COM_VISFORMS_TITLE'),
			'access_level' => JText::_('JGRID_HEADING_ACCESS'),
			'nbfields'   => JText::_('COM_VISFORMS_FIELDS'),
			'username'   => JText::_('COM_VISFORMS_AUTHOR'),
			'language'       => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.created'      => JText::_('COM_VISFORMS_DATE'),
			'a.id'           => JText::_('COM_VISFORMS_ID'),
			'a.hits'     => JText::_('COM_VISFORMS_HITS')
		);
	}
}
