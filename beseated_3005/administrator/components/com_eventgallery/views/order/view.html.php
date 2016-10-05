<?php
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
jimport( 'joomla.html.pagination');


class EventgalleryViewOrder extends EventgalleryLibraryCommonView
{

    /**
     * @var JForm
     */
    protected $form;
    protected $item;
    protected $state;

    /**
     * Display the view
     * @param null $tpl
     * @return bool|mixed
     */
    public function display($tpl = null)
    {
        // Initialiase variables.
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
                JError::raiseError(500, implode("\n", $errors));
                return false;
            }
        $this->addToolbar();
        EventgalleryHelpersEventgallery::addSubmenu('order');      
        $this->sidebar = JHtmlSidebar::render();
        return parent::display($tpl);
    }

    private function addToolbar() {

        JToolbarHelper::title(  JText::_( 'COM_EVENTGALLERY_ORDER' ) .' '. $this->item->getDocumentNumber() );

        JToolbarHelper::apply('order.apply');
        JToolbarHelper::save('order.save');
        JToolbarHelper::cancel( 'order.cancel' , JText::_( 'JTOOLBAR_CLOSE' ));

        $bar = JToolbar::getInstance('toolbar');

        // Add a resend mail button.
                
        $bar->appendButton('Confirm', 'COM_EVENTGALLERY_ORDER_RESEND_MAIL_ALERT', 'mail', 'COM_EVENTGALLERY_ORDER_RESEND_MAIL',  'order.resendmail', false);
        


    }

}
