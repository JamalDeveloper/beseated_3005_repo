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
 * The Beseated Registration Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerRegistration extends JControllerAdmin
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
	public function getModel($name = 'Registration', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function save()
	{
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$data      = $input->get('jform',array(),'array');
		$email     = trim($data['email']);
		$password  = trim($data['password']);
		$password2 = trim($data['password2']);
		$flg       = 0;

		if($password === $password2)
		{
			$flg = 1;
		}

		$model    = $this->getModel();
		$response = $model->save($data);

		if($response ==1)
		{
			$app      = JFactory::getApplication();
			$menu     = $app->getMenu();
			$menuItem = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
			$app->redirect('index.php?option=com_users&view=login&Itemid='.$menuItem->id,JText::_('COM_BCTED_REGISTRATION_CREATED_SUCCESSFULLY'));
		}
		else if($response == 701)
		{
			$app      = JFactory::getApplication();
			$menu     = $app->getMenu();
			$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=registration', true );
			$app->redirect('index.php?option=com_beseated&view=registration&Itemid='.$menuItem->id,JText::_('COM_BCTED_REGISTRATION_USERNAME_ALREADY_REGISTERED'));
		}
		else if($response == 702)
		{
			$app      = JFactory::getApplication();
			$menu     = $app->getMenu();
			$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=registration', true );
			$app->redirect('index.php?option=com_beseated&view=registration&Itemid='.$menuItem->id,JText::_('COM_BCTED_REGISTRATION_EMAIL_ALREADY_REGISTERED'));
		}
		else
		{
			$app      = JFactory::getApplication();
			$menu     = $app->getMenu();
			$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=registration', true );
			$app->redirect('index.php?option=com_beseated&view=registration&Itemid='.$menuItem->id,JText::_('COM_BCTED_REGISTRATION_NOT_REGISTERED'));
		}
	}
}
