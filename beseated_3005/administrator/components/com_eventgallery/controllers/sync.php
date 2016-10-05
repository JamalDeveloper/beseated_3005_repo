<?php
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport( 'joomla.application.component.controllerform' );

require_once(__DIR__.'/../controller.php');

class EventgalleryControllerSync extends JControllerForm
{


    /**
     * The root folder for the physical images
     *
     * @var string
     */

    protected $default_view = 'sync';

    public function __construct($config = array())
    {

        parent::__construct($config);
    }

	public function getModel($name = 'Sync', $prefix ='EventgalleryModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    /**
     * just cancels this view
     * @param null $key
     * @return bool|void
     */
	public function cancel($key = NULL) {
		$this->setRedirect( 'index.php?option=com_eventgallery&view=events');
	}

    /**
     * starts the syncronization.
     *
     * @param bool $cachable
     * @param array $urlparams
     */
    public function start($cachable = false, $urlparams = array()) {
        JSession::checkToken();

        $app = JFactory::getApplication();
        // make sure the database contains the right stuff
        $errors = $this->getModel()->addNewFolders();
        foreach($errors as $error) {
            $app->enqueueMessage($error, 'error');
        }
        JRequest::setVar('layout', 'sync');
        $this->display($cachable, $urlparams);
    }

    /**
     * Syncs one folder
     *
     * @param bool $cachable
     * @param array $urlparams
     */
    public function process(/** @noinspection PhpUnusedParameterInspection */$cachable = false, $urlparams = array()) {
        JSession::checkToken();
        $folder = JRequest::getString('folder','');
        $syncResult =  $this->getModel()->syncFolder($folder);

        $result = Array();
        $result['folder'] = htmlspecialchars($folder);
        $result['status'] = $syncResult;
        
        echo json_encode($result);
    }


}
