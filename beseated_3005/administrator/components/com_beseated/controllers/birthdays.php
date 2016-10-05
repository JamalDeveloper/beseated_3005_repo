<?php
/**
 * @package     Bcted.Administrator
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * Bcted Packages Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerBirthdays extends JControllerAdmin
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

		//$this->registerTask('unfeatured',	'featured');
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
	public function getModel($name = 'Guests', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function contacted()
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		$user_id     = $input->get('user_id', '', 'string');

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_user_profile'))
			->set($db->quoteName('contacted') . ' = ' . $db->quote('1'))
			->set($db->quoteName('contacted_date_time') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id));

		// Set the query and execute the update.
		$db->setQuery($query);

		$db->execute();

		echo "200";
		exit;
	}


}
