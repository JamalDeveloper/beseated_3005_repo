<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_BASE . '/components/com_users/controller.php';

/**
 * Registration controller class for Users.
 *
 * @package     Joomla.Site
 * @subpackage  com_users
 * @since       1.6
 */
class UsersControllerUser extends UsersController
{
	/**
	 * Method to log in a user.
	 *
	 * @since   1.6
	 */
	public function login()
	{
		JSession::checkToken('post') or jexit(JText::_('JInvalid_Token'));


		$app    = JFactory::getApplication();
		$menu   = $app->getMenu();
		$input  = $app->input;
		$method = $input->getMethod();

		// Populate the data array:
		$data = array();

		$data['return']    = base64_decode($app->input->post->get('return', '', 'BASE64'));
		$data['username']  = $input->$method->get('username', '', 'USERNAME');
		$data['password']  = $input->$method->get('password', '', 'RAW');
		$data['secretkey'] = $input->$method->get('secretkey', '', 'RAW');


		// Set the return URL if empty.
		if (empty($data['return']))
		{
			$data['return'] = 'index.php?option=com_users&view=profile';
		}

		// Set the return URL in the user state to allow modification by plugins
		$app->setUserState('users.login.form.return', $data['return']);

		// Get the log in options.
		$options = array();
		$options['remember'] = $this->input->getBool('remember', false);
		$options['return']   = $data['return'];

		// Get the log in credentials.
		$credentials = array();
		$credentials['username']  = $data['username'];
		$credentials['password']  = $data['password'];
		$credentials['secretkey'] = $data['secretkey'];


		// Perform the log in.
		if (true === $app->login($credentials, $options))
		{
			// Success
			if ($options['remember'] == true)
			{
				$app->setUserState('rememberLogin', true);
			}


			$user        = JFactory::getUser();
			$bctedConfig = $this->getExtensionParam();
			$userType    ='';

			$groups = $user->get('groups');

			if(in_array($bctedConfig->club, $groups))
			{
				$userType = 'Club';
			}
			else if(in_array($bctedConfig->service_provider, $groups))
			{
				$userType = 'ServiceProvider';
			}
			else if(in_array($bctedConfig->guest, $groups))
			{
				$userType = 'Registered';
			}

			$app->setUserState('users.login.form.data', array());

			if($data['return'] == 'index.php?option=com_users&view=profile')
			{
				if($userType == 'Club')
				{
					$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubsummary', true );
					$app->redirect(JRoute::_('index.php?option=com_beseated&view=clubsummary&Itemid='.$menuItem->id, false));

				}
				else if($userType == "ServiceProvider")
				{
					$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=companysummary', true );
					$app->redirect(JRoute::_('index.php?option=com_beseated&view=companysummary&Itemid='.$menuItem->id, false));
				}
				else
				{
					//$app->redirect(JRoute::_($app->getUserState('users.login.form.return'), false));


					//$menuItem = $menu->getDefault();
					$menuItem = $menu->getItems( 'link', 'index.php?option=com_content&view=featured', true );
        			$app->redirect(JRoute::_($menuItem->link.'&Itemid='.$menuItem->id, false));
				}
			}
			else
			{
				//$menuItem = $menu->getItems( 'link', 'index.php?option=com_content&view=featured', true );
        		//$app->redirect(JRoute::_($menuItem->link.'&Itemid='.$menuItem->id, false));
				$app->redirect(JRoute::_($app->getUserState('users.login.form.return'), false));
			}


		}
		else
		{
			// Login failed !

			$menu = $app->getMenu();
			$menuItem = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
			$loginItemID = $menuItem->id;
			$data['remember'] = (int) $options['remember'];
			$app->setUserState('users.login.form.data', $data);
			$app->redirect(JRoute::_('index.php?option=com_users&view=login&Itemid='.$loginItemID, false));
		}
	}

