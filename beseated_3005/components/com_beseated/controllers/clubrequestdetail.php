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
 * The Beseated Guest List Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerClubRequestDetail extends JControllerAdmin
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
	public function getModel($name = 'ClubRequestDetail', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function changeRequstStatus()
	{
		$app           = JFactory::getApplication();
		$input         = $app->input;
		$model         = $this->getModel();
		$user          = JFactory::getUser();
		$requestID     = $input->get('request_id', 0, 'int');
		$status        = $input->get('status', '', 'string');
		$owner_message = $input->get('owner_message', '', 'string');

		if(empty($status))
		{
			$erMsg = JText::_('COM_BCTED_INVALIED_GUEST_LIST_REQUST_PARAMETERS');
			echo "0";
			exit;
		}

		if(!$requestID)
		{
			echo "0";
			exit;
		}

		$result = $model->changeRequestStatus($requestID,$status,$owner_message);

		if($result)
		{
			if($result == 3)
			{
				$message = 'Your response has been sent to the user.';
				$title = 'Success';
				BeseatedHelper::setBeseatedSessionMessage($message,$title);
				echo "3";
				exit;
			}

			$message = JText::_('COM_BCTED_VENUE_TABLE_ACCEPTE_SUCCESS_MESSAGE');
			$title = JText::_('COM_BCTED_VENUE_TABLE_ACCEPTE_SUCCESS_MESSAGE_TITLE');
			BeseatedHelper::setBeseatedSessionMessage($message,$title);
			echo "1";
			exit;
		}

		echo "0";
		exit;
	}
}
