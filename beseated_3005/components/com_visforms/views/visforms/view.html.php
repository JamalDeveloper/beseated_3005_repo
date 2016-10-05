<?php
/**
 * Visforms view for Visforms
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
 * Visforms View class
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsViewVisforms extends JViewLegacy
{
    protected $menu_params;
	protected $visforms;
	protected $formLink;
    protected $state;
    protected $return;

    
	function display($tpl = null)
	{
        $app = JFactory::getApplication();
		$this->menu_params = $this->get('menuparams');
        $this->visforms = $this->get('Form');
        $this->return = JHTMLVisforms::base64_url_encode(JUri::getInstance()->toString());
        if (empty($this->visforms))
        {
            JError::raiseWarning(403, JText::_('COM_VISFORMS_FORM_MISSING'));
			return;
        }
        
        
            //check if user access level allows view
            $user = JFactory::getUser();
            $groups = $user->getAuthorisedViewLevels();
            $access = (isset($this->visforms->access) && in_array($this->visforms->access, $groups)) ? true : false;
            if ($access == false)
            {
                $app->setUserState('com_visforms.form' . $this->visforms->id . '.fields', null);
                $app->setUserState('com_visforms.form' . $this->visforms->id , null);
                JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
                return;
            }

            $fields = $this->get('Fields');
            $app->setUserState('com_visforms.form' . $this->visforms->id , null);
            $this->visforms->fields = $fields;

            //Trigger onFormPrepare event 
            JPluginHelper::importPlugin('visforms');
            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger('onVisformsFormPrepare', array ('com_visforms.form', &$this->visforms, &$this->menu_params));	

            $this->formLink = "index.php?option=com_visforms&task=visforms.send&id=".$this->visforms->id;
            $options = array();
            $options['showRequiredAsterix'] = (isset($this->visforms->requiredasterix)) ? $this->visforms->requiredasterix : 1;
            $options['parentFormId'] = 'visform' . $this->visforms->id;

            //process form layout
            $olayout = VisformsLayout::getInstance($this->visforms->formlayout, $options);
            if(is_object($olayout))
            {
                //add layout specific css
                $olayout->addCss();
            }	
        
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
        if ($app->get('sitename_pagetitles', 0) == 1)
        {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        }
        elseif ($app->get('sitename_pagetitles', 0) == 2)
        {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
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
