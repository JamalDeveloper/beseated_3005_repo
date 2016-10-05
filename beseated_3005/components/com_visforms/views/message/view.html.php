<?php
/**
 * Message view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */
 
// no direct access
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');
jimport( 'joomla.html.parameter');

/**
 * Visforms Message View class
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsViewMessage extends JViewLegacy
{
    protected $menu_params;
   
	function display($tpl = null)
	{
        $app = JFactory::getApplication();
		$fid = $app->input->get('id', 0, 'int');
		if (empty($fid))
        {
            JError::raiseWarning(403, JText::_('COM_VISFORMS_FORM_MISSING'));
			return;
        }
		$this->menu_params = $app->getUserState('com_visforms.form' . $fid . '.menu_params');
		$this->message = $app->getUserState('com_visforms.form' . $fid . '.message');
		$app->setUserState('com_visforms.form' . $fid , null);
        
        $this->prepareDocument();
		parent::display($tpl);
		
	}
    
    private function prepareDocument()
    {
        $app = JFactory::getApplication();
        $title = '';
        if (isset($this->menu_params) && $this->menu_params->get('page_title'))
        {
            $title = $this->menu_params->get('page_title') ;
        }
        if ($title != '')
        {
            $this->document->setTitle($title);
        }
        // Set metadata Description and Keywords	
        if (isset($this->menu_params) && $this->menu_params->get('menu-meta_description'))
        {
            $this->document->setDescription($this->menu_params->get('menu-meta_description'));
        }
        if (isset($this->menu_params) && $this->menu_params->get('menu-meta_keywords'))
        {
            $this->document->setMetadata('keywords', $this->menu_params->get('menu-meta_keywords'));
        }
    }

}
?>
