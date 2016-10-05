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
jimport('joomla.filesystem.folder');
require_once JPATH_ROOT.'/components/com_eventgallery/config.php';

class EventgalleryLibraryFolderLocal extends EventgalleryLibraryFolder
{

    protected static $_maindir = NULL;

    /**
     * @param int $limitstart
     * @param int $limit
     * @param int $imagesForEvents if true load the main images at the first position
     * @return array
     */
    public function getFiles($limitstart = 0, $limit = 0, $imagesForEvents = 0) {

        // database handling
        // database handling
        $db = JFactory::getDBO();
        $query = $db->getQuery(true)
            ->select('file.*, COUNT(comment.id) AS '.$db->quoteName('commentCount'))
            ->from($db->quoteName('#__eventgallery_file') . ' AS file')
            ->join('INNER', $db->quoteName('#__eventgallery_folder') . ' AS folder ON folder.folder=file.folder and folder.published=1')
            ->join('LEFT', $db->quoteName('#__eventgallery_comment') . ' AS comment ON file.folder=comment.folder and file.file=comment.file')
            ->where('file.folder=' . $db->Quote($this->_foldername))
            ->where('file.published=1')
            ->group('file.id');

        if ($imagesForEvents == 0) {
            // find files which are allowed to show in a list
            $query->where('file.ismainimageonly=0')
                ->order('ordering DESC, file.file');
        } else {
            // find files and sort them with the main images first
            $query->order('file.ismainimage DESC, ordering DESC, file.file');
        }




        if ($limit != 0) {
            $db->setQuery($query, $limitstart, $limit);
        } else {
            $db->setQuery($query);
        }

        $entries = $db->loadObjectList();

        $result = Array();
        /**
         * @var EventgalleryLibraryFactoryFile $fileFactory
         */
        $fileFactory = EventgalleryLibraryFactoryFile::getInstance();

        foreach ($entries as $entry) {
            $result[] =  $fileFactory->getFile($entry->folder, $entry->file);
        }


        return $result;

    }

    /**
     * syncs a local folder
     *
     * @param string $foldername
     * @return int|void
     */
    public static function syncFolder($foldername) {

        $db = JFactory::getDBO();
        $user = JFactory::getUser();

        // delete the folder if it does not exist.
        $folderpath = COM_EVENTGALLERY_IMAGE_FOLDER_PATH.$foldername;
        if (!file_exists($folderpath)) {
            self::deleteFolder($foldername);
            return EventgalleryLibraryManagerFolder::$SYNC_STATUS_DELTED;
        }

        /**
         * the array holding the physical files
         */
        $files = Array();
        set_time_limit(120);

        // collect all the physical files.
        $dir=dir($folderpath);
        while ($elm = $dir->read())
        {
            if (is_file($folderpath.DIRECTORY_SEPARATOR.$elm))
                array_push($files, $elm);
        }

        // remove deleted files fromes from the database
        $query = $db->getQuery(true);
        $query->delete('#__eventgallery_file')
            ->where('folder='.$db->quote($foldername))
            ->where('file not in (\''.implode('\',\'',$files).'\')');
        $db->setQuery($query);
        $db->execute();

        $query = $db->getQuery(true);
        $query->select('file')
            ->from($db->quoteName('#__eventgallery_file'))
            ->where('folder='.$db->quote($foldername));
        $db->setQuery($query);
        $currentfiles = $db->loadAssocList(null, 'file');


        // update the files we already know.
        foreach($currentfiles as $file)
        {
            self::updateMetadata($folderpath.DIRECTORY_SEPARATOR.$file, $foldername, $file);
        }

        # add all new files of a directory to the database
        foreach(array_diff($files, $currentfiles) as $file)
        {
            if ($file == 'index.html' || $file == '.htaccess') {
                continue;
            }

            $filepath = $folderpath.DIRECTORY_SEPARATOR.$file;

            $created = date('Y-m-d H:i:s',filemtime($filepath));

            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__eventgallery_file'))
                ->columns(
                    'folder,file,published,'
                    .'userid,created,modified,ordering'
                    )
                ->values(implode(',',array(
                    $db->quote($foldername),
                    $db->quote($file),
                    '1',
                    $db->Quote($user->id),
                    $db->quote($created),
                    'now()',
                    0
                    )));
            $db->setQuery($query);
            $db->execute();

            self::updateMetadata($folderpath.DIRECTORY_SEPARATOR.$file, $foldername, $file);
        }

        $data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
        $dataHtaccess = "order deny,allow\ndeny from all";

        JFile::write($folderpath . "/index.html", $data);
		JFile::write($folderpath . "/.htaccess", $dataHtaccess);
        
        return EventgalleryLibraryManagerFolder::$SYNC_STATUS_SYNC;
    }

    /**
     * Deletes a local folder
     *
     * @param $foldername string
     */
    protected static function deleteFolder($foldername) {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query->delete('#__eventgallery_folder')
            ->where('folder='.$db->quote($foldername));
        $db->setQuery($query);
        $db->execute();

        $query = $db->getQuery(true);
        $query->delete('#__eventgallery_file')
            ->where('folder='.$db->quote($foldername));
        $db->setQuery($query);

    }

