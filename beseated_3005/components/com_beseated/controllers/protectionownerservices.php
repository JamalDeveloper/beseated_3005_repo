<?php
/**
 * @package     The Beseated.Administrator
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * The Beseated Club Owner Tables Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerProtectionOwnerServices extends JControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config	An optional associative array of configuration settings.
	 * @return  ContentControllerArticles
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   0.0.1
	 */
	public function getModel($name = 'ProtectionOwnerServices', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function deleteService()
	{
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$serviceID = $input->get('service_id',0,'int');

		if(!$serviceID)
		{
			echo "400";
			exit;
		}

		$model  = $this->getModel();
		$result = $model->deleteService($serviceID);

		echo $result;
		$app->close();
		exit;

	}
}
