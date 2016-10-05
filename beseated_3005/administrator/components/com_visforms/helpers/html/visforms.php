<?php
/**
 * JHTMLHelper for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */
defined('_JEXEC') or die( 'Direct Access to this location is not allowed.' );

/**
 * Utility class for creating HTML Grids
 *
 * @static
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @since   1.5.5
 */
class JHTMLVisforms
{
    /**
     * Method to displays the credits in backend
     *
     * @return  void
     * @since   1.0.6
     */
    public static function creditsBackend()
    {
      ?>
              <div class="visformbottom span12" style="text-align: center;">
                  Visforms Version <?php echo self::getVersion(); ?>, &copy; 2012 - <?php echo self::getCopyRightDate(); ?> by <a href="http://vi-solutions.de" target="_blank" class="smallgrey">vi-solutions</a>, all rights reserved. 
                      visForms is Free Software released under the <a href="http://www.gnu.org/licenses/gpl-2.0.html"target="_blank" class="smallgrey">GNU/GPL License</a>. 
              </div>
      <?php
      }
      
      /**
        * Method to display credits in frontend
        *
        * @return  void
        *
        * @since   11.1
        */
	public static function creditsFrontend() {
        ?>
            <div id="vispoweredby"><a href="http://vi-solutions.de" target="_blank"><?php echo JText::_( 'COM_VISFORMS_POWERED_BY' ); ?></a></div>
	<?php 
        }
        
     /**
	 * Method to get the version number of installed version of visforms.
	 *
	 * @return  string  version number
	 *
	 * @since   11.1
	 */
	public static function getVersion() {
		$xml_file = JPath::clean(JPATH_ADMINISTRATOR . '/components/com_visforms/visforms.xml');
		$installed_version = '1.0.0';
		if(file_exists($xml_file))
        {   
            $xml = JFactory::getXML($xml_file);
            $installed_version = $xml->version;
        }
		return $installed_version;
	}
    
    /**
	 * Method to get the version number of installed version of visforms.
	 *
	 * @return  string  version number
	 *
	 * @since   11.1
	 */
	public static function getFrontendDataEditVersion() {
		$xml_file = JPath::clean(JPATH_ADMINISTRATOR . '/manifests/files/vffrontedit.xml');
		$installed_version = '1.0.0';
		if(file_exists($xml_file))
        {   
            $xml = JFactory::getXML($xml_file);
            $installed_version = $xml->version;
        }
		return $installed_version;
	}
    
