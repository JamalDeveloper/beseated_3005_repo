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
 * The Beseated Forgot Password Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerForgotpassword extends JControllerAdmin
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
	public function getModel($name = 'Forgotpassword', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function setpassword()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$email = $input->get('email','','string');
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('email') . ' = ' . $db->quote($email));

		$db->setQuery($query);

		$userID = $db->loadResult();

		if($userID>0)
		{
			$password_set = JUserHelper::genRandomPassword(6);
			$userDetail   = JFactory::getUser($userID);
			$queryUPDT    = $db->getQuery(true);

			$queryUPDT->update($db->quoteName('#__beseated_user_profile'))
				->set($db->quoteName('token') . ' = ' . $db->quote($password_set))
				->where($db->quoteName('userid') . ' = ' . $db->quote($userID));

			$db->setQuery($queryUPDT);
			$db->execute();

			$app           = JFactory::getApplication();
			$menu          = $app->getMenu();
			$menuItem      = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
			$loginLink     = "index.php?option=com_users&view=login&Itemid=".$menuItem->id;
			$htmlLoginLink = '<a href="'.JUri::base().$loginLink.'">Here</a>';
			$imgPath       = JUri::base().'images/email-footer-logo.png';
			$imageLink     = '<img src="'.$imgPath.'"/>';
			$app           = JFactory::getApplication();
			$config        = JFactory::getConfig();
			$site          = $config->get('sitename');
			$from          = $config->get('mailfrom');
			$sender        = $config->get('fromname');

			$emailSubject	= JText::sprintf(
				'COM_BCTED_FORGOT_PASSWORD_EMAIL_SUBJECT'
			);

			$emailBody = JText::sprintf(
				'COM_BCTED_FORGOT_PASSWORD_EMAIL_BODY',
				$userDetail->name,
				$userDetail->email,
				$password_set,
				$htmlLoginLink,
				$imageLink
			);
			// Clean the email data.
			$sender  = JMailHelper::cleanAddress($sender);
			$subject = JMailHelper::cleanSubject($emailSubject);
			$body    = JMailHelper::cleanBody($emailBody);

			// Send the email.
			$return = JFactory::getMailer()->sendMail($from, $sender, $email, $emailSubject, $body,true);

			// Check for an error.
			if ($return !== true)
			{
				$menu     = $app->getMenu();
				$menuItem = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
				$Itemid   = $menuItem->id;
				$app->redirect('index.php?option=com_users&view=login&Itemid='.$Itemid, JText::_('COM_BCTED_PASSWORD_SEND_ERROR'));
				$app->close();
			}

			$menu     = $app->getMenu();
			$menuItem = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
			$Itemid   = $menuItem->id;
			$app->redirect('index.php?option=com_users&view=login&Itemid='.$Itemid, JText::_('COM_BCTED_PASSWORD_SEND_SUCCESS'));
			$app->close();
		}

		$menu     = $app->getMenu();
		$menuItem = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$Itemid   = $menuItem->id;
		$app->redirect('index.php?option=com_users&view=login&Itemid='.$Itemid, JText::_('COM_BCTED_PASSWORD_EMAIL_NOT_FOUND'));
		$app->close();
	}
}
