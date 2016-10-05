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
class BeseatedViewChauffeurshareuserpay extends JViewLegacy
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
		$this->chauffeurBookingDetail       = $this->get('ChauffeurBookingDetail');
		$this->chauffeurShareUserDetail     = $this->get('ChauffeurSharedUserDetail');
		$this->percentagePaidSharedUser = $this->get('PercentagePaidSharedUser');

		//echo "<pre>";print_r($this->percentagePaidSharedUser);echo "<pre/>";exit();


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

		// Display the template
		parent::display($tpl);
	}
}
