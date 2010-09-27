<?php

require_once('sqlpatch_class.php');
class module_installer extends sqlPatch{
	var $module_installer_status = false;
	var $module_db_info = false;
	var $module_installer_path;
	var $module_path;
	var $module_db_path;
	var $module_php_path;
	var $module_version_file_path;
	var $module_version_name = '';
	var $module_patch_level = -1;
	var $module_db_patch_files = array();
	var $module_php_patch_files = array();

	// php4 constructor
	function module_installer(){
		$this->module_installer_path = DIR_FS_ADMIN.'includes/module_installation/';
		$module_installer_status = $this->check_version_tracker_table();
	}

	function set_module($module_code){
		$this->module_code = $module_code;
		// init paths
		$this->module_path = $this->module_installer_path.$this->module_code.'/';
		$this->module_db_path = $this->module_path.'db/';
		$this->module_php_path = $this->module_path.'php/';
		$this->module_version_file_path = $this->module_path.'current_version.txt';		
	}
	
	function check_module(){
		// get current version
		$this->get_current_version();
		$this->get_module_db_info();
		return $this->check_for_upgrade();
	}
	
	function get_module_db_info(){
		global $db;
		$sql = 'SELECT * FROM '.TABLE_MODULE_VERSION_TRACKER.' WHERE module_code=\''.$this->module_code.'\' LIMIT 1';
		require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'ri_utility.php');
		$this->module_db_info = RIUtility::dbResultToArray($db->Execute($sql), true);
		return $this->module_db_info;
	}

	function get_current_version(){
		global $messageStack;
		$file_content = @file_get_contents($this->module_version_file_path);
		if($file_content !== false){
			$temp = explode('|', $file_content);
			if(!is_int($temp[0]) && (int)$temp[0] < 0){
				// sound the alarm here. version has to be a positive number
				$messageStack->add("Version number found in current_version.txt is not a positive integer", 'error');
			}
			else{
				$this->module_patch_level = (int)$temp[0];
				if(isset($temp[1]))
				$this->module_version_name = $temp[1];
			}
		}
		else
		$messageStack->add(sprintf("%s is missing", $this->module_version_file_path), 'warning');
	}

	function check_for_upgrade(){
		global $messageStack;
		$result = false;
		if($this->module_patch_level == -1){
			if($this->module_db_info === false){
				// sound the alarm
				$messageStack->add("Module version could not be found in file and database", 'error');
			}
			else
				$this->create_version_file();
		}
		else{
			if($this->module_db_info === false){
				// not in database yet? = not installed
				$this->insert_module_to_db();
				$result = -1;
			}
			elseif($this->module_patch_level > $this->module_db_info['patch_level']){
				$messageStack->add("Your module needs to be upgraded. Database patches will be applied!", 'warning');
				$result = (int)$this->module_db_info['patch_level'];
			}
			elseif($this->module_version_name != $this->module_db_info['version_name']){
				$messageStack->add("Your module version has been updated! No database patches applied!", 'warning');
				$this->update_module_patch_level($this->module_patch_level);
				$result = false;
			}
		}
		return $result;
	}

	function upgrade_module(){
		global $messageStack;
		if(($current_patch_level = $this->check_module()) === false)
			return;
			
		// let upgrade
		$this->get_db_patch_files();
		$this->get_php_patch_files();
		
		for($patch_level = ($current_patch_level + 1); $patch_level <= $this->module_patch_level; $patch_level++){
			// Check if the php patch is available
			$update_obj = false;
			if(!empty($this->module_php_patch_files[$patch_level])){
				require_once($this->module_php_path.$this->module_php_patch_files[$patch_level]);
				$update_obj = $this->_get_object("update_$patch_level");
			}
			// try to run before db_update
			if($update_obj != false && method_exists($update_obj, 'before_db_update'))
					$update_obj->before_db_update();

			// try to run db update
			if(!empty($this->module_db_patch_files[$patch_level])){
				$file = file($this->module_db_path.$this->module_db_patch_files[$patch_level]);
				if($this->execute_sql_file($file)){
					if($this->update_module_patch_level($patch_level) > 0)
					$messageStack->add("Upgraded database to patch level $patch_level", 'success');
					else
					$messageStack->add("Database upgraded but failed to update version_tracker", 'error');
				}
				else
				$messageStack->add("Faield to upgrade database to patch level $patch_level", 'error');
			}
			
			// try to run after db update
			if($update_obj != false && method_exists($update_obj, 'after_db_update'))
					$update_obj->after_db_update();
		}
	}

	function update_module_patch_level($patch_level){
		global $db;
		$data = array('patch_level' => $patch_level, 'version_name' => $this->module_version_name);
		zen_db_perform(TABLE_MODULE_VERSION_TRACKER, $data, 'update', 'module_code = \''.$this->module_code.'\'');
		return mysql_affected_rows($db->link);
	}

	// TODO: add checking if insert success
	function insert_module_to_db(){
		$data = array('module_code' => $this->module_code,
		'patch_level' => -1,
		'version_name' => $this->module_version_name);
		zen_db_perform(TABLE_MODULE_VERSION_TRACKER, $data);
	}

	function create_version_file(){
		global $messageStack;
		if($handle = @fopen($this->module_version_file_path, "w")){
			$content = $this->module_db_info['patch_level'].'|'.$this->module_db_info['version_name'];
			if (fwrite($handle, $content) === FALSE) {
				$messageStack->add(sprintf("Could write to %s",$this->module_version_file_path), 'error');
			}
			fclose($handle);
		}
		else{
			$messageStack->add(sprintf("Could not open %s to write",$this->module_version_file_path), 'error');
		}
	}

	function get_db_patch_files(){
		$this->module_db_patch_files = $this->_get_patch_files($this->module_db_path, 'sql');
	}

	function get_php_patch_files(){
		$this->module_php_patch_files = $this->_get_patch_files($this->module_php_path, 'php');
	}

	function _get_patch_files($path, $extension){
		global $messageStack;
		// create an array to hold file list
		$results = array();
		// create a handler for the directory
		// keep going until all files in directory have been read
		if ($handler = @opendir($path)){
			while (($file = readdir($handler)) !== false) {
				// if $file isn't this directory or its parent,
				// add it to the results array
				if ($file != '.' && $file != '..'){
					$file_parts = explode('.', $file);
					if(count($file_parts) > 2)
					$messageStack->add(sprintf("Illegal file name found in %s: %s:",$path, $file), 'error');
					else{
						$file_name = (int)$file_parts[0];
						$file_extension = $file_parts[1];
						if($file_extension == $extension){
							for($i =0; $i < $file_name; $i++)
							$results[] = '';
							$results[$file_name] = $file;
						}
					}
				}
			}
			closedir($handler);
		}
		return $results;
	}

	function check_version_tracker_table(){
		global $db, $messageStack;
		// TODO: check if database exists
		if(!@file_exists($this->module_installer_path.'.keep')){
			if($this->execute_sql_file(file($this->module_installer_path.'install.sql'))){
				$messageStack->add("Set up version tracker table", 'success');
				if($handle = @fopen($this->module_installer_path.'.keep', "w"))
				fclose($handle);
				else
				$messageStack->add("Failed to create .keep file", 'error');
			}
			else{
				$messageStack->add("Failed to set up version tracker table", 'error');
				return false;
			}
		}
		return true;
	}

	function _get_object( $name ){
		$obj = false;
		if( class_exists( $name ) ){
			$obj = new $name();
		}
		return $obj;
	}
}
?>