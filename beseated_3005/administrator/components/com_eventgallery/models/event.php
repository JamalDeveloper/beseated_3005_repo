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

jimport( 'joomla.application.component.modeladmin' );
jimport('joomla.html.pagination');
jimport('joomla.filesystem.folder');

/** @noinspection PhpUndefinedClassInspection */
class EventgalleryModelEvent extends JModelAdmin
{

    public function getItem($pk = null) {
        $item = parent::getItem($pk);

        if ($item!== false) {
            // Convert the params field to an array.
            $registry = new JRegistry;
            $registry->loadString($item->attribs);
            $item->attribs = $registry->toArray();

            // convert metadata field into an array.
            $registry = new JRegistry;
            $registry->loadString($item->metadata);
            $item->metadata = $registry->toArray();
        }

        return $item;
    }

    public function getTable($type = 'Folder', $prefix = 'Table', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }


	function changeFolderName($oldFolder, $newFolder)
	{
		$db = JFactory::getDBO();

        // update the file table
		$query = $db->getQuery(true)
			->update($db->quoteName('#__eventgallery_file'))
			->set('folder=' . $db->quote($newFolder))
			->where('folder=' . $db->quote($oldFolder));
		$db->setQuery($query);
		$db->query();

        // update the comment table
		$query = $db->getQuery(true)
			->update($db->quoteName('#__eventgallery_comment'))
			->set('folder=' . $db->quote($newFolder))
			->where('folder=' . $db->quote($oldFolder));
		$db->setQuery($query);
		$db->query();

        // update the imagelineitem table
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__eventgallery_imagelineitem'))
            ->set('folder=' . $db->quote($newFolder))
            ->where('folder=' . $db->quote($oldFolder));
        $db->setQuery($query);
        $db->query();
	}

	

    public function getForm($data = array(), $loadData = true) {

        $form = $this->loadForm('com_eventgallery.event', 'event', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)){
            return false;
        }

