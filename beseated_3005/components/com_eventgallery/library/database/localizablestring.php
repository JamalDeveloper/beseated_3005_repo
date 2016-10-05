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
/**
 * Class EventgalleryLibraryDatabaseLocalizablestring handles a string which contains multiple languages.
 * As of now this string has to be json encoded and contains key value pairs where the key is the locale like en_US or de_DE
 */

class EventgalleryLibraryDatabaseLocalizablestring
{

    var $_entries = NULL;
    var $_jsonstring = NULL;

    function __construct($jsonstring)
    {
        $this->_jsonstring = $jsonstring;
        $this->_entries = json_decode($jsonstring);
    }

    /**
     * @return string returns the encoded entries so you can store them into a database.
     */
    public function getEncodedString()
    {
        return json_encode($this->_entries);
    }

    /**
     * @param string $langTag the language tag you want to get the value for. If the tag is null, 
     * the current lang is used. If no value is available for the given language it tries to use 
     * the system default language for the front end.
     *
     * @return string the value for the given language.
     */
    public function get($langTag = NULL)
    {

        if ($langTag == NULL) {
            $lang = JFactory::getLanguage();
            $langTag = $lang->getTag();
        }

        /**
         * @var \Joomla\Registry\Registry $languageParams
         */
        $languageParams =  JComponentHelper::getParams('com_languages');
        $defaultLanguageTag = $languageParams->get('site');

        if (isset($this->_entries->$langTag)) {
            $value = $this->_entries->$langTag;
            if (strlen(trim($value))>0) {
                return $value;
            }            
        }

        if (isset($this->_entries->$defaultLanguageTag)) {
            return $this->_entries->$defaultLanguageTag;
        }

        if (count($this->_entries)>0) {
            return "";
        }

        return $this->_jsonstring;
    }

    /**
     * @param string $langTag the language tag like en_US or de_DE
     * @param string $value   the value for the specified language
     */
    public function set($langTag, $value)
    {
        if ($value == NULL || $langTag == NULL) {
            return;
        }

        $this->_entries->$langTag = $value;
    }

}