<?php
/**
 * Vistools view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

defined('_JEXEC') or die;

/**
 * Vistools view
 *
 * @package    Visoforms
 * @subpackage Components
 */
class VisformsViewVistools extends JViewLegacy
{
	/**
	 * For loading extension state
	 */
	protected $state;

	/**
	 * For loading extension details
	 */
	protected $extension;

	/**
	 * For loading the source form
	 */
	protected $form;

	/**
	 * For loading source file contents
	 */
	protected $source;

	/**
	 * Encrypted file path
	 */
	protected $file;

	/**
	 * Name of the present file
	 */
	protected $fileName;

	/**
	 * Type of the file - image, source, font
	 */
	protected $type;

	
	/**
	 * A nested array containing lst of files and folders
	 */
	protected $files;

	/**
	 * Execute and display a edit css viwe.
	 *
	 * @param   string  $tpl  The name of the extension ; automatically searches through the css folder.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$app            = JFactory::getApplication();
		$doc = JFactory::getDocument();
		
        $css = '.icon-visform {background:url(../administrator/components/com_visforms/images/visforms_logo_32.png) no-repeat;}'.
            ' [class^="icon-visform"] {display: block; float: left; height: 32px; line-height: 32px; width: 32px;}'.
           '  .visformbottom {	text-align: center;	padding-top: 15px;	color: #999;}';
   		$doc->addStyleDeclaration($css);
        $doc->addStyleSheet(JURI::root(true).'/administrator/components/com_visforms/css/visforms_min.css');
        
        // Add a Apply and save button
		
		$this->file     = $app->input->get('file');
		$this->fileName = base64_decode($this->file);
		$explodeArray   = explode('.', $this->fileName);
		$ext            = end($explodeArray);
		$this->files    = $this->get('Files');
		$this->state    = $this->get('State');
        $this->canDo = VisformsHelper::getActions();
        if (!($this->canDo->get('core.edit.css')))
        {
            JFactory::getApplication()->redirect('index.php?option=com_visforms&view=visforms', JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
        }

		
		$sourceTypes  = array('css');
		

		if (in_array($ext, $sourceTypes))
		{
			$this->form   = $this->get('Form');
			$this->form->setFieldAttribute('source', 'syntax', $ext);
			$this->source = $this->get('Source');
			$this->type   = 'file';
		}
		else
		{
			$this->type = 'home';
		}

		//$this->id            = $this->state->get('extension.id');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage(implode("\n", $errors));

			return false;
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		$app   = JFactory::getApplication();
		$app->input->set('hidemainmenu', true);

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		$explodeArray = explode('.', $this->fileName);
		$ext = end($explodeArray);

		JToolbarHelper::title(JText::_('COM_VISFORMS_EDIT_CSS_BUTTON_TEXT'), 'visform');
        // Add a new file button
        JToolbarHelper::modal('fileModal', 'icon-file', 'COM_VISFORMS_BUTTON_FILE');
         if (!($this->type == 'home'))
		{
            //Add a rename file button
            JToolbarHelper::modal('renameModal', 'icon-refresh', 'COM_VISFORMS_BUTTON_RENAME_FILE');
            // Add a delete file button
            JToolbarHelper::modal('deleteModal', 'icon-remove', 'COM_VISFORMS_BUTTON_DELETE_FILE');
        }
        
		// Add a Apply and save button
		if ($this->type == 'file')
		{
				JToolbarHelper::apply('vistools.apply');
                JToolbarHelper::save('vistools.save');
		}
        
       

		if ($this->type == 'home')
		{
			JToolbarHelper::cancel('vistools.cancel', 'JTOOLBAR_CLOSE');
		}
		else
		{
			JToolbarHelper::cancel('vistools.close', 'COM_VISFORMS_BUTTON_CLOSE_FILE');
		}

	}

	/**
	 * Method for creating the collapsible tree.
	 *
	 * @param   array  $array  The value of the present node for recursion
	 *
	 * @return  string
	 *
	 * @note    Uses recursion
	 * @since   3.2
	 */
	protected function directoryTree($array)
	{
		$temp        = $this->files;
		$this->files = $array;
		$txt         = $this->loadExtension('tree');
		$this->files = $temp;

		return $txt;
	}
}