        return $form;
    }

    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_eventgallery.edit.event.data', array());

        if (empty($data))
        {
            $data = $this->getItem();
            $data->usergroups = explode(',', $data->usergroupids);
        }
        
		if (method_exists($this, 'preprocessData')) {
        	$this->preprocessData('com_eventgallery.event', $data);
        }

        return $data;
    }

    function cartable($pks, $iscartable)
    {
        $table = $this->getTable();
        $pks = (array) $pks;
        $result = true;

        foreach ($pks as $i => $pk)
        {
            $table->reset();

            if ($table->load($pk))
            {
                $table->cartable= $iscartable;
                $table->store();
            }
            else
            {
                $this->setError($table->getError());
                unset($pks[$i]);
                $result = false;
            }
        }



        return $result;
    }

    public function validate($form, $data, $group = null) {
        // clean up the folder name if it is no picasa album
        if (empty($data['id'])) {
			// make the foldername for local folders safe
            if ($data['foldertype']==0) {
                $data['folder'] = JFolder::makeSafe($data['folder']);
            }
        }

        $validData =  parent::validate($form, $data, $group);

        if (is_bool($validData) && $validData == false) {
            return false;
        }

        if (!isset($data['usergroups']) || count($data['usergroups'])==0) {
            $validData['usergroupids'] = '';
        } else {
            $validData['usergroupids'] = implode(',', $data['usergroups']);
        }

        return $validData;
    }

    public function delete(&$pks) {

        $folders = array();
        $db = JFactory::getDBO();
        $pks = (array) $pks;
        $table = $this->getTable();

        // Iterate the items to remember to items which needs to be deleted
        foreach ($pks as $i => $pk)
        {

            if ($table->load($pk))
            {
                $folders[$pk] = $table->folder;
            }
        }

        $result = parent::delete($pks);

        $maindir = COM_EVENTGALLERY_IMAGE_FOLDER_PATH;
        //remove the files and folders
        foreach($folders as $key=>$folder) {
            // if the folder does not longer exist
            if (!$table->load($key)) {

                // remove the physical files
                if (strlen($folder)>0) {
                    $this->delTree($maindir.$folder);
                }

                // remove files
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__eventgallery_file'))
                    ->where('folder=' . $db->quote($folder));
                $db->setQuery($query);
                $db->query();

                // remove comments
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__eventgallery_comment'))
                    ->where('folder=' . $db->quote($folder));
                $db->setQuery($query);
                $db->query();
            }
        }

        return $result;
    }

    /**
     * @param string $dir a path to the folder which should be deleted
     * @return bool
     */
    private function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    /**
     * Method to perform batch operations on an item or a set of items.
     *
     * @param   array  $commands  An array of commands to perform.
     * @param   array  $pks       An array of item ids.
     * @param   array  $contexts  An array of item contexts.
     *
     * @return  boolean  Returns true on success, false on failure.
     *
     * @since   12.2
     */
    public function batch($commands, $pks, $contexts)
    {
        // Sanitize ids.
        $pks = array_unique($pks);
        JArrayHelper::toInteger($pks);

        // Remove any values of zero.
        if (array_search(0, $pks, true))
        {
            unset($pks[array_search(0, $pks, true)]);
        }

        if (empty($pks))
        {
            $this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));

            return false;
        }

        $done = false;

        // Set some needed variables.
        $this->user = JFactory::getUser();
        $this->table = $this->getTable();


        if (!empty($commands['category_id']))
        {
            $cmd = JArrayHelper::getValue($commands, 'move_copy', 'c');

            if ($cmd == 'c')
            {
                $result = $this->batchCopy($commands['category_id'], $pks, $contexts);

                if (is_array($result))
                {
                    $pks = $result;
                }
                else
                {
                    return false;
                }
            }
            elseif ($cmd == 'm' && !$this->batchMove($commands['category_id'], $pks, $contexts))
            {
                return false;
            }

            $done = true;
        }

        if (!empty($commands['usergroup']))
        {
            if (!$this->batchUsergroup($commands['usergroup'], $pks, $contexts))
            {
                return false;
            }

            $done = true;
        }

        if (!empty($commands['password']))
        {
            if (!$this->batchPassword($commands['password'], $pks, $contexts))
            {
                return false;
            }

            $done = true;
        }

        if (!empty($commands['watermark']))
        {
            if (!$this->batchWatermark($commands['watermark'], $pks, $contexts))
            {
                return false;
            }

            $done = true;
        }

        if (!empty($commands['imagetypeset']))
        {
            if (!$this->batchImageTypeSet($commands['imagetypeset'], $pks, $contexts))
            {
                return false;
            }

            $done = true;
        }


        if (!empty($commands['tag']))
        {
            /** @noinspection PhpVoidFunctionResultUsedInspection */
            if (!$this->batchTag($commands['tag'], $pks, $contexts))
            {
                return false;
            }

            $done = true;
        }


        if (!empty($commands['tags']))
        {
            $mode = JArrayHelper::getValue($commands, 'tags_action', 'add');

            if (!$this->batchTags($commands['tags'], $pks, $contexts, $mode))
            {
                return false;
            }

            $done = true;
        }

        if (!$done)
        {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;

    }

    /**
     * Batch tags a list of item.
     *
     * @param   integer  $value     The value of the new tag.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     * @param   string   $mode      The mode: add|remove
     *
     * @return  boolean.
     *
     */
	protected function batchTags($value, $pks, $contexts, $mode) {

        // Parent exists so we proceed
        foreach ($pks as $pk)
        {
            if (!$this->user->authorise('core.edit', $contexts[$pk]))
            {
                $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }

            // Check that the row actually exists
            if (!$this->table->load($pk))
            {
                if ($error = $this->table->getError())
                {
                    // Fatal error
                    $this->setError($error);

                    return false;
                }
                else
                {
                    // Not fatal error
                    $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Set the new tags

            $newTags = EventgalleryHelpersTags::splitTags($value);
            $tags = EventgalleryHelpersTags::splitTags($this->table->foldertags);

            foreach($newTags as $newTag) {

                if ($mode == 'remove') {
                    if(($key = array_search($newTag, $tags)) !== false) {
                        unset($tags[$key]);
                    }
                }

                if ($mode == 'add') {
                    array_push($tags, $newTag);
                    $tags = array_unique($tags);
                }
            }
            $this->table->foldertags = implode(",", $tags);

            // Check the row.
            if (!$this->table->check())
            {
                $this->setError($this->table->getError());

                return false;
            }

            // Store the row.
            if (!$this->table->store())
            {
                $this->setError($this->table->getError());

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch passwords for a list of item.
     *
     * @param   integer  $value     The value of the new tag.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  boolean.
     *
     */
    protected function batchPassword($value, $pks, $contexts) {

        // Parent exists so we proceed
        foreach ($pks as $pk)
        {
            if (!$this->user->authorise('core.edit', $contexts[$pk]))
            {
                $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }

            // Check that the row actually exists
            if (!$this->table->load($pk))
            {
                if ($error = $this->table->getError())
                {
                    // Fatal error
                    $this->setError($error);

                    return false;
                }
                else
                {
                    // Not fatal error
                    $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            if ($value=='-') {
                $this->table->password = '';
            } else {
                $this->table->password = $value;
            }

            // Check the row.
            if (!$this->table->check())
            {
                $this->setError($this->table->getError());

                return false;
            }

            // Store the row.
            if (!$this->table->store())
            {
                $this->setError($this->table->getError());

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch watermark for a list of item.
     *
     * @param   integer  $value     The value of the new tag.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  boolean.
     *
     */
    protected function batchWatermark($value, $pks, $contexts) {

        // Parent exists so we proceed
        foreach ($pks as $pk)
        {
            if (!$this->user->authorise('core.edit', $contexts[$pk]))
            {
                $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }

            // Check that the row actually exists
            if (!$this->table->load($pk))
            {
                if ($error = $this->table->getError())
                {
                    // Fatal error
                    $this->setError($error);

                    return false;
                }
                else
                {
                    // Not fatal error
                    $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            if ($value == -1) {
                $this->table->watermarkid = "";
            } else {
                $this->table->watermarkid = $value;
            }


            // Check the row.
            if (!$this->table->check())
            {
                $this->setError($this->table->getError());

                return false;
            }

            // Store the row.
            if (!$this->table->store())
            {
                $this->setError($this->table->getError());

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch image type set for a list of item.
     *
     * @param   integer  $value     The value of the new tag.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  boolean.
     *
     */
    protected function batchImageTypeSet($value, $pks, $contexts) {

        // Parent exists so we proceed
        foreach ($pks as $pk)
        {
            if (!$this->user->authorise('core.edit', $contexts[$pk]))
            {
                $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }

            // Check that the row actually exists
            if (!$this->table->load($pk))
            {
                if ($error = $this->table->getError())
                {
                    // Fatal error
                    $this->setError($error);

                    return false;
                }
                else
                {
                    // Not fatal error
                    $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            $this->table->imagetypesetid = $value;


            // Check the row.
            if (!$this->table->check())
            {
                $this->setError($this->table->getError());

                return false;
            }

            // Store the row.
            if (!$this->table->store())
            {
                $this->setError($this->table->getError());

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch usergrpups for a list of item.
     *
     * @param   array  $value       The value of the new user groups.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  boolean.
     *
     */
    protected function batchUsergroup($value, $pks, $contexts) {

        // Parent exists so we proceed
        foreach ($pks as $pk)
        {
            if (!$this->user->authorise('core.edit', $contexts[$pk]))
            {
                $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }

            // Check that the row actually exists
            if (!$this->table->load($pk))
            {
                if ($error = $this->table->getError())
                {
                    // Fatal error
                    $this->setError($error);

                    return false;
                }
                else
                {
                    // Not fatal error
                    $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            if(($key = array_search("", $value)) !== false) {
                unset($value[$key]);
            }

            if (count($value)>0) {
                $this->table->usergroupids = implode(',', $value);
            }


            // Check the row.
            if (!$this->table->check())
            {
                $this->setError($this->table->getError());

                return false;
            }

            // Store the row.
            if (!$this->table->store())
            {
                $this->setError($this->table->getError());

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }





}
