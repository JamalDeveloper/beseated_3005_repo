<?php

/**
 * @version		$Id: script.php 22354 2011-11-07 05:01:16Z github_bot $
 * @package		com_visforms
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.log.log');
class JLogLoggerFormattedtextVisforms extends JLogLoggerFormattedtext
{
	/**
	 * @var array Translation array for JLogEntry priorities to SysLog priority names.
	 * @since 11.1
	 */
	protected $priorities = array(
		JLog::EMERGENCY => 'EMG',
		JLog::ALERT => 'ALT',
		JLog::CRITICAL => 'CRI',
		JLog::ERROR => 'ERR',
		JLog::WARNING => 'WRN',
		JLog::NOTICE => 'NTC',
		JLog::INFO => 'INF',
		JLog::DEBUG => 'DBG');
}

class com_visformsInstallerScript
{
    private $release;
    private $oldRelease;
    private $minimum_joomla_release;
    private $status;
    private $versionsWithDatabaseChanges;
  
    public function __construct(JAdapterInstance $adapter) 
    {
        $this->status = new stdClass();
        $this->status->fixTableVisforms = array();
        $this->status->modules = array();
        $this->status->plugins = array();
		$this->status->tables = array();
        $this->status->folders = array();
        $this->status->component = array();
		$this->status->messages = array();
		$this->release = $adapter->get( "manifest" )->version; 
		$this->minimum_joomla_release = $adapter->get( "manifest" )->attributes()->version;
        $this->oldRelease = "";
        //list all no rollback versions here
        $this->versionsWithDatabaseChanges = array('3.1.0', '3.2.0', '3.3.0', '3.4.0', '3.4.1', '3.5.0', '3.5.1', '3.5.3', '3.6.0', '3.6.3', '3.6.5', '3.7.0', '3.7.1');
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
   protected function findVfMinVersion()
   {
       $count = count($this->versionsWithDatabaseChanges);
       for ($i = 0; $i < $count - 1; $i++)
       {
           $j = $i + 1;
           if (((array_key_exists($j, $this->versionsWithDatabaseChanges))) && (version_compare($this->versionsWithDatabaseChanges[$i], $this->oldRelease, 'lt')) && (version_compare($this->versionsWithDatabaseChanges[$j], $this->oldRelease, 'ge')))
           {
               return  $this->versionsWithDatabaseChanges[$j];
           }
       }
       //we have reached the last array element and condition was not met for the pair last array key /second last array key, so we return the last element of the array
       return $this->versionsWithDatabaseChanges[$count - 1];
   }

	/**
	 * Called before any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
    public function preflight($route, JAdapterInstance $adapter) {
		$jversion = new JVersion();
        $msg = "";
		$date = new JDate('now');	

		// abort if the current Joomla release is to old
		if( version_compare( $jversion->getShortVersion(), $this->minimum_joomla_release, 'lt' ) ) {
			Jerror::raiseWarning(null, JText::_('COM_VISFORMS_WRONG_JOOMLA_VERSION') .$this->minimum_joomla_release);
			return false;
		}

		// abort if the component being installed is not newer than the currently installed version
		if ( $route == 'update' ) 
		{
			JLog::add("*** Start Update: " . $date . " ***" , JLog::INFO, 'com_visforms');
			//set permissions for css files (which might be edited through backend and set to readonly) so they can be updated
			$files = array ('visforms.css', 'visforms.min.css', 'bootstrapform.css');
			foreach ($files as $cssfile)
			{
				@chmod(JPath::clean(JPATH_ROOT . '/media/com_visforms/css/' . $cssfile), 0755);
			}
			$this->oldRelease = $this->getExtensionParam('version');
			$rel = $this->oldRelease . JText::_('COM_VISFORMS_TO') . $this->release;
			JLog::add("Installed version is: " . $this->oldRelease . " Update version is : " . $this->release , JLog::INFO, 'com_visforms');
            //only prevent reinstalling Visforms with same Version, if a version comes with database changes
            $updateMinVerison = $this->findVfMinVersion();
			if ((!empty($this->oldRelease)) && (version_compare( $this->release, $updateMinVerison, 'le')))
			{
				JLog::add("Update aborted due to wrong version sequence: " . $rel , JLog::ERROR, 'com_visforms');
				Jerror::raiseWarning(null, JText::_('COM_VISFORMS_WRONG_VERSION') . $rel);
				return false;
			}
            else
            {
                //process preflight for specific versions
                if(version_compare($this->oldRelease, '2.0.0', 'lt'))
                {
					JLog::add("Update aborted due to incompatible version with sequence: " . $rel , JLog::ERROR, 'com_visforms');
                    Jerror::raiseWarning(null, JText::_('COM_VISFORMS_INCOMPATIBLE_VERSION') . $rel);
					return false;
                }
            }
		}
        else 
		{
			JLog::add("*** Start Install: " . $date  . " ***", JLog::INFO, 'com_visforms');
			JLog::add("Version is: " . $this->release, JLog::INFO, 'com_visforms');
			$rel = $this->release; 
		}
		//create installation success message (only display if complete installation is executed successfully)
		if ($route == 'update') 
        {
			$msg =  JText::_('COM_VISFORMS_UPDATE_VERSION') . $rel . JText::_('COM_VISFORMS_SUCESSFULL');
			if (version_compare($this->oldRelease, '3.6.2', 'lt'))
			{
				$msg .= '<br /><strong style="color: red;">' . JText::_('COM_VISORMS_DELETE_TEMPLATE_OVERRIDES') . '</strong>';
			}
		}
		else if ($route == 'install') {
			$msg = JText::_('COM_VISFORMS_INSTALL_VERSION') . $rel . JText::_('COM_VISFORMS_SUCESSFULL');
		}

        $this->status->component = array('name' => 'visForms', 'type' => $route, 'msg' => $msg);
	}

	/**
	 * Called after any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
    public function postflight($route, JAdapterInstance $adapter) 
    {
        if($route == 'update')
        {
            //run specific component adaptation for specific update versions
            if ((!empty($this->oldRelease)) && ((version_compare($this->oldRelease, '3.0.0', 'ge')) || (version_compare($this->oldRelease, '2.1.0', 'lt'))))
            {
                foreach ($this->versionsWithDatabaseChanges as $versionWithDatabaseChanges)
                {
                    if (version_compare($this->oldRelease, $versionWithDatabaseChanges, 'lt'))
                    {
                        $postFlightFunctionPostfix = str_replace('.', '_', $versionWithDatabaseChanges);
                        $postFlightFunctionName = 'postFlightForVersion'.$postFlightFunctionPostfix;
                        if (method_exists($this, $postFlightFunctionName))
                        {
                            $this->$postFlightFunctionName();
                        }
                    }
                }
            }
        }
        
        if($route == 'install')
        {
            $this->createFolder(array('images', 'visforms'));
        }
        
        //Install or update all extensions that come with component visForms
        $this->installExtensions($route, $adapter);
	}

	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
    public function install(JAdapterInstance $adapter) 
	{
		//JFactory::getApplication()->enqueueMessage(JText::_('COM_VISFORMS_INSTALL_MESSAGE'));
	}

	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	//public function update(JAdapterInstance $adapter);

	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function uninstall(JAdapterInstance $adapter)
	{

		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$tablesAllowed = $db->getTableList(); 
		
		$date = new JDate('now');
		JLog::add("*** Start Uninstall: " . $date . "***" , JLog::INFO, 'com_visforms');
		JLog::add("Version is: " . $this->release, JLog::INFO, 'com_visforms');		

		if ($db) 
		{
			JLog::add("*** Try to delete tables ***", JLog::INFO, 'com_visforms');	
            //delete all visforms related tables in database
			$db->setQuery("SELECT * FROM #__visforms");
			try
			{
				$forms = $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				JLog::add('Unable to load form list from database: ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
			}
	
			$n=count($forms);
			for ($i=0; $i < $n; $i++)
			{
				$row = $forms[$i];
				$tnfulls = array($db->getPrefix() . "visforms_".$row->id, $db->getPrefix() . "visforms_".$row->id . "_save");
				foreach ($tnfulls as $tnfull)
				{
					if (in_array($tnfull, $tablesAllowed)) {
						$tn = str_replace($db->getPrefix(), "#__", $tnfull);
						$db->setQuery("drop table if exists ".$tn);
						try
						{
							$db->execute();
							$message = JText::sprintf('COM_VISFORMS_SAVE_DATA_TABLE_DROPPED', $row->id);
							$this->status->tables[] = array('message' => $message);
							JLog::add('Table dropped: ' . $tn, JLog::INFO, 'com_visforms');
						}
						catch (RuntimeException $e)
						{
							$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
							$this->status->tables[] = array('message' => $message);
							JLog::add('Unable to drop table: ' . $tn . ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
						}
					}
				}
			}

			$db->setQuery("drop table if exists #__visfields");
			try
            {
                $db->execute();
				$message = JText::_('COM_VISFORMS_FIELD_TABLE_DROPPED');
                $this->status->tables[] = array('message' => $message);
				JLog::add('Table dropped: #__visfields', JLog::INFO, 'com_visforms');
            }
            catch (RuntimeException $e)
            {
				$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
                $this->status->tables[] = array('message' => $message);
				JLog::add('Unable to drop table: #__visfields, ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
            }			
			
			$db->setQuery("drop table if exists #__visforms");
			try
            {
                $db->execute();
				$message = JText::_('COM_VISFORMS_FORMS_TABLE_DROPPED');
                $this->status->tables[] = array('message' => $message);
				JLog::add('Table dropped: #__visforms', JLog::INFO, 'com_visforms');
            }
            catch (RuntimeException $e)
            {
				$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
                $this->status->tables[] = array('message' => $message);
				JLog::add('Unable to drop table: #__visforms, ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
            }
		}
		
        //uninstall plugins
		JLog::add("*** Try to uninstall extensions ***", JLog::INFO, 'com_visforms');
        $manifest = $adapter->getParent()->manifest;
        $plugins = $manifest->xpath('plugins/plugin');
        foreach ($plugins as $plugin)
        {
            $name = (string)$plugin->attributes()->plugin;
            $group = (string)$plugin->attributes()->group;
            $plgWhere = $db->quoteName('type') . ' = ' . $db->quote('plugin') . ' AND ' . $db->quoteName('element') . ' = ' . $db->quote($name) . ' AND ' . $db->quoteName('folder') . ' = ' . $db->quote($group);
            $query = $db->getQuery(true);
            $query
                ->select($db->quoteName('extension_id'))
                ->from($db->quoteName('#__extensions'))
                ->where($plgWhere);
            $db->setQuery($query);
			try
			{
				$extensions = $db->loadColumn();
			}
			catch (RuntimeException $e)
            {
				JLog::add('Unable to get extension_id: ' . $name . ', '. $e->getMessage(), JLog::ERROR, 'com_visforms');
				continue;
            }
				if (count($extensions))
				{
					foreach ($extensions as $id)
					{
						$installer = new JInstaller;
						try
						{
							$result = $installer->uninstall('plugin', $id);
							$this->status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
							if ($result)
							{
								JLog::add('Plugin sucessfully removed: ' . $name, JLog::INFO, 'com_visforms');
							}
							else
							{
								JLog::add('Removal of plugin failed: ' . $name, JLog::ERROR, 'com_visforms');
							}
						}
						catch (RuntimeException $e)
						{
							JLog::add('Removal of plugin failed: ' . $name . ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
						}
					}
				}		
            }
        //uninstall modules
        $modules = $manifest->xpath('modules/module');
        foreach ($modules as $module)
        {
            $name = (string)$module->attributes()->module;
            $client = (string)$module->attributes()->client;
            if (is_null($client))
            {
                $client = 'site';
            }
            if($client == 'site')
            {
                $client_id = 0;
            }
            else
            {
                $client_id = 1;
            }
            $db = JFactory::getDbo();
            $modWhere = $db->quoteName('type') . ' = ' . $db->quote('module') . ' AND ' . $db->quoteName('element') . ' = ' . $db->quote($name) . ' AND ' . $db->quoteName('client_id') . ' = ' . $db->quote($client_id);
            $query = $db->getQuery(true);
            $query
                ->select($db->quoteName('extension_id'))
                ->from($db->quoteName('#__extensions'))
                ->where($modWhere);
            $db->setQuery($query);
			try
			{
				$extensions = $db->loadColumn();
			}
			catch (RuntimeException $e)
            {
				JLog::add('Unable to get extension_id: ' . $name . ', '. $e->getMessage(), JLog::ERROR, 'com_visforms');
				continue;
            }
				if (count($extensions))
				{
					foreach ($extensions as $id)
					{
						$installer = new JInstaller;
						try
						{
							$result = $installer->uninstall('module', $id);
							$this->status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
							if ($result)
							{
								JLog::add('Module sucessfully removed: ' . $name, JLog::INFO, 'com_visforms');
							}
							else
							{
								JLog::add('Removal of module failed: ' . $name, JLog::ERROR, 'com_visforms');
							}
						}
						catch (RuntimeException $e)
						{
							JLog::add('Removal of module failed: ' . $name . ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
						}
					}
				}
            }
        
        //delete folders in image folder
		JLog::add("*** Try to delete custom files and folders ***", JLog::INFO, 'com_visforms');
        jimport('joomla.filesystem.file');      
        $folder  = JPATH_ROOT.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'visforms';
        if(JFolder::exists($folder))
        {
            $result = array();
			try
			{
				$result[]     = JFolder::delete($folder);
				$this->status->folders[] = array('folder' => $folder, 'result' => $result[0]); 
				if ($result[0])
				{
					JLog::add("Folder successfully removed: " . $folder, JLog::INFO, 'com_visforms');
				}
				else
				{
					JLog::add('Problems removing folder: ' . $folder, JLog::ERROR, 'com_visforms');
				}
			}
			catch (RuntimeException $e)
			{
				JLog::add('Problems removing folder: ' . $folder. ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
			}
			
        }
        
        //delete visuploads folder
        $folder  = JPATH_ROOT.DIRECTORY_SEPARATOR.'visuploads';
        if(JFolder::exists($folder))
        {
            $result = array();
			try
			{
				$result[]     = JFolder::delete($folder);
				$this->status->folders[] = array('folder' => $folder, 'result' => $result[0]);  
				if ($result[0])
				{
					JLog::add("Folder successfully removed: " . $folder, JLog::INFO, 'com_visforms');
				}
				else
				{
					JLog::add('Problems removing folder: ' . $folder, JLog::ERROR, 'com_visforms');
				}
			}
			catch (RuntimeException $e)
			{
				JLog::add('Problems removing folder: ' . $folder. ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
			}
        }
        
        $this->uninstallationResults();
	}
    
    /*
     * Method to show installation results in a nicely formatted html table
     * HTML is captured by content buffer in adapter install function
     * 
     * @return void
     */
	
	 private function installationResults($route)
    {		
        $language = JFactory::getLanguage();
        $language->load('com_visforms');
        $rows = 0; 
		$extension_message = array();
		$extension_message[] = '<h2 style="text-align: center;">' . JText::_('COM_VISFORMS_INSTALL_MESSAGE') .'</h2>';
		$extension_message[] = ($route == 'update') ? '<img src="http://www.vi-solutions.de/images/logo-banner-u.png" alt="visForms" align="right" />' : '<img src="http://www.vi-solutions.de/images/logo-banner.png" alt="visForms" align="right" />';
        $extension_message[] = '<h2>'.JText::_('COM_VISFORMS_INSTALLATION_STATUS').'</h2>';
        $extension_message[] = '<table class="adminlist table table-striped">';
		$extension_message[] = '<thead>';
		$extension_message[] = '<tr>';
		$extension_message[] = '<th class="title" colspan="2" style="text-align: left;">'.JText::_('COM_VISFORMS_EXTENSION').'</th>';
		$extension_message[] = '<th width="30%" style="text-align: left;">'.JText::_('COM_VISFORMS_STATUS').'</th>';
		$extension_message[] = '</tr>';
		$extension_message[] = '</thead>';
        $extension_message[] = '<tfoot>';
        $extension_message[] = '<tr>';
        $extension_message[] = '<td colspan="3"></td>';
        $extension_message[] = '</tr>';
        $extension_message[] = '</tfoot>';
        $extension_message[] = '<tbody>';
        $extension_message[] = '<tr class="row0">';
        $extension_message[] = '<td class="key" colspan="2">'.JText::_('COM_VISFORMS_COMPONENT').'</td>';
        $extension_message[] = '<td><strong>'.$this->status->component['msg'].'</strong></td>';
        $extension_message[] = '</tr>';
        if (count($this->status->modules)):
			$extension_message[] = '<tr>';
			$extension_message[] = '<th>'.JText::_('COM_VISFORMS_MODULE').'</th>';
			$extension_message[] = '<th>'.JText::_('COM_VISFORMS_CLIENT').'</th>';
			$extension_message[] = '<th></th>';
			$extension_message[] = '</tr>';
			foreach ($this->status->modules as $module):
				$module_message = "";
				if (!isset($module['type']))
				{
					$plugin_message = ($module['result']) ? '<strong>'. JText::_('COM_VISFORMS_REMOVED') : '<strong style="color: red">' . JText::_('COM_VISFORMS_NOT_REMOVED');
				}
				else
				{
					$module_message = ($module['result']) ? (($module['type'] == 'install') ? '<strong>' . JText::_('COM_VISFORMS_INSTALLED') : '<strong>' . JText::_('COM_VISFORMS_UPDATED')) : (($module['type'] == 'install') ? '<strong style="color: red">' . JText::_('COM_VISFORMS_NOT_INSTALLED') : '<strong style="color: red">' . JText::_('COM_VISFORMS_NOT_UPDATED'));
				}
				$extension_message[] = '<tr class="row'.(++$rows % 2).'">';
				$extension_message[] = '<td class="key">'.$module['name'].'</td>';
				$extension_message[] = '<td class="key">'.ucfirst($module['client']).'</td>';
				$extension_message[] = '<td>'.$module_message.'</strong></td>';
				$extension_message[] = '</tr>';
			endforeach;
        endif;
        if (count($this->status->plugins)):
			$extension_message[] = '<tr>';
			$extension_message[] = '<th>'.JText::_('COM_VISFORMS_PLUGIN').'</th>';
            $extension_message[] = '<th>'.JText::_('COM_VISFORMS_GROUP').'</th>';
			$extension_message[] = '<th></th>';
			$extension_message[] = '</tr>';
            foreach ($this->status->plugins as $plugin):
				$plugin_message = '';
				if (!isset($plugin['type']))
				{
					$plugin_message = ($plugin['result']) ? '<strong>'. JText::_('COM_VISFORMS_REMOVED') : '<strong style="color: red">' . JText::_('COM_VISFORMS_NOT_REMOVED');
				}
				else
				{
					$plugin_message = ($plugin['result']) ? (($plugin['type'] == 'install') ? '<strong>' . JText::_('COM_VISFORMS_INSTALLED') : '<strong>' . JText::_('COM_VISFORMS_UPDATED')) : (($plugin['type'] == 'install') ? '<strong style="color: red">' . JText::_('COM_VISFORMS_NOT_INSTALLED') : '<strong style="color: red">' . JText::_('COM_VISFORMS_NOT_UPDATED'));
				}
                $extension_message[] = '<tr class="row'.(++$rows % 2).'">';
                $extension_message[] = '<td class="key">'.ucfirst($plugin['name']).'</td>';
                $extension_message[] = '<td class="key">'.ucfirst($plugin['group']).'</td>';
                $extension_message[] = '<td>'. $plugin_message.'</strong></td>';
				$extension_message[] = '</tr>';
            endforeach;
        endif;
        if (count($this->status->folders)):
			$extension_message[] = '<tr>';
            $extension_message[] = '<th colspan="2">'.JText::_('COM_VISFORMS_FILESYSTEM').'</th>';
			$extension_message[] = '<th></th>';
			$extension_message[] = '</tr>';
            foreach ($this->status->folders as $folder):
				$folder_message = '';
				$folder_message = ($folder['result']) ? '<strong>' . JText::_('COM_VISFORMS_CREATED') :  '<strong style="color: red">' . JText::_('COM_VISFORMS_NOT_CREATED');
                $extension_message[] = '<tr class="row'.(++$rows % 2).'">';
                $extension_message[] = '<td class="key" colspan="2">'.ucfirst($folder['folder']).'</td>';
                $extension_message[] = '<td>'.$folder_message.'</strong></td>';
				$extension_message[] = '</tr>';
            endforeach;
        endif;
        if (count($this->status->fixTableVisforms)):
			$extension_message[] = '<tr>';
            $extension_message[] = '<th colspan="2">'.JText::_('COM_VISFORMS_UPDATE_FIX_FOR_FORM_DATA').'</th>';
			$extension_message[] = '<th></th>';
			$extension_message[] = '</tr>';
            foreach ($this->status->fixTableVisforms as $recordset):
				$table_message = '';
				$table_message = ($recordset['result']) ? '<strong>' . $recordset['resulttext'] :  '<strong style="color: red">' . $recordset['resulttext'];
                $extension_message[] = '<tr class="row'.(++$rows % 2).'">';
                $extension_message[] = '<td class="key" colspan="2">'.JText::_('COM_VISFORMS_FORM_WITH_ID') . $recordset['form'].'</td>';
                $extension_message[] = '<td>'.$table_message.'</strong></td>';
			$extension_message[] = '</tr>';
            endforeach;
        endif;
		if (count($this->status->messages)) :
			$extension_message[] = '<tr>';
            $extension_message[] = '<th colspan="2">'.JText::_('COM_VISFORMS_MESSAGES').'</th>';
			$extension_message[] = '<th></th>';
			$extension_message[] = '</tr>';
			foreach ($this->status->messages as $message)
			{
				$extension_message[] = '<tr class="row'.(++$rows % 2).'">';
                $extension_message[] = '<td class="key" colspan="2"></td>';
                $extension_message[] = '<td><strong style="color: red">' . $message['message'] . '</strong></td>';
				$extension_message[] = '</tr>';			
			}
		endif;
        $extension_message[] = '</tbody>';
        $extension_message[] = '</table>';
		$msg_string = implode(' ', $extension_message);
		$jversion = new JVersion();
		if (($route == 'update') && version_compare( $jversion->getShortVersion(), '3.4.0', 'ge' ))
		{
			$app = JFactory::getApplication();
			$app->setUserState('com_installer.redirect_url', 'index.php?option=com_visforms');
			$app->setUserState('com_visforms.update_message', $msg_string);
			echo $msg_string;
		}
		else
		{
			echo $msg_string;
		}
    }
	
	private function uninstallationResults()
    {
    $language = JFactory::getLanguage();
    $language->load('com_visforms');
    $rows = 0;
 ?>
        <h2><?php echo JText::_('COM_VISFORMS_REMOVAL_STATUS'); ?></h2>
        <table class="adminlist table table-striped">
            <thead>
                <tr>
                    <th class="title" colspan="2" style="text-align: left;"><?php echo JText::_('COM_VISFORMS_EXTENSION'); ?></th>
                    <th width="30%" style="text-align: left;"><?php echo JText::_('COM_VISFORMS_STATUS'); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
            <tbody>
                <tr class="row0">
                    <td class="key" colspan="2"><?php echo JText::_('COM_VISFORMS_COMPONENT'); ?></td>
                    <td><strong><?php echo JText::_('COM_VISFORMS_REMOVED'); ?></strong></td>
                </tr>
                <?php if (count($this->status->modules)): ?>
                <tr>
                    <th><?php echo JText::_('COM_VISFORMS_MODULE'); ?></th>
                    <th><?php echo JText::_('COM_VISFORMS_CLIENT'); ?></th>
                    <th></th>
                </tr>
                <?php foreach ($this->status->modules as $module): ?>
                <tr class="row<?php echo(++$rows % 2); ?>">
                    <td class="key"><?php echo $module['name']; ?></td>
                    <td class="key"><?php echo ucfirst($module['client']); ?></td>
                    <td><?php echo ($module['result']) ? '<strong>' . JText::_('COM_VISFORMS_REMOVED') : '<strong style="color: red">' . JText::_('COM_VISFORMS_NOT_REMOVED'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
        
                <?php if (count($this->status->plugins)): ?>
                <tr>
                    <th><?php echo JText::_('COM_VISFORMS_PLUGIN'); ?></th>
                    <th><?php echo JText::_('COM_VISFORMS_GROUP'); ?></th>
                    <th></th>
                </tr>
                <?php foreach ($this->status->plugins as $plugin): ?>
                <tr class="row<?php echo(++$rows % 2); ?>">
                    <td class="key"><?php echo ucfirst($plugin['name']); ?></td>
                    <td class="key"><?php echo ucfirst($plugin['group']); ?></td>
                    <td><?php echo ($plugin['result']) ? '<strong>'. JText::_('COM_VISFORMS_REMOVED') : '<strong style="color: red">' . JText::_('COM_VISFORMS_NOT_REMOVED'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
				<?php if (count($this->status->tables)){ ?>
				<tr>
                    <th><?php echo JText::_('COM_VISFORMS_TABLES'); ?></th>
                    <th></th>
                    <th></th>
                </tr>
					<?php foreach ($this->status->tables as $table){ ?>
					<tr class="row<?php echo(++$rows % 2); ?>">
						<td class="key" colspan="3"><?php echo ucfirst($table['message']); ?></td>
					</tr>
					<?php } ?>
				<?php } ?>
                <?php if (count($this->status->folders)): ?>
                <tr>
                    <th colspan="2"><?php echo JText::_('COM_VISFORMS_FILESYSTEM'); ?></th>
                    <th></th>
                </tr>
                <?php foreach ($this->status->folders as $folder): ?>
                <tr class="row<?php echo(++$rows % 2); ?>">
                    <td class="key" colspan="2"><?php echo ucfirst($folder['folder']); ?></td>
                    <td><?php echo ($folder['result']) ? '<strong>' . JText::_('COM_VISFORMS_DELETED') :  '<strong style="color: red">' . JText::_('COM_VISFORMS_NOT_DELETED'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
				<?php if (count($this->status->messages)) : ?>
				<tr>
                    <th colspan="2"><?php echo JText::_('COM_VISFORMS_MESSAGES'); ?></th>
                    <th></th>
                </tr>
				<?php foreach ($this->status->messages as $message)
				{
				?>
				<tr class="row<?php echo(++$rows % 2); ?>">
                    <td class="key" colspan="2"></td>
                    <td><?php echo '<strong style="color: red">' . $message['message'] . '</strong>'; ?></td>
                </tr>
				<?php } ?>
				<?php endif; ?>
            </tbody>
        </table>
    <?php
    }
    
    private function createFolder($folders = array())
    {
		
		JLog::add("*** Try to create folders ***", JLog::INFO, 'com_visforms');
        //create visforms folder in image directory and copy an index.html into it
        jimport('joomla.filesystem.file');
        $folder  = JPATH_ROOT;
        foreach ($folders as $name) 
        {
            $folder .= DIRECTORY_SEPARATOR . $name;
        }
        
        if (($folder != JPATH_ROOT) && !(JFolder::exists($folder)))
        {
            $result = array();
			try
			{
				$result[]     = JFolder::create($folder);
				$this->status->folders[] = array('folder' => $folder, 'result' => $result[0]);
				if ($result[0])
				{
					JLog::add("Folder successfully created: ". $folder, JLog::INFO, 'com_visforms');
				}
				else
				{
					JLog::add("Problems creating folder: ". $folder, JLog::ERROR, 'com_visforms');
				}
			}
			catch (RuntimeException $e)
			{
				JLog::add("Problems creating folders, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
			}
			
            $src  = JPATH_ROOT.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'com_visforms'.DIRECTORY_SEPARATOR.'index.html';
            $dest = JPath::clean($folder.DIRECTORY_SEPARATOR.'index.html');

			try
			{
				$result[] = JFile::copy($src, $dest);
				$this->status->folders[] = array('folder' => $folder.DIRECTORY_SEPARATOR.'index.html', 'result' => $result[1]);
				if ($result[1])
				{
					JLog::add("File successfully copied: ". $dest, JLog::INFO, 'com_visforms');
				}
				else
				{
					JLog::add("Problems copying file: ". $dest, JLog::ERROR, 'com_visforms');
				}
			}
			catch (RuntimeException $e)
			{
				JLog::add("Problems copying files, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
			}
        }
    }
    
    public function installExtensions($route, JAdapterInstance $adapter) {
		JLog::add("*** Try to install extensions ***", JLog::INFO, 'com_visforms');
		$db = JFactory::getDbo();
        $src = $adapter->getParent()->getPath('source');
        $manifest = $adapter->getParent()->manifest;
		$types = array(array('libraries', 'library'), array('plugins', 'plugin'), array('modules', 'module'));
		foreach ($types as $type)
		{
			$xmldefs = $manifest->xpath($type[0]. '/' . $type[1]);
			foreach ($xmldefs as $xmldef)
			{
				$name = (string)$xmldef->attributes()->$type[1];
				$newVersion = (string) $xmldef->attributes()->version;
				$version = "";
				$extWhere = $db->quoteName('type') . ' = ' . $db->quote($type[1]) . ' AND ' . $db->quoteName('element') . ' = ' . $db->quote($name);
				if ($type[1] == 'plugin')
				{
					$group = (string)$xmldef->attributes()->group;
					$path = $src.'/' . $type[0] . '/'.$group;
					if (JFolder::exists($src.'/' . $type[0] . '/'.$group.'/'.$name))
					{
						$path = $src.'/' . $type[0] . '/'.$group.'/'.$name;
					}
					$extWhere .= ' AND ' . $db->quoteName('folder') . ' = ' . $db->quote($group);
				}
				if ($type[1] == 'module')
				{
					$client = (string)$xmldef->attributes()->client;
					if (is_null($client))
					{
						$client = 'site';
					}
					if($client == 'site')
					{
						$client_id = 0;
					}
					else
					{
						$client_id = 1;
					}
					($client == 'administrator') ? $path = $src.'/administrator/' . $type[0] . '/'.$name : $path = $src.'/' . $type[0] . '/'.$name;
					$extWhere .= ' AND ' . $db->quoteName('client_id') . ' = ' . $db->quote($client_id);
				}
				if ($type[1] == 'library')
				{
					$path = $src.'/' . $type[0] . '/'.$name;
				}
				$query = $db->getQuery(true);
				$query
					->select($db->quoteName('extension_id'))
					->from($db->quoteName('#__extensions'))
					->where($extWhere);
				$db->setQuery($query);
				$extension = array();
				try
				{
					$extension = $db->loadColumn();
				}
				catch (RuntimeException $e)
				{
					$message = JText::sprintf('COM_VISFORMS_UNABLE_TO_GET_EXTENSION_ID', $name) . " " . JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
					$this->status->messages[] = array('message' => $message);
					JLog::add('Unable to get extension_id: ' . $name . ', '. $e->getMessage(), JLog::ERROR, 'com_visforms');
					continue;
				}
				$installer = new JInstaller;
				if (count($extension))
				{
					//make sure we have got only on id, if not use the first
					if (is_array($extension))
					{
						$extension = $extension[0];
					}
					//check if we need to update
					try
					{
						$version = $this->getExtensionParam('version', (int) $extension);
					}
					catch (RuntimeException $e)
					{
						$message = JText::sprintf('COM_VISFORMS_UNABLE_TO_GET_EXTENSION_PARAMS', $name) . " " . JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
						$this->status->messages[] = array('message' => $message);
						JLog::add('Unable to get ' . $type[1]. ' params: ' . $name . ', '. $e->getMessage(), JLog::ERROR, 'com_visforms');
						continue;
					}
					if ( version_compare( $newVersion, $version, 'gt' ))
					{
						$installationType = "update";
					}
				}
				else
				{
					$installationType = "install";
				}
				if (isset($installationType))
				{
					try
					{
						$result = $installer->$installationType($path);
						$resultArray = array('name' => $name, 'result' => $result, 'type' => $installationType);
						if ($type[1] == "plugin")
						{
							$resultArray['group'] = $group;
							$this->status->plugins[] = $resultArray;
                            //we have to enable the content plugin visforms
                            if ($name == 'visforms')
                            {
                                JLog::add("Try to enable " . $type[1] . " " . $name, JLog::INFO, 'com_visforms');
                                $this->enableExtension($extWhere);
                            }
                            //enable plugin visform spambotcheck
							if ($name == 'spambotcheck')
                            {
                                JLog::add("Try to enable " . $type[1] . " " . $name, JLog::INFO, 'com_visforms');
                                $this->enableExtension($extWhere);
                            }
							//enable plugin editor-xtd visformfields
							if ($name == 'visformfields')
                            {
                                JLog::add("Try to enable " . $type[1] . " " . $name, JLog::INFO, 'com_visforms');
                                $this->enableExtension($extWhere);
                            }
							
						}
						if ($type[1] == "module")
						{
							$resultArray['client'] = $client;
							$this->status->modules[] = $resultArray;
						}
						if ($result)
						{
							JLog::add($installationType . " of " . $type[1]. ' sucessfully: ' . $name, JLog::INFO, 'com_visforms');
						}
						else
						{
							JLog::add($installationType . " of " . $type[1]. ' failed: ' . $name, JLog::ERROR, 'com_visforms');
						}
					}
					catch (RuntimeException $e)
					{
						JLog::add($installationType . " of " . $type[1]. ' failed: ' . $name. ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
					}
					unset($installationType);
				}
			}
		}
		$this->installationResults($route);
	}
    
    /*
	 * get a variable from the manifest cache of the version that is to be updated.
     * 
     * @param string name Parametername
     * @param int id    extension id
     * 
     * @return string parameter value
	 */
	private function getExtensionParam( $name, $eid = 0) {
		$db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('manifest_cache'));
        $query->from($db->quoteName('#__extensions'));
        //check if a extenstion id is given. If yes we want a parameter from this extension
        if($eid != 0)
        {
            $query->where($db->quoteName('extension_id') . ' = ' . $db->quote($eid)); 
        }
        else
        {
            //we want a parameter from component visForms
            $query->where($this->getComponentWhereStatement());
        }
            
		$db->setQuery($query);
        try
        {
            $manifest = json_decode($db->loadResult(), true);
        }
        catch (RuntimeException $e)
        {
            $message = JText::sprintf('COM_VISFORMS_UNABLE_TO_GET_VALUE_OF_PARAM', $name) . " " . JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
            $this->status->messages[] = array('message' => $message);
            JLog::add('Unable to get value of param ' . $name . ', '. $e->getMessage(), JLog::ERROR, 'com_visforms');
        }
		return $manifest[$name];
	}
    
    /*
	 * sets parameter values in the component's row of the extension table
	 */
	private function setExtensionParams($param_array) {
		if ( count($param_array) > 0 ) {
			// read the existing component value(s)
			$db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__extensions'))
                ->where($this->getComponentWhereStatement());  
			$db->setQuery($query);
			$params = json_decode( $db->loadResult(), true );
			// add the new variable(s) to the existing one(s)
			foreach ( $param_array as $name => $value ) {
				$params[ (string) $name ] = (string) $value;
			}
			// store the combined new and existing values back as a JSON string
			$paramsString = json_encode( $params );
			$db->setQuery('UPDATE #__extensions SET params = ' .
				$db->quote( $paramsString ) . ' WHERE ' . $this->getComponentWhereStatement());
				$db->execute();
		}
	}
    
     /*
	 * sets parameter values in table
	 */
	private function setParams($param_array, $table, $fieldName, $where = "") 
	{
		
		if ( count($param_array) > 0 ) 
		{
			JLog::add("*** Try to add params to table: #__" . $table  . " ***", JLog::INFO, 'com_visforms');
			// read the existing value(s)
			$db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select($db->quoteName(array('id', $fieldName)))
                ->from($db->quoteName('#__' . $table)); 
            if ($where != "")
            {
                $query->where($where);
            }
            
			$db->setQuery($query);
			$results = new stdClass();
			try
			{
				$results = $db->loadObjectList();
				JLog::add(count($results) . ' recordsets to process', JLog::INFO, 'com_visforms');
			}
			catch (RuntimeException $e)
            {
				JLog::add('Unable to load param fields, '. $e->getMessage(), JLog::ERROR, 'com_visforms');
            }
            if ($results)
            {
                foreach ($results as $result)
                {
                    $params = json_decode( $result->$fieldName, true );
                    // add the new variable(s) to the existing one(s)
                    foreach ( $param_array as $name => $value ) {
                        $params[ (string) $name ] = (string) $value;
                    }
                    // store the combined new and existing values back as a JSON string
                    $paramsString = json_encode( $params );
                    $db->setQuery('UPDATE #__' . $table . ' SET ' . $fieldName . ' = ' .
                    $db->quote( $paramsString ) . ' WHERE id=' . $result->id);
					try
				   {
						$db->execute();
						JLog::add("Params successfully added", JLog::INFO, 'com_visforms');
				   }
				   catch (RuntimeException $e)
					{
						JLog::add('Problems with adding params ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
					}
                }
            }
		}
	}
    
    /*
     * Methode to create where statement to manipulate component dataset in extensions table
     * 
     * @return  string  where statement to select the visForms component dataset in extensions table
     */
    
    private function getComponentWhereStatement()
    {
        $db = JFactory::getDbo();
        $where = $db->quoteName('type') . ' = ' . $db->quote('component') . ' AND ' . $db->quoteName('element') . ' = ' . $db->quote('com_visforms') . ' AND ' . $db->quoteName('name') . ' = ' . $db->quote('visforms');
        return $where;
    }
	
	
    
    private function deleteOldFiles($filesToDelete = array(), $foldersToDelete = array())
    {       
       jimport( 'joomla.filesystem.file' );
       foreach ($filesToDelete as $fileToDelete)
       {
           $oldfile =JPath::clean(JPATH_ROOT . $fileToDelete);
            if ( JFile::exists($oldfile) ) 
            {
				try
				{
					JFile::delete($oldfile);
					JLog::add($oldfile . "deleted", JLog::INFO, 'com_visforms');
				}
				catch (RuntimeException $e)
				{
					JLog::add('Unable to delete ' . $oldfile . ': ' . $e->getMessage(), JLog::INFO, 'com_visforms');
					throw $e;
				}
            }
			else
			{
				JLog::add($oldfile . " does not exist.", JLog::INFO, 'com_visforms');
			}
			
       }
       foreach ($foldersToDelete as $folderToDelete)
       {
           $folder =JPath::clean(JPATH_ROOT . $folderToDelete);
            if ( JFolder::exists($folder) ) 
            {
				try
				{
					JFolder::delete($folder);
					JLog::add($folder . "deleted", JLog::INFO, 'com_visforms');
				}
				catch (RuntimeException $e)
				{
					JLog::add('Unable to delete ' . $folder . ': ' . $e->getMessage(), JLog::INFO, 'com_visforms');
					throw $e;
				}
            }
			else
			{
				JLog::add($folder . " does not exist.", JLog::INFO, 'com_visforms');
			}
			
       }

    }
    
    private function postFlightForVersion3_1_0()
    {		
		JLog::add('*** Perform postflight for Version 3.1.0 ***', JLog::INFO, 'com_visforms');
		JLog::add('*** Try to add columns to table: #__visforms ***', JLog::INFO, 'com_visforms');
		//Add new fields to table visforms
		$columnsToAdd = array('emailreceiptsettings', 'frontendsettings');
		$db = JFactory::getDbo();
		foreach ($columnsToAdd as $columnToAdd)
		{
			$queryStr = $db->getQuery(true);
			$queryStr = ("ALTER TABLE " . $db->quoteName('#__visforms') . "ADD COLUMN " . $db->quoteName($columnToAdd) . " text");
			$db->setQuery($queryStr);
			try
			{
				$db->execute();
				JLog::add('Column added: ' . $columnToAdd, JLog::INFO, 'com_visforms');
			}
			catch (RuntimeException $e)
			{
				$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
				$this->status->messages[] = array('message' => $message);
				JLog::add('Unable to add column: ' . $columnToAdd . ': ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
			}
			
		}
		//delete old files no longer used
		JLog::add('*** Try to delete old files no longer used ***', JLog::INFO, 'com_visforms');
	   $filesToDelete = array (
           '/administrator/components/com_visforms/images/icon-16-visforms.png',
           '/adminstrator/components/com_visforms/views/vistools/tmpl/css.php',
           '/components/com_visforms/captcha/images/audio_icon.gif'
	   );
	   JLog::add(count($filesToDelete) . " files to delete", JLog::INFO, 'com_visforms');
		try
		{
			$this->deleteOldFiles($filesToDelete);
	   }
	   catch (Exception $e)
	   {
			JLog::add('Problems deleting old files: ' . $e->getMessage(), JLog::WARNING, 'com_visforms');
	   }
	   
        //fix recordsets in visforms table
		JLog::add('*** Try to run fixTableVisforms3_1_0 ***', JLog::INFO, 'com_visforms');
		try
		{
			$this->fixTableVisforms3_1_0();
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_PROBLEM_UPDATE_DATABASE', 'fixTableVisforms3_1_0') . " " . JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add('Problems with update of tables: #__visforms', JLog::ERROR, 'com_visforms');
		}
		//add new menu params
		JLog::add('*** Try to add new menu params ***', JLog::INFO, 'com_visforms');
		$menu_params = array('sortorder' => 'id', 'display_num' => '20');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('id', 'link','params')))
			->from($db->quoteName('#__menu'))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'));
		$db->setQuery($query);
		$menus = new stdClass();
		try
		{
			$menus = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$message = JText::_('COM_VISFORMS_UNABLE_TO_UPDATE_MENU_PARAMS') . " " . JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add('Unable to load menu params: ' . $e->getMessage(), JLog::WARNING, 'com_visforms');
		}
		if ($menus)
		{

			foreach ($menus as $menu)
			{
				if ((isset($menu->link)) && ($menu->link != "") && (strpos($menu->link, "view=visformsdata") !== false))
				{
					$params = json_decode( $menu->params, true );
					// add the new variable(s) to the existing one(s)
					foreach ( $menu_params as $name => $value ) 
					{
						$params[ (string) $name ] = (string) $value;
						// store the combined new and existing values back as a JSON string
						$paramsString = json_encode( $params );
						$db->setQuery('UPDATE #__menu SET params = ' .
						$db->quote( $paramsString ) . ' WHERE ' . $db->quoteName('id') . ' = ' . $db->quote($menu->id));
						try
						{
							$db->execute();
							JLog::add('Param added: ' . $name . 'to menu with id: ' . $menu->id, JLog::INFO, 'com_visforms');
						}
						catch (RuntimeException $e)
						{
							JLog::add('Unable to add param :' . $name . 'to menu with id: ' . $menu->id . " " . $e->getMessage(), JLog::ERROR, 'com_visforms');
						}
					}
				}
			}
		}
    }
    
    private function fixTableVisforms3_1_0()
    {		
       $db = JFactory::getDbo();
       $query = $db->getQuery(true);
	   //Former emailreceipt params into new param field emailreceiptsettings
	   JLog::add('*** Try to move emailreceipt params into new param field emailreceiptsettings ***', JLog::INFO, 'com_visforms');
       $query
           ->select($db->quoteName(array('id', 'emailreceiptincfield', 'emailreceiptincfile', 'emailrecipientincfilepath')))
           ->from($db->quoteName('#__visforms'));
       $db->setQuery($query);
	   try
	   {
			$forms = $db->loadObjectList();
			JLog::add(count($forms) . " form recordsets to process", JLog::INFO, 'com_visforms');
	   }
	   catch (RuntimeException $e)
		{
			JLog::add('Unable to get forms: ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
       if (count($forms) > 0)
       {
           foreach ($forms as $form)
           {
               $emailreceiptsettings = array();
               if (isset($form->emailreceiptincfield))
               {
                   $emailreceiptsettings['emailreceiptincfield'] = $form->emailreceiptincfield;
               }
               else
               {
                   $emailreceiptsettings['emailreceiptincfield'] = 0;
               }
               if (isset($form->emailreceiptincfile))
               {
                   $emailreceiptsettings['emailreceiptincfile'] = $form->emailreceiptincfile;
               }
               else
               {
                   $emailreceiptsettings['emailreceiptincfile'] = 0;
               }
               if (isset($form->emailrecipientincfilepath))
               {
                   $emailreceiptsettings['emailrecipientincfilepath'] = $form->emailrecipientincfilepath;
               }
               else
               {
                   $emailreceiptsettings['emailrecipientincfilepath'] = 0;
               }
			   $emailreceiptsettings['emailreceiptinccreated'] = 1;
			   $emailreceiptsettings['emailreceiptincformtitle'] = 1;
               if (is_array($emailreceiptsettings))
               {
                   $registry = new JRegistry;
                   $registry->loadArray($emailreceiptsettings);
                   $emailreceiptsettings = (string)$registry;
                   $query = $db->getQuery(true);
                   $query->update($db->quoteName('#__visforms'))
                       ->set($db->quoteName('emailreceiptsettings') . " = " . $db->quote($emailreceiptsettings))
                       ->where($db->quoteName('id') . " = " . $db->quote($form->id));
                   $db->setQuery($query);
				   try
				   {
						$result = $db->execute();
						JLog::add('Update successfull for form with id: ' . $form->id, JLog::INFO, 'com_visforms');
				   }
				   catch (RuntimeException $e)
					{
						JLog::add('Problems with update for form with id: ' . $form->id . ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
					}
               }
			   else
			   {
					JLog::add('Frontendsettings have invalid type. Cannot update form with id: ' . $form->id, JLog::ERROR, 'com_visforms');
			   }
           }
		   //drop fields no longer used from table visforms
		   JLog::add("*** Try to drop fields from table #__visforms ***", JLog::INFO, 'com_visforms');
            $columnsToDelete = array('emailreceiptincfield', 'emailreceiptincfile', 'emailrecipientincfilepath');
			JLog::add(count($columnsToDelete) . " fields to drop", JLog::INFO, 'com_visforms');
            foreach ($columnsToDelete as $columnToDelete)
            {
                $queryStr = ("ALTER TABLE " . $db->quoteName('#__visforms') . "DROP COLUMN " . $db->quoteName($columnToDelete));
                $db->setQuery($queryStr);
				try
				{
					$db->execute();
					JLog::add("Field successfully dropped: " . $columnToDelete, JLog::INFO, 'com_visforms');
				}
				catch (RuntimeException $e)
				{
					JLog::add("Problems dropping field: " . $columnToDelete . ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
				}
            }
		}
		   //Former params for frontend display into new param field frontendsettings
		   JLog::add('*** Try to move params for frontend display into new param field frontendsettings ***', JLog::INFO, 'com_visforms');
		   $query = $db->getQuery(true);
       $query
           ->select($db->quoteName(array('id', 'displayip', 'displaydetail', 'autopublish')))
           ->from($db->quoteName('#__visforms'));
       $db->setQuery($query);
       try
	   {
			$forms = $db->loadObjectList();
			JLog::add(count($forms) . " form recordsets to process", JLog::INFO, 'com_visforms');
	   }
	   catch (RuntimeException $e)
		{
			JLog::add('Unable to get forms: ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
       if (count($forms) > 0)
       {
           foreach ($forms as $form)
           {
               $frontendsettings = array();
               if (isset($form->displayip))
               {
                   $frontendsettings['displayip'] = $form->displayip;
               }
               else
               {
                   $frontendsettings['displayip'] = 0;
               }
               if (isset($form->displaydetail))
               {
                   $frontendsettings['displaydetail'] = $form->displaydetail;
               }
               else
               {
                   $frontendsettings['displaydetail'] = 0;
               }
                if (isset($form->autopublish))
               {
                   $frontendsettings['autopublish'] = $form->autopublish;
               }
               else
               {
                   $frontendsettings['autopublish'] = 1;
               }
			   $frontendsettings['displayid'] = 0;
               if (is_array($frontendsettings))
               {
                   $registry = new JRegistry;
                   $registry->loadArray($frontendsettings);
                   $frontendsettings = (string)$registry;
                   $query = $db->getQuery(true);
                   $query->update($db->quoteName('#__visforms'))
                       ->set($db->quoteName('frontendsettings') . " = " . $db->quote($frontendsettings))
                       ->where($db->quoteName('id') . " = " . $db->quote($form->id));
                   $db->setQuery($query);
                   try
				   {
						$result = $db->execute();
						JLog::add('Update successfull for form with id: ' . $form->id, JLog::INFO, 'com_visforms');
				   }
				   catch (RuntimeException $e)
				   {
						JLog::add('Problems with update for form with id: ' . $form->id, JLog::ERROR, 'com_visforms');
				   }
               }
			   else
			   {
					JLog::add('Frontendsettings have invalid type. Cannot update form with id: ' . $form->id, JLog::ERROR, 'com_visforms');
			   }
           }
           //drop fields no longer used from table visforms
		   JLog::add("*** Try to drop fields from table #__visforms ***", JLog::INFO, 'com_visforms');
            $columnsToDelete = array('displayip', 'displaydetail', 'autopublish');
			JLog::add(count($columnsToDelete) . " fields to drop", JLog::INFO, 'com_visforms');
            foreach ($columnsToDelete as $columnToDelete)
            {
                $queryStr = ("ALTER TABLE " . $db->quoteName('#__visforms') . "DROP COLUMN " . $db->quoteName($columnToDelete));
                $db->setQuery($queryStr);
                try
				{
					$db->execute();
					JLog::add("Field successfully dropped: " . $columnToDelete, JLog::INFO, 'com_visforms');
				}
				catch (RuntimeException $e)
				{
					JLog::add("Problems dropping field: " . $columnToDelete . ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
				}
            }
       }
    }
    
    private function postFlightForVersion3_2_0()
    {		
		JLog::add('*** Perform postflight for Version 3.2.0 ***', JLog::INFO, 'com_visforms');
		$db = JFactory::getDbo();
		//Add new fields to table visfields
		try
		{
			$this->addColumns(array('allowurlparam' => array('name' => 'allowurlparam', 'type' => 'TINYINT', 'length' => '4', 'notNull' => true, 'default' => '0'),
				'customtextposition'=> array('name' => 'customtextposition', 'type' => 'TINYINT', 'length' => '4', 'notNull' => true, 'default' => '0'),
				'uniquevaluesonly' => array('name' => 'uniquevaluesonly', 'type' => 'TINYINT', 'length' => '4', 'notNull' => true, 'default' => '0'),
				'restrictions' => array('name' => 'restrictions', 'type' => 'TEXT')
				), 
				'visfields');
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Problems adding fields to table: #__visfields, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
        //Add new fields to table visforms
		try
		{
			$this->addColumns(array('layoutsettings' => array('name' => 'layoutsettings', 'type' => 'TEXT'),
				'emailreceiptfrom' => array('name' => 'emailreceiptfrom', 'type' => 'TEXT'),
				'emailreceiptfromname' => array('name' => 'emailreceiptfromname', 'type' => 'TEXT')
				)
			); 
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Problems adding fields to table: #__visforms, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
		
		try
		{
			$this->convertParamsToJsonField('layoutsettings', 
				array('formCSSclass' => "", 'required' => 'top'), 
				array('formlayout' => 'visforms', 'usebootstrapcss' => '0', 'requiredasterix' => '1')
			);
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Problems converting params in table: #__visforms, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
        //drop fields no longer used from table visforms
		try
		{
			$this->dropColumns(array('formCSSclass', 'required'));
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Problems with dropping fields from table: #__visforms, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
		
		try
		{
			$this->setParams(array('f_submit_attribute_class' => 'btn ', 'f_reset_attribute_class' => 'btn '), 'visfields', 'defaultvalue', $db->quoteName('typefield') . " in ( " . $db->quote('submit') . ", " . $db->quote('reset') . ")");
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Unable to set params in table #__visfields, " . $e->getMessage(), JLog::WARNING, 'com_visforms');
		}
        //Add ShowIP Param to emailreceiptsettings
		try
		{
			$this->setParams(array('emailreceiptincip' => '1'), 'visforms', 'emailreceiptsettings');
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Unable to set params in table #__visforms, " . $e->getMessage(), JLog::WARNING, 'com_visforms');
		}
        //set values for emailreceiptfrom
		JLog::add("*** Try to set values in field emailreceiptfrom and emailreceiptfromname in table: #__visforms ***", JLog::INFO, 'com_visforms');
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('id', 'emailfrom', 'emailfromname')))
            ->from('#__visforms');
         $db->setQuery($query);
         try
	   {
			$forms = $db->loadObjectList();
	   }
	   catch (RuntimeException $e)
		{
			JLog::add('Unable to get forms: ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
        if (count($forms) > 0)
        {
			JLog::add(count($forms) . " form recordsets to process", JLog::INFO, 'com_visforms');
            foreach ($forms as $form)
            {
                $query = $db->getQuery(true);
                $query->update($db->quoteName('#__visforms'))
                    ->set($db->quoteName('emailreceiptfrom') . ' = ' . $db->quote($form->emailfrom) . ', ' . $db->quoteName('emailreceiptfromname') . ' = ' . $db->quote($form->emailfromname))
                    ->where($db->quoteName('id') . " = " . $db->quote($form->id));
                $db->setQuery($query);
                try
                {
                     $result = $db->execute();
                     JLog::add("Value successfully set for form with id: " . $form->id , JLog::INFO, 'com_visforms');
                }
                catch (RuntimeException $e)
                {
                     $this->status->fixTableVisforms[] = array('form' => $form->id, 'result' => false, 'resulttext' => JText::sprintf('COM_VISFORMS_EMAIL_ADDRESS_FIELD_UPDATE_FAILED', JText::_('COM_VISFORMS_EMAIL_RECEIPT_FROM') ));
                     JLog::add("Problems setting value for form with id: " . $form->id . ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
                }
            }
        }
		else
		{
			JLog::add("No form recordsets to process", JLog::INFO, 'com_visforms');
		}
		
		//enforce creation of _save datatable
		try
		{
			$this->createDataTableSave3_2_0();	
		}
		catch (RuntimeException $e)
		{
			JLog::add("Problems creating _save tables, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
        
        //Add column ismfd to data tables
		try
		{
			$this->updateDataTable3_2_0();	
		}
		catch (RuntimeException $e)
		{
			JLog::add("Problems updateing data tables, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
        //convert option list of radio buttons and selects from former custom format string to json in table visfields
        try
		{
			$this->convertSelectRadioOptionList();	
		}
		catch (RuntimeException $e)
		{
			JLog::add("Problems converting option list string, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
    }   
    
    private function postFlightForVersion3_3_0()
    {
        JLog::add('*** Perform postflight for Version 3.3.0 ***', JLog::INFO, 'com_visforms');
        //create visforms table field spamprotection
        $this->addColumns(array('spamprotection' => array('name' => 'spamprotection', 'type' => 'TEXT')));
        //copy params from plg_visforms_spambotcheck into forms or set default values in form
        JLog::add("*** Try to copy params from Plugin Visforms Spambotcheck to forms ***", JLog::INFO, 'com_visforms');
        $plgParamsForm = array("spbot_check_ip"=>"1",
            "spbot_check_email"=>"1",
            "allow_generic_email_check"=>"0",
            "spbot_whitelist_email"=>"",
            "spbot_whitelist_ip"=>"",
            "spbot_log_to_db"=>"0",
            "spbot_stopforumspam"=>"1",
            "spbot_stopforumspam_max_allowed_frequency"=>"0",
            "spbot_projecthoneypot"=>"0",
            "spbot_projecthoneypot_api_key"=>"",
            "spbot_projecthoneypot_max_allowed_threat_rating"=>"0",
            "spbot_sorbs"=>"1",
            "spbot_spamcop"=>"1",
            "spbot_blacklist_email" => "");
        
        $newPlgParamsForm = $this->getPlgvscParmas($plgParamsForm);
        
        if (is_array($newPlgParamsForm))
        {
            $plgParamsForm = $newPlgParamsForm;
        } 
		$db = JFactory::getDbo();		
        $query = $db->getQuery(true);
        $query->update($plgParamsForm);
        $registry = new JRegistry;
        $registry->loadArray($plgParamsForm);
        $plgParamsForm = (string)$registry;
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__visforms'))
            ->set($db->quoteName('spamprotection') . " = " . $db->quote($plgParamsForm));
        $db->setQuery($query);
        try
         {
             $db->execute();
             JLog::add("Plugin Visforms Spambotcheck params added to forms", JLog::INFO, 'com_visforms');
         }
         catch (RuntimeException $e)
         {
             JLog::add("Unable to add plugin Visforms Spambotcheck params to forms: "  . $e->getMessage(), JLog::ERROR, 'com_visforms');
         }
        
    }
    
    private function postFlightForVersion3_4_0()
    {
        JLog::add('*** Perform postflight for Version 3.4.0 ***', JLog::INFO, 'com_visforms');
        try
		{
			$this->setParams(array('allowfedv' => '1', 'displaycreated' => '0', 'displaycreatedtime' => '0'), 'visforms', 'frontendsettings');
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Unable to set params in table #__visforms, " . $e->getMessage(), JLog::WARNING, 'com_visforms');
		}
        //set values for emailreceiptfrom
		JLog::add("*** Try to set frontendaccess in table: #__visforms ***", JLog::INFO, 'com_visforms');
		$db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('id', 'access')))
            ->from('#__visforms');
        $db->setQuery($query);
        try
        {
             $forms = $db->loadObjectList();
        }
        catch (RuntimeException $e)
        {
             JLog::add('Unable to get forms: ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
        }
        if ($forms)
        {
			JLog::add(count($forms) . " form recordsets to process", JLog::INFO, 'com_visforms');
            foreach ($forms as $form)
            {
                try
                {
					$this->setParams(array('frontendaccess' => $form->access), 'visforms', 'frontendsettings', $db->quoteName('id') . " = " . $db->quote($form->id));
                     JLog::add("Value successfully set for form with id: " . $form->id , JLog::INFO, 'com_visforms');
                }
                catch (RuntimeException $e)
                {
                     $this->status->fixTableVisforms[] = array('form' => $form->id, 'result' => false, 'resulttext' => JText::sprintf('COM_VISFORMS_EMAIL_ADDRESS_FIELD_UPDATE_FAILED', 'frontendaccess' ));
                     JLog::add("Problems setting value for form with id: " . $form->id . ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
                }
            }
        }
		else
		{
			JLog::add("No form recordsets to process", JLog::INFO, 'com_visforms');
		}
    }
    
    private function postFlightForVersion3_4_1()
    {
        JLog::add('*** Perform postflight for Version 3.4.1 ***', JLog::INFO, 'com_visforms');
        //create visforms table field captchaoptions
        $this->addColumns(array('captchaoptions' => array('name' => 'captchaoptions', 'type' => 'TEXT')));
        try
		{
			$this->convertParamsToJsonField('captchaoptions', 
				array('captchacustominfo' => '', 'captchacustomerror' => ''), 
				array('captchalabel' => 'Captcha', 'showcaptchalabel' => '0')
			);
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Problems converting params in table: #__visforms, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
        //drop fields no longer used from table visforms
		try
		{
			$this->dropColumns(array( 'captchacustominfo', 'captchacustomerror'));
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Problems with dropping fields from table: #__visforms, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
    }
    
    private function postFlightForVersion3_5_0()
    {
        JLog::add('*** Perform postflight for Version 3.5.0 ***', JLog::INFO, 'com_visforms');
        try
		{
        //create visfields table field bootstrap_size
            $this->addColumns(array('bootstrap_size' => array('name' => 'bootstrap_size', 'type' => 'TINYINT', 'length' => '3', 'notNull' => true, 'attribute' => 'unsigned', 'default' => 0)), 'visfields');
        }
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Problems adding fields to table: #__visforms, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
    }
    
    private function postFlightForVersion3_5_1()
    {  
        JLog::add('*** Perform postflight for Version 3.5.1 ***', JLog::INFO, 'com_visforms');
        try
		{
			//create field visformscaptaoptions in table visforms
            $this->addColumns(array('viscaptchaoptions' => array('name' => 'viscaptchaoptions', 'type' => 'text'),
                'emailresultsettings' => array('name' => 'emailresultsettings', 'type' => 'TEXT'),
                'emailresulttext' => array('name' => 'emailresulttext', 'type' => 'LONGTEXT')
                )
            );
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Problems adding fields to table: #__visforms, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
        
        try
		{
			$this->convertParamsToJsonField('viscaptchaoptions', 
				array(), 
				array('image_width' => '215', 'image_height' => '80', 'image_bg_color' => '#ffffff', 'text_color' => '#616161', 'line_color' => '#616161',
                    'noise_color' => '#616161', 'text_transparency_percentage' => '50', 'use_transparent_text' => '0', 'code_length' => '6', 'case_sensitive' => '0', 
                    'perturbation' => '0.75', 'num_lines' => '8', 'captcha_type' => 'self::SI_CAPTCHA_STRING'
                    )
			);
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Problems converting params in table: #__visforms, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
		
		try
		{
			$this->convertParamsToJsonField('emailresultsettings', 
				array('emailresultincfile' => "0"), 
				array('emailresultincfield' => '1', 'emailresultinccreated' => '1', 'emailresultincformtitle' => '1', 'emailresultincip' => '1')
			);
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Problems converting params in table: #__visforms, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
        //drop fields no longer used from table visforms
		try
		{
			$this->dropColumns(array('emailresultincfile'));
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Problems with dropping fields from table: #__visforms, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
    }
    
    private function postFlightForVersion3_5_3()
    {
        JLog::add('*** Perform postflight for Version 3.5.3 ***', JLog::INFO, 'com_visforms');
        try
		{
        //create visfields table field frontaccess
            $this->addColumns(array('frontaccess' => array('name' => 'frontaccess', 'type' => 'INT', 'length' => '11', 'notNull' => true, 'default' => 0)), 'visfields');
        }
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Problems adding fields to table: #__visforms, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
        JLog::add("Set value in new field frontaccess", JLog::INFO, 'com_visforms');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__visfields'))
            ->set($db->quoteName('frontaccess') . " = " . 1);
        $db->setQuery($query);
        try
         {
             $db->execute();
             JLog::add("Value in new field frontaccess set to 1", JLog::INFO, 'com_visforms');
         }
         catch (RuntimeException $e)
         {
             JLog::add("Unable to set value in new field frontaccess", JLog::ERROR, 'com_visforms');
         }
    }
    
    private function postFlightForVersion3_6_0()
    {
        //add field created by to data tables
        $this->updateDataTable3_6_0();
        $filesToDelete = array(
           '/components/com_visforms/views/visforms/tmpl/message.php',
            '/components/com_visforms/views/visforms/tmpl/message.xml'
        );
        $this->deleteOldFiles($filesToDelete);
    }
    
    private function updateDataTable3_6_0()
    {
        JLog::add("*** Try to update data tables ***", JLog::INFO, 'com_visforms');
       
       //get all form records from database
       $db = JFactory::getDbo();
       $query = $db->getQuery(true);
       $query
           ->select($db->quoteName(array('id', 'saveresult')))
           ->from($db->quoteName('#__visforms'));
       $db->setQuery($query);
	   try
	   {
			$forms = $db->loadAssocList();
	   }
	   catch (RuntimeException $e)
	   {
			JLog::add('Unable to get forms: ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
	   }
       if (count($forms) > 0)
       {
		   JLog::add(count($forms) . " form recordsets to process", JLog::INFO, 'com_visforms');           		
           foreach($forms as $form)
           {               
               //add field created_by (=userid) to datatables if saveresult is true
               if(isset($form['saveresult']) && $form['saveresult'] == 1)
               {
					try
                    {
                        $this->addColumns(array('created_by' => array('name' => 'created_by', 'type' => 'INT', 'length' => '11', 'notNull' => true, 'default' => '0')
                            ), 
                            'visforms_'. $form['id']);
                    }
                    catch (RuntimeException $e)
                    {
                        $message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
                        $this->status->messages[] = array('message' => $message);
                        JLog::add("Problems adding fields to table: #__visforms, " . $form['id'] . " " . $e->getMessage(), JLog::ERROR, 'com_visforms');
                    }
                    try
                    {
                        $this->addColumns(array('created_by' => array('name' => 'created_by', 'type' => 'INT', 'length' => '11', 'notNull' => true, 'default' => '0')
                            ), 
                            'visforms_'. $form['id'].'_save');
                    }
                    catch (RuntimeException $e)
                    {
                        $message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
                        $this->status->messages[] = array('message' => $message);
                        JLog::add("Problems adding fields to table: #__visforms, " . $form['id'] . "_save " . $e->getMessage(), JLog::ERROR, 'com_visforms');
                    }
               } 
               else
               {
					JLog::add("Nothing to to for form with id: " . $form['id'], JLog::INFO, 'com_visforms');
               }
           }
       }
    }

    private function getPlgvscParmas ($plgParamsForm = array())
    {        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('params'))
            ->from('#__extensions')
            ->where($db->quoteName('name') . " = " . $db->quote("plg_visforms_spambotcheck") . " AND " . $db->quoteName('folder') . " = " . $db->quote("visforms"));
         $db->setQuery($query);
        try
        {
			$params = json_decode( $db->loadResult(), true );
        }
        catch (RuntimeException $e)
        {
            JLog::add("Cannot retrieve Plugin params, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
            return false;
        }
        if (!isset($params) || !is_array($params) || !(count($params) > 0))
        {
            JLog::add("Cannot retrieve Plugin params", JLog::ERROR, 'com_visforms');
            return false;
        }
        if ($params['spbot_projecthoneypot_api_key'] != "")
        {
            $plgParamsForm['spbot_projecthoneypot'] = "1";
        }
        return $newPlgParamsForm = array_merge($plgParamsForm, $params);
    }
    
    /**
     * Add new columns to existing table
     * @param array $columnsToAdd array of columns. Each column is an array.
     * @param string $table tablename
     */
    private function addColumns($columnsToAdd = array(), $table = "visforms")
    {		
        if(count($columnsToAdd) > 0)
        {
			JLog::add("*** Try to add new fields to table: #__" . $table  . " ***", JLog::INFO, 'com_visforms');
			JLog::add(count($columnsToAdd) . " fields to add", JLog::INFO, 'com_visforms');
            $db = JFactory::getDbo();
            foreach ($columnsToAdd as $columnToAdd)
            {
                //we need at least a column name
                if (!(isset($columnToAdd['name'])) || ($columnToAdd['name'] == ""))
                {
                    continue;
                }
                $queryStr = $db->getQuery(true);
                $queryStr = ("ALTER TABLE " . $db->quoteName('#__'. $table) . "ADD COLUMN " . $db->quoteName($columnToAdd['name']) . 
                    ((isset($columnToAdd['type']) && ($columnToAdd['type'] != "")) ? " " . $columnToAdd['type'] : " text") .
                    ((isset($columnToAdd['length']) && ($columnToAdd['length'] != "")) ? "(" . $columnToAdd['length'] . ")" : "") .
                    ((isset($columnToAdd['attribute']) && ($columnToAdd['attribute'] != "")) ? " " . $columnToAdd['attribute'] : "") .
                    ((isset($columnToAdd['notNull']) && ($columnToAdd['notNull' ]== true)) ? " not NULL" : "") .
                    ((isset($columnToAdd['default']) && ($columnToAdd['default'] != "")) ? " DEFAULT " . $db->quote($columnToAdd['default']) : ""));
                $db->setQuery($queryStr);
				try
				{
					$db->execute();
					JLog::add("Field added: " . $columnToAdd['name'], JLog::INFO, 'com_visforms');
				}
				catch (RuntimeException $e)
				{
					JLog::add("Unable to add field: " . $columnToAdd['name'] . ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
				}
            }
        }
    }
    
    /**
     * Drop column from existing tabel
     * @param array $columnsToDrop array of column names 
     * @param string $table tablename
     */
    private function dropColumns($columnsToDrop = array(), $table = "visforms")
    {		
		JLog::add("*** Try to drop fields from table #__" . $table  . " ***", JLog::INFO, 'com_visforms');
        if(count($columnsToDrop) > 0)
        {
			JLog::add(count($columnsToDrop) . " fields to drop", JLog::INFO, 'com_visforms');
            $db = JFactory::getDbo();
            foreach ($columnsToDrop as $columnToDrop)
            {
                $queryStr = ("ALTER TABLE " . $db->quoteName('#__' . $table) . "DROP COLUMN " . $db->quoteName($columnToDrop));
                $db->setQuery($queryStr);
                try
				{
					$db->execute();
					JLog::add("Field successfully dropped: " . $columnToDrop, JLog::INFO, 'com_visforms');
				}
				catch (RuntimeException $e)
				{
					JLog::add("Problems dropping field: " . $columnToDrop . ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
				}
            }
        }
		else
		{
			JLog::add('No fields to drop', JLog::INFO, 'com_visforms');
		}
    }
    
    /**
     * 
     * @param string $paramFieldName Name of database field that contains the params (as JSON Object)
     * @param array $oldFields array of database field names and default values that should be converted into the new param database field
     * @param array $newFields array of field names and defaultvalues of fields that should be newly created inte the new param database field
     * @param string $table table name
     */
    private function convertParamsToJsonField ($paramFieldName, $oldFields = array(), $newFields = array(), $table = 'visforms')
    {
       $db = JFactory::getDbo();
       $query = $db->getQuery(true);
	   JLog::add("*** Try to convert params in table: #__" . $table  . "***", JLog::INFO, 'com_visforms');
	   if (count($oldFields) > 0)
	   {
			$fields = array_merge(array('id'), array_keys($oldFields));	
		}
		else
		{
			$fields = array('id');
		}
       $query
           ->select($db->quoteName($fields))
           ->from($db->quoteName('#__' . $table));
       $db->setQuery($query);
	   try
	   {
			$forms = $db->loadObjectList();
	   }
	   catch (RuntimeException $e)
		{
			JLog::add('Unable to get forms: ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
       if (count($forms) > 0)
       {
			
			JLog::add(count($forms) . " form recordsets to process", JLog::INFO, 'com_visforms');
           foreach ($forms as $form)
           {
               $paramArray = array();
               if (count($oldFields) > 0)
               {
                    foreach ($oldFields as $oldFieldName => $oldFieldDefault)
                    {
                        if (isset($form->$oldFieldName))
                        {
                            $paramArray[$oldFieldName] = $form->$oldFieldName;
                        }
                        else
                        {
                            $paramArray[$oldFieldName] = $oldFieldDefault;
                        }
                    }
               }
               if (count($newFields) > 0)
               {
					foreach ($newFields as $newFieldName => $newFieldDefault)
                    {
                        $paramArray[$newFieldName] = $newFieldDefault;
                    }
               }
               if (is_array($paramArray))
               {
                   $registry = new JRegistry;
                   $registry->loadArray($paramArray);
                   $paramArray = (string)$registry;
                   $query = $db->getQuery(true);
                   $query->update($db->quoteName('#__' . $table))
                       ->set($db->quoteName($paramFieldName) . " = " . $db->quote($paramArray))
                       ->where($db->quoteName('id') . " = " . $db->quote($form->id));
                   $db->setQuery($query);
				   try
					{
						$db->execute();
						JLog::add("Modified params saved in form with id: " . $form->id, JLog::INFO, 'com_visforms');
					}
					catch (RuntimeException $e)
					{
						$this->status->fixTableVisforms[] = array('form' => $form->id, 'result' => false, 'resulttext' => JText::_('COM_VISFORMS_PARAMS_LOST'));
						JLog::add("Unable to save modified params in form with id: " . $form->id . ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
					}
               }
			    else
			   {
					JLog::add('Params have invalid type. Cannot update form with id: ' . $form->id, JLog::ERROR, 'com_visforms');
			   }
           }
       }
	   else
	   {
			JLog::add('No form recordsets to process', JLog::INFO, 'com_visforms');
	   }
    }
    
    /*Method to create a _save table for user inputs when updateing from Visforms 3.1.x to 3.2.0
     * 
     */
    private function createDataTableSave3_2_0() 
    { 		
		JLog::add("*** Try to create _save tables ***", JLog::INFO, 'com_visforms');
       //include classes and paths to Visforms model
       jimport('joomla.application.component.model');
       JModelLegacy::addIncludePath(JPATH_SITE.'/administrator/components/com_visforms/models', 'VisformsModel');
       
       //get all form records from database
       $db = JFactory::getDbo();
       $query = $db->getQuery(true);
       $query
           ->select($db->quoteName(array('id', 'saveresult')))
           ->from($db->quoteName('#__visforms'));
       $db->setQuery($query);
	   try
	   {
			$forms = $db->loadAssocList();
	   }
	   catch (RuntimeException $e)
		{
			JLog::add('Unable to get forms: ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
       if (count($forms) > 0)
       {
		   JLog::add(count($forms) . " form recordsets to process", JLog::INFO, 'com_visforms');
           $model = JModelLegacy::getInstance('Visform', 'VisformsModel', array());
           		
           foreach($forms as $form)
           {               
               //create __save datatable if saveresult is true and it doesn't exists
               if(isset($form['saveresult']) && $form['saveresult'] == 1)
               {
					try
					{
						$result = $model->createDataTables($form['id']);
                       $this->status->fixTableVisforms[] = array('form' => $form['id'], 'result' => true, 'resulttext' => JText::_('COM_VISFORMS_CREATION_OF_DATATABLE_SAVE_SUCCESSFUL'));
					   JLog::add("_save table successfully create for form with id: " . $form['id'], JLog::INFO, 'com_visforms');
				   }
				   catch (RuntimeException $e)
				   {
					   $this->status->fixTableVisforms[] = array('form' => $form['id'], 'result' => false, 'resulttext' => JText::_('COM_VISFORMS_CREATION_OF_DATATABLE_SAVE_FAILED'));
					   JLog::add("Unable to create _save table for form with id: " . $form['id'] . ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
				   }
               } 
				else
				{
					JLog::add("Nothing to to for form with id: " . $form['id'], JLog::INFO, 'com_visforms');
				}
           }
       }
    }
    
    private function updateDataTable3_2_0()
    {
        JLog::add("*** Try to update data tables ***", JLog::INFO, 'com_visforms');
       
       //get all form records from database
       $db = JFactory::getDbo();
       $query = $db->getQuery(true);
       $query
           ->select($db->quoteName(array('id', 'saveresult')))
           ->from($db->quoteName('#__visforms'));
       $db->setQuery($query);
	   try
	   {
			$forms = $db->loadAssocList();
	   }
	   catch (RuntimeException $e)
	   {
			JLog::add('Unable to get forms: ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
	   }
       if (count($forms) > 0)
       {
		   JLog::add(count($forms) . " form recordsets to process", JLog::INFO, 'com_visforms');           		
           foreach($forms as $form)
           {               
               //create __save datatable if saveresult is true and it doesn't exists
               if(isset($form['saveresult']) && $form['saveresult'] == 1)
               {
					try
                {
                    $this->addColumns(array('ismfd' => array('name' => 'ismfd', 'type' => 'TINYINT', 'length' => '4', 'notNull' => true, 'default' => '0'),
                        array('name' => 'checked_out', 'type' => 'int', 'length' => '10', 'notNull' => true, 'default' => '0'),
                        array('name' => 'checked_out_time', 'type' => 'datetime', 'notNull' => true, 'default' => '0000-00-00 00:00:00')
                        ), 
                        'visforms_'. $form['id']);
                }
                catch (RuntimeException $e)
                {
                    $message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
                    $this->status->messages[] = array('message' => $message);
                    JLog::add("Problems adding fields to table: #__visforms, " . $form['id'] . " " . $e->getMessage(), JLog::ERROR, 'com_visforms');
                }
               } 
				else
				{
					JLog::add("Nothing to to for form with id: " . $form['id'], JLog::INFO, 'com_visforms');
				}
           }
       }
    }
    
    /**
     * convert option list of radio buttons and selects from former custom format string to json in table visfields
     */
    private function convertSelectRadioOptionList()
    {
        JLog::add("*** Try to convert option list string of radio buttons and selects to json in table: #__visfields ***", JLog::INFO, 'com_visforms');
        //get all field records from database
       $db = JFactory::getDbo();
       $query = $db->getQuery(true);
       $query
           ->select($db->quoteName(array('id','typefield', 'defaultvalue')))
           ->from($db->quoteName('#__visfields'))
           ->where($db->quoteName('typefield') . " IN (" . $db->quote('select') . ", " . $db->quote('radio') . ")");
       $db->setQuery($query);
	   try
	   {
			$fields = $db->loadObjectList();
	   }
	   catch (RuntimeException $e)
	   {
			JLog::add('Unable to get fields: ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
	   }
       if (count($fields) > 0)
       {
		   JLog::add(count($fields) . " field recordsets to process", JLog::INFO, 'com_visforms');
           foreach ($fields as $field)
           {
               //convert defaultvalue to array
               $registry = new JRegistry;
               $registry->loadString($field->defaultvalue);
               $field->defaultvalue = $registry->toArray();
               $optionFieldName = "f_" . $field->typefield . "_list_hidden";
               //get old option string
               $oldOptions = $field->defaultvalue[$optionFieldName];
               JLog::add("Old option list value in field with id: " . $field->id . " is " . $oldOptions, JLog::INFO, 'com_visforms');
               $newOptsString = '';
               //extract old options
               if ($oldOptions != "")
               {
					//index of newOptions has to start with 1 not with 0
                    $i = 1;
					$newOptsString .= '{';
                    $options = explode("[-]", $oldOptions);
                    foreach ($options as $option)
                    {
                        $val = explode("==", $option);
                        $key = explode("||", $val[1]);
                        $ipos = strpos ($key[1],' [default]');
                        //remove the [default]
                        if ($ipos != false)
                        {
                            $key[1] = substr($key[1],0,$ipos);
                            $ipos = "1";
                        }
                        
                        $newOptsString .= '"'.$i.'":{"listitemid":' . $i .',"listitemvalue":"' . $key[0] . '","listitemlabel":"' . $key[1].'"';
                        
                        //add listitemischecked if the option is set as default
                        if ($ipos == "1")
                        {
                            $newOptsString .= ',"listitemischecked":"' . $ipos .'"';
                        }
                        $newOptsString .= "},";
                        $i++;
                    }
					$newOptsString = rtrim($newOptsString, ",") . '}';
               }
               if ($newOptsString != "")
               {
                   JLog::add("New option list value in field with id: " . $field->id . " is " . $newOptsString, JLog::INFO, 'com_visforms');
                   //attach string to defaultvalue array
                   $field->defaultvalue[$optionFieldName] = $newOptsString;
                   
                   //convert defaultvalues to string
                   $registry = new JRegistry();
                   $registry->loadArray($field->defaultvalue);
                   $newDefaultvalue = (string)$registry;
                   //save new defaultvalue in db
                   $query = $db->getQuery(true);
                   $query->update($db->quoteName('#__visfields'))
                       ->set($db->quoteName('defaultvalue') . " = " . $db->quote($newDefaultvalue))
                       ->where($db->quoteName('id') . " = " . $db->quote($field->id));
                   $db->setQuery($query);
				   try
					{
						$db->execute();
						JLog::add("Modified option list saved in field with id: " . $field->id, JLog::INFO, 'com_visforms');
					}
					catch (RuntimeException $e)
					{
						JLog::add("Unable to save modified option list in field with id: " . $field->id . ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
					}
               }
           }
       }
    }
    
    /**
     * Methode to enable a extension
     * @param string $extWhere where statement for extension
     */
    private function enableExtension($extWhere)
    {
		$db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('enabled') . " = 1")
            ->where($extWhere);
        $db->setQuery($query);
        try
        {
            $db->execute();
            JLog::add("Extension successfully enabled", JLog::INFO, 'com_visforms');
        }
        catch (RuntimeException $e)
        {
            JLog::add("Unable to enable extension " . $e->getMessage(), JLog::ERROR, 'com_visforms');
        }
    }
    
    private function postFlightForVersion3_6_3()
    {        
        //uninstall content plugin visforms which was replaced with the visforms plugin visforms
        JLog::add('Try to uninstall content plugin visforms', JLog::INFO, 'com_visforms');
        $name = (string) 'visforms';
        $group = (string)'content';
        $db = JFactory::getDbo();
        $plgWhere = $db->quoteName('type') . ' = ' . $db->quote('plugin') . ' AND ' . $db->quoteName('element') . ' = ' . $db->quote($name) . ' AND ' . $db->quoteName('folder') . ' = ' . $db->quote($group);
        $query = $db->getQuery(true);
        $query
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($plgWhere);
        $db->setQuery($query);
        try
        {
            $extensions = $db->loadColumn();
        }
        catch (RuntimeException $e)
        {
            JLog::add('Unable to get extension_id: ' . $name . ', '. $e->getMessage(), JLog::ERROR, 'com_visforms');
        }
        if (count($extensions))
        {
            foreach ($extensions as $id)
            {
                $installer = new JInstaller;
                try
                {
                    $result = $installer->uninstall('plugin', $id);
                    $this->status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
                    if ($result)
                    {
                        JLog::add('Plugin sucessfully removed: ' . $name, JLog::INFO, 'com_visforms');
                    }
                    else
                    {
                        JLog::add('Removal of plugin failed: ' . $name, JLog::ERROR, 'com_visforms');
                    }
                }
                catch (RuntimeException $e)
                {
                    JLog::add('Removal of plugin failed: ' . $name . ', ' . $e->getMessage(), JLog::ERROR, 'com_visforms');
                }
            }
        }
    }
    
    private function postFlightForVersion3_6_5()
    {
        //delete files no longer used
        $filesToDelete = array (
           '/administrator/components/com_visforms/images/icon-16-visforms.png',
           '/adminstrator/components/com_visforms/views/vistools/tmpl/css.php',
           '/administrator/components/com_visforms/models/fields/aeffrontenddataedit.php'
	   );
	   JLog::add(count($filesToDelete) . " files to delete", JLog::INFO, 'com_visforms');
       try
		{
			$this->deleteOldFiles($filesToDelete);
	   }
	   catch (Exception $e)
	   {
			JLog::add('Problems deleting old files: ' . $e->getMessage(), JLog::WARNING, 'com_visforms');
	   }
       //Add includehadline Param to exportsettings in form
		try
		{
			$this->setParams(array('includeheadline' => '1'), 'visforms', 'exportsettings');
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Unable to set params in table #__visforms, " . $e->getMessage(), JLog::WARNING, 'com_visforms');
		}
    }
    
    private function postFlightForVersion3_7_0()
    {
        $this->fixSepInStoredUserInputsFromMultiSelect();
        $this->convertTableEngine();
    }
    
    private function postFlightForVersion3_7_1()
    {
       JLog::add('*** Try to delete old files no longer used ***', JLog::INFO, 'com_visforms');
	   $filesToDelete = array (
           '/administrator/components/com_visforms/models/fields/spaceraefhidden.php'
	   );
	   JLog::add(count($filesToDelete) . " files to delete", JLog::INFO, 'com_visforms');
		try
		{
			$this->deleteOldFiles($filesToDelete);
	   }
	   catch (Exception $e)
	   {
			JLog::add('Problems deleting old files: ' . $e->getMessage(), JLog::WARNING, 'com_visforms');
	   }
       try
		{
			$this->addColumns(array('editonlyfield' => array('name' => 'editonlyfield', 'type' => 'TINYINT', 'length' => '1', 'notNull' => true, 'default' => 0),
				'addtoredirecturl'=> array('name' => 'addtoredirecturl', 'type' => 'TINYINT', 'length' => '1', 'notNull' => true, 'default' => 0),
				'includeinresultmail' => array('name' => 'includeinresultmail', 'type' => 'TINYINT', 'length' => '1', 'notNull' => true, 'default' => 1),
				'includeinreceiptmail' => array('name' => 'includeinreceiptmail', 'type' => 'TINYINT', 'length' => '1', 'notNull' => true, 'default' => 1)
				), 
				'visfields');
		}
		catch (RuntimeException $e)
		{
			$message = JText::sprintf('COM_VISFORMS_DB_FUNCTION_FAILED', $e->getMessage());
			$this->status->messages[] = array('message' => $message);
			JLog::add("Problems adding fields to table: #__visfields, " . $e->getMessage(), JLog::ERROR, 'com_visforms');
		}
    }
    private function fixSepInStoredUserInputsFromMultiSelect()
    {
        JLog::add('Try to change separator in stored user inputs in fields of type select and multicheckbox with mulitselect', JLog::INFO, 'com_visforms');
        //get a list of all fields of type select or multiselect
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->qn(array('fid', 'id', 'typefield', 'defaultvalue')))
            ->from($db->qn('#__visfields'))
            ->where($db->qn('typefield') . ' = ' . $db->q('select'), 'OR')
            ->where($db->qn('typefield') . ' = ' . $db->q('multicheckbox'));
        $db->setQuery($query);
        try
        {
            $fields = $db->loadObjectList();
        }
        catch (RuntimeException $e)
        {
            JLog::add("Unable to get fields definition: " . $e->getMessage(), JLog::ERROR, 'com_visforms');
            return false;
        }
        if (empty($fields))
        {
            JLog::add("No fields to process", JLog::INFO, 'com_visforms');
            return true;            
        }
        $count = count($fields);
        JLog::add($count . " fields to process", JLog::INFO, 'com_visforms');
        $tablelist = $db->getTableList();
        foreach ($fields as $field)
        {
			$test = $db->getPrefix(). 'visforms_' . $field->fid;
            //only process fields if stored user inputs is enabled (data table exists).
            if ((empty($tablelist)) || (!in_array($test, $tablelist)))
            {
				JLog::add("User inputs of form with id " . $field->fid . " are not stored in database. Nothing to do.", JLog::INFO, 'com_visforms');
                continue;
            }
            if (!empty($field->defaultvalue))
            {
                $registry = new JRegistry;
                $registry->loadString($field->defaultvalue);
                $field->defaultvalue = $registry->toArray();
                $key = 'f_' . $field->typefield ;
                //only process fields which actually have a multi select option enabled
				//if an admins has changed this options results of conversion may be incorrect
                switch($field->typefield)
				{
					case 'select':
						if (empty($field->defaultvalue[$key . '_attribute_multiple']))
						{
							//skip this field
							JLog::add("Option multi select is not enabled in field with id " . $field->id . " Nothing to do.", JLog::INFO, 'com_visforms');
							continue 2;
						}
						break;
					case 'multicheckbox':
						if ((empty($field->defaultvalue[$key . '_attribute_maxlength'])) || ($field->defaultvalue[$key . '_attribute_maxlength'] < 2))
						{
							//skip this field
							JLog::add("Multi selection is not enabled multi checkbox field with id " . $field->id . " Nothing to do.", JLog::INFO, 'com_visforms');
							continue 2;
						}
						break;
					default:
						//should never happen but skip this field
						continue 2;
				}
                //extract array of allowed options from field definition
                $options = json_decode($field->defaultvalue[$key. '_list_hidden']);               
                $returnopts = array();
				$hasOptionWithComma = false;
                if ((!empty($options)) && (is_object($options)))
                {
                    foreach ($options as $option)
                    {
						if ((!empty($option)) && (!empty($option->listitemvalue)) && (substr_count($option->listitemvalue, ',')))
						{
							$hasOptionWithComma = true;
						}
                        $returnopts[] =  $option->listitemvalue;
                    }
                }
                //get stored user inputs from data table
                $datatablefieldkey = 'f'.$field->id;
                $query = $db->getQuery(true);
                $query->select($db->qn(array('id', $datatablefieldkey)))
                    ->from($db->qn('#__visforms_' . $field->fid));
                $db->setQuery($query);
                try
                {
                    $storedValues = $db->loadObjectList();
                } 
                catch(RuntimeException $e )
                {
                    JLog::add("Unable to get stored user input for field with id " . $field->id . ":" . $e->getMessage(), JLog::ERROR, 'com_visforms');
                }
				//no user inputs stored
                if (!empty($storedValues))
                {
                    foreach ($storedValues as $storedValue)
                    {
                        if (empty($storedValue->$datatablefieldkey))
                        {
							JLog::add("User inputs for recordset " . $storedValue->id. " for field with id " . $field->id . " are empty. Nothing to do.", JLog::INFO, 'com_visforms');
                            continue;
                        }
                        $fixedStoreValue = $this->replaceSeparator($storedValue->$datatablefieldkey, $returnopts, $storedValue->id, $field->id, $hasOptionWithComma);
                        $fixedData = new stdClass();
                        $fixedData->id = $storedValue->id;
                        $fixedData->$datatablefieldkey = $fixedStoreValue;
                        try
                        {
                            $db->updateObject('#__visforms_' . $field->fid, $fixedData, 'id');
							JLog::add("Fixed user inputs for recordset " . $storedValue->id. " for field with id " . $field->id . " stored. Original value was: " . $storedValue->$datatablefieldkey . " fixed value is: " . $fixedStoreValue , JLog::INFO, 'com_visforms');
                        } 
                        catch(RuntimeException $e )
                        {
                            JLog::add("Unable to store fixed value for recordset " . $storedValue->id. " for field with id " . $field->id . " in database. Original value was: " . $storedValue->$datatablefieldkey . " fixed value is: " . $fixedStoreValue . ": " . $e->getMessage(), JLog::ERROR, 'com_visforms');
                        }
                    }
                }
                unset($storedValue);
                unset($storedValues);
                unset($fixedData);
                unset($fixedStoreValue);
                //fix data stored in "save" table
                if ((empty($tablelist)) || (!in_array($db->getPrefix(). 'visforms_' . $field->fid . '_save', $tablelist)))
                {
                    continue;
                }
                $query = $db->getQuery(true);
                $query->select($db->qn(array('id', $datatablefieldkey)))
                    ->from($db->qn('#__visforms_' . $field->fid . '_save'));
                $db->setQuery($query);
                try
                {
                    $storedValues = $db->loadObjectList();
                } 
                catch(RuntimeException $e )
                {
                    JLog::add("Unable to get stored user input for field with id " . $field->id . " from save table:" . $e->getMessage(), JLog::ERROR, 'com_visforms');
                }
                if (!empty($storedValues))
                {
                    foreach ($storedValues as $storedValue)
                    {
                        if (empty($storedValue->$datatablefieldkey))
                        {
							JLog::add("User inputs for recordset " . $storedValue->id. " for field with id " . $field->id . " in save table are empty. Nothing to do.", JLog::INFO, 'com_visforms');
                            continue;
                        }
                        $fixedStoreValue = $this->replaceSeparator($storedValue->$datatablefieldkey, $returnopts, $storedValue->id, $field->id, $hasOptionWithComma);
                        $fixedData = new stdClass();
                        $fixedData->id = $storedValue->id;
                        $fixedData->$datatablefieldkey = $fixedStoreValue;
                        try
                        {
                            $db->updateObject('#__visforms_' . $field->fid . '_save', $fixedData, 'id');
							JLog::add("Fixed user inputs for recordset " . $storedValue->id. " for field with id " . $field->id . " stored in save table. Original value was: " . $storedValue->$datatablefieldkey . " fixed value is: " . $fixedStoreValue , JLog::INFO, 'com_visforms');
                        } 
                        catch(RuntimeException $e )
                        {
                            JLog::add("Unable to store fixed value in save database. Original value was: " . $storedValue->$datatablefieldkey . " fixed value is: " . $fixedStoreValue . ": " . $e->getMessage(), JLog::ERROR, 'com_visforms');
                        }
                    }
                }
                unset($storedValue);
                unset($storedValues);
                unset($datatablefieldkey);
                unset($returnopts);
                unset($fixedData);
                unset($fixedStoreValue);
                unset($options);
                unset($key);
				unset($hasOptionWithComma);
            }
            unset($field);
        }
    }
    
    private function replaceSeparator($storedValue, $validOptions, $fieldid, $recordid, $hasOptionWithComma = false)
    {
		if (empty($hasOptionWithComma))
		{
			$tmp = explode(", ", $storedValue);
		}
		else
		{
			//start with the longest option value
			usort($validOptions, function ($a, $b) {if (strlen($a) == strlen($b)) {return 0;} return (strlen($a) > strlen($b)) ? -1 : 1;});
			//array with used valid options
			$tmp = array();
			foreach ($validOptions as $validOption)
			{
				//check if this value is part of the stored string, add it to the new array and remove it from stored string
				if ((!empty($storedValue)) && (strpos($storedValue . ', ', $validOption . ', ') !== false))
				{
					$tmp[] = $validOption;
					$storedValue = str_replace( $validOption . ', ', '', $storedValue . ', ');
				}
			}
			//stored user input contains parts which are no valid option, add these to fixed stored Value
			if (!empty($storedValue))
			{
				$trimmed = rtrim($storedValue, ', ');
				if (!empty($trimmed))
				{
					$tmp[] = $trimmed;
				}
			}
			$addLogEntry = true;
		}
        $fixedStoreValue = implode("\0, ", $tmp);
		if (!empty($addLogEntry))
		{
			JLog::add("Potential problem: Selected vaules in recordset " . $recordid . " for field with id " . $fieldid . " contains options with comma. Converting options with commas can cause invalid data. Old value:  " . $storedValue . ". Stored new values:" . $fixedStoreValue, JLog::INFO, 'com_visforms');
		}
        return $fixedStoreValue;
    }
    
    private function convertTableEngine()
    {
        JLog::add('Try to change storage engine', JLog::INFO, 'com_visforms');
        //get a list all fields of type select or multiselect
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $tablelist = $db->getTableList();
        foreach ($tablelist as $table)
        {
            if ((strpos($table, '_visforms') !== false) || (strpos($table, '_visfields') !== false))
            {
                $query = 'ALTER TABLE ' . $table . ' ENGINE=InnoDB';
                try
                {
                    $db->setQuery($query);
                    $result = $db->execute();

                } 
                catch(Exception $e)
                {
                    JLog::add("Unable to change storage engine to InnoDB for " . $table . ": " . $e->getMessage(), JLog::ERROR, 'com_visforms');
                }
            }
        }
    }

	function cmp($a, $b)
	{
		if (strlen($a) == strlen($b)) {
			return 0;
		}
		return (strlen($a) > strlen($b)) ? 1 : -1;
	}
}
?>