	public function getExtensionParam()
	{
		$app    = JFactory::getApplication();

		$option = "com_beseated";
		$db     = JFactory::getDbo();

		$option = '%' . $db->escape($option, true) . '%';

		// Initialiase variables.
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->qn('#__extensions'))
			->where($db->qn('name') . ' LIKE ' . $db->q($option))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->order($db->qn('ordering') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();

			$params = json_decode($result->params);
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), 500);
		}

		return $params;
	}

	/**
	 * Method to log out a user.
	 *
	 * @since   1.6
	 */
	public function logout()
	{
		JSession::checkToken('request') or jexit(JText::_('JInvalid_Token'));

		$app = JFactory::getApplication();

		// Perform the log in.
		$error  = $app->logout();
		$input  = $app->input;
		$method = $input->getMethod();

		// Check if the log out succeeded.
		if (!($error instanceof Exception))
		{
			// Get the return url from the request and validate that it is internal.
			$return = $input->$method->get('return', '', 'BASE64');
			$return = base64_decode($return);
			if (!JUri::isInternal($return))
			{
				$return = '';
			}

			// Redirect the user.
			$app->redirect(JRoute::_($return, false));
		}
		else
		{
			$app->redirect(JRoute::_('index.php?option=com_users&view=login', false));
		}
	}

	/**
	 * Method to register a user.
	 *
	 * @since   1.6
	 */
	public function register()
	{
		JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		// Get the application
		$app = JFactory::getApplication();

		// Get the form data.
		$data = $this->input->post->get('user', array(), 'array');

		// Get the model and validate the data.
		$model  = $this->getModel('Registration', 'UsersModel');
		$return	= $model->validate($data);

		// Check for errors.
		if ($return === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'notice');
				}
			}

			// Save the data in the session.
			$app->setUserState('users.registration.form.data', $data);

			// Redirect back to the registration form.
			$this->setRedirect('index.php?option=com_users&view=registration');
			return false;
		}

		// Finish the registration.
		$return	= $model->register($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('users.registration.form.data', $data);

			// Redirect back to the registration form.
			$message = JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_users&view=registration', $message, 'error');
			return false;
		}

		// Flush the data from the session.
		$app->setUserState('users.registration.form.data', null);

		exit;
	}

	/**
	 * Method to login a user.
	 *
	 * @since   1.6
	 */
	public function remind()
	{
		// Check the request token.
		JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		$app   = JFactory::getApplication();
		$model = $this->getModel('User', 'UsersModel');
		$data  = $this->input->post->get('jform', array(), 'array');

		// Submit the username remind request.
		$return	= $model->processRemindRequest($data);

		// Check for a hard error.
		if ($return instanceof Exception)
		{
			// Get the error message to display.
			if ($app->get('error_reporting'))
			{
				$message = $return->getMessage();
			}
			else
			{
				$message = JText::_('COM_USERS_REMIND_REQUEST_ERROR');
			}

			// Get the route to the next page.
			$itemid = UsersHelperRoute::getRemindRoute();
			$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
			$route  = 'index.php?option=com_users&view=remind' . $itemid;

			// Go back to the complete form.
			$this->setRedirect(JRoute::_($route, false), $message, 'error');

			return false;
		}
		elseif ($return === false)
		{
			// Complete failed.
			// Get the route to the next page.
			$itemid = UsersHelperRoute::getRemindRoute();
			$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
			$route  = 'index.php?option=com_users&view=remind' . $itemid;

			// Go back to the complete form.
			$message = JText::sprintf('COM_USERS_REMIND_REQUEST_FAILED', $model->getError());
			$this->setRedirect(JRoute::_($route, false), $message, 'notice');

			return false;
		}
		else
		{
			// Complete succeeded.
			// Get the route to the next page.
			$itemid = UsersHelperRoute::getLoginRoute();
			$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
			$route	= 'index.php?option=com_users&view=login' . $itemid;

			// Proceed to the login form.
			$message = JText::_('COM_USERS_REMIND_REQUEST_SUCCESS');
			$this->setRedirect(JRoute::_($route, false), $message);

			return true;
		}
	}

	/**
	 * Method to login a user.
	 *
	 * @since   1.6
	 */
	public function resend()
	{
		// Check for request forgeries
		JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));
	}
}
