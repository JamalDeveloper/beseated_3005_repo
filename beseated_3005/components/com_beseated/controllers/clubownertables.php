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
class BeseatedControllerClubOwnerTables extends JControllerAdmin
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
	public function getModel($name = 'ClubOwnerTableEdit', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function save()
	{
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$view      = $input->get('view');
		$file      = $input->files->get('jform');
		$data      = $input->get('jform',array(),'array');
		$tableID   = $input->get('table_id',0,'int');
		$venueID   = $input->get('venue_id',0,'int');
		$user      = JFactory::getUser();
		$venueID   = $venueID > 0 ? $venueID : $data['venue_id'];
		$imagePath = BeseatedHelper::uploadFile($file['image'],'Venue',$venueID);

		if(!empty($imagePath))
		{
			$postData['image'] = $imagePath;
			$pathInfo          = pathinfo(JPATH_SITE.'/images/beseated/'.$imagePath);
			$thumbPath         = $pathInfo['dirname'].'/thumb/thumb_'.$pathInfo['basename'];
			$storeThumbPath    = "Venue/". $venueID . "/Tables/thumb/thumb_".$pathInfo['basename'];

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

		if(!empty($data['table_name']))
		{
			$postData['table_name']        = $data['table_name'];
		}

		if(!empty($data['premium_table_id']))
		{
			$postData['premium_table_id']  = $data['premium_table_id'];
		}

		if(!empty($data['min_price']))
		{
			$postData['min_price']        = $data['min_price'];
		}

		if(!empty($data['capacity']))
		{
			$postData['capacity']        = $data['capacity'];
		}

		$postData['table_id'] = $data['table_id'];

		if($venueID)
		{
			$postData['venue_id'] = $venueID;
		}
		else
		{
			$postData['venue_id'] = $data['venue_id'];
		}

		//$postData['user_id']              = $user->id;
		$postData['published']              = 1;
		$postData['created']                = date('Y-m-d H:i:s');
		$postData['time_stamp']             = time();

		$model    = $this->getModel();
		$result   = $model->save($postData,$tableID);
		$menuItem = BeseatedHelper::getBctedMenuItem('club-owner-table');
		$Itemid   = $menuItem->id;
		$link     = $menuItem->link.'&Itemid='.$Itemid;

		$this->setRedirect($link,'Table Saved');
	}

	public function deleteTable()
	{
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$tableID   = $input->get('table_id',0,'int');
		$premiumID = $input->get('premium_id',0,'int');

		if(!$tableID)
		{
			echo "400";
			exit;
		}

		$model  = $this->getModel();
		$result = $model->deleteTable($tableID,$premiumID);

		echo $result;
		$app->close();
		exit;

	}
}
