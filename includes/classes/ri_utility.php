<?php
// PHP4 always pass object by value (default) while PHP5 pass by reference
if (version_compare(phpversion(), '5.0') < 0) {
    eval('
    function clone($object) {
      return $object;
    }
    ');
}


## HOMEBREW str_ireplace() FOR PRE-PHP 5.0
if(!function_exists('str_ireplace')) {
	function str_ireplace($search,$replace,$subject) {
	$search = preg_quote($search, "/");
	return preg_replace("/".$search."/i", $replace, $subject); } }
	
class RIUtility{
	
	static function dbResultToString($glue, $db_result, $field=''){
		$temp_array = array();
		if(self::isObj($db_result,'queryFactoryResult')){
			// We need to clone, because we don't want to touch the real object
			if($db_result->RecordCount() >0){
				while(!$db_result->EOF){
					if(empty($field)){
						foreach($db_result->fields as $key => $value)
							$temp_array[] = $value;
					}
					else 
						$temp_array[] = $db_result->fields[$field];
					$db_result->MoveNext();
				}
				$db_result->Move(0);
			}
		}
		return implode($glue,$temp_array);
	}
	
	static function dbResultToArray($db_result, $convert_array = false){
		$final_array = array();
		if(self::isObj($db_result,'queryFactoryResult')){
			// We need to clone, because we don't want to touch the real object
			if($db_result->RecordCount() >0){
				while(!$db_result->EOF){
					$temp_array = array();
					foreach($db_result->fields as $key => $value)
						$temp_array[$key] = $value;
					$final_array[] = $temp_array;
					$db_result->MoveNext();
				}				
				$db_result->Move(0);
			}
			else
				return false;
		}
		else 
			return false;
		if(count($final_array) > 1 || !$convert_array)
			return $final_array;
		else
			return $final_array[0];
	}
	
	static function getArrayField(&$array, $field=null){
		if(empty($field) || !array_key_exists($field, $array))
			return $array;
		else
			return $array[$field];
	}
	
	// TODO: improve this function to better support multiple table and tables which are related
	static function dbFind($tables, $fields, $conditions, $params = null){
		global $db;
		if(is_array($tables))
			$tables = implode(',',$tables);
		if(is_array($fields))
			$fields = implode(',',$fields);	
		if(is_array($conditions))	
			$conditions = self::arrayPrepareInput($conditions);
		if(is_array($params))	
			$params = self::arrayPrepareInput($params);
		$find_sql = "SELECT $fields FROM $tables WHERE $conditions $params";
		return $db->Execute($find_sql);
	} 
	
	// This function does not work for all languages! BEWARE
	static function arrayToUpper(&$entries){
		foreach($entries as $entry){
			$entry = strtoupper($entry);
		}
	}
	
	static function arrayCleanUp(&$the_array){
		if(!is_array($the_array))
			$the_array=(array)$the_array;	
		self::arrayTrim($the_array);
		if(count($the_array)>1){
			array_filter($the_array);
			array_unique($the_array);
		}
	}
	
	// does not accept nested array (since we should not need it here)
	static function arrayPrepareInput(&$the_array,$glue=" AND "){
		if(!is_array($the_array))
			$the_array=(array)$the_array;	
		$temp_array = array();
		foreach ($the_array as $key => $value){
				self::stringPrepareInput($the_array[$key]);
  				$temp_array[] = "$key = '$value'";
		}
		return implode($glue,$temp_array);	
	}
	
	static function stringPrepareInput(&$string){
		if (function_exists('mysql_real_escape_string')) {
	      	$string = mysql_real_escape_string($string);
	    } 
	    elseif (function_exists('mysql_escape_string')) {
	    	$string = mysql_escape_string($string);
	    } 
	    else {
	     	$string = addslashes($string);
	    }
	    return $string;
	}
	
	static function arrayTrim(&$the_array){
		array_walk($the_array, array($this, '_trim'));
	}
	
	static function _trim(&$value){
        if(is_array($value)){
            self::arrayTrim($value);
        }
     		else{
          $value = trim($value);
      	}
  	}
	
	// http://us3.php.net/manual/en/function.is-object.php#66370
	static function isObj( &$object, $check=null, $strict=false ){
		if (is_object($object)) {
	    	if ($check == null) {
	        	return true;
	      	} else {
	        	$object_name = get_class($object);
	        	return ($strict === true)?( $object_name == $check ):( strtolower($object_name) == strtolower($check) );
	      	}   
	  	} else {
	    	return false;
	  	}
	}
	
	static function makeRand ($minlength, $maxlength, $useupper, $usespecial, $usenumbers){
		/*
		Author: Peter Mugane Kionga-Kamau
		http://www.pmkmedia.com
		
		Description: string str_makerand(int $minlength, int $maxlength, bool $useupper, bool $usespecial, bool $usenumbers)
		returns a randomly generated string of length between $minlength and $maxlength inclusively.
		
		Notes:
		- If $useupper is true uppercase characters will be used; if false they will be excluded.
		- If $usespecial is true special characters will be used; if false they will be excluded.
		- If $usenumbers is true numerical characters will be used; if false they will be excluded.
		- If $minlength is equal to $maxlength a string of length $maxlength will be returned.
		- Not all special characters are included since they could cause parse errors with queries.
		
		Modify at will.
		*/
		$charset = "abcdefghijklmnopqrstuvwxyz";
		if ($useupper) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if ($usenumbers) $charset .= "0123456789";
		if ($usespecial) $charset .= "~@#$%^*()_+-={}|]["; // Note: using all special characters this reads: "~!@#$%^&*()_+`-={}|\\]?[\":;'><,./";
		if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength);
		else $length = mt_rand ($minlength, $maxlength);
		for ($i=0; $i<$length; $i++) $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
		return $key;
	}
}