<?php
/**
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6
 * 
 */

// No direct access
defined('_JEXEC') or die;
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Content component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsAEF
{
	public static $allowfrontenddataedit = 0;
    public static $delaydoubleregistrationexists = 1;
    public static $maxsubmissions = 2;
    public static $mailattachments = 3;
    public static $vfcustommailadr = 4;
    public static $vfdataview = 5;
    public static $vfformview = 6;
    public static $searchvisformsdata = 7;
    public static $searchbar = 8;
    
    public static function checkAEF($feature)
    {
        switch($feature)
        {
            case self::$allowfrontenddataedit :
                return self::featureExists(JPATH_ROOT.'/components/com_visforms/views/edit/view.html.php');
                break;
            case self::$delaydoubleregistrationexists :
                return self::featureExists(JPATH_ROOT.'/plugins/visforms/vfdelaydoubleregistration/vfdelaydoubleregistration.xml');
                break;
            case self::$maxsubmissions :
                    return self::featureExists(JPATH_ROOT.'/plugins/visforms/vfmaxsubmissions/vfmaxsubmissions.xml');
                break;
            case self::$mailattachments :
                    return self::featureExists(JPATH_ROOT.'/plugins/visforms/vfmailattachments/vfmailattachments.xml');
                break;
                case self::$vfcustommailadr :
                    return self::featureExists(JPATH_ROOT.'/plugins/visforms/vfcustommailadr/vfcustommailadr.xml');
                break;
                case self::$vfdataview :
                    return self::featureExists(JPATH_ROOT.'/plugins/content/vfdataview/vfdataview.xml');
                break;
                case self::$vfformview :
                    return self::featureExists(JPATH_ROOT.'/plugins/content/vfformview/vfformview.xml');
                break;
                case self::$searchvisformsdata :
                    return self::featureExists(JPATH_ROOT.'/plugins/search/visformsdata/visformsdata.xml');
                break;
            case self::$searchbar :
                    return self::featureExists(JPATH_ROOT.'/administrator/manifests/files/vfsearchbar.xml');
                break;
            default:
                break;
        }
    }
    public static function checkForOneAef()
    {
        $exist = false;
        $vars = get_class_vars('VisformsAEF');
        foreach ($vars as $name => $var)
        {
            //only check static properties
            if (isset(self::$$name))
            {
                if (self::checkAEF(self::$$name))
                {
                    $exist = true;
                    break;
                }
            }
        }
        return $exist;
    }
    public static function checkForAllAef()
    {
        $exist = true;
        $vars = get_class_vars('VisformsAEF');
        foreach ($vars as $name => $var)
        {
            //only check static properties
            if (!(isset(self::$$name)) || (!(self::checkAEF(self::$$name))))
            {               
                $exist = false;
                break;
            }
        }
        return $exist;
    }
    protected static function featureExists ($file)
    {
        if (!(JFile::exists(JPath::clean($file))))
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}
