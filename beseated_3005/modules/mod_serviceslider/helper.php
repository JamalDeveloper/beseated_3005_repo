<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_articles_category
 *
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @since       1.6
 */
class ModServiceSliderHelper
{

	public static function getServiceImages($elementId,$service_id,$elementType)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($elementId))
			->where($db->quoteName('service_id') . ' = ' . $db->quote($service_id))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
			->order($db->quoteName('is_default') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$images = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return $images;
	}

}