    /**
     * upaded meta information
     * @param $path
     * @param $foldername
     * @param $filename
     */
    public static function updateMetadata($path, $foldername, $filename) {

        /**
         * @var \Joomla\Registry\Registry $params
         */
        $params = JComponentHelper::getParams('com_eventgallery');

        $libPath = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_eventgallery'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'vendors'.DIRECTORY_SEPARATOR.'pel'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR;
        require_once($libPath.'PelJpeg.php');
        require_once($libPath.'PelTiff.php');

        /** @noinspection PhpUnusedLocalVariableInspection */
        @list($width, $height, $type, $attr) = getimagesize($path);
        $exif = array();

        try {
            $input_jpeg = new PelJpeg($path);

            $app1 = $input_jpeg->getExif();

            if ($app1) {
                $tiff = $app1->getTiff();
                $ifd0 = $tiff->getIfd();
                $exifData = $ifd0->getSubIfd(PelIfd::EXIF);



                if ($exifData) {
                    if ($data = $exifData->getEntry(PelTag::APERTURE_VALUE)) {
                        $value = $data->getValue();
                        $aperture = floor(pow(2, $value[0]/$value[1]/2)*10.0)/10.0;
                        $exif['fstop'] = sprintf('%.1f', $aperture);
                    }
                    if ($data = $exifData->getEntry(PelTag::FOCAL_LENGTH)) {
                        $value = $data->getValue();
                        $exif['focallength'] = sprintf('%.0f',$value[0]/$value[1]);
                    }
                    if ($data = $ifd0->getEntry(PelTag::MODEL)) {
                        $exif['model'] = $data->getText();
                    }
                    if ($data = $exifData->getEntry(PelTag::ISO_SPEED_RATINGS)) {
                        $exif['iso'] = $data->getText();
                    }

                    // we need to store the image size differently if we rotate the image later.
                    if ($params->get('use_autorotation',1)==1 && $ifd0 != null) {

                        $orientation = $ifd0->getEntry(PelTag::ORIENTATION);

                        if ($orientation != null) {
                            if ($orientation->getValue()==6 || $orientation->getValue()==8) {
                                $tempWidth = $width;
                                $width = $height;
                                $height = $tempWidth;
                            }
                        }
                    }
                }


            }
        } catch (Exception $e) {

        }

        $exifJson = json_encode($exif);

        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query->update("#__eventgallery_file");
        $query->set("width=".$db->quote($width));
        $query->set("height=".$db->quote($height));
        $query->set("exif=".$db->quote($exifJson));
        $query->where('folder='.$db->quote($foldername));
        $query->where('file='.$db->quote($filename));
        $db->setQuery($query);
        $db->execute();

        Pel::clearExceptions();
        unset($input_jpeg);

    }


    /**
     * adds new folder to the database
     * @return array with error Strings
     */
    public static function addNewFolders() {

        $errors = Array();
        $db = JFactory::getDBO();
        $user = JFactory::getUser();

        $folders = Array();

        if (file_exists(COM_EVENTGALLERY_IMAGE_FOLDER_PATH)) {
            $verzeichnis = dir(COM_EVENTGALLERY_IMAGE_FOLDER_PATH);
        } else {
            return $errors;
        }

        # Hole die verfügbaren Verzeichnisse
        while ($elm = $verzeichnis->read())
        { //sucht alle Verzeichnisse mit Bilder
            if (is_dir(COM_EVENTGALLERY_IMAGE_FOLDER_PATH.$elm) && $elm!='.' && $elm!='..' && !preg_match("/.cache/",$elm))
            {
                if (is_dir(COM_EVENTGALLERY_IMAGE_FOLDER_PATH.$elm.DIRECTORY_SEPARATOR ))
                {
                    array_push($folders, $elm);
                }
            }
        }

        $query = $db->getQuery(true);
        $query->select('folder')
            ->from($db->quoteName('#__eventgallery_folder'));
        $db->setQuery($query);
        $currentfolders = $db->loadAssocList(null, 'folder');

        # Füge Verzeichnisse in die DB ein
        foreach(array_diff($folders, $currentfolders) as $folder)
        {
            #Versuchen wir, ein paar Infos zu erraten
            if (strcmp($folder,JFolder::makeSafe($folder))!=0) {
                $errors[] = JText::sprintf('COM_EVENTGALLERY_SYNC_DATABASE_SYNC_ERROR_FOLDERNAME',$folder, JFolder::makeSafe($folder));
                continue;
            }


            $break = false;
            foreach($currentfolders as $currentfolder) {
                if(strcasecmp($folder, $currentfolder) == 0 ) {
                    $errors[] = JText::sprintf('COM_EVENTGALLERY_SYNC_DATABASE_SYNC_ERROR_DUPLICATE_FOLDERNAME', $folder, $currentfolder);
                    $break = true;
                }
            }

            if ($break) {
                continue;
            }

            $date = "";
            $temp = array();
            $created = date('Y-m-d H:i:s',filemtime(COM_EVENTGALLERY_IMAGE_FOLDER_PATH.$folder));

            if (preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/",$folder, $temp))
            {
                $date = $temp[0];
                $description = str_replace($temp[0],'',$folder);
            }
            else {
                $description = $folder;
            }

            $db = JFactory::getDbo();
            $db->setQuery('SELECT MAX(ordering) FROM #__eventgallery_folder');
            $max = $db->loadResult();

            $description = trim(str_replace("_", " ", $description));

            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__eventgallery_folder'))
                ->columns(
                    'folder,published,date,description,'
                    .'userid,created,modified,ordering'
                    )
                ->values(implode(',', array(
                    $db->quote($folder),
                    '0',
                    $db->quote($date),
                    $db->quote($description),
                    $db->quote($user->id),
                    $db->quote($created),
                    'now()',
                    $max+1
                    )));
            $db->setQuery($query);
            $db->execute();

        }

        return $errors;
    }

    public static function getFileFactory() {
        return EventgalleryLibraryFactoryFileLocal::getInstance();
    }
}
