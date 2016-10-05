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
class JHtmlVisformsselect
{
    
    public static $nullbyte = "\0";
    public static $msdbseparator = "\0, ";
    public static $multiSelectSeparator = "\0, %";
    
    /**
     * Explode database value of stored user input of fields with type select or multicheckbox
     * @param string $dbvalue: multiple values of multiselect or multichechbox are separated by "\0, "
     * @return array
     * @since  Visform 3.7.0
     */
    public static function explodeMsDbValue ($dbvalue)
    {
        $values = explode(self::$msdbseparator , $dbvalue);
        foreach ( $values as $index => $word)
        {
             $values[$index] = trim($word);
        }
        return $values;
    }
    
    //remove Nullbit from string
    public static function removeNullbyte($value)
    {
        if ((!empty($value)) && is_string($value))
        {
            $value = str_replace(self::$nullbyte, "", $value);
        }
        return $value;
    }

    public static function extractHiddenList ($optionString = '')
    {
        $options = array();
        $returnopts = array();
        if ($optionString != "")
        {
            $options = json_decode($optionString);
            foreach ($options as $option)
            {
                if (!empty($option->listitemvalue))
                {
                    $option->listitemvalue = trim($option->listitemvalue);
                }

                if (isset($option->listitemischecked) && ($option->listitemischecked == "1"))
                {
                    $selected = true;
                }
                else
                {
                    $selected = false;
                }

                $returnopts[] = array( 'id' => $option->listitemid, 'value' => $option->listitemvalue, 'label' => $option->listitemlabel, 'selected' => $selected);
            }
        }       
        return $returnopts;
    }
    
    public static function mapDbValueToOptionLabel ($dbValue, $fieldHiddenList)
    {
        $newextracteditemfieldvalues = array();
        $fieldoptions = JHtmlVisformsselect::extractHiddenList($fieldHiddenList);                   
        if (empty($fieldoptions))
        {
            return false;
        }
        $extracteditemvalues = JHtmlVisformsselect::explodeMsDbValue($dbValue);
        $newextracteditemfieldvalues = array();
        foreach ($fieldoptions as $fieldoption)
        {
            foreach ($extracteditemvalues as $extracteditemvalue)
            {
                if ($extracteditemvalue == $fieldoption['value'])
                {
                    $newextracteditemfieldvalues[] = $fieldoption['label'];
                }                      
            }
        }
        return $newextracteditemfieldvalues;
    }
}
?>