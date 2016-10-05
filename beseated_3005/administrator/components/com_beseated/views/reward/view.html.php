<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Beseated
 * @author     jamal <derdiwalanawaz@gmail.com>
 * @copyright  Copyright (C) 2015. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 *
 * @since  1.6
 */
class BeseatedViewReward extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}


		$this->addToolbar();


		parent::display($tpl);


	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function addToolbar()
	{
		$input = JFactory::getApplication()->input;



		// Hide Joomla Administrator Main menu
		$input->set('hidemainmenu', true);

		$isNew = ($this->item->reward_id == 0);

		if ($isNew)
		{
			$title = JText::_('COM_BESEATED_MANAGE_REWARD_NEW');
		}
		else
		{
			$title = JText::_('COM_BESEATED_MANAGE_REWARD_EDIT');
		}

		JToolBarHelper::title($title, 'Reward');
		JToolBarHelper::save('reward.save');
		JToolBarHelper::cancel(
			'reward.cancel',
			$isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
		);
	}
}
