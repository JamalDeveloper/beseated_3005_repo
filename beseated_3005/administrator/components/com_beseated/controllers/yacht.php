<?php
/**
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * Beesated Yacht Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerYacht extends JControllerForm
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return  void
	 * @since   1.6
	 */
	public function unpublish()
	{
		$user   = JFactory::getUser();
		$ids    = $this->input->get('cid', 0, 'int');

		// Get the model.
		$model = $this->getModel();

		// Publish the items.
		if (!$model->unpublished($ids))
		{
			JError::raiseWarning(500, $model->getError());
		}

		$this->setRedirect('index.php?option=com_beseated&view=yachts');
	}

	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return  void
	 * @since   1.6
	 */
	public function publish()
	{
		$user   = JFactory::getUser();
		$ids    = $this->input->get('cid', 0, 'int');

		// Get the model.
		$model = $this->getModel();

		// Publish the items.
		if (!$model->published($ids))
		{
			JError::raiseWarning(500, $model->getError());
		}

		$this->setRedirect('index.php?option=com_beseated&view=yachts');
	}

	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return  void
	 * @since   1.6
	 */
	public function delete()
	{
		$user   = JFactory::getUser();
		$ids    = $this->input->get('cid', array(), 'array');

		// Get the model.
		$model = $this->getModel();

		// Publish the items.
		if (!$model->delete($ids))
		{
			JError::raiseWarning(500, $model->getError());
		}

		$this->setRedirect('index.php?option=com_beseated&view=yachts');
	}
}
