<?php
/**
 * Visformsdata model for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

//Legacy code for older versions of content plugin vfdataview
if (!class_exists('VisformsHelper'))
{
    JLoader::register('VisformsHelper', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/visforms.php');
}
if (!class_exists('VisformsAEF'))
{
    JLoader::register('VisformsAEF', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/aef/aef.php');
}
if (!class_exists('JHtmlVisformsselect'))
{
    JLoader::register('JHtmlVisformsselect', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/html/visformsselect.php');
}

/**
 * Visdata model class for Visforms
 *
 * @package      Joomla.Site
 * @subpackage   com_visforms
 *
 * @since        Joomla 1.6 
 */
class VisformsModelVisformsdata extends JModelList
{
	protected $datafields;
	
	// form id
	protected $id;
	
	//stored user inputs
	protected $detail;
    
    //Alternative params set by plugin
    var $pparams;
    
    //Dot free string to use as requestprefix and context for pagination, searchfilter and sort fields
    var $paginationcontext;
    
    var $displayedDefaultDbFields = array();
    
    var $visform;
    
    var $pluginfieldlist;
	
	/*
	 * Constructor
     * Note the model is used in component, plugins and modules!
	 *
	 */
	function __construct($config = array())
	{
        if (!empty($config['formid']))
        {
            $id = $config['formid'];
        }
        else
        {
            $id = JFactory::getApplication()->input->getInt('id', -1);
        }
		$this->setId($id);
        if (isset($config['context']) && $config['context'] != "") 
        {
            $this->context = $config['context'];
        }
        parent::__construct($config);
        
        //create a unique context which is used to distinguish mulitple adminForms on one page
        $itemid = 0;
        if ($menu = $this->getMenuItem())
        {
            $itemid = ($menu->id) ? $menu->id : 0;
        }
        $this->paginationcontext = str_replace('.', '_', $this->context . '_' . $itemid . '_' . $id . '_');
        
        if (isset($config['pparams']) && is_array($config['pparams']))
        {
            $this->pparams = $config['pparams'];
        }

		//get an array of fieldnames that can be used as search filter fields
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array();
		}
        $this->datafields = $this->getDatafields();
		$fields = $this->datafields;
        if (!empty($fields))
        {
            //add field id's to filter_fields
            foreach ($fields as $field) 
            {
                if (in_array($field->typefield, array('select', 'radio', 'multicheckbox')))
                {
                    $config['filter_fields'][] = $this->paginationcontext.$field->name;
                }
            }
        }
        if ($canPublish = $this->canPublish())
        {
            $config['filter_fields'][] = $this->paginationcontext.'published';
        }
        
