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
 * Beseated Venues Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerVenues extends JControllerAdmin
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
	public function getModel($name = 'Venue', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Function to delete Venues
	 *
	 * @return  boolean
	 *
	 * @since   0.0.1
	 */
	public function delete()
	{
		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			$result = $model->deleteVenue($cid);

			if ($result){
				JFactory::getApplication()->enqueueMessage('Venue deleted successfully.');
			}

			$this->setRedirect('index.php?option=com_beseated&view=venues');
		}
	}

	public function updateHasGuestlistValue()
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		$venue_id     = $input->get('venue_id', 0, 'int');
		$checkBoxVal = $input->get('checkBoxVal', 0, 'int');

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_venue'))
			->set($db->quoteName('has_guestlist') . ' = ' . $db->quote($checkBoxVal))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venue_id));

		// Set the query and execute the update.
		$db->setQuery($query);

		$db->execute();

		echo "200";
		exit;
	}

	public function updateHasActivePayments()
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		$venue_id     = $input->get('venue_id', 0, 'int');
		$checkBoxVal = $input->get('checkBoxVal', 0, 'int');

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_venue'))
			->set($db->quoteName('active_payments') . ' = ' . $db->quote($checkBoxVal))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venue_id));

		// Set the query and execute the update.
		$db->setQuery($query);

		$db->execute();

		echo "200";
		exit;
	}

}
