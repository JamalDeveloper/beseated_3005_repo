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
 * Beseated Private Jets Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerConcierges extends JControllerAdmin
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
	public function getModel($name = 'Concierges', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Function to delete Event
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
			$result = $model->deleteConcierge($cid);

			if ($result){
				JFactory::getApplication()->enqueueMessage('Concierges deleted successfully.');
			}

			$this->setRedirect('index.php?option=com_beseated&view=concierges');
		}
	}

	public function isDefault()
	{//echo "<pre/>";print_r("hi");exit;

		$conciergeID = $this->getConciergeID();

		//echo "<pre/>";print_r($conciergeID);exit;

	    $concierge_id = JFactory::getApplication()->input->get('concierge_id', 0, 'int');
		$value        = JFactory::getApplication()->input->get('value', 0, 'int');

		if($conciergeID ==  $concierge_id && $value == 0)
		{
			echo "0";exit;
		}

		if($value == '1')
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base update statement.
			$query->update($db->quoteName('#__beseated_concierge'))
				->set($db->quoteName('is_default') . ' = ' . $db->quote('1'))
				->where($db->quoteName('concierge_id') . ' = ' . $db->quote($concierge_id));

			// Set the query and execute the update.
			$db->setQuery($query);

			$db->execute();

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base update statement.
			$query->update($db->quoteName('#__beseated_concierge'))
				->set($db->quoteName('is_default') . ' = ' . $db->quote('0'))
				->where($db->quoteName('concierge_id') . ' != ' . $db->quote($concierge_id));

			// Set the query and execute the update.
			$db->setQuery($query);

			$db->execute();
		}
		else
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base update statement.
			$query->update($db->quoteName('#__beseated_concierge'))
				->set($db->quoteName('is_default') . ' = ' . $db->quote('0'));

			// Set the query and execute the update.
			$db->setQuery($query);

			$db->execute();

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base update statement.
			$query->update($db->quoteName('#__beseated_concierge'))
				->set($db->quoteName('is_default') . ' = ' . $db->quote('1'))
				->where('select min(concierge_id) FROM #__beseated_concierge)');

			// Set the query and execute the update.
			$db->setQuery($query);

			$db->execute();

			//echo "<pre/>";print_r("hi");exit;
		}

		echo 'index.php?option=com_beseated&view=concierges';exit;


	}

	public function getConciergeID()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('concierge_id')
			->from($db->quoteName('#__beseated_concierge'))
			->where($db->quoteName('is_default') . ' = ' . $db->quote('1'));

		// Set the query and load the result.
		$db->setQuery($query);
		$conciergeID = $db->loadResult();

		return $conciergeID;


	}

}
