<?php
/**
 * visdata model for Visforms
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
defined('_JEXEC') or die( 'Restricted access' );

/**
 * Visdata model class for Visforms
 *
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 *
 * @since        Joomla 1.6 
 */
class VisformsModelVisdatas extends JModelList
{
	
	/**
	* data of selected form
	*
	* @var array
	* @since Joomla 1.6
	*/
	
	var $_data = Array();
	
	/**
	* Visdata form id
	*
	* @var protected $_id Form Id
	*
	* @since Joomla 1.6
	*/
	protected $_id = null;
	
	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @since	1.6
	 */
	 
	 
	public function __construct($config = array())
	{
        if (!(empty($config['id'])))
        {
            $id = $config['id'];
        }
        else
        {
            $id = JFactory::getApplication()->input->getInt('fid', -1);
        }
		$this->setId($id);

		//get an array of fieldnames that can be used to sort data in datatable
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'a.id', 'a.ipaddress', 'a.published', 'a.created', 'a.ismfd', 'a.created_by',
                'id', 'ipaddress', 'published', 'ismfd', 'created_by'
			);
		}
		
		//get all form field id's from database
		$db	= JFactory::getDbo();	
        $query = $db->getQuery(true);
        $query->select($db->quoteName('c.id'))
            ->from($db->quoteName('#__visfields') . ' as c ')
            ->where($db->quoteName('c.fid') . " = " . $id);
		$db->setQuery( $query );
		$fields = $db->loadObjectList();
		
		//add field id's to filter_fields
		foreach ($fields as $field) {
			$config['filter_fields'][] = "a.F" . $field->id;
            $config['filter_fields'][] = "F" . $field->id;
		}
		
		parent::__construct($config);
		
	}
	
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.6
	 */
	 
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();
		$session = JFactory::getSession();

		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		// List state information.
		parent::populateState('a.id', 'asc');
	}
	
	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.published');

		return parent::getStoreId($id);
	}
	
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser();
        $fields = $this->getDatafields();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'*'
			)
		);
		$tn = "#__visforms_" . $this->_id;
		$query->from($tn . ' AS a');

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '') {
			$query->where('(a.published = 0 OR a.published = 1)');
		}

		// Filter by search
		$filter = $this->getFilter();		
		if (!($filter === '')) {
			$query->where($filter);
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.id');
		$orderDirn	= $this->state->get('list.direction', 'asc');
        //we store dates as strings in database. If sort order field is of type date we have to convert the strings before we order the recordsets
        foreach ($fields as $field)
        {
            $fname = 'F'.$field->id;
            if (($field->typefield == 'date') && (($orderCol == $fname) || ($orderCol == 'a.' . $fname)))
            {
                $formats = explode(';', $field->defaultvalue['f_date_format']);
                $format = $formats[1]; 
                $orderCol = ' STR_TO_DATE(' . $orderCol . ', '. $db->quote($format).  ') ';
                break;
               
            }
        }
		$query->order(($orderCol.' '.$orderDirn));
		return $query;
	}
	
	/**
	 * Method to set the form identifier
	 *
	 * @param	int form identifier
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function setId($id)
	{
		// Set id and wipe data
		$this->_id = $id;
	}

	/**
	 * Method to set the text for SQL where statement for search filter
	 *
	 * @return string where statement for SQL
	 * @since	1.6
	 */
	public function getFilter()
	{
		// Get Filter parameters
		$visfilter = $this->getState('filter.search');
		$filter = '';	
		if ($visfilter != '')
		{
			$filter = $filter." (";
			$fields = $this->getDatafields();
			$keywords = explode(" ", $visfilter);
			$k=count( $keywords );
			
			for ($j=0; $j < $k; $j++)
			{
				$n=count( $fields );
				for ($i=0; $i < $n; $i++)
				{
					$rowField = $fields[$i];
					if ($rowField->typefield != 'button' && $rowField->typefield != 'fieldsep')
					{
						$prop="F".$rowField->id;
						$filter = $filter." upper(".$prop.") like upper('%".$keywords[$j]."%') or ";
					}
				}
				$filter = $filter." ipaddress like '%".$keywords[$j]."%' or ";
			}
			$filter = rtrim($filter,'or '); 
			$filter = $filter." )";
								 
		}
		
		return $filter;
	}
	
	/**
	 * Method to retrieves the fields list
	 *
	 * @return array Array of objects containing the data from the database
	 * @since	1.6
	 */
	public function getDatafields($where = "")
	{
		// Lets load the data if it doesn't already exist
		
			$query = ' SELECT * from #__visfields as c where c.fid='.$this->_id;
            if ($where != '')
            {
                $query .= $where;
            }
            $query .= ' ORDER BY c.ordering ASC ';
								
			$datafields = $this->_getList( $query );
            foreach($datafields as $datafield)
            {                            
                $registry = new JRegistry;
                $registry->loadString($datafield->defaultvalue);
                $datafield->defaultvalue = $registry->toArray();
                if($datafield->typefield == "fieldsep" || $datafield->typefield == "image" || $datafield->typefield == "submit" || $datafield->typefield == "reset")
                {
                    $datafield->showFieldInDataView = false;
                }
                else
                {
                    $datafield->showFieldInDataView = true;
                }
            }
			      
		return $datafields;
	}
    
    /**
	 * Method to test whether a record can be exported.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   11.1
	 */
	public function canExport($fid)
	{
        $user = JFactory::getUser();
		// Check form settings.
		if ($fid != -1) 
        {
			return $user->authorise('core.export.data', 'com_visforms.visform.' . (int) $fid);
		}
		else
		{
			//use component settings
            return $user->authorise('core.export.data', 'com_visforms');
        }
    }
    
    /**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 *
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{	
		$form = parent::getFilterForm($data, $loadData);
        
		if (empty($form)) 
        {
			return false;
		}
        
        //configure sort list - create two options for each visforms form field (asc and desc) and replace definition of fullordering field in filter_visdatas.xml 
        $xml = 
            '<field
			name="fullordering"
			type="list"
			label="COM_VISFORMS_LIST_FULL_ORDERING"
			description="COM_VISFORMS_LIST_FULL_ORDERING_DESC"
			onchange="this.form.submit();"
			default="a.id ASC"
			>
			<option value="">JGLOBAL_SORT_BY</option>
            <option value="a.id ASC">JGRID_HEADING_ID_ASC</option>
			<option value="a.id DESC">JGRID_HEADING_ID_DESC</option>
            <option value="a.published ASC">JSTATUS_ASC</option>
			<option value="a.published DESC">JSTATUS_DESC</option>
            <option value="a.created ASC">JDATE_ASC</option>
			<option value="a.created DESC">JDATE_DESC</option>
			<option value="a.ipaddress ASC">COM_VISFORMS_SORT_IP_ASC</option>
			<option value="a.ipaddress DESC">COM_VISFORMS_SORT_IP_DESC</option>
            <option value="a.ismfd ASC">COM_VISFORMS_SORT_ISMFD_ASC</option>
			<option value="a.ismfd DESC">COM_VISFORMS_SORT_ISMFD_DESC</option>
            <option value="a.created_by ASC">COM_VISFORMS_SORT_CREATED_BY_ASC</option>
			<option value="a.created_by DESC">COM_VISFORMS_SORT_CREATED_BY_DESC</option>'
        ;
     
        $datafields = $this->getDatafields();
        foreach($datafields as $datafield)
           {
               if(isset($datafield->showFieldInDataView) && $datafield->showFieldInDataView == true)
               {
                   $xml .= '<option value="a.F' . $datafield->id . ' ASC">' . $datafield->name . ' ' . JText::_("COM_VISFORMS_ASC") . '</option>';
                   $xml .= '<option value="a.F' . $datafield->id . ' DESC">' . $datafield->name . ' ' . JText::_("COM_VISFORMS_DESC") . '</option>';

               } 
           }

        $xml .= '</field>';
        $xmlfield = new SimpleXMLElement($xml);
        $form->setField($xmlfield, 'list', 'true');
		return $form;
	}
    
     /**
	 * Method create content of one export cell
	 *
     * @param object $row visforms field object
     * @param string $type  Export cell type (field/label)
     * @param object $params export params form form
     * @param string $prop  field property to be exported
	 * @return string   export cell content
	 *
	 * @since Joomla 1.6 
	 */
    public function createExportCell($row, $type = Null, $params = Null, $prop = Null)
    {
        $data = "";
        if($type == 'field')
        {
            $prop = $prop;
        }
        else if ($type == 'label')
        {
            $prop = $type;
        }
        else
        {
            return $data;
        }
        if ((!isset($prop)) || (!is_string($prop)))
        {
            return $data;
        }
        if (isset($params->usewindowscharset) && $params->usewindowscharset == 0) {
            $unicode_str_for_Excel = $row->$prop;
        }
        else
        {
            //convert characters into window characterset for easier using with excel
            $unicode_str_for_Excel = iconv("UTF-8", "windows-1250//TRANSLIT", $row->$prop);
        }
        
        $unicode_str_for_Excel = JHtmlVisformsselect::removeNullbyte($unicode_str_for_Excel);
        $unicode_str_for_Excel = str_replace("\"", "\"\"", $unicode_str_for_Excel);
        
        $separator = (isset($params->expseparator)) ? $params->expseparator : ";";

        $pos = strpos($unicode_str_for_Excel, $separator);
        if ($pos === false) 
        {
            $data .= $unicode_str_for_Excel;
        } else {
            $data .= "\"".$unicode_str_for_Excel."\"";
        }				

        return $data;
    }
    
    /**
	 * Method create content of export cells for invariant form fields (id, published) placed at the front of each export row
	 *
     * @param object $params export params form form
     * @param object $row visforms field object
     * 
	 * @return string   export cell content
	 *
	 * @since Joomla 1.6 
	 */
    public function createPreFields ($params, $row, $separator = ";")
    {
        $data = "";
        if (isset($params->expfieldid) && $params->expfieldid == 1)
        {
            $data .= $row->id . $separator;
        }
        if (isset($params->expfieldpublished) && $params->expfieldpublished == 1)
        {
            $data .= $row->published . $separator;
        }
        if (isset($params->expfieldcreated) && $params->expfieldcreated == 1)
        {
            $data .= $row->created . $separator;
        }
        return $data;
    }
    
    /**
	 * Method create content of export cells for invariant form fields (ipaddress) placed at the end of each export row
	 *
     * @param object $params export params form form
     * @param object $row visforms field object
     * 
	 * @return string   export cell content
	 *
	 * @since Joomla 1.6 
	 */
    public function createPostFields ($params, $row, $separator = ";")
    {
        $data = "";
        if (isset($params->expfieldip) && $params->expfieldip == 1)
        {
            $data .= $separator .$row->ipaddress;
        }
        if (isset($params->expfieldismfd) && $params->expfieldismfd == 1)
        {
            $data .= $separator .$row->ismfd;
        }
        return $data;
    }
    
    public function createExportBuffer ($params = null, $cids = array())
    {    
        if (!(is_object($params)))
        {
            return "";
        }
        //get submitted form dataset
		$items = $this->getItems();
        //get fields to export from database
        //according to export parameters of field and form
        $where = ' and c.includefieldonexport = 1';
        $where .= (!(empty($params->exppublishfieldsonly))) ? ' and c.published = 1' : '';
		$fields = $this->getDatafields($where);
        $buffer = "";
		$nbItems=count( $items );
		$nbFields=count( $fields );
        $separator = (isset($params->expseparator)) ? $params->expseparator : ";";
		
		//create tableheaders from fieldnames
        //previous default was, that headers were alwalys created
        if ((!(isset($params->includeheadline))) || ((isset($params->includeheadline)) && ($params->includeheadline == 1)))
        {
            if (isset($params->expfieldid) && $params->expfieldid == 1)
            {
                $buffer .= JText::_( 'COM_VISFORMS_ID' ) . $separator;
            }
            if (isset($params->expfieldpublished) && $params->expfieldpublished == 1)
            {
                $buffer .= JText::_( 'COM_VISFORMS_PUBLISHED' ) . $separator;
            }
            if (isset($params->expfieldcreated) && $params->expfieldcreated == 1)
            {
                $buffer .= JText::_( 'COM_VISFORMS_FIELD_CREATED_LABEL' ) . $separator;
            }

            for ($i=0; $i < $nbFields; $i++)
            {
                $rowField = $fields[$i];
                if ($rowField->typefield != 'submit' && $rowField->typefield != 'image' && $rowField->typefield != 'reset' && $rowField->typefield != 'fieldsep')
                {
                    $buffer .= $this->createExportCell($rowField, 'label', $params);
                    //Add Separator
                    if($i < ($nbFields - 1))
                    {
                        $buffer .= $separator; 
                    }				
                }			
            }
            if (isset($params->expfieldip) && $params->expfieldip == 1)
            {
                $buffer .= $separator .JText::_( 'COM_VISFORMS_IP' );
            }
            if (isset($params->expfieldismfd) && $params->expfieldismfd == 1)
            {
                $buffer .= $separator .JText::_( 'COM_VISFORMS_MODIFIED' );
            }
            //Add linebreak
            $buffer.= " \n";
        }
        //create datasets from rows
		for ($i=0; $i < $nbItems; $i++)
		{
            $row = $items[$i];
            //exclude unpublished datasets according to form settings
            if(!(empty($params->exppublisheddataonly)) && !$row->published)
            {
                continue;
            }
            //Some datasets are checked, we export only those
            if(count($cids) > 0)
            {
                foreach($cids as $value) 
                {
                    if($row->id == $value) 
                    {
                        $buffer .= $this->createPreFields ($params, $row);
                        for ($j=0; $j < $nbFields; $j++) 
                        {
                            $rowField = $fields[$j];
                            if ($rowField->typefield != 'submit' && $rowField->typefield != 'image' && $rowField->typefield != 'reset' && $rowField->typefield != 'fieldsep')
                            {
                                $prop="F".$rowField->id;
                            
                                if ($rowField->typefield == "file")
                                {
                                    //we must decode JSON Object, get the file name and set it as value
                                    $row->$prop = JHtml::_('visforms.getUploadFileName', $row->$prop);
                                }
                                $buffer .= $this->createExportCell($row, 'field', $params, $prop);
                                //Add Separator
                                if($j < ($nbFields - 1))
                                {
                                    $buffer .= $separator;
                                }
                            }
                        }
                        $buffer .= $this->createPostFields ($params, $row);
                        
                         //Add linebreak
                        $buffer.= " \n";
                    }
                }
            }
            //No datasets checked, we export all datasets
            else
            {
                $buffer .= $this->createPreFields ($params, $row, $separator);
                for ($j=0; $j < $nbFields; $j++)
                {
                    $rowField = $fields[$j];   
                    if ($rowField->typefield != 'submit' && $rowField->typefield != 'image' && $rowField->typefield != 'reset' && $rowField->typefield != 'fieldsep')
                    {
                        $prop="F".$rowField->id;
                        if ($rowField->typefield == "file")
                        {
                         //we must decode JSON Object, get the file name and set it as value
                            $row->$prop = JHtml::_('visforms.getUploadFileName', $row->$prop);
                         }
                    
                        $buffer .= $this->createExportCell($row, 'field', $params, $prop);
                        //Add Separator
                        if($j < ($nbFields - 1))
                        {
                            $buffer .= $separator;
                        }	 		
                    }
                }
               $buffer .= $this->createPostFields ($params, $row, $separator);
                 //Add linebreak
               $buffer.=" \n";
            }
		}
        return $buffer;
    }
}
