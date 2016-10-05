<?php
/**
 * Visfield table class
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
 * visfield Table class
 *
 * @package    Joomla.Administrator
 * @subpackage com_visforms
 */
class VisformsTableVisfield extends JTable
{	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	 
	 public function __construct(&$db)
	{
		parent::__construct('#__visfields', 'id', $db);
	}
	
	public function bind($array, $ignore = '')
	{
			// Bind the rules. 
			if (isset($array['rules'])) { 
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
        return 'com_visforms.visform.'. $this->fid . '.visfield.'.$this->id;
	}
	
	 /**
	 * Redefined asset name, as we support action control
	 */
	protected function _getAssetTitle() {
			return $this->label;
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
            $this->name = "field-" . self::getNextOrder($this->_db->quoteName('fid').'=' . $this->_db->Quote($this->fid));
        }
        
        //When we submit a form, fields are added to the $Request with the field name as request parameter name. 
        //If a field name matches a default request parameter (like id) this will be overridden with the user input for the form field.
        //This can cause strange errors
        $forbiddenFieldNames = array('id', 'fid', 'view', 'task', 'option', 'lang', 'language', 'itemid', 'restrictions', 'return', 'creturn', 'tmpl', 'layout');
        foreach($forbiddenFieldNames as $fvalue)
        {
            if($this->name == $fvalue)
            {
                $this->name = "field-" . self::getNextOrder($this->_db->quoteName('fid').'=' . $this->_db->Quote($this->fid));
                 JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_VISFORMS_INVALID_FIELD_NAME_REPLACED',$fvalue, $this->name), 'warning');
            }
        }
        
		// Remove accented UTF-8 charachters in field name
		$this->name = JApplication::stringURLSafe($this->name, ENT_QUOTES);

		// Set label
		if (empty($this->label)) {
			$this->label = $this->name;
		}

		// Set ordering
		if (empty($this->ordering)) {
			// Set ordering to last if ordering was 0
			$this->ordering = self::getNextOrder($this->_db->quoteName('fid').'=' . $this->_db->Quote($this->fid));
		}

		return true;
	}
	
	/**         
	* Method to get the asset-parent-id of the item         
	*         
	* @return      int         
	*/        
	protected function _getAssetParentId(JTable $table = null, $id = null)        {                
		// We will retrieve the parent-asset from the visforms table 
		$assetId = null;
		$fid = $this->fid;
		if ($fid > 0) {
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery(true);
			$query->select($this->_db->quoteName('asset_id'));
			$query->from($this->_db->quoteName('#__visforms'));
			$query->where($this->_db->quoteName('id') . ' = ' 
				. (int) $fid);
	 
			// Get the asset id from the database.
			$this->_db->setQuery($query);
			if ($result = $this->_db->loadResult())
				$assetId = (int) $result;
		}
		else
		{
			//use component as default
			$assetParent = JTable::getInstance('Asset');                
			// Default: if no asset-parent can be found we take the global asset                
			$assetId = $assetParent->getRootId();                                
		                                   
			// The item has the component as asset-parent                        
			$assetParent->loadByName('com_visforms');
			// Return the found asset-parent-id                
			if ($assetParent->id)                
			{                        
				$assetId = $assetParent->id;                
			}    
		}
		
		// Return the asset id.
		if ($assetId)
			return $assetId;
	 
		return parent::_getAssetParentId($table, $id);
	}
	
	function store($updateNulls = false) {
            $db = JFactory::getDbo();
		// Verify that the fieldname is unique (we need that for proper form validation)
		$table = JTable::getInstance('Visfield', 'VisformsTable');
		if ($table->load(array('name' => $this->name, 'fid' => $this->fid))
			&& ($table->id != $this->id || $this->id == 0))
		{
			
			$this->setError(JText::_('COM_VISFORMS_UNIQUE_FIELD_NAME'));
			
			return false;
		}
        $date = JFactory::getDate();
		$user = JFactory::getUser();


		// New Field. A field created and created_by field can be set by the user,
		// so we don't touch either of these if they are set.
		if (!intval($this->created))
		{
			$this->created = $date->toSql();
		}

		if (empty($this->created_by))
		{
			$this->created_by = $user->get('id');
		}

		return parent::store($updateNulls = false);
	}
}
?>
