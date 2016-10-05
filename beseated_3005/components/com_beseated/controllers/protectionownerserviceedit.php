<?php
/**
 * @package     The Beseated.Administrator
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * The Beseated Club Owner Tables Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerProtectionOwnerServiceEdit extends JControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config	An optional associative array of configuration settings.
	 * @return  ContentControllerArticles
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   0.0.1
	 */
	public function getModel($name = 'ProtectionOwnerServiceEdit', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function save()
	{
		$app          = JFactory::getApplication();
		$input        = $app->input;
		$view         = $input->get('view');
		$file         = $input->files->get('jform');
		$data         = $input->get('jform',array(),'array');
		$serviceID    = $input->get('service_id',0,'int');
		$protectionID = $input->get('protection_id',0,'int');
		$protectionID = $protectionID > 0 ? $protectionID : $data['protection_id'];
		$user         = JFactory::getUser();
		$imagePath    = BeseatedHelper::uploadFile($file['image'],'Protection',$protectionID);

		if(!empty($imagePath))
		{
			$postData['image'] = $imagePath;
			$pathInfo          = pathinfo(JPATH_SITE.'/images/beseated/'.$imagePath);
			$thumbPath         = $pathInfo['dirname'].'/thumb/thumb_'.$pathInfo['basename'];
			$storeThumbPath    = "Protection/". $protectionID . "/Services/thumb/thumb_".$pathInfo['basename'];

			if(!JFolder::exists($pathInfo['dirname'].'/thumb/'))
			{
				@chmod($pathInfo['dirname'].'/thumb/',0777);
				JFolder::create($pathInfo['dirname'].'/thumb/');
				@chmod($pathInfo['dirname'].'/thumb/',0777);
			}

			BeseatedHelper::createThumb(JPATH_SITE.'/images/beseated/'.$imagePath,$thumbPath);

			if(!empty($storeThumbPath))
			{
				$postData['thumb_image'] = $storeThumbPath;
			}
		}

		if(!empty($data['service_name']))
		{
			$postData['service_name']        = $data['service_name'];
		}

		if(!empty($data['price_per_hours']))
		{
			$postData['price_per_hours']  = $data['price_per_hours'];
		}

		$postData['service_id']   = $serviceID;

		if ($protectionID){
			$postData['protection_id'] = $protectionID;
		}else{
			$postData['protection_id'] = $data['protection_id'];
		}

		$postData['published']    = 1;
		$postData['created']      = date('Y-m-d H:i:s');
		$postData['time_stamp']   = time();

		$model    = $this->getModel();
		$result   = $model->save($postData,$serviceID);
		$menuItem = BeseatedHelper::getBeseatedMenuItem('protection-services');
		$Itemid   = $menuItem->id;
		$link     = $menuItem->link.'&Itemid='.$Itemid;

		$this->setRedirect($link,'Service Saved');
	}
}
