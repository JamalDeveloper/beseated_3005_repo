<?php
/**
 * Visforms default controller
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


/**
 * Visforms Controller Class
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsControllerVisforms extends JControllerLegacy
{
	/**
	 * Method to display the captcha to validate the form
	 *
	 * @access	public
	 */
	public function captcha()
	{
		include("components/com_visforms/captcha/securimage.php");
		
        $model = $this->getModel('visforms');
        $options = array();
        //only try to set options if we have an id parameter in query else we use the captcha default options
        $formid = $this->input->get('id', null);
        if (!empty($formid))
        {
			$visform = $model->getForm();       
            foreach ($visform->viscaptchaoptions as $name => $value) 
            {
               //make names shorter and set all captchaoptions as properties of form object               
               $options[$name] = $value;   
            }
        }
        $img = new Securimage($options);
        $img->namespace = 'form' . $this->input->getInt('id', 0);
		$img->ttf_file = "components/com_visforms/captcha/elephant.ttf";
		$img->show();
	}

	/**
	 * save a record (and redirect to main page)
	 * and send emails
	 * @return void
	 */
	public function send()
	{

        jimport( 'joomla.filesystem.folder');
		$model = $this->getModel('visforms');
		$visform = $model->getForm();	
        $spambotcheck = $visform->spambotcheck;
        
        $app=JFactory::getApplication();
        $return = $this->input->post->get('return', null, 'cmd');
        //if we come from module or plugin we remove a potential page cache created by system cache plugin of the page with the form
        $url = isset($return) ? JHTMLVisforms::base64_url_decode($return) :  '';
        if (!empty($url))
        {
            $cache = JFactory::getCache('page');
            $folder = JPath::clean(JPATH_CACHE  . '/page');
            //clean page cache, used by system cache plugin
            if ( JFolder::exists($folder))
            {
                $cacheresult = $cache->remove($url, 'page');
            }
        }
        $fields = $model->getValidatedFields();
        if (!(isset($visform->errors)))
        {
            $visform->errors = array();
        }		
		// include plugin spambotcheck
		if (isset($visform->spambotcheck) && $visform->spambotcheck == 1)
		{
            JPluginHelper::importPlugin( 'visforms' ); 
            $dispatcher = JDispatcher::getInstance();
			$results = $dispatcher->trigger('onVisformsSpambotCheck', 'com_visforms.visform');
            foreach($results as $result)
            {
                if ($result === true)
				{
                    array_push($visform->errors, JText::_('PLG_VISFORMS_SPAMBOTCHECK_USER_LOGIN_SPAM_TXT'));
					//Show form again, keep values already typed in
					if ($url != "" )
					{
						$this->setRedirect(JRoute::_($url));
						return false;
					}
					else
					{
						$this->display();
						return false;
					}
				}
            } 
		}
		
		//Check that data is ok, in case that javascript may not work properly
		
        foreach ($fields as $field)
        {
            if (isset($field->isValid) && $field->isValid == false)
            { 
                //we have at least one invalid field
                //Show form again, keep values already typed in
                return $this->getErrorRedirect($url);
            }
        }
		
		// Captcha ok?	
		if ($visform->captcha == 1){
			include("components/com_visforms/captcha/securimage.php");
			
			$img = new Securimage();
			$img->namespace = 'form' . $this->input->getInt('id', 0,'int');
			$valid = $img->check($_POST['recaptcha_response_field']);
            //we may deal with an old version of vfformview plugin and the form id is missing in the request, so we fall back on form0 as namespace
            if ($valid == false)
            {
                $img = new Securimage();
                $img->namespace = 'form0';
                $valid = $img->check($_POST['recaptcha_response_field']);
            }
			
			if($valid == false)
            {
                array_push($visform->errors, JText::_("COM_VISFORMS_CODE_INVALID"));
				//Show form again, keep values already typed in
                return $this->getErrorRedirect($url);
			}
		}
        if ($visform->captcha == 2)
        {
            JPluginHelper::importPlugin('captcha');
            $dispatcher = JDispatcher::getInstance();
            $res = $dispatcher->trigger('onCheckAnswer',$_POST['recaptcha_response_field']);
            if(!$res[0])
            {
                array_push($visform->errors, JText::_("COM_VISFORMS_CODE_INVALID"));
				//Show form again, keep values already typed in
                return $this->getErrorRedirect($url);
            }
        }
		
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$send_once = $app->getUserState('vis_send_once'.$visform->id);
        if (!empty($send_once))
        {
            $app->setUserState('vis_send_once'.$visform->id, null);
        }
		else if (isset( $_SESSION['vis_send_once'.$visform->id])) {
			unset($_SESSION['vis_send_once'.$visform->id]);			
		}
        else 
        {
            
            array_push($visform->errors, JText::_('COM_VISFORMS_CAN_SENT_ONLY_ONCE'));
            //Show form again, keep values already typed in
            return $this->getErrorRedirect($url);		
		}
		
        //trigger before save event
        JPluginHelper::importPlugin( 'visforms' ); 
        $dispatcher = JDispatcher::getInstance();
        $onBeforeFormSaveResults = $dispatcher->trigger('onVisformsBeforeFormSave', array ('com_visforms.form', $visform, $fields));
        if ((!empty($onBeforeFormSaveResults)) && is_array($onBeforeFormSaveResults))
        {
            foreach ($onBeforeFormSaveResults as $onBeforeFormSaveResult)
            {
                if ($onBeforeFormSaveResult === false)
                {
                    return $this->getErrorRedirect($url);
                }
            }
        }
		//save data to db
        try
        {
            $model->saveData();
        } 
        catch(RuntimeException $e)
        {
			$message = $e->getMessage();
            if (empty($message))
            {
                $fields = $model->reloadFields();
            }
            //we get a custom error message set by visforms
            array_push($visform->errors, $e->getMessage());
            //Show form again, keep values already typed in
            return $this->getErrorRedirect($url);
        }
        
        //trigger after save event
        $dispatcher->trigger('onVisformsAfterFormSave', array ('com_visforms.form', $visform, $fields));
        		
		$msg = JText::sprintf('COM_VISFORMS_FORM_SEND_SUCCESS', 1);		
		if (!empty($visform->redirecturl)) 
        {
            $tmpUrl = new JUri($visform->redirecturl);
            $query = $tmpUrl->getQuery(true);
            $urlParams = $model->getRedirectParams($fields, $query);
            if (!empty($urlParams))
            {
               
                $tmpUrl->setQuery($urlParams);
                $visform->redirecturl = $tmpUrl->toString();
            }
			$this->setRedirect(JRoute::_($visform->redirecturl));
			$app->setUserState('com_visforms.form' . $visform->id , null);
            return true;
        } 
        else if ((empty($visform->redirecturl)) && (!empty($visform->textresult)))
        {
            //remove result page from page cache
            $folder = JPath::clean(JPATH_CACHE  . '/page');
            //clean page cache, used by system cache plugin
            if ( JFolder::exists($folder))
            {
                $cache = JFactory::getCache('page');
                $uri = JUri::getInstance();
                $prefix = $uri->toString(array('scheme', 'host', 'port'));
                $cacheid = $prefix . JRoute::_('index.php?option=com_visforms&view=message&layout=message&id='.$visform->id.$tmpl, false);
                $cacheresult = $cache->remove($cacheid, 'page');
            }
            
            $message = JHTMLVisforms::replacePlaceholder($visform, $visform->textresult);

            if ($tmpl = $this->input->get('tmpl', null, 'cmd'))
            {
                $tmpl = "&tmpl=" . $tmpl;
            }
            $menu_params = $app->getParams();
            $app->setUserState('com_visforms.form' . $visform->id . '.menu_params', $menu_params);		
            $app->setUserState('com_visforms.form' . $visform->id . '.message', $message);
            $app->setUserState('com_visforms.form' . $visform->id . '.fields', null);
            $this->setRedirect(JRoute::_('index.php?option=com_visforms&view=message&layout=message&id='.$visform->id.$tmpl, false));
            return true;
        }
        else
        {
            $app->setUserState('com_visforms.form' . $visform->id , null);
			$this->setRedirect(JRoute::_(JURI::base()), $msg);
            return true;
		}
	}
    
    protected function getErrorRedirect($url = '')
    {
        if ($url != '' )
        {
            $this->setRedirect(JRoute::_($url));
        }
        else
        {
            $this->display();
        }
        return false;
    }
}
?>
