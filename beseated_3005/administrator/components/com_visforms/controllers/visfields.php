<?php
/**
 * visfields controller for Visforms
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
 * Visdfields controller class for Visforms
 *
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 *
 * @since        Joomla 1.6 
 */
class VisformsControllerVisfields extends JControllerAdmin
{
	/**
	 * Class constructor. Register extra tasks
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$fid = JFactory::getApplication()->input->getInt( 'fid', 0 );
		$this->view_list = 'visfields&fid=' . $fid;
                $this->text_prefix = 'COM_VISFORMS_FIELD';
	}
	
	/**
	 * Proxy for getModel.
	 *
	 * @param	string	$name	The name of the model.
	 * @param	string	$prefix	The prefix for the PHP class name.
	 *
	 * @return	JModel
	 * @since	1.6
	 */
	public function getModel($name = 'Visfield', $prefix = 'VisformsModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}	
    
    /**
	 * Method to redirect to forms view
	 *
	 * @return void
	 *
	 * @since Joomla 1.6 
	 */
    public function forms()
    {
        
        $this->setRedirect('index.php?option=com_visforms');
    }
    
    /**
	 * Method to redirect to form view (not yet used)
	 *
	 * @return void
	 *
	 * @since Joomla 1.6 
	 */
    public function form()
    {
        $fid = JFactory::getApplication()->input->getInt('fid', 0);
        $app = JFactory::getApplication();
        $context = "com_visforms.edit.visform.id";
        if ($fid != 0)
        {
            $app->setUserState($context, (array) $fid);
        }
        $this->setRedirect('index.php?option=com_visforms&view=visform&layout=edit&id=' . $fid);
    }
}
?>
