<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
jimport( 'joomla.application.component.helper' );
jimport('joomla.filesystem.folder');
/**
 * The Beseated Registration Model
 *
 * @since  0.0.1
 */
class BeseatedModelRegistration extends JModelAdmin
{
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   0.0.1
	 */
	public function getTable($type = 'Profile', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   0.0.1
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_beseated.registration',
			'registration',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   0.0.1
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState(
			'com_beseated.edit.Registration.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	public function save($data)
	{
		$post['username']   = $data['email'];
		$post['relname']    = $data['first_name'] . ' ' . $data['last_name'];
		$post['password']   = $data['password'];
		$post['password1']  = $data['password'];
		$post['password2']  = $data['password2'];
		$post['token']      = $data['password'];
		$post['mobile']    = $data['mobile'];
		$post['email1']     = $data['email'];
		$post['email2']     = $data['email'];
		$post['email']      = $data['email'];
		$post['first_name'] = $data['first_name'];
		$post['last_name']  = $data['last_name'];
		$post['birthdate']  = $data['birthdate'];
		$post['city']       = (isset($data['city']))?$data['city']:'';

		$db       = JFactory::getDbo();
		$query    = $db->getQuery(true);
		$username = str_replace("\n", "", trim($post['username']));
		// Create the base select statement.
		$query->select('id')
			->from($db->qn('#__users'))
			->where($db->qn('username') . ' = ' . $db->q($username));
		$db->setQuery($query);
		if ( $db->loadResult() > 0)
		{
			return 701;
		}

		$query = $db->getQuery(true);
		$emails = str_replace("\n", "", trim($post['email']));
		$query->select('id')
			->from($db->qn('#__users'))
			->where($db->qn('email') . ' = ' . $db->q($emails));
		$db->setQuery($query);
		if ($db->loadResult() > 0)
		{
			return 702;
		}

		$params            = JComponentHelper::getParams('com_users');
		$system            = $params->get('new_usertype', 2);
		$useractivation    = $params->get('useractivation');
		$user              = new JUser;
		$post['name']      = trim(str_replace("\n", "", $post['relname']));
		$post['username']  = trim(str_replace("\n", "", $post['username']));
		$post['password']  = $post['password1'] = $post['password2'] = trim(str_replace("\n", "", $post['password']));
		$post['email']     = $post['email1'] = $post['email2'] = trim(str_replace("\n", "", $post['email']));
		$post['groups'][0] = $system;
		$post['phone']     = trim(str_replace("\n", "", $post['mobile']));
		$post['city']      = trim(str_replace("\n", "", $post['city']));

		// Check if the user needs to activate their account.
		if (($useractivation == 1) || ($useractivation == 2))
		{
			$post['activation'] = JApplication::getHash(JUserHelper::genRandomPassword());
			$post['block'] = 1;
		}

		$user->bind($post);
		if (!$user->save())
		{
			return 500;
		}


		$aclval = $user->id;

		if (!$aclval)
		{
			return 500;
		}

		$data             = $user->getProperties();
		$config           = JFactory::getConfig();
		$data['fromname'] = $config->get('fromname');
		$data['mailfrom'] = $config->get('mailfrom');
		$data['sitename'] = $config->get('sitename');
		$data['siteurl']  = JUri::root();

		// Handle account activation/confirmation emails.
		if ($useractivation == 1)
		{
			// Set the link to confirm the user email.
			$uri              = JURI::getInstance();
			$base             = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base . JRoute::_('index.php?option=com_users&task=registration.activate&token=' . $data['activation'], false);
			$emailSubject     = JText::sprintf(
				'COM_BESEATED_USER_ACTIVATION_EMAIL_SUBJECT'
			);

			$imgPath   = JUri::base().'images/email-footer-logo.png';
			$imageLink = '<img src="'.$imgPath.'"/>';
			$emailBody = JText::sprintf(
					'COM_BESEATED_USER_ACTIVATION_EMAIL_BODY',
					ucwords($data['name']),
					"<a href='".$data['siteurl'] . "index.php?option=com_users&task=registration.activate&token=" . $data["activation"]."'>here</a>",
					$imageLink
			);
		}
		elseif ($useractivation == 1)
		{
			$uri              = JURI::getInstance();
			$base             = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base . JRoute::_('index.php?option=com_users&task=registration.activate&token=' . $data['activation'], false);
			$emailSubject     = JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			if ($sendpassword)
			{
				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
					$data['name'],
					$data['sitename'],
					$data['siteurl'] . 'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
					$data['siteurl'],
					$data['username'],
					$data['password_clear']
				);
			}
			else
			{
				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY_NOPW',
					$data['name'],
					$data['sitename'],
					$data['siteurl'] . 'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
					$data['siteurl'],
					$data['username']
				);
			}
		}
		else
		{
			$emailSubject = JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBody = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl']
			);
		}

		

		$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody,true);
		if ($return !== true)
		{
			return 500;
		}

		// Send Notification mail to administrators
		if (($params->get('useractivation') < 2) && ($params->get('mail_to_admin') == 1))
		{
			$emailSubject = JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBodyAdmin = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY',
				$data['name'],
				$data['username'],
				$data['siteurl']
			);

			// Get all admin users
			$query = 'SELECT name, email, sendEmail
					FROM #__users
					WHERE sendEmail=1';
			$this->db->setQuery($query);
			$rows = $this->db->loadObjectList();

			// Send mail to all superadministrators id
			foreach ($rows as $row)
			{
				$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBodyAdmin);
				if ($return !== true)
				{
					IJReq::setResponseCode(500);
					IJReq::setResponseMessage(JText::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));

					return false;
				}
			}
		}

		// Check for an error.
		if ($return !== true)
		{
			$query = $this->db->getQuery(true);
			$query->select('id')
				->from($this->db->qn('#__users'))
				->where($this->db->qn('block') . ' = ' . $this->db->q('0'))
				->where($this->db->qn('sendEmail') . ' = ' . $this->db->q('1'));

			$this->db->setQuery($query);
			$sendEmail = $this->db->loadColumn();

			if (count($sendEmail) > 0)
			{
				$jdate    = new JDate;
				$query    = $this->db->getQuery(true);
				$messages = array();
				foreach ($sendEmail as $userid)
				{
					$messages[] = "({$userid}, {$userid}, '{$jdate->toSql()}', '" . JText::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT') . "', '" . JText::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $return, $data['username']) . "')";
				}

				// Build the query to add the messages
				$query->insert($this->db->qn('#__messages'))
					->columns(
						array(
							$this->db->qn('user_id_from'),
							$this->db->qn('user_id_to'),
							$this->db->qn('date_time'),
							$this->db->qn('subject'),
							$this->db->qn('message')
							)
						)
					->values(implode(',', $messages));

				$this->db->setQuery($query);
				$this->db->execute();
			}

			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'));

			return false;
		}

		return 1;
	}
}
