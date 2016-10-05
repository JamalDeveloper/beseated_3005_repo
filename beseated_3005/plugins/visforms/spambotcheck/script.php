<?php

/**
 * @version		$Id: script.php 22354 2011-11-07 05:01:16Z github_bot $
 * @package		com_visforms
 * @subpackage	plg_visforms_spamcheck
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

class plgvisformsspambotcheckInstallerScript
{
        /**
         * Constructor
         *
         * @param   JAdapterInstance  $adapter  The object responsible for running this script
         */
        public function __constructor(JAdapterInstance $adapter)
        {
            // Log to a specific text file.
            JLog::addLogger(
                array(
                    'text_file' => 'visforms_update.php',
                    'text_entry_format' => '{PRIORITY} {MESSAGE}',
                    'logger' => 'FormattedtextVisforms'
                ),
                JLog::ALL,
                array('com_visforms')
            );
        }
 
        /**
         * Called before any type of action
         *
         * @param   string  $route  Which action is happening (install|uninstall|discover_install)
         * @param   JAdapterInstance  $adapter  The object responsible for running this script
         *
         * @return  boolean  True on success
         */
        //public function preflight($route, JAdapterInstance $adapter);
 
        /**
         * Called after any type of action
         *
         * @param   string  $route  Which action is happening (install|uninstall|discover_install)
         * @param   JAdapterInstance  $adapter  The object responsible for running this script
         *
         * @return  boolean  True on success
         */
       // public function postflight($route, JAdapterInstance $adapter);
 
        /**
         * Called on installation
         *
         * @param   JAdapterInstance  $adapter  The object responsible for running this script
         *
         * @return  boolean  True on success
         */
        public function install(JAdapterInstance $adapter) {
			
			//Give a warning if cURL is not enabled on system; plugin will not be able to identify spammer
			$extension = 'curl';
			if (!extension_loaded($extension)) {
				JFactory::getApplication()->enqueueMessage(JText::_('PLG_VISFORMS_SPAMBOTCHECK_CURL_MISSING'), 'warning');
			}
		}
 
        /**
         * Called on update
         *
         * @param   JAdapterInstance  $adapter  The object responsible for running this script
         *
         * @return  boolean  True on success
         */
        public function update(JAdapterInstance $adapter)
	{
		//include update features of all versions between the installed release and the used update release
		if (version_compare($this->oldRelease, '3.1.0', 'lt'))
		{
            JLog::add('*** Remove params of Plugin Visforms Spambotcheck ***', JLog::INFO, 'com_visforms');
            //remove params
            $db = JFactory::getDbo();
            $db->setQuery('UPDATE #__extensions SET params = ' .
				$db->quote( "{}") .
				' WHERE name = "plg_visforms_spambotcheck" AND folder = "visforms"' );
            try
            {
                $result = $db->execute();
                JLog::add('Params removed', JLog::INFO, 'com_visforms');
            }
            catch (Exception $e) 
            {
                JLog::add('Unable to remove params: ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
            }
		}
	}
 
        /**
         * Called on uninstallation
         *
         * @param   JAdapterInstance  $adapter  The object responsible for running this script
         */
       // public function uninstall(JAdapterInstance $adapter);
}

?>