        $this->visform = $this->getForm();
        $this->displayedDefaultDbFields = $this->setDisplayedDefaultDbFields();
        if (array_key_exists('ismfd', $this->displayedDefaultDbFields))
        {
             $config['filter_fields'][] = $this->paginationcontext.'ismfd';
        }         
        $this->pluginfieldlist = $this->setPluginFieldList();
        if (isset($config['filter_fields']))
		{
			$this->filter_fields = $config['filter_fields'];
		}
        
	}

	function setId($id)
	{
		// Set id and wipe data
		$this->id = $id;
	}
	
	function getId()
	{
		return $this->id;
	}
	 
	protected function populateState($ordering = null, $direction = null)
	{
        // Initialise variables.
		$app = JFactory::getApplication();
        $lang = JFactory::getLanguage();
        $itemid = 0;
        if(isset($this->pparams) && is_array($this->pparams))
        {
            $params = new JRegistry;
            $params->loadArray($this->pparams);
        }
        else
        {
            $params = $app->getParams();
            if ($menu = $this->getMenuItem())
            {
                //$params->loadString($menu->params);
                $itemid = ($menu->id) ? $menu->id : 0;
            }
        }
        $this->setState('params', $params);
        $this->setState('itemid', $itemid);
        //Param count comes from plugin, if we have a list view with a limited fix amount of recordsets
        $count = $params->get('count');
        $limit = (isset($count) && is_numeric($count)) ? intval($count) : $params->get('display_num', 20);
        if ($limit)
        {
            $value = $app->input->get($this->paginationcontext.'limit', $limit, 'uint');
            $this->setState('list.limit', $value);
        }

		$value = $app->getUserStateFromRequest($this->paginationcontext. '.limitstart', $this->paginationcontext.'limitstart', 0, 'uint');
        $app->setUserState($this->paginationcontext.'.limitstart', $value);
		$this->setState('list.start', $value);
        // Receive & set filters
        //in the filters form filter is a fields group. 
        //Therefore filters fields are submitted as an array. 
        //The original populateState function from model list loops over the filter array and sets the values properly in the state.
        //We use unique context to distiguish filters between different adminForms (different menu items and dataview plugin instances)
        $inputFilter = JFilterInput::getInstance();
        if ($filters = $app->getUserStateFromRequest($this->paginationcontext . '.filter', 'filter', array(), 'array'))
        {
            foreach ($filters as $name => $value)
            {
                $filtername = str_replace($this->paginationcontext, '', $name);
                // Exclude if blacklisted
                if (!in_array($filtername, $this->filterBlacklist))
                {
                    $app->setUserState($this->paginationcontext.'.filter.'.$name, $value);
                    $this->setState('filter.' . $filtername, $value);
                }
            }
        }
        //Out of the box, with Joomla! it is not possible to have more than one sortable table on a page (no prefix supported as for pagination), so one request can only handle one value for each parameter
        //we add a unique context everywhere to distinguish between different adminForms and make sure that always the right filter_order and filter_order_dir control is filled in the admin form
        $ordering = $app->getUserStateFromRequest($this->paginationcontext. '.ordering', $this->paginationcontext.'filter_order', $params->get('sortorder', 'id'), 'string');
        $this->setState('list.ordering', $ordering);
        $direction = $app->getUserStateFromRequest($this->paginationcontext. '.direction', $this->paginationcontext.'filter_order_Dir', $params->get('sortdirection', 'ASC'), 'string');
        $this->setState('list.direction', $direction);
	}
    
	public function getPagination()
	{
		// Get a storage key.
		$store = $this->getStoreId('getPagination');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Create the pagination object.
		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new JPagination($this->getTotal(), $this->getStart(), $limit, $this->paginationcontext);

		// Add the object to the internal cache.
		$this->cache[$store] = $page;

		return $this->cache[$store];
	}
	
	protected function getStoreId($id = '')
	{
		// Compile the store id.
        $id	.= ':'.$this->getState('filter.search');
		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser();
        $userId = $user->get('id');
        $canDo = VisformsHelper::getActions($this->id);
        $canPublish = $this->canPublish();
        $fields = $this->datafields;
        $menu_params = $this->getState('params', new JRegistry());
        $editableonly = $menu_params->get('editableonly', 0);
        $vffronteditVersion = JHTMLVisforms::getFrontendDataEditVersion();

		// Select the required fields from the table.
		$query->select($this->getState('list.select', '*'));
		$tn = "#__visforms_" . $this->id;
        $query->from($db->quoteName($tn) . ' AS a');
        if (!empty($canPublish))
        {
            $searchfilter = $this->getState('filter.'.'published');
            if ((isset($searchfilter)) && ($searchfilter != ''))
            {
                $query->where($db->quoteName('published') . ' = ' . $searchfilter);
            }
        }
        else
        {
            $query->where($db->quoteName('published') . ' = ' . 1);
        }
         //only use the items specified in the fieldselect list
        if (isset($this->pparams['fieldselect']) && is_array($this->pparams['fieldselect']) && (!empty($fields)))
        {
            foreach ($this->pparams['fieldselect'] as $name => $value)
            {
                if (is_numeric($name))
                {
                    $name = "F" . $name;
                }
                foreach ($fields as $field)
                {
                    //different aproach for fields with multi select options
                    if ('F'.$field->id == $name)
                    {
                        if (in_array($field->typefield, array('select', 'multicheckbox')))
                        {
                            $viewSelection = JHtmlVisformsselect::$multiSelectSeparator . $value . JHtmlVisformsselect::$multiSelectSeparator;
                            $storedSelections = $query->concatenate(array($db->q(JHtmlVisformsselect::$multiSelectSeparator), $db->quoteName($name), $db->q(JHtmlVisformsselect::$multiSelectSeparator)));
                            $query->where('('. $storedSelections . ' like ' . $db->q($viewSelection) . ')');
                        }
                        else
                        {
                            $query->where($db->quoteName($name) ." = " . $db->quote($value), "AND");
                        }
                    }
                }  
            }
        }
        
        if (VisformsAEF::checkAEF(VisformsAEF::$allowfrontenddataedit))
        {
            if ($editableonly == 1)
            {
                if ($canDo->get('core.edit.data'))
                {
                    //get all record sets
                }
                else if ($canDo->get('core.edit.own.data'))
                {
                    $query->where($db->quoteName('created_by') ." = " . $userId);
                }
                else {
                    //don't return any record sets
                   $query->where($db->quoteName('created_by') ." = -1 ");
                }
            }
        }
        // Filter by search
		$filter = $this->getFilter();		
		if (!($filter === '')) {
			$query->where($filter);
		}
        if (!empty($fields))
        {
            //apply select filter selctions
            foreach ($fields as $field)
            {
                //in plugin context use only fields which ar in the plugin field display list
                if ((!empty($this->pparams)) && (!(in_array($field->id, $this->pluginfieldlist))))
                {
                    continue;
                }
                if (in_array($field->typefield, array('select', 'radio', 'multicheckbox')))
                {
                    $selectfilter = $this->getState('filter.'.$field->name);
                    // 0 is a valid option
                    if ((!isset($selectfilter)) || ($selectfilter === ''))
                    {
                        continue;
                    }
                    //select recordsets
                    $viewSelection = JHtmlVisformsselect::$multiSelectSeparator . $selectfilter . JHtmlVisformsselect::$multiSelectSeparator;
                    $storedSelections = $query->concatenate(array($db->q(JHtmlVisformsselect::$multiSelectSeparator), $db->quoteName('F'.$field->id), $db->q(JHtmlVisformsselect::$multiSelectSeparator)));
                    $query->where('('. $storedSelections . ' like ' . $db->q($viewSelection) . ')');
                    continue;
                }
            }
        }
        //foreach ($this->displayedDefaultDbFields as $fieldname => $name)
        if ((!empty($this->displayedDefaultDbFields)) && isset($this->displayedDefaultDbFields['ismfd']))
        {
            $searchfilter = $this->getState('filter.'.'ismfd');
            if ((isset($searchfilter)) && ($searchfilter != ''))
            {
                $query->where($db->quoteName('ismfd') . ' = ' . $searchfilter);
            }
        }
		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'id');
        if (is_numeric($orderCol))
        {
            $orderCol = "F" . $orderCol;
        }
        $this->setState('list.ordering', $orderCol);
		$orderDirn	= $this->state->get('list.direction', 'asc');
        //we store dates as strings in database. If sort order field is of type date we have to convert the strings before we order the recordsets
        if (!empty($fields))
        {
            foreach ($fields as $field)
            {
                $fname = 'F'.$field->id;
                if (($field->typefield == 'date') && (($orderCol == $fname) || ($orderCol == 'a.' . $fname)))
                {
                    $formats = explode(';', $field->format);
                    $format = $formats[1];
                    $orderCol = ' STR_TO_DATE(' . $orderCol . ', '. $db->quote($format).  ') ';
                    break; 
                }
            }
        }
        $query->order($orderCol.' '.$orderDirn);
		return $query;
	}

	function getDatafields()
	{      
		// Lets load the data if it doesn't already exist
        //exclude all fieldtypes that should not be published in frontend (submits, resets, fieldseparator)
		$datafields = $this->datafields;
		if (empty($datafields))
		{
            $db	= JFactory::getDbO();
            $user = JFactory::getUser();
            $groups = $user->getAuthorisedViewLevels();
            $frontaccess = implode(", ", $groups);
            $excludedFieldTypes = "'reset', 'submit', 'image', 'fieldsep'";
            $query = $db->getQuery(true);
            $query->select('*')
                ->from($db->quoteName('#__visfields'))
                ->where($db->quoteName('fid') . " = " . $this->id)
                ->where($db->quoteName('published') . ' = ' . 1)
                ->where($db->quoteName('frontaccess') . " in (" . $frontaccess . ")")
                ->where($db->quoteName('typefield') . "not in (". $excludedFieldTypes . ")")
                ->where('('. $db->qn('frontdisplay') . ' is null or ' . $db->qn('frontdisplay') . ' in (1,2,3))')
                ->order($db->quoteName('ordering') . " asc");
            $db->setQuery($query);	
            try
            {
                $datafields = $db->loadObjectList();
            } catch(Exception $ex)
            {

            }
			$n = count($datafields);
			for ($i=0; $i < $n; $i++)
			{ 
				$registry = new JRegistry;
				$registry->loadString($datafields[$i]->defaultvalue);
				$datafields[$i]->defaultvalue = $registry->toArray();
				
				foreach ($datafields[$i]->defaultvalue as $name => $value) 
				{
					//make names shorter and set all defaultvalues as properties of field object
					$prefix =  'f_' . $datafields[$i]->typefield . '_';
					if (strpos($name, $prefix) !== false) {
							$key = str_replace($prefix, "", $name);
							$datafields[$i]->$key = $value;
					}
				}
				
				//delete defaultvalue array
				unset($datafields[$i]->defaultvalue);
			}
            $this->datafields = $datafields;
		}
        return $datafields;
	}
	
	function getDetail()
	{
        $db	= JFactory::getDbO();
        $app = JFactory::getApplication();
        $array = $app->input->get('cid', array(), 'ARRAY');
        JArrayHelper::toInteger($array);
        $layout = $app->input->get('layout', 'data', 'string');
        $canPublish = $this->canPublish();
		$id=(int)$array[0];
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__visforms_'.$this->id))
            ->where($db->quoteName('id') . " = " . $id);
        //if a user can publish/unpublish a recordset, unpublished recordsets are displayed in the list view and the user can display a details view of the unpublished record as well
        //so we set no published where condition if a user can publish this record set
        if (empty($canPublish))
        {
                $query->where($db->quoteName('published') . ' = ' . 1);
        }
        $db->setQuery($query);
        try
        {
            $detail = $db->loadObject();
        } catch(Exception $ex)
        {
            return false;
        }
        //for fields of type select, radio, multicheckbox and checkbox display option label in data view instead of the stored option values 
        //frontend data edit up to version 1.3.0 uses this function to get stored user inputs for the data edit view
        //do not replace stored values in this case
		if ((!empty($detail)) && ($layout != 'edit'))
        {
            $fields = $this->datafields;
            foreach ($fields as $field)
            {
                $detailfieldname="F".$field->id;
                if (in_array($field->typefield, array('select', 'radio', 'multicheckbox')))
                {
                    $detailfieldvalue = $detail->$detailfieldname;
                    if ((!isset($detailfieldvalue)) || ($detailfieldvalue === '') || (empty($field->list_hidden)))
                    {
                        continue;
                    }
                    $newextracteditemfieldvalues = JHtmlVisformsselect::mapDbValueToOptionLabel($detailfieldvalue, $field->list_hidden);
                    $newitemfieldvalue = implode('<br />', $newextracteditemfieldvalues);
                    $detail->$detailfieldname = $newitemfieldvalue;
                }
            }
        }
		return $detail;
	}

	
	/**
	* Method to get the form
	* @return object with data
	* @since        Joomla 1.6
	*/
	function getForm()
	{
		$form = $this->visform;
        if (empty($form))
		{
            $db	= JFactory::getDbO();
            $query = $db->getQuery(true);
            $query->select('*')
                ->from($db->quoteName('#__visforms'))
                ->where($db->quoteName('id') . " = " . $this->id)
                ->where($db->quoteName('published') . ' = ' . 1);
            $db->setQuery($query);
            $form = $db->loadObject();
            if (empty($form))
            {
                return $form;
            }		
            $registry = new JRegistry;
            //Convert frontendsettings field to an array
            $registry->loadString($form->frontendsettings);
            $form->frontendsettings = $registry->toArray();
            foreach ($form->frontendsettings as $name => $value) 
            {
               //make names shorter and set all frontendsettings as properties of form object               
               $form->$name = $value;   
            }
        }
		
		return $form;
	}
    
    private function getMenuItem()
    {
        $app = JFactory::getApplication();
        $menu = $app->getMenu()->getActive();
        $lang = JFactory::getLanguage();
        if (!$menu)
        {
            $menu = $app->getMenu()->getDefault($lang->getTag());
        }
        
        return $menu;        
    }
    
    public function getFilterForm($data = array(), $loadData = true)
	{
        //we need to add the path explicitely for use with plugin dataview
        JForm::addFormPath(JPATH_ROOT . '/components/com_visforms/models/forms');
        $form = parent::getFilterForm($data, false);
        if (!empty($form))
        {
            $searchfieldxml = new SimpleXMLElement('<field
                name="search"
                type="text"
                label="COM_VISFORMS_FILTER_SEARCH_DESC"
                hint="JSEARCH_FILTER"
            />');
            
            $form->setField($searchfieldxml, 'filter');
                       
            $form->setFieldAttribute('search', 'name', $this->paginationcontext .'search', 'filter');
            if (array_key_exists('ismfd', $this->displayedDefaultDbFields))
            {
                $ismfdfieldxml = new SimpleXMLElement('<field
                    name="ismfd"
                    type="list"
                    label="COM_VISFORMS_FILTER_ISMFD"
                    description="COM_VISFORMS_FILTER_ISMFD_DESCR"
                    onchange="this.form.submit();"
                    >
                    <option value="">COM_VISFORMS_OPTION_SELECT_ISMFD</option>
                    <option value="1">
                            JYES</option>
                        <option value="0">
                            JNO</option>
                </field>');
                $form->setField($ismfdfieldxml, 'filter');
                $form->setFieldAttribute('ismfd', 'name', $this->paginationcontext .'ismfd', 'filter');
            }
			$canPublish = $this->canPublish();
            if (!empty($canPublish))
            {
                $publishedfieldxml = new SimpleXMLElement('<field
                    name="published"
                    type="list"
                    label="COM_VISFORMS_FILTER_PUBLISHED"
                    description="COM_VISFORMS_FILTER_PUBLISHED_DESC"
                    onchange="this.form.submit();"
                    >
                    <option value="">JOPTION_SELECT_PUBLISHED</option>
                    <option value="1">
                            JPUBLISHED</option>
                        <option value="0">
                            JUNPUBLISHED</option>
                </field>');
                $form->setField($publishedfieldxml, 'filter');
                $form->setFieldAttribute('published', 'name', $this->paginationcontext .'published', 'filter');
            }
        }
        $fields = $this->datafields;
        //if we come from the dataview plugin, only show filter of fields which are in the plugins fieldlist
        foreach ($fields as $field)
        {
            if ((!empty($this->pparams)))
            {
                if (!(in_array($field->id, $this->pluginfieldlist)))
                {
                    continue;
                }
            }
            //only search filter for fields which are displayed in list view
            else if ((!empty($field->frontdisplay)))
            {
                if ($field->frontdisplay == 3)
                {
                    continue;
                }
            }
            if (in_array($field->typefield, array('select', 'radio', 'multicheckbox')))
            {
                $addFilterField = new SimpleXMLElement($this->getFilterField($field));
                $form->setField($addFilterField, 'filter');
            }
        }
        
        $data = $this->loadFormData();
        $form->bind($data);
		return $form;
	}
    
    public function getActiveFilters()
	{
		$activeFilters = array();

		if (!empty($this->filter_fields))
		{
			foreach ($this->filter_fields as $filter)
			{
                $contextfreefiltername = str_replace($this->paginationcontext, '', $filter);
				$filterName = 'filter.' . $contextfreefiltername;

				if (property_exists($this->state, $filterName) && (!empty($this->state->{$filterName}) || is_numeric($this->state->{$filterName})))
				{
					$activeFilters[$filter] = $this->state->get($filterName);
				}
			}
		}

		return $activeFilters;
	}
    
    protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState($this->paginationcontext, new stdClass);

		// Pre-fill the list options
		if (!property_exists($data, 'list'))
		{
			$data->list = array(
				'direction' => $this->state->{'list.direction'},
				'limit'     => $this->state->{'list.limit'},
				'ordering'  => $this->state->{'list.ordering'},
				'start'     => $this->state->{'list.start'}
			);
		}

		return $data;
	}
    
    /**
	 * Method to set the text for SQL where statement for search filter
	 *
	 * @return string where statement for SQL
	 * @since	1.6
	 */
	protected function getFilter()
	{
		// Get Filter parameters
        $fields = $this->datafields;
		$searchfilter = $this->getState('filter.search');
        $db = JFactory::getDbo();
		$filter = '';
		if (($searchfilter != '') && (!empty($fields)))
		{
			$filter .= " (";			
            foreach ($fields as $field)
            {
                if ((!empty($this->pparams)) && (!(in_array($field->id, $this->pluginfieldlist))))
                {
                    continue;
                }
                if (!in_array($field->typefield, array('select', 'radio', 'multicheckbox')))
                {
                    $prop="F".$field->id;
                    $filter .= " upper(".$prop.") like upper('%".$searchfilter."%') or ";
                }
            }
            foreach ($this->displayedDefaultDbFields  as $fieldname => $name)
            {
                $dateformat = JText::_('DATE_FORMAT_LC4');
                $mySqlDateFormat = str_replace('d', '%d', str_replace('m', '%m', str_replace('Y', '%Y', $dateformat)));
                if (($fieldname == 'created') && ($name == 'displaycreated'))
                {
                    $filter .= " DATE_FORMAT(".$fieldname.", '".$mySqlDateFormat."') like '%".$searchfilter."%' or ";
                }
                else if (($fieldname == 'created') && ($name == 'displaycreatedtime'))
                {
                    $filter .= " DATE_FORMAT(".$fieldname.", '".$mySqlDateFormat." %H:%i:%s') like '%".$searchfilter."%' or ";
                }
                else if ($fieldname !='ismfd')
                {
                    $filter .= " ".$fieldname." like '%".$searchfilter."%' or ";
                }
            }           
			$filter = rtrim($filter,'or '); 	
            $filter = $filter." )";						 
		}
		return $filter;
	}
    
    public function getContext()
    {
        if (!empty($this->paginationcontext))
        {
            return $this->paginationcontext;
        }
        return '';
    }
    
    protected function getFilterField($field)
    {
        if (empty($field->list_hidden))
        {
            return false;
        }
        $fieldoptions = JHtmlVisformsselect::extractHiddenList($field->list_hidden);
        if (empty($fieldoptions))
        {
            return false;
        }
        $options = '<option value="">'.JText::sprintf('COM_VISORMS_FILTER_SELECT_SELECT_A_VALUE', $field->label).'</option>';
        foreach ($fieldoptions as $fieldoption)
        {
            $options .= '<option value="' . htmlspecialchars($fieldoption['value'],ENT_COMPAT, 'UTF-8') . '">'. htmlspecialchars($fieldoption['label'],ENT_COMPAT, 'UTF-8') .'</option>';
        }
        $xmlstring = '<field
			name="'.$this->paginationcontext.$field->name.'"
			type="list"
			label="JOPTION_FILTER_PUBLISHED"
			onchange="this.form.submit();"
			>'.$options.'
			
		</field>';
        return $xmlstring;
    }
    
    //display options labels for selects, radios, multicheckboxes and checkboxes in frontend data views not the stored option values
    public function getItems()
    {
        $items = parent::getItems();
        $fields = $this->datafields;
        if ((empty($items)) || empty($fields))
        {
            return $items;
        }
        $n = count($items);
        for ($i = 0; $i < $n; $i++)
        {
            foreach ($fields as $field)
            {
                $itemfieldname="F".$field->id;
                if (in_array($field->typefield, array('select', 'radio', 'multicheckbox')))
                {
                    $itemfieldvalue = $items[$i]->$itemfieldname;
                    if ((!isset($itemfieldvalue)) || ($itemfieldvalue === '') || (empty($field->list_hidden)))
                    {
                        continue;
                    }
                    $newextracteditemfieldvalues = JHtmlVisformsselect::mapDbValueToOptionLabel($itemfieldvalue, $field->list_hidden);
                    $newitemfieldvalue = implode('<br />', $newextracteditemfieldvalues);
                    $items[$i]->$itemfieldname = $newitemfieldvalue;
                }
            }
        }
        return $items;
    }
    
    //only use in list views!
    protected function setDisplayedDefaultDbFields()
    {
		$displayedDefaultDbFields = $this->displayedDefaultDbFields;
        if (empty($displayedDefaultDbFields))
		{
        $form = $this->visform;
        $displayedDefaultDbFields = array();
        $formParamNames = array('displayip' => 'ipaddress', 'displaycreated' => 'created', 'displaycreatedtime' => 'created', 'displayismfd' => 'ismfd');
        foreach ($formParamNames as $name => $fieldname)
        {
            if ((isset($form->$name)) && (in_array($form->$name, array('1', '2'))))
            {
                if ((empty($this->pparams)) || ((isset($this->pparams[$name])) && ($this->pparams[$name] === 'true')))
                {
                    //use named array, in order to prevent two elements with value created
                    $displayedDefaultDbFields[$fieldname] = $name;
                }
            }
        }
        }
        return $displayedDefaultDbFields;
    }
    
    protected function canPublish()
    {
        $user	= JFactory::getUser();
        $userId = $user->get('id');
        $canDo = VisformsHelper::getActions($this->id);
        $vffronteditVersion = JHTMLVisforms::getFrontendDataEditVersion();
        $layout = JFactory::getApplication()->input->get('layout', 'data', 'string');
		$canFrontendEdit = VisformsAEF::checkAEF(VisformsAEF::$allowfrontenddataedit);
        if ((!empty($canFrontendEdit)) && ($canDo->get('core.edit.data.state')) 
            && (version_compare($vffronteditVersion, '1.3.0', 'ge'))
            && (($layout == 'detailedit') || ($layout == 'dataeditlist')))
        {
            return true;
        }
        return false;
    }
    
    //only use in list views!
    protected function setPluginFieldList()
    {
        $pluginfieldlist = array();
        if ((!empty($this->pparams)) && (!empty($this->pparams['fieldlist'])))
        {
            $rawpluginfieldlist = explode(',', $this->pparams['fieldlist']);
            $fields = $this->datafields;
            foreach ($rawpluginfieldlist as $value) 
            {
                $fieldid = trim($value);
                foreach ($fields as $field)
                {
                    //if any sort of frontdisplay is enable for the field in field configuration, it is displayed by the plugin vfdataview
                    if (($field->id == $fieldid) && (in_array($field->frontdisplay, array('1','2', '3'))))
                    {
                        $pluginfieldlist[] = $fieldid;
                    }
                }               
            }
        }
        return $pluginfieldlist;
    }
}
