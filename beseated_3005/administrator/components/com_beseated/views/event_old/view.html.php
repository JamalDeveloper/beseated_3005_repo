<?php
/**
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Beseated Event View
 *
 * @since  0.0.1
 */
class BeseatedViewEvent extends JViewLegacy
{
	/**
	 * View Event
	 *
	 * @var   form
	 */
	protected $form = null;

	/**
	 * Display the Beseated Event view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   0.0.1
	 */
	public function display($tpl = null)
	{
		// Get the Data
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->eventTickets = $this->get('EventTickets');

		$app = JFactory::getApplication();
		$input = $app->input;
		$this->uniqueCode = $input->get('event_id',0,'int');

		/*if($input->get('event_id',0,'int'))
		{
			$this->uniqueCode = 0;
		}*/

		if(!$this->uniqueCode)
		{
			$prvUniqueCode = $input->get('unique_code','','string');

			if(empty($prvUniqueCode))
			{
				$this->uniqueCode = $this->getToken();
			}
			else
			{
				$this->uniqueCode = $prvUniqueCode;
			}
		}
		else
		{
			$this->uniqueCode = 0;
		}

		//echo "<pre>";print_r($this->uniqueCode);echo "<pre/>";

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new RuntimeException(implode('<br />', $errors), 500);

			return false;
		}

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   0.0.1
	 */
	protected function addToolBar()
	{
		$input = JFactory::getApplication()->input;

		// Hide Joomla Administrator Main menu
		$input->set('hidemainmenu', true);

		$isNew = ($this->item->event_id == 0);

		if ($isNew)
		{
			$title = JText::_('COM_BESEATED_MANAGE_EVENT_NEW');
		}
		else
		{
			$title = JText::_('COM_BESEATED_MANAGE_EVENT_EDIT');
		}

		JToolBarHelper::title($title, 'Event');
		JToolBarHelper::save('event.save');
		//JToolBarHelper::apply('event.apply');
		JToolBarHelper::cancel(
			'event.cancel',
			$isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
		);
	}

	public function getToken($length = 8)
	{
	    $token = "";
	    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
	    $codeAlphabet.= "0123456789";
	    $max = strlen($codeAlphabet) - 1;
	    for ($i=0; $i < $length; $i++) {
	        $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max)];
	    }
	    return $token;
	}

	public function crypto_rand_secure($min, $max)
	{
	    $range = $max - $min;
	    if ($range < 1) return $min; // not so random...
	    $log = ceil(log($range, 2));
	    $bytes = (int) ($log / 8) + 1; // length in bytes
	    $bits = (int) $log + 1; // length in bits
	    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
	    do {
	        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
	        $rnd = $rnd & $filter; // discard irrelevant bits
	    } while ($rnd >= $range);
	    return $min + $rnd;
	}
}
