<?php
/**
 * NoNumber Framework Helper File: Assignments: Templates
 *
 * @package         NoNumber Framework
 * @version         15.4.5
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once JPATH_PLUGINS . '/system/nnframework/helpers/assignment.php';

class nnFrameworkAssignmentsTemplates extends nnFrameworkAssignment
{
	function passTemplates()
	{
		$template = $this->getTemplate();

		// Put template name and name + style id into array
		// The '::' separator was used in pre Joomla 3.3
		$template = array($template->template, $template->template . '--' . $template->id, $template->template . '::' . $template->id);

		return $this->passSimple($template, true);
	}

	public function getTemplate()
	{
		$template = JFactory::getApplication()->getTemplate(true);

		if (isset($template->id))
		{
			return $template;
		}

		$params = json_encode($template->params);

		// Find template style id based on params, as the template style id is not always stored in the getTemplate
		$query = $this->db->getQuery(true)
			->select('id')
			->from('#__template_styles as s')
			->where('s.client_id = 0')
			->where('s.template = ' . $this->db->quote($template->template))
			->where('s.params = ' . $this->db->quote($params));
		$this->db->setQuery($query, 0, 1);
		$template->id = $this->db->loadResult('id');

		if ($template->id)
		{
			return $template;
		}

		// No template style id is found, so just grab the first result based on the template name
		$query->clear('where')
			->where('s.client_id = 0')
			->where('s.template = ' . $this->db->quote($template->template));
		$this->db->setQuery($query, 0, 1);
		$template->id = $this->db->loadResult('id');

		return $template;
	}
}
