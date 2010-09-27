<?php
require_once(DIR_WS_CLASSES.'module_installer.php');
class module_manager extends module_installer {

	function list_modules(){
		global $db;
		$sql = "SELECT * FROM ".TABLE_MODULE_VERSION_TRACKER;
		require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'ri_utility.php');
		$modules = RIUtility::dbResultToArray($db->Execute($sql));
		return $modules;
	}

	function update_module($module_id){
		$module_code = $this->_get_module_code_by_id($module_id);
		if(!empty($module_code)){
			$this->set_module($module_code);
			$this->upgrade_module();
		}
	}

	function _get_module_code_by_id($module_id){
		global $db;
		$sql = 'SELECT module_code FROM '.TABLE_MODULE_VERSION_TRACKER.' WHERE ID='.$module_id;
		$result = $db->Execute($sql);
		return (($result->RecordCount() > 0) ? $result->fields['module_code'] : '');
	}

	function get_new_modules($installed_modules = array()){
		$TrackDir=opendir($this->module_installer_path);
		$result = array();
		while ($file = readdir($TrackDir)) {
			if (!in_array($file,$installed_modules) && $file != "." && $file != ".." && 
				$file != ".svn" && is_dir($this->module_installer_path.$file)){
			$result[] = $file;	
			}
		}
		closedir($TrackDir);
		return $result;
	}
}
?>