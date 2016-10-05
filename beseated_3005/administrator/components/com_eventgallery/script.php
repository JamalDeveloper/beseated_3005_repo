<?php
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


//the name of the class must be the name of your component + InstallerScript
//for example: com_contentInstallerScript for com_content.
class com_eventgalleryInstallerScript
{

    private $eventgalleryCliScripts = array(
        'eventgallery-sync.php'
    );

    /*
    * $parent is the class calling this method.
    * $type is the type of change (install, update or discover_install, not uninstall).
    * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
    * If preflight returns false, Joomla will abort the update and undo everything already done.
    */
    function preflight( /** @noinspection PhpUnusedParameterInspection */$type, $parent ) {


        // Get the extension ID
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query->select('extension_id')
            ->from('#__extensions')
            ->where($db->qn('element').' = '.$db->q('com_eventgallery'));
        $db->setQuery($query);
        $eid = $db->loadResult();

        if ($eid != null) {
            // Get the schema version
            $query = $db->getQuery(true);
            $query->select('version_id')
                ->from('#__schemas')
                ->where('extension_id = ' . $eid);
            $db->setQuery($query);
            $version = $db->loadResult();

            if (version_compare($version, '3.4.1_2015-08-11', 'gt')) {
                $msg = "<p>Downgrades are not supported. Please install the same or a newer version.</p>";
                JError::raiseWarning(100, $msg);
                return false;
            }
        }

        $folders = array(  
            JPATH_ROOT . '/administrator/components/com_eventgallery/controllers',
            JPATH_ROOT . '/administrator/components/com_eventgallery/media',
            JPATH_ROOT . '/administrator/components/com_eventgallery/models',
            JPATH_ROOT . '/administrator/components/com_eventgallery/views',
            //JPATH_ROOT . '/administrator/components/com_eventgallery/sql',
            JPATH_ROOT . '/components/com_eventgallery/controllers',
            JPATH_ROOT . '/components/com_eventgallery/helpers',
            JPATH_ROOT . '/components/com_eventgallery/language',
            JPATH_ROOT . '/components/com_eventgallery/library',
            JPATH_ROOT . '/components/com_eventgallery/media',
            JPATH_ROOT . '/components/com_eventgallery/models',
            JPATH_ROOT . '/components/com_eventgallery/tests',
            JPATH_ROOT . '/components/com_eventgallery/views'
        );

        $files = array(
            JPATH_ROOT . '/language/en-GB/en-GB.com_eventgallery.ini',
            JPATH_ROOT . '/language/de-DE/de-DE.com_eventgallery.ini',
            JPATH_ROOT . '/administrator/language/en-GB/en-GB.com_eventgallery.ini',
            JPATH_ROOT . '/administrator/language/en-GB/en-GB.com_eventgallery.sys.ini'
        );

        foreach($folders as $folder) {
            if (JFolder::exists($folder)) {
                JFolder::delete($folder);
            }
        }

        foreach($files as $file) {
            if (JFolder::exists($file)) {
                JFolder::delete($file);
            }
        }

        $this->_copyCliFiles($parent);
    }   

    /**
     * Copies the CLI scripts into Joomla!'s cli directory
     *
     * @param JInstaller $parent
     */
    private function _copyCliFiles($parent)
    {
        $src = $parent->getParent()->getPath('source');

        if(empty($this->eventgalleryCliScripts)) {
            return;
        }

        foreach($this->eventgalleryCliScripts as $script) {
            if(JFile::exists(JPATH_ROOT.'/cli/'.$script)) {
                JFile::delete(JPATH_ROOT.'/cli/'.$script);
            }
            if(JFile::exists($src.'/cli/'.$script)) {
                JFile::move($src.'/cli/'.$script, JPATH_ROOT.'/cli/'.$script);
            }
        }
    }


    function uninstall( /** @noinspection PhpUnusedParameterInspection */$parent ) {
        // remove CLI
		foreach($this->eventgalleryCliScripts as $script) {
            if(JFile::exists(JPATH_ROOT.'/cli/'.$script)) {
                JFile::delete(JPATH_ROOT.'/cli/'.$script);
            }
        }
	}
    
    function postflight( /** @noinspection PhpUnusedParameterInspection */$type, $parent )
    {

        $db = JFactory::getDbo();

        $plugins = Array(
                Array('system', 'picasaupdater'),
                Array('installer', 'eventgallery')
        );


        foreach($plugins as $pluginData) {
            
            // Let's get the information of the update plugin
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->qn('#__extensions'))
                ->where($db->qn('folder').' = '.$db->quote($pluginData[0]))
                ->where($db->qn('element').' = '.$db->quote($pluginData[1]))
                ->where($db->qn('type').' = '.$db->quote('plugin'));               
            $db->setQuery($query);
            $plugin = $db->loadObject();
            
            // If it's missing or enabledthere's nothing else to do
            if (!is_object($plugin) || $plugin->enabled)
            {
                continue;
            }


            // Otherwise, try to enable it
            $pluginObject = (object)array(
                'extension_id'  => $plugin->extension_id,
                'enabled'       => 1
            );

            try
            {
                $db->updateObject('#__extensions', $pluginObject, 'extension_id');
            }
            catch (Exception $e)
            {
            }
        }
    }

}