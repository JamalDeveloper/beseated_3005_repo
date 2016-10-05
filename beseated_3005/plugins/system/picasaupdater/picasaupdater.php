<?php
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


// no direct access
defined('_JEXEC') or die;

/**
 * Updates picasa albums during a request.
 *
 * @package     Joomla.Plugin
 * @since       2.5
 */
class plgSystemPicasaupdater extends JPlugin
{


/**
	 * Method to catch the onAfterDispatch event.
	 *
	 * This is where we setup the click-through content highlighting for.
	 * The highlighting is done with JavaScript so we just
	 * need to check a few parameters and the JHtml behavior will do the rest.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   2.5
	 */
	public function onAfterDispatch()
	{
		// Check that we are in the site application.
		if (JFactory::getApplication()->isAdmin())
		{
			return true;
		}

		/**
		* no need to run if we don't need to show any event gallery related stuff. 
		* This plugin only kicks in if we are in the component and all the classes 
		* are registered
		*/
		if (!class_exists('EventgalleryLibraryManagerFolder')) {
			return true;
		}

		$db = JFactory::getDbo();

		/**
		* find empty picasa folders
		**/
 		$query = $db->getQuery(true)	
                ->select('folder.*')
                ->from($db->quoteName('#__eventgallery_folder') . ' AS folder')
                ->join('LEFT', $db->quoteName('#__eventgallery_file') . ' AS file ON folder.folder = file.folder and file.published=1')
                ->where('file.file IS NULL')
                ->where('folder.foldertypeid=1');
                

		$db->setQuery($query);
		$entries = $db->loadObjectList();

        /**
         * @var EventgalleryLibraryFactoryFolder $folderFactory
         */
        $folderFactory = EventgalleryLibraryFactoryFolder::getInstance();

        // use the picasa reload magic to refresh empty albums.
        foreach ($entries as $entry)
        {
            $folder = $folderFactory->getFolder($entry->folder);
            // we need to call the method getAlbum in order to start the XML sync
            if (method_exists($folder, 'getAlbum') ) {
            	$folder->getAlbum();
            }
        }

        return true;
	}

}
