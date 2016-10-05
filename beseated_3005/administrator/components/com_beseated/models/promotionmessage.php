<?php
/**
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Beseated Events Model
 *
 * @since  0.0.1
 */
class BeseatedModelPromotionMessage extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelList
	 * @since   0.0.1
	 */

	public $total;

	///$this->pagination->total;
	public function __construct($config = array())
	{
		/*if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'promotion_message_id','promotion_message_id',
				'element_name','element_name',
				'city','city',
				'people_count','people_count'
			);
		}*/

	   $this->total = 0;

		parent::__construct($config);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	public function getPromotionDetails()
	{
		//JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$search = $this->getState('filter.search');

		$limit     = $this->state->get('list.limit');
		$start     = $this->state->get('list.start');
		//$orderDirn = $this->state->get('list.direction');
		//$orderCol  = $this->state->get('list.ordering');

		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_promotion_message'));

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('(subject LIKE ' . $like.') OR (message LIKE '.$like .') OR (city LIKE '.$like .')');
		}

		$db->setQuery($query);

		$promotionMsgDetail = $db->loadObjectList();

		foreach ($promotionMsgDetail as $key => $msgDetail)
		{
			$element_name = $msgDetail->element_type.'_name';

			$tblElement        = JTable::getInstance(ucfirst($msgDetail->element_type), 'BeseatedTable');
			$tblElement->load($msgDetail->element_id);

			$elem_name = $tblElement->$element_name;

			$msgDetail->element_name = $elem_name;

		}

		$fullordering = $this->state->get('list.fullordering');


		if(empty($fullordering))
		{
			$fullordering ='created Desc';
		}

		$orderArray = explode(" ", $fullordering);

		$ordering = $orderArray[0];
		$direction = $orderArray[1];

		if(strtoupper($direction) == 'ASC')
		{
			$sortingOrder = SORT_ASC;
		}
		else
		{
			$sortingOrder = SORT_DESC;
		}

		$this->array_sort_by_column($promotionMsgDetail,$ordering,$sortingOrder);

		$this->total = count($promotionMsgDetail);


		$limit     = $this->state->get('list.limit');
		$start     = $this->state->get('list.start');
		$promotionMsgDetail = array_slice($promotionMsgDetail, $start, $limit);

		return $promotionMsgDetail;
	}

	function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
	{
		$sort_col = array();

		if(empty($col))
		{
			$col = 'promotion_message_id';
			$dir = SORT_ASC;
		}

		foreach ($arr as $key=> $row)
		{
			$sort_col[$key] = $row->$col;
		}

		//echo "<pre/>";print_r($dir);
		array_multisort($sort_col, $dir, $arr);
	}

	public function getPagination()
	{
		//echo "<pre>";print_r($this->total);echo "</pre>";
		//$this->total = 10;
		jimport('joomla.html.pagination');
		$this->_pagination = new JPagination($this->total, $this->state->get('list.start'), $this->state->get('list.limit') );
		return $this->_pagination;
	}

}
