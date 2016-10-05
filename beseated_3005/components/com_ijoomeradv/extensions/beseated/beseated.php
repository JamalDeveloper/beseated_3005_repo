<?php
/*--------------------------------------------------------------------------------
# Ijoomeradv Extension : Beseated_1.0 (compatible with joomla 2.5)
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die( 'Restricted access' );
class beseated {
	public $classname = "beseated";
	public $sessionWhiteList=array('categories.singleCategory');

	function init(){
		jimport('joomla.utilities.date');
		jimport('joomla.html.pagination');

		$lang = JFactory::getLanguage();
		$lang->load('com_beseated');
		$plugin_path = JPATH_COMPONENT_SITE.DS.'extensions';
		$lang->load('beseated',$plugin_path.DS.'beseated', $lang->getTag(), true);

		if(file_exists(JPATH_COMPONENT_SITE.DS.'extensions'.DS.'beseated'.DS."helper.php")){
			require_once(JPATH_COMPONENT_SITE.DS.'extensions'.DS.'beseated'.DS."helper.php");
		}
	}

	function write_configuration(&$d) {
		$db =JFactory::getDbo();
		$query = 'SELECT *
				  FROM #__ijoomeradv_beseated_config';
		$db->setQuery($query);
		$my_config_array = $db->loadObjectList();
		foreach ($my_config_array as $ke=>$val){
			if(isset($d[$val->name])){
				$sql = "UPDATE #__ijoomeradv_beseated_config
						SET value='{$d[$val->name]}'
						WHERE name='{$val->name}'";
				$db->setQuery($sql);
				$db->query();
			}
		}
	}

	function getconfig(){
		$jsonarray=array();
		return $jsonarray;
	}

	function prepareHTML(&$Config)
	{
		//TODO : Prepare custom html for ICMS
	}
}

class lgom_menu {
	public function getRequiredInput($extension,$extTask,$menuoptions){
		$menuoptions = json_decode($menuoptions,true);
		switch ($extTask){
			case 'getAllvideos':
				$selvalue = $menuoptions['remoteUse']['id'];

				$db = JFactory::getDbo();
				$query = "SELECT * FROM #__categories WHERE `extension` = 'com_mediamallfactory'";
				$db->setQuery($query);
				$items = $db->loadObjectList();

				$html = '<fieldset class="panelform">
							<label title="" class="hasTip required" for="jform_request_id" id="jform_request_id-lbl" aria-invalid="false">'.JText::_('COM_IJOOMERADV_MEDIAMALL_SELECT_CATEGORY').'
								<span class="star">&nbsp;*</span>
							</label>';

				$html .= '<select name="jform[request][id]" id="jform_request_id">';
				foreach ($items as $key1=>$value1){
					$selected = ($selvalue == $value1->id) ? 'selected' : '';
					$level = '';
					for ($i=1; $i<$value1->level; $i++){
						$level .= '-';
					}
					$html .= '<option value="'.$value1->id.'" '.$selected.'>'.$level.$value1->title.'</option>';
				}
				$html .= '</select>';
				$html .= '</fieldset>';
				return $html;
				break;
		}
	}

	public function setRequiredInput($extension,$extView,$extTask,$remoteTask,$menuoptions,$data){
		$db = &JFactory::getDBO();
		$options = null;
		switch ($extTask){
			case 'getAllvideos':
				$categoryid = $menuoptions['id'];
				$options = '{"serverUse":{},"remoteUse":{"id":'.$categoryid.'}}';
				break;

		}

		if($options){
			$sql = "UPDATE #__ijoomeradv_menu
					SET menuoptions = '".$options."'
					WHERE views = '".$extension.".".$extView.".".$extTask.".".$remoteTask."'
					AND id='".$data['id']."'";
			$db->setQuery($sql);
			$db->query();
		}
	}
}