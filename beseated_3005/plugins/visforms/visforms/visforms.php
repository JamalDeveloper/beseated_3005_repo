<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Example Content Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Content.joomla
 * @since		1.6
 */
class plgVisformsVisforms extends JPlugin
{

	/**
	 * Delete field when deleting a form and delete field in data table when deleting a field
	 * @param	string	The context for the content passed to the plugin.
	 * @param	object	The data relating to the content that was deleted.
	 * @return	boolean
	 * @since	1.6
	 */
	public function onVisformsBeforeJFormDelete($context, $data)
	{
		
		// Skip plugin if we are deleting something other than a visforms form or field
		if (($context != 'com_visforms.visfield') && ($context != 'com_visforms.visform')) {
			return true;
        }  
           
        if ($context == 'com_visforms.visfield')
		{
            $success = true;
            $fid = $data->fid;
            $id = $data->id;
            
            // Convert the defaultvalues field to an array.
            $registry = new JRegistry;
            $registry->loadString($data->defaultvalue);
            $defaultvalues = $registry->toArray();
            
            //Remove restrtictions
            $model = new VisformsModelVisfield();
            //getRestricts
            if ($restricts = $model->getRestricts($data->id, $data->name, $defaultvalues))
            {
                //remove Restrictions
                //ToDo set an errror and return false if something went wrong
                try
                {
                    $model->removeRestrictions($restricts);
                }
                catch (RuntimeException $e)
                {
                    JError::raiseWarning(500, $e->getMessage);
                    return false;
                }
            }
  
			$db = JFactory::getDbo();
			$tablesAllowed = $db->getTableList(); 
            $tablesToDeleteFrom = array("visforms_".$fid, "visforms_".$fid. "_save");
            foreach($tablesToDeleteFrom as $tn)
            {
                $tnfull = $db->getPrefix() . $tn;

            //Delete field in data table when deleting a field
                if (in_array($tnfull, $tablesAllowed)) 

                {
                    
                    $tableFields = $db->getTableColumns('#__' . $tn,false);
                    $fieldname = "F" . $id;

                    if (isset( $tableFields[$fieldname] ))  
                    {

                        $query = "ALTER TABLE #__".$tn." DROP ".$fieldname;
                        $db->setQuery($query);
                        try
                        {
                            $db->execute();
                        }
                        catch (RuntimeException $e)
                        {
                            //set already deleted restrictions again
                            $model->setRestrictions($restricts);
                            JError::raiseWarning(500, $e->getMessage);
                            $success = false;
                            continue;
                        }
                    }
                }
			}
            
            if (!$success)
            {
                //set already deleted restrictions again
                $model->setRestrictions($restricts);

            }
			return $success;
        }
        
        //Delete fields in visfields table when deleting a form and delete datatable if table exists
        if ($context == 'com_visforms.visform')
		{
            $success = true;
            $fid = $data->id;
			$db = JFactory::getDbo();
            $query = $db->getQuery(true);
			$query->delete($db->quoteName('#__visfields'))
                ->where($db->quoteName('fid') . " = " . $fid);
		
            $db->setQuery($query);
            try
            {
                $db->execute();
            }
            catch (RuntimeException $e)
            {
                JError::raiseWarning(500, $e->getMessage);
                $success = false;
            }
            $tablesAllowed = $db->getTableList();
            $tablesToDelete = array("visforms_".$fid, "visforms_".$fid. "_save");
            foreach($tablesToDelete as $tn)
            {
            $tnfull = $db->getPrefix() . $tn;
                if (in_array($tnfull, $tablesAllowed)) {
                    //no datatable exists, nothing to do
                    $db->setQuery("drop table if exists #__".$tn);
                    try
                    {
                        $db->execute();
                    }
                    catch (RuntimeException $e)
                    {
                        JError::raiseWarning(500, $e->getMessage);
                       $success = false;
                    }
                }
            }
        }
        return $success;
	}
}
