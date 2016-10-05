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
 * The Beseated Messages View
 *
 * @since  0.0.1
 */
class BeseatedViewVenuePromotion extends JViewLegacy
{
	protected $messages;

	protected $pagination;

	protected $state;

	protected $user;

	protected $userType;

	protected $offset;
	/**
	 * Display the Beseated Messages view
	 *
	 * @param   string  $tpl  The name of the template file to parse;
	 * automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   0.0.1
	 */
	function display($tpl = null)
	{
		// Get data from the model
		//$this->messages = $this->get('MessageThread');
		$this->user     = JFactory::getUser();
		if(!$this->user->id)
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?option=com_users&view=login');
		}

		$this->userType = BeseatedHelper::getUserType($this->user->id);
		$this->offset   = BeseatedHelper::getUserTimezoneDifferent($this->user->id);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		// Display the template
		parent::display($tpl);
	}
}
