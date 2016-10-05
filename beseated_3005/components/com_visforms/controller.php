<?php
/**
 * Visforms default controller
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
require_once JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'visforms.php';


/**
 * Visforms Controller Class
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsController extends JControllerLegacy
{
	
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController          This object to support chaining.
	 *
	 * @since	1.6
	 */
	public function display($cachable = false, $urlparams = false)
	{
        $vName = $this->input->get('view','visforms');
        $this->input->set('view', $vName);
        if ($vName == 'visforms')
        { 
            $app = JFactory::getApplication();
            $layout = $this->input->get('layout', 'default');
            $task = $this->input->getCmd('task');
            //$tmpl = $input->get('tmpl');
            $model = $this->getModel('visforms');
            if ($layout == 'default' && !(isset($task)))
            {
                $model->addHits();
            }
            $app->setUserState('vis_send_once'.$this->input->getInt('id'), '1');
        }
        if ($vName == 'visformsdata')
        {
            $cid = $this->input->getInt('cid');
            $this->input->set('view', 'visformsdata');
			//only display data list view with edit link if a menu item exists
			if (!($this->checkDataViewMenuItemExists()))
            {
                $layout = $this->input->get('layout', 'data', 'string');
				if ($layout == 'dataeditlist' || $layout == 'detailedit')
				{
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
					return false;
				}
            }
        }
		parent::display($cachable, $urlparams);
	}
    
    public function captcha ()
    {
        //legacy code for old version of vfformview plugin
        $controller = New VisformsControllerVisforms();
        $controller->execute('captcha');
        $controller->redirect();

    }
    
    public function send ()
    {
        //legacy code for old version of vfformview plugin
        $controller = New VisformsControllerVisforms();
        $controller->execute('send');
        $controller->redirect();
    }
    
    protected function checkDataViewMenuItemExists()
    {
        //don't allow access to data view if there is not visforms dataview menu item
        $id = $this->input->get('id', 0, 'int');
        $app = JFactory::getApplication();
        $menuitems = $app->getMenu()->getItems('link', 'index.php?option=com_visforms&view=visformsdata&layout=dataeditlist&id='.$id);
        if (!(empty($menuitems)))
        {
            return true;
        }
        return false;
    }
}
?>
