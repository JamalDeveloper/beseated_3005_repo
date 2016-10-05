<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * The Beseated Club Account History Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerClubAccountHistory extends JControllerAdmin
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
	public function getModel($name = 'ClubAccountHistory', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function add_user_in_blacklist()
	{
		$app     = JFactory::getApplication();
		$input   = $app->input;
		$userID  = $input->get('user_id',0,'int');
		$venueID = $input->get('venue_id',0,'int');

		if(!$userID || !$venueID)
		{
			echo "400";
			exit;
		}

		$model    = $this->getModel();
		$response = $model->addUserToBlackList($userID,$venueID);

		echo $response;
		exit;
	}

	public function remove_user_from_blacklist()
	{
		$app     = JFactory::getApplication();
		$input   = $app->input;
		$userID  = $input->get('user_id',0,'int');
		$venueID = $input->get('venue_id',0,'int');

		if(!$userID || !$venueID)
		{
			echo "400";
			exit;
		}

		$model    = $this->getModel();
		$response = $model->removeUserFromBlackList($userID,$venueID);

		echo $response;
		exit;
	}
}
