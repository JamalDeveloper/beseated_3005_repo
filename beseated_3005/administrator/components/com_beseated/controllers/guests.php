<?php
/**
 * @package     Bcted.Administrator
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * Bcted Packages Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerGuests extends JControllerAdmin
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

		//$this->registerTask('unfeatured',	'featured');
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
	public function getModel($name = 'Guests', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return  void
	 * @since   1.6
	 */
	public function getUserLoyaltyList()
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/beseated/helper.php';
		$this->helper = new beseatedAppHelper();

		$app = JFactory::getApplication();
		$input = $app->input;
		$user_id    = $input->get('user_id', 0, 'int');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_loyalty_point'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
			->where($db->quoteName('is_valid') . ' = ' . $db->quote(1))
			->order($db->quoteName('time_stamp') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObjectList();

		/*echo "<pre>";
		print_r($result);
		echo "</pre>";
		exit;*/

		$output = '<table class="activity" id="accordion">
            <thead>
                <tr>
                    <th width="2%">Date</th>
                    <th width="10%">Reference</th>
                    <th width="15%">Points</th>
                    <th  width="60%">Description</th>
                </tr>
            </thead>
            <tbody>';
            foreach ($result as $key => $loyalty)
            {
            	$output.= '<tr>';
            		 $output.= '<td>'.date('d-m-Y',strtotime($loyalty->created)).'</td>';
                        $output.= '<td>'.$loyalty->loyalty_point_id.'</td>';
                        $output.= '<td>'.$loyalty->earn_point.'</td>';
                        $output.= '<td style="font-size: 15px;">';
						$output.= $loyalty->title ;
                        $output.= '</td>';
            	$output.= '</tr>';
            }

            $output.= '</tbody>';
        $output.= '</table>';

       echo $output;
       exit;
	}

	public function changePoint()
	{
		$app        = JFactory::getApplication();
		$input      = $app->input;
		$user_id    = $input->get('user_id', 0, 'int');
		$point      = $input->get('admin_point', 0, 'int');
		$point_type = $input->get('point_type', 'add', 'string');

		$point_app = "admin.added";
		$title     = 'Added By Admin';

		if($point_type != 'add')
		{
			$point = $point * (-1);
			$point_app = "admin.removed";
			$title     = 'Deducted By Admin';
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base insert statement.
		$query->insert($db->quoteName('#__beseated_loyalty_point'))
			->columns(
				array(
					$db->quoteName('user_id'),
					$db->quoteName('money_usd'),
					$db->quoteName('earn_point'),
					$db->quoteName('point_app'),
					$db->quoteName('title'),
					$db->quoteName('cid'),
					$db->quoteName('is_valid'),
					$db->quoteName('created'),
					$db->quoteName('time_stamp')
				)
			)
			->values(
				$db->quote($user_id) . ', ' .
				$db->quote(0) . ', ' .
				$db->quote($point) . ', ' .
				$db->quote($point_app) . ', ' .
				$db->quote($title) . ', ' .
				$db->quote(0) . ', ' .
				$db->quote(1) . ', ' .
				$db->quote(date('Y-m-d H:i:s')) . ', ' .
				$db->quote(time())
			);

		// Set the query and execute the insert.
		$db->setQuery($query);

		$db->execute();

		echo "200";
		exit;
	}

	public function updateShowPublicTableValue()
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		//$user_id     = $input->get('user_id', '', 'string');
		$checkBoxVal = $input->get('checkBoxVal', 0, 'int');

		$user_id = JRequest::getVar('user_id');

		//echo "<pre>";print_r($checkBoxVal);echo "</pre>";exit;


		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_user_profile'))
			->set($db->quoteName('show_public_table') . ' = ' . $db->quote($checkBoxVal))
			//->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id));
			->where('user_id IN (' . $user_id . ')');

		// Set the query and execute the update.
		$db->setQuery($query);

		$db->execute();

		echo "200";
		exit;
	}


}
