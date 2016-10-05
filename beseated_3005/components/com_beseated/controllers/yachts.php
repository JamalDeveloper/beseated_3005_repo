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
 * The Beseated Venues Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerYachts extends JControllerAdmin
{
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
	public function getModel($name = 'Yachts', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function addtofavourite()
	{
		// Get items to remove from the request.
		$yachtID = JFactory::getApplication()->input->get('yacht_id', 0, 'int');
		$userID  = JFactory::getApplication()->input->get('user_id', 0, 'int');

		if (!$yachtID || !$userID)
		{
			echo "3";
			exit;
		}
		else
		{
			$model  = $this->getModel();
			$result = $model->addtofavourite($yachtID,$userID);
			echo $result;
			exit;
		}

		echo 0;
		exit;
	}

	/**
	 * Function to delete Category
	 *
	 * @return  boolean
	 *
	 * @since   0.0.1
	 */
	public function removefromfavourite()
	{
		$yachtID = JFactory::getApplication()->input->get('yacht_id', 0, 'int');
		$userID  = JFactory::getApplication()->input->get('user_id', 0, 'int');

		if (!$yachtID || !$userID)
		{
			echo "3";
			exit;
		}
		else
		{
			$model  = $this->getModel();
			$result = $model->removefromfavourite($yachtID,$userID);
			echo $result;
			exit;
		}

		echo 0;
		exit;
	}
}
