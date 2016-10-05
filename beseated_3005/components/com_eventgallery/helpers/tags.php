<?php

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

class EventgalleryHelpersTags
{

    /**
     * Checks if at least one needle tag is part of the tags string.
     * Returns false if the needle is empty
     *
     *
     * @param String $tags a comma or space separated string (foo, bar, foo2)
     * @param String $needleTags a comma or space separated string (foo, bar, foo2)
     * @return bool returns true if one element in the needeTags is contained in the tags
     */
    public static function checkTags($tags, $needleTags)
    {
        if (strlen(trim($needleTags)) == 0) {
            return false;
        }

        // handle space and comma separated lists like "foo bar" or "foo, bar"
        $needleTags = self::splitTags($needleTags);
        $heyStackTags = self::splitTags($tags);

        $intersect = array_intersect($needleTags, $heyStackTags);

        if (count($intersect)>0) {
            return true;
        }

        // no match
        return false;

    }

    /**
     * Splits a tag string into an array of tags. Tags can be separated by space or comma.
     * Does not return empty tags
     *
     * @param string $tagString
     * @return array
     */
    public static function splitTags($tagString) {
        $tags =  explode(',', str_replace(" ", ",", $tagString));
        array_walk($tags, 'trim');
        $tags = array_unique($tags);
        foreach($tags as $key=>$tag) {
            $content = trim($tag);
            if (empty($content)) {
                unset($tags[$key]);
            }
        }
        return $tags;
    }

}