<?php
/**
 * @package     BigSubscription.Administrator
 * @subpackage  com_bigsubscription
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * BigSubscription mailtype Form Field class for the BigSubscription component
 *
 * @since  0.0.1
 */
class JFormFieldUserlist extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var         string
	 */
	protected $type = 'userlist';

	protected $messages = null;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 */
	protected function getOptions()
	{
		$this->getUserList();

		$options  = array();
		$users = $this->users;


		if ($users)
		{
			foreach ($users as $user)
			{
				$options[] = JHtml::_('select.option', $user->user_id, $user->email);
			}
		}
		//echo "<pre/>";print_r($options);exit;
		return $options;
	}

	/**
	 * Get list of mail's type to display
	 *
	 * @return  [type]  [description]
	 */
	protected function getUserList()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_user_profile').' AS a')
			->where($db->quoteName('a.is_deleted') . ' = ' . $db->quote('0'))
			->where($db->quoteName('b.block') . ' = ' . $db->quote('0'))
			->where($db->quoteName('a.user_type') . ' = ' . $db->quote('beseated_guest'))
			->join('INNER', '#__users AS b ON b.id=a.user_id')
			->order($db->quoteName('email') . ' ASC');

		// Set the query and load the result.
		$db->setQuery((string) $query);

		try
		{
			$this->users = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}
	}
}