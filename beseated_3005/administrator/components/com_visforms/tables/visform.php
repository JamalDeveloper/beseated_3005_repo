<?php
/**
 * Visform table class
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


/**
 * Form Table class
 *
 * @package    Joomla.Administrator
 * @subpackage com_visforms
 */
class VisformsTableVisform extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__visforms', 'id', $db);
	}
	
	public function bind($array, $ignore = '')
	{
        // Bind the rules. 
        if (isset($array['rules'])) 
        { 
            $rules = new JAccessRules($array['rules']); 
            $this->setRules($rules); 
        }
        return parent::bind($array, $ignore);
	}
	
	/**
	* Method to compute the name of the asset
	*
	* @return  string  The asset name
	* @since   2.0
	*/
	protected function _getAssetName()
	{
        return 'com_visforms.visform.'.$this->id;
	}
	
	 /**
	 * Redefined asset name, as we support action control
	 */
	protected function _getAssetTitle() 
	{
        return $this->title;
	}
	
	/**         
	* Method to get the asset-parent-id of the item         
	*         
	* @return      int         
	*/        
	protected function _getAssetParentId(JTable $table = null, $id = null)        
	{                
		// We will retrieve the parent-asset from the Asset-table                
		$assetParent = JTable::getInstance('Asset');                
		// Default: if no asset-parent can be found we take the global asset                
		$assetParentId = $assetParent->getRootId();                                
		                                   
		// The item has the component as asset-parent                        
		$assetParent->loadByName('com_visforms');                
					
		// Return the found asset-parent-id                
		if ($assetParent->id)                
		{                        
			$assetParentId=$assetParent->id;                
		}                
		return $assetParentId;        
	}
    
    /**
	 * Overloaded check function
	 *
	 * @return	boolean
	 * @see		JTable::check
	 * @since	1.5
	 */
	function check()
	{
        if (empty($this->name)) 
        {
            $this->name = "form_" . self::getNextOrder();
        }
		// Remove accented UTF-8 charachters in field name
		$this->name = JApplication::stringURLSafe($this->name, ENT_QUOTES);

		// Set label
		if (empty($this->title)) 
        {
			$this->title = $this->name; 
		}
        
        // Check upload directory
		JLoader::import('joomla.filesystem.folder');
        
        //convert backslashes to slashes
        $uploadpath = preg_replace('#\\\\#', '/', $this->uploadpath);
		$this->uploadpath = $uploadpath;
        //remove slashes at the beginning and the end of string
		$this->uploadpath = rtrim($this->uploadpath,'/');
        $this->uploadpath = ltrim($this->uploadpath,'/');
		$check = trim($this->uploadpath);
		if(!empty($check)) 
		{
				$check = JPath::clean($check);
				if(!JFolder::exists($this->uploadpath)) 
				{
					$directory = JPATH_SITE.'/'.$this->uploadpath;
					if(!JFolder::exists($directory)) 
					{
						$this->setError(JText::_('COM_VISFORMS_DIRECTORY_DOESNT_EXISTS'));
						return false;
					}
			}
		} 
		else 
		{
			$this->setError(JText::_('COM_VISFORMS_DIRECTORY_EMPTY'));
			return false;
		}

		return true;
	}
	
	/**
	 * Overrides JTable::store to set modified data and user id.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();


		// New Form. A form created and created_by field can be set by the user,
		// so we don't touch either of these if they are set.
		if (!intval($this->created))
		{
			$this->created = $date->toSql();
		}

		if (empty($this->created_by))
		{
			$this->created_by = $user->get('id');
		}
		
		return parent::store($updateNulls);
	}
}
?>
