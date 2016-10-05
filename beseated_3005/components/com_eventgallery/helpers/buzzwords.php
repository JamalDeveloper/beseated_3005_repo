<?php
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class BuzzwordsHelper
{
    public static function validateBuzzwords($buzzwords, $text)
    {
        foreach ($buzzwords as $buzzword) {
            if (strlen($buzzword) > 0) {
                $buzzword = strtoupper(trim($buzzword));
                if (strpos(strtoupper("  " . $text), strtoupper($buzzword)) > 0) {
                    return false;
                }
            }
        }
        return true;
    }
}
