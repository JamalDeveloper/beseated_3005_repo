<?php
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;


jimport('joomla.application.component.view');


class EventgalleryViewCommentMail extends EventgalleryLibraryCommonView
{
    function display($tpl = NULL)
    {
        $this->_loadData();
        parent::display($tpl);
    }

    function loadTemplate($tpl = NULL)
    {
        $this->_loadData();
        return parent::loadTemplate($tpl);
    }

    function _loadData()
    {

        $model = $this->getModel();
        $newComment = $model->getData(JRequest::getVar('newCommentId'));
        $file = $model->getFile($newComment->id);


        $this->assignRef('newComment', $newComment);
        $this->assignRef('file', $file);
    }
}

