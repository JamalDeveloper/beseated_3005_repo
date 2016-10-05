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
 * The Beseated Message Detail View
 *
 * @since  0.0.1
 */
class BeseatedViewVenueMessageDetail extends JViewLegacy
{
	protected $messages;

	protected $otherUser;

	protected $pagination;

	protected $state;

	protected $connectionID;

	protected $userType;

	protected $user;

	protected $userProfile;

	protected $offset;
	/**
	 * Display the Beseated Message Detail view
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
		//$this->messages     = $this->get('Items');
		$this->pagination   = $this->get('Pagination');
		$this->state        = $this->get('State');
		$input              = JFactory::getApplication()->input;
		$otherUserID        = $input->get('user_id',0,'int');
		$this->connectionID = $input->get('connection_id',0,'int');
		$this->otherUser    = JFactory::getUser($otherUserID);
		$this->user         = JFactory::getUser();
		if(!$this->user->id)
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?option=com_users&view=login');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		$this->userType    = BeseatedHelper::getUserType($this->user->id);
		$this->userProfile = BeseatedHelper::getUserElementID($this->user->id);
		$this->offset      = BeseatedHelper::getUserTimezoneDifferent($this->user->id);

		// Display the template
		parent::display($tpl);
	}
}
