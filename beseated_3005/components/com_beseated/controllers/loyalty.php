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
 * The Beseated Loyalty Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerLoyalty extends JControllerAdmin
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
	public function getModel($name = 'Loyalty', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function send_invitation()
	{
		$app           = JFactory::getApplication();
		$input         = $app->input;
		$emails        = $input->get('invite_user','','string');
		$Itemid        = $input->get('Itemid',0,'int');
		$loginUser     = JFactory::getUser();
		$bctRegiEmails = BeseatedHelper::filterEmails($emails);
		$emailsArray   = explode(",", $emails);
		$filterEmails  = array();

		foreach ($emailsArray as $key => $singleEmail)
		{
			if(in_array($singleEmail, $bctRegiEmails['allRegEmail']))
			{
				if(in_array($singleEmail, $bctRegiEmails['company'])){ continue; }
				if(in_array($singleEmail, $bctRegiEmails['venue'])){ continue; }
				if(in_array($singleEmail, $bctRegiEmails['guest']) && $loginUser->email == $singleEmail){ continue; }
			}

			$filterEmails[] = $singleEmail;
		}

		if(count($filterEmails)==0)
		{
			$app->redirect('index.php?option=com_beseated&view=loyalty&Itemid='.$Itemid,'Invalid email address.');
		}

		$emailsArray = $filterEmails;

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('email')
			->from($db->quoteName('#__users'));

		// Set the query and load the result.
		$db->setQuery($query);

		$registeredEmails = $db->loadColumn();

		$query2 = $db->getQuery(true);

		// Create the base select statement.
		$query2->select('refer_email')
			->from($db->quoteName('#__beseated_user_refer'))
			->where($db->quoteName('userid') . ' = ' . $db->quote($loginUser->id));

		// Set the query and load the result.
		$db->setQuery($query2);

		$alreadyReferEmails = $db->loadColumn();

		$emailsAfterRegi = array();

		foreach ($emailsArray as $key => $singleEmail)
		{
			if(!in_array($singleEmail, $registeredEmails))
			{
				$emailsAfterRegi[] = $singleEmail;
			}
		}

		$referEmails = array();
		foreach ($emailsAfterRegi as $key => $singleEmail)
		{
			if(!in_array($singleEmail, $alreadyReferEmails))
			{
				$referEmails[] = $singleEmail;
			}
		}

		if(count($referEmails)==0)
		{
			$app->redirect('index.php?option=com_beseated&view=loyalty&Itemid='.$Itemid,'Email address provided is member of beseated');
			$app->close();
		}

		foreach ($referEmails as $key => $value)
		{
			$tblRefer = JTable::getInstance('Refer', 'BeseatedTable');
			$tblRefer->load(0);
			$referData['userid']        = $loginUser->id;
			$referData['refer_email']   = $value;
			$referData['is_registered'] = 0;
			$referData['ref_user_id']   = 0;
			$referData['is_fp_done']    = 0;
			$referData['created']       = date('Y-m-d H:i:s');
			$referData['time_stamp']    = time();

			$tblRefer->bind($referData);
			if($tblRefer->store())
			{
				require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
				$emailAppHelper = new BeseatedEmailHelper();
				$emailAppHelper->beseatedInvitationMail($value,$loginUser->name);
			}
		}
		$app->redirect('index.php?option=com_beseated&view=loyalty&Itemid='.$Itemid,'Invitation(s) sent successfully.');
	}
}
