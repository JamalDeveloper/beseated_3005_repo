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
 * The Beseated Message Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerContact extends JControllerAdmin
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
	public function getModel($name = 'Messages', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function send_contact_message()
	{
		$loginUser     = JFactory::getUser();

		if(!$loginUser->id)
		{
			echo 704;
			exit;
		}

		require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

	    $this->emailHelper =  new BeseatedEmailHelper;

		$input        = JFactory::getApplication()->input;
		$subject      = $input->getstring('subject');
		$message      = $input->getstring('message');

		$userDetail =BeseatedHelper::guestUserDetail($loginUser->id);

		if(strtolower($userDetail->user_type) == 'yacht')
		{
			$managerDetail = BeseatedHelper::yachtUserDetail($loginUser->id);
	    }
	    elseif(strtolower($userDetail->user_type) == 'protection') 
	    {
	    	$managerDetail = BeseatedHelper::protectionUserDetail($loginUser->id);
	    }
	    elseif(strtolower($userDetail->user_type) == 'venue') 
	    {
	    	$managerDetail = BeseatedHelper::venueUserDetail($loginUser->id);
	    }
	    elseif(strtolower($userDetail->user_type) == 'chauffeur') 
	    {
	    	$managerDetail = BeseatedHelper::chauffeurUserDetail($loginUser->id);
	    }
	    

	    if(strtolower($userDetail->user_type) == 'beseated_guest')
	    {
			$userID          = $userDetail->user_id;
			$elementID       = '0';
			$elementType     = 'user';
	    }
	    else
	    {
	    	$company_id   = strtolower($userDetail->user_type).'_id';
			$companyID   = $managerDetail->$company_id;

			$userID          = $managerDetail->user_id;
			$elementID       = $companyID;
			$elementType     = strtolower($userDetail->user_type);
	    }

		BeseatedHelper::storeContactRequest($userID,$elementID,$elementType,$subject,$message);
		//$this->emailHelper->contactAdmin($subject, $message);
		//$this->emailHelper->contactThankYouEmail();

		echo 200;
		exit();
	}
}