    public static function getCopyRightDate() {
		$xml_file = JPath::clean(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_visforms' . DIRECTORY_SEPARATOR . 'visforms.xml');
		$crd = JHtml::_('date',  'now', 'Y' );
		if(file_exists($xml_file))
        {
            $xml = JFactory::getXML($xml_file);
            $cdate = $xml->creationDate;
        }
		return $crd;
	}
    
    /**
     * Method to create html code for text "Required" according to form layout settings
     * 
     * @param object $form form object 
     * 
     * @return string html code for text "Required"
     * 
     * @since 
     */
    public static function getRequired($form)
    {
        $html = "";
        if ($form->formlayout == "bthorizontal")
        {
            $html .= '<div class="control-group">';
            $html .= '<label class="control-label vis_mandatory">' . JText::_( 'COM_VISFORMS_REQUIRED' ) . ' *</label>';
            $html .= '</div>';
        }
        else
        {
            $html .= '<label class="vis_mandatory visCSSbot10 visCSStop10">' . JText::_( 'COM_VISFORMS_REQUIRED' ) . ' *</label>';
        }
        return $html;
    }
        	
     /**
	 * Method to create html code for tooltips when using ## as seperator between title and tiptext (backwards compatibility)
	 *
	 * @param   object  $fields  Object of form fields
	 *
	 * @return  string  html code for tooltip or field label
	 *
	 * @since   11.1
	 */
	public static function createTip($field) 
	{
		$tip = array();
		$html = "";
			
		//Show Helptext in Tooltip
		if (isset($field->custominfo) && $field->custominfo != "") {
			$tip = explode('##', $field->custominfo, 2);
		}
		
		if ($tip) {
			if (!isset($tip[1])) 
            {
                //tip has no title seperated by ##
				$html = JHtml::_('tooltip', $tip[0],'','',$field->label);
			}
			else
			{
                //tip has text and title 
				$html = JHtml::_('tooltip', $tip[1], $tip[0], '',$field->label);
			}
		 }
		 else
		 {
			//return field label as text
            $html = $field->label;
		 }
		 return $html;
	}
    
    /**
	 * Method to create html code for captcha tooltips
	 *
	 * @param   object  $form  Visforms form object
	 *
	 * @return  string  html code for tooltip or field label
	 *
	 * @since   11.1
	 */
	public static function createCaptchaTip($form) 
	{
		$html = "";
			
		//Show Helptext in Tooltip
        $captchalabel = "Captcha";
        if (isset($form->captchalabel))
        {
            $captchalabel = $form->captchalabel;
        }
        if (isset($form->captchacustominfo) && ($form->captchacustominfo != ""))
        {
            $html = JHtml::_('tooltip', $form->captchacustominfo,'','',$captchalabel); 
        }
		 else 
         {
            $html = $captchalabel;
		 }
		 return $html;
	}
        
    /**
	 * Method to create html code to generate the captcha
	 *
	 * @param   object  $fields  Object of form fields
	 *
	 * @return  string  html code for radio
	 *
	 * @since   11.1
	 */
        public static function getCaptchaHtml ($form, $clear = false)
        {
            $html = "";
            if (isset($form->captcha))
            {
                if ($form->formlayout == "visforms")
                {
                    $html .= '<div class="captchaCont required">';
                }
                //Create a div with the right class where we can put the validation errors into
                $html .= '<div class="fc-tbxrecaptcha_response_field"></div>';
                if ($form->formlayout == "bthorizontal")
                {
                    $html .= '<div class="control-group required">';
                }
                if ($form->formlayout == "btdefault")
                {
                    $html .= '<div class="required">';
                }
                if (!(isset($form->showcaptchalabel)) || ($form->showcaptchalabel == 0))
                {
                    $html .= '<label';
                    if ($form->formlayout == "bthorizontal") 
                    { 
                        $html .= ' class="control-label" '; 
                    }
                    if ($form->formlayout == "visforms") 
                    { 
                        $html .= ' class ="visCSSlabel" '; 
                    }
                    $html .= ' id="captcha-lbl" for="recaptcha_response_field">' . self::createCaptchaTip($form) . '</label>';
                }
                else
                {
                    if ($form->formlayout == "btdefault") 
                    { 
                        $html .= '<span class ="asterix-ancor"></span>'; 
                    }
                    if ($form->formlayout == "bthorizontal") 
                    { 
                        $html .= '<span class="control-label"></span>'; 
                    }
                    if ($form->formlayout == "visforms") 
                    { 
                        $html .= '<label class ="asterix-ancor visCSSlabel"></label>'; 
                    }
                }
                if ($clear && !($form->formlayout == "bthorizontal"))
                {
                    $html .= '<div class="clr"> </div>';
                }
                if ($form->formlayout == "bthorizontal")
                {
                    $html .= '<div class="controls">';
                }
                if ($form->captcha == 1) 
                {
                    
                    $html .= '<img id="captchacode' . $form->id . '" class="captchacode" src="index.php?option=com_visforms&task=visforms.captcha&sid=c4ce9d9bffcf8ba3357da92fd49c2457&id=' . $form->id . '" align="absmiddle"> &nbsp; ';          
                    $html .= '<img alt="' . JText::_( 'COM_VISFORMS_REFRESH_CAPTCHA' ) . '" class="captcharefresh' . $form->id . '" src="' . JURI::root(true) . '/components/com_visforms/captcha/images/refresh.gif' . '" align="absmiddle"> &nbsp;';
                    $html .= '<input class="visCSStop10" required="required" type="text" id="recaptcha_response_field" name="recaptcha_response_field" />'; 
 
                }
                else if ($form->captcha == 2) 
                {
                    $captcha = JCaptcha::getInstance('recaptcha');
                    $html .= $captcha->display(null, 'dynamic_recaptcha_1', 'required');
                }
                if ($form->formlayout == "bthorizontal")
                {
                    $html .= '</div>';
                    $html .= '</div>';
                }
                if ($form->formlayout == "visforms" || $form->formlayout == "btdefault") 
                {
                    $html .= '</div>';
                }
            }
            
            return $html;
        }
    
    public static function getRestrictedId ($restrict)
    {
        return preg_replace('/[^0-9]/', '', $restrict);
    }
        
    /**
     * Method to extract uploadfile Link from JRegistry Object and create link HTML
     * in earlier versions of visforms link information was not stored
     * 
     *
     * @param   object  $registryString  JRegistry Object that contains link and file name information
     *
     * @return  string  ancor tag (HTML) or filename (if no link information is available)
     *
     * @since   11.1
     */
    public static function getUploadFileLink ($registryString) 
    {
         //info about uploaded files are stored in a JSON Object. Earlier versions just have a string.
        $registry = new JRegistry;
        $registry->loadString($registryString);
        $fileInfo = $registry->toArray();
        if (isset($fileInfo['folder']))
        {
            //return link
            return '<a href="' . JUri::root()  . $fileInfo['folder'] . '/' . $fileInfo['file'] . '" target="_blank">'. JUri::root()  . $fileInfo['folder'] . '/' . $fileInfo['file'] . '</a>';
        }   
        else 
        {
            return basename($registryString);
        }
    }
    
    /**
	 * Method to extract upload filename from JRegistry Object
	 *
	 * @param   object  $registryString  JRegistry Object that contains link and file name information
	 *
	 * @return  string  filename
	 *
	 * @since   11.1
	 */
    public static function getUploadFilePath ($registryString)
    {
        //info about uploaded files are stored in a JSON Object. Earlier versions just have a string.
        $registry = new JRegistry;
        $registry->loadString($registryString);
        $fileInfo = $registry->toArray();
        if ((isset($fileInfo['file'])) && (isset($fileInfo['folder'])))
        {
            return $fileInfo['folder'] . '/' . $fileInfo['file'];
        }
        else 
        {
            return basename($registryString);
        }
    }
        
    /**
	 * Method to extract upload filename from JRegistry Object
	 *
	 * @param   object  $registryString  JRegistry Object that contains link and file name information
	 *
	 * @return  string  filename
	 *
	 * @since   11.1
	 */
    public static function getUploadFileName ($registryString)
    {
        //info about uploaded files are stored in a JSON Object. Earlier versions just have a string.
        $registry = new JRegistry;
        $registry->loadString($registryString);
        $fileInfo = $registry->toArray();
        if (isset($fileInfo['file']))
        {
            return $fileInfo['file'];
        }
        else 
        {
            return basename($registryString);
        }
    }
    
    /**
	 * Method to load visforms specific css and javascripts files; Load them only once 
	 *
     * @param   boolean  $includeBootstrap  include additonal CSS with Bootstrap form CSS if set to true
     * 
	 * @return  void
	 *
	 * @since   11.1
	 */
    public static function includeScriptsOnlyOnce ($cssScripts = array('visforms' => true, 'bootstrapform' => false)) 
    {
        // Add css and js links
        $doc = JFactory::getDocument();
        $header = $doc->getHeadData();
        if (!isset ($cssScripts['visforms.min']))
        {
            $cssScripts['visforms.min'] = true;
        }
        //include all css files with "custom" in filename
        $customCSS = self::getCustomCssFileNameList();
        $cssScripts = array_merge($cssScripts, $customCSS);
        //initialize some control vars
        foreach ($cssScripts as $scriptName => $scriptValue)
        {
            $cssAlreadyIncluded = false;
            
            //We use addStyleSheet to include css file. If already included they are stored in this array
            if (isset($header['styleSheets'])) {
                foreach ($header['styleSheets'] as $key => $value) {
                   if (strpos($key, '/media/com_visforms/css/'.$scriptName.'.css') !== false)
                   {
                       $cssAlreadyIncluded = true;
                   }
                }
            }
           
            //we include the css only if it is not already included
            if (!$cssAlreadyIncluded && $scriptValue)
            {
                $doc->addStyleSheet(JURI::root(true).'/media/com_visforms/css/'.$scriptName.'.css');
            }
        }
         //we use addCustomTag to load jQuery library and depending scripts. If already included the are stored in this array
        $jQueryAlreadyIncluded = false;
        $visfromsAlreadyIncluded = false;
        JHtml::_('jquery.framework');
        if (isset($header['custom'])) {
            foreach ($header['custom'] as $value) {
               if (strpos($value, '/media/com_visforms/js/jquery.validate.min.js') !== false)
               {
                   $jQueryAlreadyIncluded = true;
               }
               if (strpos($value, '/media/com_visforms/js/visforms.js') !== false)
               {
                   $visfromsAlreadyIncluded = true;
               }
            }
        }
        //we load all three jQuery scripts unless all three are already included
        if (!$jQueryAlreadyIncluded)
        {
            $doc->addCustomTag('<script type="text/javascript" src="'.JURI::root(true).'/media/com_visforms/js/jquery.validate.min.js"></script>');
        }
        if (!$visfromsAlreadyIncluded)
        {
            $doc->addCustomTag('<script type="text/javascript" src="'.JURI::root(true).'/media/com_visforms/js/visforms.js"></script>');
        }
    }
    
    public static function replacePlaceholder($form, &$text='')
    {
        $fieldValue = ' ';

        if($text != '')
        {
            //enclose pattern in '' and // as delimeter
            $pattern = '/\[[A-Z0-9]{1}[A-Z0-9\-]*]/';
            if (preg_match_all($pattern, $text, $matches))
            {
                //found matches are store in the $matches[0] array
                foreach($matches[0] as $match)
                {
                    $str = trim($match, '\[]');
                    $field = JString::strtolower($str);
                    $fieldValue = " ";
                    if (isset($field) && ($field != "") && (is_array($form->fields)))
                    {
                        foreach ($form->fields as $ffield)
                        {
                            //Match is a real form field
                            if ($field == $ffield->name)
                            {
                                //get field type
                                $fieldtype = $ffield->typefield;
                                //we use the fieldtype to distinguish between upload fields and the rest which can be captured from $field->dbValue
                                if ($fieldtype == "file")
                                {
                                    if (isset($form->emailrecipientincfilepath) && ($form->emailrecipientincfilepath == true) && isset($ffield->file['filelink']))
                                    {
                                        $fieldValue = $ffield->file['filelink'];
                                    }
                                    else if (isset($_FILES[$ffield->name]['name']) && $_FILES[$ffield->name]['name'] !='')
                                    {
                                        $fieldValue = $_FILES[$ffield->name]['name'];
                                    }
                                }
                                else
                                {
                                    $fieldValue = $ffield->dbValue;
                                }
                                $fieldValue = JHtmlVisformsselect::removeNullbyte($fieldValue);
                            }
                        }
                    }
                    
                    //replace the match
                    $newText = preg_replace('\''. preg_quote($match) . '\'', $fieldValue, $text);
                    $text = $newText;
                }
            }
        }
        return $text;
    }
    
    public static function fixLinksInMail(&$text)
    {
        $urlPattern = '/^(http|https|ftp|mailto)\:.*$/i';
        $aPattern = '/<[ ]*a[^>]+href=[("\')]([^("\')]*)/';
        $imgPattern = '/<[ ]*img[^>]+src=[("\')]([^("\')]*)/';
        if (preg_match_all($aPattern, $text, $hrefs))
        {
            foreach($hrefs[1] as $href)
            {
                if(!(preg_match($urlPattern, $href) == 1))
                {
                    //we deal with an intern Url without Root path
                    $link = JURI::base() . $href;
                    $newText = preg_replace('\'' . preg_quote($href ) . '\'', $link, $text);
                    $text = $newText;
                }
            }
        }
        if (preg_match_all($imgPattern, $text, $srcs))
        {
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');
            foreach($srcs[1] as $src)
            {
                if(JFile::exists($src))
                {
                    //we deal with a local img
                    if (!(preg_match('\'' . preg_quote(Juri::base()) . '\'', $src) == 1))
                    {
                        //we deal with an intern Url without base Uri
                        $link = Juri::base() . $src;
                        $newText = preg_replace('\'' . preg_quote($src ) . '\'', $link, $text);
                        $text = $newText;
                    }
                }
            }
        }
        
        return $text;
    }
    
    /**
	 * Method to sort a column in a grid
	 *
	 * @param   string  $title          The link title
	 * @param   string  $order          The order field for the column
	 * @param   string  $direction      The current direction
	 * @param   string  $selected       The selected ordering
	 * @param   string  $task           An optional task override
	 * @param   string  $new_direction  An optional direction for the new column
	 * @param   string  $tip            An optional text shown as tooltip title instead of $title
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
    //this is a copy of JHtml::_(gird.sort...
    //necessary to use our own function because we cannot change the icon prefix in Joomla! grid.sort function into visicon
    //and to allow multipe sort forms on one page (since Visforms 3.7.1)
	public static function sort($title, $order, $direction = 'asc', $selected = '', $task = null, $new_direction = 'asc', $tip = '', $form = 'adminForm', $jsFunction = "Joomla.tableOrdering")
	{
		JHtml::_('behavior.core');
		JHtml::_('bootstrap.tooltip');

		$direction = strtolower($direction);
		$icon = array('arrow-up-3', 'arrow-down-3');
		$index = (int) ($direction == 'desc');

		if ($order != $selected)
		{
			$direction = $new_direction;
		}
		else
		{
			$direction = ($direction == 'desc') ? 'asc' : 'desc';
		}

		$html = '<a href="#" onclick="'.$jsFunction.'(\'' . $order . '\',\'' . $direction . '\',\'' . $task . '\',document.getElementById(\'' . $form . '\'));return false;"'
			. ' class="hasTooltip" title="' . JHtml::tooltipText(($tip ? $tip : $title), 'JGLOBAL_CLICK_TO_SORT_THIS_COLUMN') . '">';

		if (isset($title['0']) && $title['0'] == '<')
		{
			$html .= $title;
		}
		else
		{
			$html .= JText::_($title);
		}

		if ($order == $selected)
		{
			$html .= ' <span class="visicon-' . $icon[$index] . '"></span>';
		}

		$html .= '</a>';

		return $html;
	}
    
    private static function getCustomCssFileNameList()
    {
        jimport('joomla.filesystem.folder');
        $path = JPath::clean(JPATH_ROOT . '/media/com_visforms/css/');
        $result = array();
		$dirFiles = scandir($path);
        $regex = '@^(.*custom.*)(\.css)$@';
		
		foreach ($dirFiles as $key => $value)
		{
            if (is_file(JPath::clean($path . $value)))
            {
                if(preg_match($regex, $value, $match))
                {
                    if ($match)
                    {
                        $match = preg_replace($regex, '$1', $value);
                        $result[$match] = true;
                    }
                }
            }
        }
        return $result;
    }
    
    public static function base64_url_encode($val) 
    {
        return strtr(base64_encode($val), '+/=', '-_,');
    }
    
    static function base64_url_decode($val) 
    {
        return base64_decode(strtr($val, '-_,', '+/='));
    }
}
?>