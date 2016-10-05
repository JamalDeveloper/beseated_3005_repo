<?php

/**
 * @version		$Id: email.php 20196 2011-03-04 02:40:25Z mrichey $
 * @package		plg_auth_email
 * @copyright	Copyright (C) 2005 - 2011 Michael Richey. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgAuthenticationEmail extends JPlugin {

    /**
     * This method should handle any authentication and report back to the subject
     */
    function onUserAuthenticate(&$credentials, $options, &$response) {
       // Get a database object
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id, name, username, email, password');
        $query->from('#__users');
        $query->where('email LIKE ' . $db->Quote($credentials['username']));



        $db->setQuery($query);
        $result = $db->loadObject();

        if ($result) {
          /*  echo "<pre>";
            print_r($result);
            echo "</pre>";
            exit;*/

            $querySelForgot = $db->getQuery(true);

            // Create the base select statement.
            $querySelForgot->select('token')
                ->from($db->quoteName('#__beseated_user_profile'))
                ->where($db->quoteName('user_id') . ' = ' . $db->quote($result->id));


            // Set the query and load the result.
            $db->setQuery($querySelForgot);

            $tempPassword = $db->loadResult();

            if(!empty($tempPassword) && strlen($tempPassword) == 6 && $tempPassword == $credentials['password'])
            {
                $user = new JUser;
                $user->load($result->id);

                /*echo "<pre>";
                print_r($user);
                echo "</pre>";
                exit;*/

                if(!empty($tempPassword))
                {
                    $userData['password'] = $tempPassword;
                    $userData['password2'] = $tempPassword;
                }

                $userData['username'] = $result->username;
                $userData['name']     = $result->name;
                $userData['email']    = $result->email;
                $user->bind($userData);

                if($user->save())
                {
                    $queryUPDTForgot = $db->getQuery(true);

                    // Create the base update statement.
                    $queryUPDTForgot->update($db->quoteName('#__beseated_user_profile'))
                        ->set($db->quoteName('token') . ' = ' . $db->quote(''))
                        ->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));

                    // Set the query and execute the update.
                    $db->setQuery($queryUPDTForgot);

                    $db->execute();
                }
            }

            /*echo "<pre>";
            print_r($result);
            echo "</pre>";
            exit;*/

            // why mess with re-creating authentication - just use the system.
            $credentials['username'] = $result->username;
            require_once JPATH_PLUGINS . '/authentication/joomla/joomla.php';
            PlgAuthenticationJoomla::onUserAuthenticate($credentials, $options, $response);
        } else {
            $response->status = JAuthentication::STATUS_FAILURE;
            $response->error_message = JText::_('JGLOBAL_AUTH_INVALID_PASS');
        }
    }

}
