<?php
defined('_JEXEC') or die('Restricted access');

class com_fbconnctInstallerScript
{
         public function install(JAdapterInstance $adapter)
		 {			
			jimport('joomla.installer.installer');
			jimport('joomla.filesystem.folder');
			jimport('joomla.filesystem.file');
			$app = JFactory::getApplication();
			$src = dirname(__FILE__);
			if(is_dir($src.'/modules/mod_fbconnct')) {
				$installer = new JInstaller;
				$result = @$installer->install($src.'/modules/mod_fbconnct');
				if($result)
				{
					$app->enqueueMessage('Installing module [mod_fbconnct] was successful.', 'message');
				}else{
					$app->enqueueMessage('Installing module [mod_fbconnct] was unsuccessful.', 'error');
				}
			}
		 }
        public function uninstall(JAdapterInstance $adapter)
		{
			$db =  JFactory::getDBO();
			$app = JFactory::getApplication();
			jimport('joomla.installer.installer');
			$db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `element` = "mod_fbconnct" AND `type` = "module"');
			$id = $db->loadResult();
			if($id)
			{	
				$installer = new JInstaller;
				$result = @$installer->uninstall('module',$id,1);
				if($result)
				{
					$app->enqueueMessage('Uninstalling module [mod_fbconnct] was successful.', 'message');
				}else{
					$app->enqueueMessage('Uninstalling module [mod_fbconnct] was unsuccessful. Module may not exist or need manual uninstallation.', 'error');
				}

			}
			
		}
}