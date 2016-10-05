<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Joomla! Formatted Text File Log class
 *
 * This class is designed to use as a base for building formatted text files for output. By
 * default it emulates the Syslog style format output. This is a disk based output format.
 *
 * @package     Joomla.Platform
 * @subpackage  Log
 * @since       11.1
 */
$version =  new JVersion();
if ($version->isCompatible('3.0')) {
    class JLogLoggerEventgalleryformattedtext extends JLogLoggerFormattedtext
    {
        protected function initFile()
        {

            $logFilenameBase = str_replace('.php', '', $this->path);

            $logFile = $logFilenameBase.'.php';

            if(JFile::exists($logFile)) {
                if(@filesize($logFile) > 1048756) {
                    $altLog = $logFilenameBase.'-'.time().'.php';
                    JFile::copy($logFile, $altLog);
                    JFile::delete($logFile);
                }
            }


            // If the file doesn't already exist we need to create it and generate the file header.
            if (!is_file($this->path))
            {

                // Make sure the folder exists in which to create the log file.
                JFolder::create(dirname($this->path));

                // Build the log file header.
                $head = $this->generateFileHeader();
            }
            else
            {
                $head = false;

            }

            // Open the file for writing (append mode).
            if (!$this->file = fopen($this->path, 'a'))
            {
                throw new RuntimeException('Cannot open file for writing log');
            }
            if ($head)
            {
                if (!fwrite($this->file, $head))
                {
                    throw new RuntimeException('Cannot fput file for log');
                }
            }
        }
    }

} else {
    class JLoggerEventgalleryformattedtext extends JLoggerFormattedtext
    {
        protected function initFile()
        {

            $logFilenameBase = str_replace('.php', '', $this->path);

            $logFile = $logFilenameBase.'.php';

            if(JFile::exists($logFile)) {
                if(@filesize($logFile) > 1048756) {
                    $altLog = $logFilenameBase.'-'.time().'.php';
                    JFile::copy($logFile, $altLog);
                    JFile::delete($logFile);
                }
            }


            // If the file doesn't already exist we need to create it and generate the file header.
            if (!is_file($this->path))
            {

                // Make sure the folder exists in which to create the log file.
                JFolder::create(dirname($this->path));

                // Build the log file header.
                $head = $this->generateFileHeader();
            }
            else
            {
                $head = false;

            }

            // Open the file for writing (append mode).
            if (!$this->file = fopen($this->path, 'a'))
            {
                throw new RuntimeException('Cannot open file for writing log');
            }
            if ($head)
            {
                if (!fwrite($this->file, $head))
                {
                    throw new RuntimeException('Cannot fput file for log');
                }
            }
        }
    }

}



