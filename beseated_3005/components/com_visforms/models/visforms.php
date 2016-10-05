<?php
/**
 * Visforms model for Visforms
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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.utilities.arrayhelper');
if (!class_exists('JHtmlVisformsselect'))
{
    JLoader::register('JHtmlVisformsselect', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/html/visformsselect.php');
}
if (!class_exists('JHtmlVisformscalendar'))
{
    JLoader::register('JHtmlVisformscalendar', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/html/visformscalendar.php');
}
if (!class_exists('VisformsmediaHelper'))
{
    JLoader::register('VisformsmediaHelper', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/visformsmedia.php');
}

/**
 * Visforms modell
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsModelVisforms extends JModelLegacy
{

	 /**
	 * The form id.
	 *
	 * @var    int
	 * @since  11.1
	 */
       protected $_id;
         
    /**
      
     /**
	 * Input from request.
	 *
	 * @var    int
	 * @since  11.1
	 */
         protected $input;
         
    /**
	 * The fields object or null.
	 *
	 * @var    mixed
	 * @since  11.1
	 */
         protected $fields;
         
     /**
	 * The form object or null.
	 *
	 * @var    mixed
	 * @since  11.1
	 */
         private $form;
         
        public static $newSubmission = 0;
        public static $editSubmission = 1;
         
    /**
     
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModel
	 * @since   11.1
	 */
    public function __construct($config = array()) 
    {
        $this->input = JFactory::getApplication()->input;
        if (isset($config['id']))
        {
            $this->setId($config['id']);
        }
        else
        {
           $this->setId();
        }
        parent::__construct($config);

    }
         
     /**
	 * Method store the form id in _id.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
    public function setId($id = null) 
    {
         if (is_null($id))
         {
            $id = $this->input->getInt('id',  0);
         }
         $this->_id = $id;
     }
         
     /**
	 * Method to get the form dataset
	 *
	 * @return  object with form data
	 *
	 * @since   11.1
	 */
         public function getForm()
         {
             $app = JFactory::getApplication();
             $form = $app->getUserState('com_visforms.form' . $this->_id);
             $formIsValid = $this->validateCachedFormSettings($form);
             //only use stored form if it's settings are valid
             if (empty($formIsValid))
             {
                $query = ' SELECT * FROM #__visforms where id='.$this->_id ;				
                $this->_db->setQuery( $query );
                $form = $this->_db->loadObject();
				if (empty($form))
                {
                    $this->form = $form;
                    return $this->form;
                }
                $registry = new JRegistry;
                //Convert receiptmailsettings field to an array
                $registry->loadString($form->emailreceiptsettings);
                $form->emailreceiptsettings = $registry->toArray();
                foreach ($form->emailreceiptsettings as $name => $value) 
                {
                   //make names shorter and set all emailreceiptsettings as properties of form object               
                   $form->$name = $value;   
                }
                $registry = new JRegistry;
                //Convert resultmailsettings field to an array
                $registry->loadString($form->emailresultsettings);
                $form->emailresultsettings = $registry->toArray();
                foreach ($form->emailresultsettings as $name => $value) 
                {
                   //make names shorter and set all emailreceiptsettings as properties of form object               
                   $form->$name = $value;   
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
                $registry = new JRegistry;
                //Convert layoutsettings field to an array
                $registry->loadString($form->layoutsettings);
                $form->layoutsettings = $registry->toArray();
                foreach ($form->layoutsettings as $name => $value) 
                {
                   //make names shorter and set all layoutsettings as properties of form object               
                   $form->$name = $value;   
                }
                $registry = new JRegistry;
                //Convert layoutsettings field to an array
                $registry->loadString($form->captchaoptions);
                $form->captchaoptions = $registry->toArray();
                foreach ($form->captchaoptions as $name => $value) 
                {
                   //make names shorter and set all captchaoptions as properties of form object               
                   $form->$name = $value;   
                }
                $registry = new JRegistry;
                //Convert viscaptchaoptions to array
                $registry->loadString($form->viscaptchaoptions);
                $form->viscaptchaoptions = $registry->toArray();
                //create property for information about validity of user inputs
                $form->isValid = true;
                $form->errors = array();
                $app->setUserState('com_visforms.form' . $this->_id, $form);
             }
             $this->form = $form;
             return $this->form;
         }
         
    /**
    * Method to get the form fields definition from database
    *
    * @return  array of form fields
    *
    * @since   11.1
    */

     public function getItems()
     {
        $db = JFactory::getDbo();
        $visform = $this->getForm();
        $query = ' SELECT * FROM #__visfields where fid='.$this->_id.' and published=1 ' ;
        $query .= 'AND NOT (editonlyfield = 1)';
        $query .= ' order by ordering asc';
        $items = $this->_getList( $query );
        return $items;
     }
         
    /**
    * Method to build the field item list
    *
    * @return  array of form fields
    *
    * @since   11.1
    */
     public function getValidatedFields($submissionType = 0) 
     {
		$visform = $this->getForm();
        $app = JFactory::getApplication();
        $this->fields = $app->getUserState('com_visforms.form' . $this->_id . '.fields');
         if (!is_array($this->fields))
         {
            $fields = $this->getItems();
            $n=count($fields );
            //get basic field definition
            for ($i=0; $i < $n; $i++)
            { 
                $ofield = VisformsField::getInstance($fields[$i], $visform);
                if(is_object($ofield))
                {
                    if ($submissionType == self::$editSubmission)
                    {
                        $cid = $this->input->get('cid', 0, 'int');
                        if (!empty($cid))
                        {
                            $ofield->setRecordId($cid);
                        }
                    }
                    $fields[$i] = $ofield->getField();
                }
            }
            // perform business logic
            for ($i=0; $i < $n; $i++)
            {
                $ofield = VisformsBusiness::getInstance($fields[$i], $visform, $fields);
                if(is_object($ofield))
                {
                    //as there may be interactions between the field processed and the rest of the form fields we always return the fields array
                    $fields = $ofield->getFields();
                }
            }

            //only after we have performed the business logic on all fields we know which fields are disabled
            //we can validate the "required" only then, because we have to omit the required validation for disabled fields!
            //we use the business class for this as well
             for ($i=0; $i < $n; $i++)
            {
                $ofield = VisformsBusiness::getInstance($fields[$i], $visform, $fields);
                if(is_object($ofield))
                {
                    $fields[$i] = $ofield->validateRequired();
                }
            }
            $this->fields = $fields;
        }
        $app->setUserState('com_visforms.form' . $this->_id . '.fields', $this->fields);
        return $this->fields;
     } 
     
     public function reloadFields()
     {
         $visform = $this->getForm();
         $app = JFactory::getApplication();
         $this->fields = $app->getUserState('com_visforms.form' . $this->_id . '.fields');
         if (!is_array($this->fields))
         {
             //should not happen because wie have already stored fields in user state
             //but if, the field should be completely correct
            $fields = $this->getValidatedFields();
         }
         else
         {
            $fields = $this->getItems();
            $n=count($fields );
            //get basic field definition
            for ($i=0; $i < $n; $i++)
            { 
                $ofield = VisformsField::getInstance($fields[$i], $visform);
                if(is_object($ofield))
                {
                    $fields[$i] = $ofield->getField();
                }
            }
            // perform business logic
            for ($i=0; $i < $n; $i++)
            {
                $ofield = VisformsBusiness::getInstance($fields[$i], $visform, $fields);
                if(is_object($ofield))
                {
                    //as there may be interactions between the field processed and the rest of the form fields we always return the fields array
                    $fields = $ofield->getFields();
                }
            }

            //only after we have performed the business logic on all fields we know which fields are disabled
            //we can validate the "required" only then, because we have to omit the required validation for disabled fields!
            //we use the business class for this as well
            for ($i=0; $i < $n; $i++)
            {
                $ofield = VisformsBusiness::getInstance($fields[$i], $visform, $fields);
                if(is_object($ofield))
                {
                    $fields[$i] = $ofield->validateRequired();
                }
            }
         }
         $this->fields = $fields;
         $app->setUserState('com_visforms.form' . $this->_id . '.fields', $this->fields);
         return $this->fields;
     }
     
     //called from view
     public function getFields()
     {
		 $visform = $this->getForm();
         $app = JFactory::getApplication();
         $this->fields = $app->getUserState('com_visforms.form' . $this->_id . '.fields');
         if (!is_array($this->fields))
         {
            $fields = $this->getValidatedFields();
         }
         else
         {
             $fields = $this->fields;
         }
         $n=count($fields );
         //prepare HTML
            for ($i=0; $i < $n; $i++)
            {
                $html = VisformsHtml::getInstance($fields[$i]);
                if (is_object($html))
                {
                    $ofield = VisformsHtmllayout::getInstance($visform->formlayout, $html);
                    if (is_object($ofield))
                    {
                        $fields[$i] = $ofield->prepareHtml();
                    }
                }
            }

            $this->fields = $fields;
            return $this->fields;
     }


     public function clearPostValue ($field)
     {
            //Form was send, but php validation failed. We use submitted values from post and show them as field values
            //In truth this will not work for selects but they are handle seperatly anyway
           if (isset($_POST[$field->name]))
           {
               if ($field->typefield == "select" || $field->typefield == "multicheckbox")
               {
                   $this->input->post->set($field->name, array());
               }
               else
               {
                   $this->input->post->set($field->name, '');                      
               }
           } 
     }
	
	/**
	 * Method to add 1 to hits
	 * @return void
	 */
	function addHits()
	{
		$dba	= JFactory::getDbo();
		$visform = $this->getForm();
		
		if (isset($visform->id))
		{
			$query = " update #__visforms set hits = ".($visform->hits + 1). " where id = ".$visform->id;

			$dba->SetQuery($query);		
			$dba->execute();
		}
	}
	
	/**
	 * Method to save data user input
	 *
	 * @paran array $post user input from $_POST
	 * @return void
	 * @since Joomla 1.6
	 */
	function saveData()
	{		
		//Form and Field structure and info from db
		$visform = $this->getForm();
        $fields = $this->getValidatedFields();
        $visform->fields = $fields;
        $folder	= $visform->uploadpath;
        
        //time zone
        $config = JFactory::getConfig();
        $offset = $config->get('offset', 'UTC');
        if ($offset)
        {
            date_default_timezone_set($offset);
        }
                
		if (VisformsmediaHelper::uploadFiles($visform) === false)
        {
            return false;
        }
		
		if ($visform->saveresult == 1) 
		{	
            if ($this->storeData($visform) === false)
            {
                return false;
            }
		}
        
        /* ************************** */
		/*     Send Email Receipt     */
		/* ************************** */
		if ($visform->emailreceipt == 1) 
		{	
            $this->sendReceiptMail($visform);
		}	
		
		/* ************************* */
		/*     Send Email Result     */
		/* ************************* */
		if ($visform->emailresult == 1) 
		{
			$this->sendResultMail($visform);			
		}			
		return true;
	}
	
	
	/**
	  * Method to retrieve menu params
	  *
	  * @return array Array of objects containing the params from active menu
	  * @since Joomla 1.6
	  */
	
	function getMenuparams () 
	{
		$app = JFactory::getApplication();
		$menu_params = $app->getParams();
		$this->setState('menu_params', $menu_params);		
		return $menu_params;
	}
        /**
	 * Deletes linebreaks in MySQL Database
	 *
	 * @param id formId Id if submitted form
	 * @param array fields Formfields
	 *
	 * @return boolean
	 * @since Joomla 1.6
	 */
    public function cleanLineBreak ($formId, $fields)
    {
        $db = JFactory::getDbo();
        $id = $db->insertid();
        $query = $db->getQuery(true);
        $updatefields = array();
        for ($i = 0; $i<count($fields); $i++)
        {
            $updatefields[] = $db->quoteName('F' . $fields[$i]->id) . ' = replace (F' . $fields[$i]->id . ', CHAR(13,10), \' \')';
        }
        $conditions = array( $db->quoteName('id') . ' = ' .$id);
        $query->update($db->quoteName('#__visforms_' . $formId))->set($updatefields)->where($conditions);
        $db->setQuery($query);
        $result = $db->execute();
    }
        
    /**
    * store data in db
    * 
    * @param object $visform Form Object with attached field information
    */
    private function storeData($visform)
    {
       $folder	= $visform->uploadpath;
       $user = JFactory::getUser();
       $db = JFactory::getDbo();
       $lockValidationFields = array();

       $datas = new stdClass();
       $datas->created = date("Y-m-d H:i:s");
       $datas->ipaddress = $_SERVER['REMOTE_ADDR'];
       $datas->published = ($visform->autopublish == 1) ? 1 : 0;
       $datas->created_by = (isset($user->id)) ? $user->id : 0;

       $n=count($visform->fields );
       for ($i=0; $i < $n; $i++)
       {	
           $field = $visform->fields[$i];
           if ((empty($field->isButton)) && ($field->typefield != 'fieldsep') && (empty($field->isDisabled)))
           {
               if ($field->typefield == 'file' && isset($_FILES[$field->name]['name']) && $_FILES[$field->name]['name'] !='' )
               { 
                   //save folder and filename
                   $file = new stdClass();
                   $file->folder = $folder;
                   $file->file = $field->file['new_name'];
                   $registry = new JRegistry($file);
                   $dbfieldvalue = $registry->toString();

               } 
               else if (isset($field->dbValue))
               {
                   $dbfieldvalue = $field->dbValue;
               } 
               else 
               {
                   $dbfieldvalue = '';
               }
               $dbfieldname = 'F' . $field->id;
               //Add field to insert object
               $datas->$dbfieldname = $dbfieldvalue;
               
               if ((!empty($field->uniquevaluesonly)) && (!empty($dbfieldvalue)))
               {
                   $validation = new stdClass();
                   $validation->id = $field->id;
                   $validation->name = $dbfieldname;
                   $validation->value = $dbfieldvalue;
                   $validation->publishedonly = (!empty($field->uniquepublishedvaluesonly)) ? 1 : 0;
                   $validation->label = $field->label;
                   $validation->typefield = $field->typefield;
                   $lockValidationFields[] = $validation;

               }
               unset($file);
               unset($dbfieldvalue);
               unset($dbfieldname);
               unset($validation);
           }
       }			
       try
       {
           /*if ($_SERVER['REMOTE_ADDR'] == '192.168.1.217' )
           {
               sleep(25);
           }*/
           foreach ($lockValidationFields as $test)
           {
               if ((!empty($test)) && (is_object($test)))
               {
                   $query = $db->getQuery(true);
                   $query->select($db->qn('id'))
                       ->from($db->qn('#__visforms_'.$visform->id));
                   if (in_array($test->typefield, array('select', 'multicheckbox')))
                   {
                       $formSelections = JHtmlVisformsselect::explodeMsDbValue($test->value);
                       $storedSelections = $query->concatenate(array($db->q(JHtmlVisformsselect::$multiSelectSeparator), $db->quoteName($test->name), $db->q(JHtmlVisformsselect::$multiSelectSeparator)));
                       foreach($formSelections as $formselection)
                       {
                           $query->where('(' . $storedSelections  . ' like ' . $db->q(JHtmlVisformsselect::$multiSelectSeparator . $formselection . JHtmlVisformsselect::$multiSelectSeparator) . ')');
                       }                            
                   }
                   else
                   {
                       $query->where($db->qn($test->name) . ' = ' . $db->q($test->value));
                   }
                   if (!empty($test->publishedonly))
                   {
                       $query->where($db->qn('published') . ' = ' . 1);
                   }
                   $db->setQuery($query);
                   try
                   {
                       $valueExistes = $db->loadResult();
                   }
                   catch (RuntimeException $e)
                   {
                   }
                   if (!empty($valueExistes))
                   {
                       //if field is select or multiselect, disable the option
                       /*if (in_array($test->typefield, array('select', 'multicheckbox')))
                       {
                           $this->disableOption($visform, $test->id, $formSelections);
                       }*/
                       //throw New RuntimeException(JText::sprintf('COM_VISFORMS_UNIQUE_VALUE_REQUIRED', $test->label, $test->value));
                       throw New RuntimeException('');
                   }
               }
           }
           $result = $db->insertObject('#__visforms_'.$visform->id, $datas);
        } 
       catch(RuntimeException $e)
       {
           $message = $e->getMessage();
           if (!empty($message))
           {
                throw new RuntimeException(JText::_('COM_VISFORMS_SAVING_DATA_FAILED'). ' ' . $message);
           }
           else
           {
               throw New RuntimeException('');
           }
       }
       //we store the record set in db regardsless of whether someone has submitted and stored a form with the same unique value in the meantime
       //after storing we check if there are duplicate values for unique value fields in the db
       //if so, we check, if our recordset has the highest id in the group the record sets with duplicate values
       //if so, we delete the record set and throw an error
       $visform->dataRecordId = $db->insertid();
        foreach ($lockValidationFields as $test)
        {
            if ((!empty($test)) && (is_object($test)))
            {
                $query = $db->getQuery(true);
                $query->select($db->qn('id'))
                    ->from($db->qn('#__visforms_'.$visform->id));
                if (in_array($test->typefield, array('select', 'multicheckbox')))
                {
                    $formSelections = JHtmlVisformsselect::explodeMsDbValue($test->value);
                    $storedSelections = $query->concatenate(array($db->q(JHtmlVisformsselect::$multiSelectSeparator), $db->quoteName($test->name), $db->q(JHtmlVisformsselect::$multiSelectSeparator)));
                    foreach($formSelections as $formselection)
                    {
                        $query->where('(' . $storedSelections  . ' like ' . $db->q(JHtmlVisformsselect::$multiSelectSeparator . $formselection . JHtmlVisformsselect::$multiSelectSeparator) . ')');
                    }                            
                }
                else
                {
                    $query->where($db->qn($test->name) . ' = ' . $db->q($test->value));
                }
                if (!empty($test->publishedonly))
                {
                    $query->where($db->qn('published') . ' = ' . 1);
                }
                $query->order($db->qn('id'));
                $db->setQuery($query);
                try
                {
                    $checkValueExistes = $db->loadColumn();
                }
                catch (RuntimeException $e)
                {
                    //$help = true;
                }
                if ((!empty($checkValueExistes)) && (count($checkValueExistes) > 1) && (is_array($checkValueExistes)))
                {
                    $firstOccurence = array_shift($checkValueExistes);
                    //we are not the first recordset stored and have to delete ourselves
                    if (in_array($visform->dataRecordId, $checkValueExistes))
                    {
                        $query = $db->getQuery(true);
                        $query->delete($db->qn('#__visforms_'.$visform->id));
                        $query->where($db->qn('id') . ' = ' . $visform->dataRecordId . ' LIMIT 1');
                        $db->setQuery($query);
                        try
                        {
                            $deleteResult = $db->execute();
                        }
                        catch (RuntimeException $e)
                        {
                            //$help = true;
                        }
                        /*if (in_array($test->typefield, array('select', 'multicheckbox')))
                        {
                            $this->disableOption($visform, $test->id, $formSelections);
                        }*/
                        //throw New RuntimeException(JText::sprintf('COM_VISFORMS_UNIQUE_VALUE_REQUIRED', $test->label, $test->value));
                        throw New RuntimeException('');
                    }

                }
            }
        }
       //Linebreaks confound data structure on export to excels. So we delete them in Database 
       $this->cleanLineBreak ($visform->id, $visform->fields);
       return true;
   }
        
    /**
     * Send Receipt Mail
     * @param object $visform Form Object with attached field information
     */
    private function sendReceiptMail($visform)
    {
        //we can only send a mail, if the form has a field of type email, that contains an email
        $isSendMail = false;
        $emailReceiptTo = '';

        $mail = JFactory::getMailer();
        $mail->CharSet = "utf-8";
        $body = array();
        if (!empty($visform->emailreceipttext))
        {
        //Do some replacements in email text
            $fixedLinks = JHTMLVisforms::fixLinksInMail($visform->emailreceipttext);
            $body[] = JHTMLVisforms::replacePlaceholder($visform, $fixedLinks);
        }
        if ($visform->emailreceiptincformtitle == 1)
        {
            $body[] = JText::_('COM_VISFORMS_FORM') . " : ".$visform->title;
        }
        if ($visform->emailreceiptinccreated == 1)
        {
            $body[] = JText::_( 'COM_VISFORMS_REGISTERED_AT' )." ".date(JText::_('DATE_FORMAT_LC4') . " H:i:s");
        }

        $n=count($visform->fields );
        //Do we have an e-mail field with value? Then get to mail address to which to send the mail to
        for ($i=0; $i < $n; $i++)
        {	
            $field = $visform->fields[$i];

            if ($field->typefield == 'email')
            {					
                if ($field->dbValue)
                {
                    $isSendMail = true;
                    $emailReceiptTo = $field->dbValue;
                    break;
                }
            }
        }

        //Include user inputs if parameter is set to true
        if ($visform->emailreceiptincfield == 1) 
        {	
             $body[] = $this->getMailIncludeData($visform, 'receipt');				
        }
        if ((!(empty($visform->dataRecordId))) && isset($visform->emailreceiptincdatarecordid) && ($visform->emailreceiptincdatarecordid == 1))
        {
            $body[] = JText::_( 'COM_VISFORMS_RECORD_SET_ID' ) . " : " . $visform->dataRecordId;
        }
        if (!isset($visform->emailreceiptincip) || (isset($visform->emailreceiptincip) && ($visform->emailreceiptincip == 1)))
        {
            $body[] = JText::_( 'COM_VISFORMS_IP_ADDRESS' ) . " : " . $_SERVER['REMOTE_ADDR'];
        }
        $mailBody = implode('<br />', $body);
        //Attach files to email
        if ($visform->emailreceiptincfile == 1)
        {
            for ($i=0; $i < $n; $i++) {
                $field = $visform->fields[$i];
                if ($field->typefield == 'file' && isset($_FILES[$field->name]['name']) && $_FILES[$field->name]['name'] !='' )
                {
                    if ($field->file['filepath'] != '') 
                    {
                        $mail->addAttachment($field->file['filepath']);
                    }
                } 
            }
        }

        //send the mail
        if (strcmp($emailReceiptTo,"") != 0 && $isSendMail == true)
        {
            $emailreceiptsubject = JHTMLVisforms::replacePlaceholder($visform, $visform->emailreceiptsubject);
            $mail->addRecipient($emailReceiptTo);

            $mail->setSender( array( $visform->emailreceiptfrom, $visform->emailreceiptfromname ) );
            $mail->setSubject( $emailreceiptsubject );
            $mail->IsHTML (true);
            $mail->Encoding = 'base64';
            $mail->setBody( $mailBody );

            JPluginHelper::importPlugin( 'visforms' ); 
            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger('onVisformsEmailPrepare', array('com_visforms.form.receiptmail', &$mail, $visform));
            $sent = $mail->Send();
        }
    }

    /**
     * Send Result Mail
     */
    private function sendResultMail($visform)
    {
        $mail = JFactory::getMailer();
        $mail->CharSet = "utf-8";
        $emailSender = "";

        //get reply to mail
        $n=count($visform->fields );
        for ($i=0; $i < $n; $i++)
        {	
            $field = $visform->fields[$i];
            if ($field->typefield == 'email')
            {					
                if (($field->dbValue))
                {
                    $emailSender = $field->dbValue;
                    break;
                }
            }
        }
        $body = array();
        if ((!empty($visform->emailresulttext)))
        {
            $fixedLinks = JHTMLVisforms::fixLinksInMail($visform->emailresulttext);
            $body[] = JHTMLVisforms::replacePlaceholder($visform, $fixedLinks);
        }            
        if ((!isset($visform->emailresultincformtitle)) || (isset($visform->emailresultincformtitle) && $visform->emailresultincformtitle == 1))
        {
            $body[] = JText::_('COM_VISFORMS_FORM') . " : ".$visform->title;
        }
        if ((!isset($visform->emailresultinccreated)) || (isset($visform->emailresultinccreated) && $visform->emailresultinccreated == 1))
        {
            $body[] = JText::_( 'COM_VISFORMS_REGISTERED_AT' )." ".date(JText::_('DATE_FORMAT_LC4') . " H:i:s");
        }

        //Include user inputs if parameter is set to true
        if ($visform->emailresultincfield == 1) 
        {
            $body[] = $this->getMailIncludeData($visform, 'result');
        }
        if ((!(empty($visform->dataRecordId))) && isset($visform->emailresultincdatarecordid) && ($visform->emailresultincdatarecordid == 1))
        {
            $body[]= JText::_( 'COM_VISFORMS_RECORD_SET_ID' ) . " : " . $visform->dataRecordId;
        }
        if (!isset($visform->emailresultincip) || (isset($visform->emailresultincip) && ($visform->emailresultincip == 1)))
        {
            $body[] = JText::_( 'COM_VISFORMS_IP_ADDRESS' ) . " : " . $_SERVER['REMOTE_ADDR'];
        }
        $mailBody = implode('<br />', $body);
         //Attach files to email
        if ($visform->emailresultincfile == 1)
        {
            for ($i=0; $i < $n; $i++) {
                $field = $visform->fields[$i];
                if ($field->typefield == 'file' && isset($_FILES[$field->name]['name']) && $_FILES[$field->name]['name'] !='' )
                {
                    if ($field->file['filepath'] != '') 
                    {
                        $mail->addAttachment($field->file['filepath']);
                    }
                } 
            }
        }

        if (strcmp($visform->emailto,"") != 0)
        {
            $mail->addRecipient( explode(",", $visform->emailto) );
        }
        if (strcmp($visform->emailcc,"") != 0)
        {
            $mail->addCC( explode(",", $visform->emailcc) );
        }
        if (strcmp($visform->emailbcc,"") != 0)
        {
            $mail->addBCC( explode(",", $visform->emailbcc) );
        }

        $mail->setSender( array( $visform->emailfrom, $visform->emailfromname ) );
        $subject = JHTMLVisforms::replacePlaceholder($visform, $visform->subject);
        $mail->setSubject( $subject );
        if ($emailSender != "")
        {
            $mail->addReplyTo($emailSender);
        }
        $mail->IsHTML (true);
        $mail->Encoding = 'base64';
        $mail->setBody( $mailBody );

        JPluginHelper::importPlugin( 'visforms' ); 
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onVisformsEmailPrepare', array('com_visforms.form.resultmail', &$mail, $visform));			
        $sent = $mail->Send();
    }
        
    protected function validateCachedFormSettings($form)
    {
        if (empty($form))
        {
            return false;
        }
        if (!is_object($form))
        {
            return false;
        }
        if (empty($form->formlayout))
        {
            return false;
        }
        return true;
    }
    
    public function getRedirectParams($fields, $query = array())
    {
        if (empty($fields))
        {
            return $query;
        }
        foreach ($fields as $field)
        {
            //setting this param is handled by the field
            //only set, if field option addtoredirecturl is enabled
            if (isset($field->redirectParam))
            {
                switch ($field->typefield)
                {
                    //just make sure that values of this field types are not added accidentally
                    case 'file' :
                    case 'image' :
                    case 'submit' :
                    case 'reset' :
                        break;
                    case 'select' :
                    case 'multicheckbox' :
                        $query[$field->name] = array();
                        foreach ($field->redirectParam as $value)
                        {
                            $query[$field->name][] = $value;
                        }
                        break;
                    default :
                        $query[$field->name] = $field->redirectParam;
                        break;
                }               
            }
        }
        return $query;
    }
    
    protected function getMailIncludeData($visform, $type)
    {
        $data = array();
        foreach ($visform->fields as $field)
        {
            $fieldValue = '';
            if (!empty($field->isButton))
            {
                continue;
            }
            if ($field->typefield == 'fieldsep')
            {
                continue;
            }
            if (!empty($field->isDisabled))
            {
                continue;
            }
            switch ($type)
            {
                case 'result' :
                    if (empty($field->includeinresultmail))
                    {
                        continue 2;
                    }
                    break;
                default :
                    if (empty($field->includeinreceiptmail))
                    {
                        continue 2;
                    }
                    break;
            }
            if ($field->typefield == 'file' && isset($_FILES[$field->name]['name']) && $_FILES[$field->name]['name'] !='' )
            {
                switch ($type)
                {
                    case 'result' :
                        if (isset($field->file['filelink']))
                        {
                            $fieldValue = $field->file['filelink'];
                        }
                        else
                        {
                            $fieldValue = $field->file['filepath'];
                        }
                        break;
                    default :
                        if ((!empty($visform->emailrecipientincfilepath)) && (isset($field->file['filelink'])))
                        {
                            $fieldValue = $field->file['filelink'];
                        }
                        else
                        {
                            $fieldValue = $field->file['name_org'];
                        }
                        break;
                }
            }
            else if (isset($field->dbValue))
            {
                $fieldValue = JHtmlVisformsselect::removeNullbyte($field->dbValue);
            }
            //stop execution for this field if fieldvalue is empty and form option is set to hide empty fields in data included in mail
            switch ($type)
            {
                case 'result' :
                    if ((!empty($visform->emailresulthideemptyfields)) && (($fieldValue === '')))
                    {
                        continue 2;
                    }
                    break;
                default :
                    if ((!empty($visform->emailreceipthideemptyfields)) && (($fieldValue === '')))
                    {
                        continue 2;
                    }
                    break;
            }
            if (($type == 'result') && (!empty($visform->receiptmailaslink)) && ($field->typefield == 'email'))
            {
                $fieldValue = '<a href="mailto:'.$fieldValue.'">'.$fieldValue.'</a>';
            }
            $data[] = $field->label . " : " . $fieldValue;
            
        }
        return implode("<br />", $data);
    }
}
