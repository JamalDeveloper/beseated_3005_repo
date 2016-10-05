<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 

// No direct access
defined('_JEXEC') or die;

/**
 * Content component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsHelper
{
	public static $extension = 'com_visforms';

	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	$vName	The name of the active view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_VISFORMS_SUBMENU_FORMS'),
			'index.php?option=com_visforms&view=visforms',
			$vName == 'visforms'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_VISFORMS_SUBMENU_HELP'),
			'index.php?option=com_visforms&view=vishelp',
			$vName == 'vishelp');
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param	int		The category ID.
	 * @param	int		The article ID.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions($formId = 0, $fieldId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($formId) && empty($fieldId)) {
			$assetName = 'com_visforms';
		}
		else if (empty($fieldId)){
			$assetName = 'com_visforms.visform.'.(int) $formId;
		}
		else
		{
			$assetName = 'com_visforms.visform.'.(int) $formId.'.visfield.'. (int) $fieldId;
		}

       $actions = JAccess::getActionsFromFile(JPath::clean(JPATH_ADMINISTRATOR . '/components/com_visforms/access.xml'), "/access/section[@name='component']/");

		foreach ($actions as $action) {
			$result->set($action->name,	$user->authorise($action->name, $assetName));
		}

		return $result;
	}
    
    /**
	 * Checks if the file can be uploaded
	 *
	 * @param   array   $file  File information
	 * @param   string  $err   An error message to be returned
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public static function canUpload($file, $allowedExtensions = "")
	{
		if (empty($file['name']))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_VISFORMS_ERROR_UPLOAD_INPUT'), 'error');

			return false;
		}

		// Media file names should never have executable extensions buried in them.
		$executable = array(
			'exe', 'phtml','java', 'perl', 'py', 'asp','dll', 'go', 'jar',
			'ade', 'adp', 'bat', 'chm', 'cmd', 'com', 'cpl', 'hta', 'ins', 'isp',
			'jse', 'lib', 'mde', 'msc', 'msp', 'mst', 'pif', 'scr', 'sct', 'shb',
			'sys', 'vb', 'vbe', 'vbs', 'vxd', 'wsc', 'wsf', 'wsh'
		);
		$explodedFileName = explode('.', $file['name']);

		if (count($explodedFileName) > 2)
		{
			foreach ($executable as $extensionName)
			{
				if (in_array($extensionName, $explodedFileName))
				{
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('COM_VISFORMS_ERROR_EXECUTABLE'), 'error');

					return false;
				}
			}
		}

		jimport('joomla.filesystem.file');

		if ($file['name'] !== JFile::makeSafe($file['name']) || preg_match('/\s/', JFile::makeSafe($file['name'])))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_VISFORMS_ERROR_WARNFILENAME'), 'error');

			return false;
		}

		$format = strtolower(JFile::getExt($file['name']));
		$allowable = array($allowedExtensions);

		if ($format == '' || $format == false || (!in_array($format, $allowable)))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_VISFORMS_ERROR_WARNFILETYPE'), 'error');

			return false;
		}

		// Max upload size set to 2 MB for Template Manager
		$maxSize = (int) (2 * 1024 * 1024);

		if ($maxSize > 0 && (int) $file['size'] > $maxSize)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_VISFORMS_ERROR_WARNFILETOOLARGE'), 'error');

			return false;
		}

		$xss_check = file_get_contents($file['tmp_name'], false, null, -1, 256);
		$html_tags = array(
			'abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink', 'blockquote',
			'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div',
			'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html',
			'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext', 'link', 'listing',
			'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object', 'ol', 'optgroup', 'option',
			'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike',
			'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml',
			'xmp', '!DOCTYPE', '!--'
		);

		foreach ($html_tags as $tag)
		{
			// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			if (stristr($xss_check, '<' . $tag . ' ') || stristr($xss_check, '<' . $tag . '>'))
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('COM_VISFORMS_ERROR_WARNIEXSS'), 'error');

				return false;
			}
		}

		return true;
	}
}
