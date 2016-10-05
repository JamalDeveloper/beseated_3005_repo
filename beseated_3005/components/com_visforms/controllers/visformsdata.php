<?php
/**
 * Visdata controller for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
require_once JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controller.php';
require_once JPATH_COMPONENT_ADMINISTRATOR.'/models/visdata.php';

/**
 * Visformsdata Controller
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */ 
class VisformsControllerVisformsdata extends VisformsController
{	
    public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('unpublish', 'publish');
    }
	
	/**
	 * Method to display the detail view
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController          This object to support chaining.
	 *
	 * @since	1.6
	 */
    
    //unused??
	function detail($cachable = false, $urlparams = false)
	{
        if (!($this->checkDataViewMenuItemExists()))
        {
            $app = JFactory::getApplication();
            $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
            return false;
        }
        $cid = $this->input->getInt('cid');
        if (!(isset($cid)))
        {
            $this->input->set('layout', 'data');
        }
        else
        {
            $this->input->set('layout', 'detail');
        }       
        $this->input->set('view', 'visformsdata');
        
		
		parent::display($cachable, $urlparams);
	}
    
    public function publish()
    {
        // Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $fid = $this->input->get('id', 0, 'int');
        //VisformsTableVisdata expects the parameter fid
        $this->input->set('fid', $fid);
        $pk = $this->input->get('cid', null, 'array');
        //this function can be called from different views, return to return url, if a return param is set in input
        $return = $this->input->get('return');
        $success = false;
        // Make sure the item ids are integers
        JArrayHelper::toInteger($pk);
        $data = array('publish' => 1, 'unpublish' => 0);
        $task = $this->getTask();
        $value = JArrayHelper::getValue($data, $task, 0, 'int');
        //check for permission
        $user = JFactory::getUser();
        $userId	= $user->get('id');
        $canDo = VisformsHelper::getActions($fid);
        if ($canDo->get('core.edit.data.state'))
        {
            if (!empty($pk))
            {
                $model = $this->getModel('Visdata', 'Visformsmodel');
                try
                {
                    $result = $model->publish($pk, $value);
                    if ($value == 1)
                    {
                        $this->setMessage(JText::_('COM_VISFORMS_RECORDSET_PUBLISHED'));
                    }
                    elseif ($value == 0)
                    {
                        $this->setMessage(JText::_('COM_VISFORMS_RECORDSET_UNPUBLISHED'));
                    }
                    $success = true;
                }
                catch (Exception $e)
                {
                    $this->setMessage($e->getMessage(), 'error');
                }
            }
            else
            {
                $success = false;
            }           
        }
        else
        {
            $this->setMessage(JText::_('COM_VISFORMS_NO_PUBLISH_AUTHOR'), 'error');
            $success = false;
        }        
        if (isset($return))
        {
            $this->setRedirect(JRoute::_(base64_decode(strtr($return, '-_,', '+/='))));
        }
        else
        {
            $this->setRedirect(JRoute::_(JURI::base()));
        }   
        return $success;
    }
}
?>
