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

jimport( 'joomla.application.component.modellist' );

class EventgalleryModelPicasasync extends JModelList
{

    /**
     * Checks if the foldername esists in the database
     *
     * @param $foldername
     * @return bool true if the folder exists in the database
     */
    public function eventExists($foldername) {

        $db = JFactory::getDBO();

        // update the file table
        $query = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__eventgallery_folder'))
            ->where('folder=' . $db->quote($foldername));
        $db->setQuery($query);
        $db->execute();
        $affectedRows = $db->getAffectedRows();

        return $affectedRows>0?true: false;

    }

    public function addEvent($album) {

        $db = JFactory::getDbo();
        $db->setQuery('SELECT MAX(ordering) FROM #__eventgallery_folder');
        $max = $db->loadResult();


        $user = JFactory::getUser();
        $timestamp = strtotime($album->date);
        // update the file table
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__eventgallery_folder'))
            ->columns(
                'folder,description,published,'
                .'userid,date,created,modified,ordering,foldertypeid'
            )
            ->values(implode(',',array(
                $db->quote($album->folder),
                $db->quote($album->description),
                '0',
                $db->quote($user->id),
                $db->quote(date('Y-m-d H:i:s',$timestamp)),
                'now()',
                'now()',
                $max+1,
            	1
            )));
        ;
        $db->setQuery($query);
        $db->execute();


    }

}
