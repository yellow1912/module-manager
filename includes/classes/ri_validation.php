<?php

/*
	array(field_name => array(0 => array(rule => message)
		 )
	
	
	$default_error_messages = array('validation_is_not_empty' 	=> 'is empty',
									'validation_is_number' 		=> 'is not a number',
									'validation_is_positive' 	=> 'is not a positive number',
									'validation_is_date'		=> 'is not a valid date');
	$default_validation_rules = array(0 => array('function' => 'validation_is_not_empty'));	
*/

	class RIValidation{
		// TODO: use 'private' when ZC moves to PHP5
		var $rules;
		var $params = array();
		var $error_count = 0;
		var $validation_errors = array();
		var $class = 'validation';
		// class is used to later display error message
		// if ($messageStack->size(class) > 0) echo $messageStack->output(class);
		function setErrorClass($class){
			$this->class = $class;
		}
		
		function setRules($rules){
			$this->rules = $rules;
		}
		
		function addRules($rules){
			foreach($rules as $key => $value){
				if(array_key_exists($key, $this->rules))
					$this->rules[$key][] = $value;
				else
					$this->rules[$key] = $value; 
			}		
		}
		
		function setMessages($messages){
			$this->default_messages = $messages;
		}
		
		function addMessages($messages){
			$this->default_messages[] = $messages;
		}
		
		function setParams($params){
			$this->params = array_merge($this->params, $params);
		}
		
		function validateFields(){
			// we loop by the rule to make sure if the the field is not passed we still check for it
			foreach($this->rules as $field => $field_options){
				
				// if not required and empty we by pass all check
				if(!array_key_exists('isNotEmpty', $field_options['rules'])){
					$class_name = isset($field_options['empty']) ? "RIValidation{$field_options['empty']}" : "RIValidationDefault";
					if(!call_user_func(array($class_name, 'isNotEmpty'), $this->params[$field], null))
						continue;				
				}					
		
				foreach($field_options['rules'] as $method_name=>$parameters){
					if(is_array($parameters)){
						$class_name = isset($parameters['class'])? "RIValidation{$parameters['class']}" : "RIValidationDefault";
						$message = isset($parameters['message']) ? $parameters['message'] : '';
						$options = isset($parameters['options']) ? $parameters['options'] : array();
					}
					else {
						$method_name = $parameters;
						$class_name = "RIValidationDefault";
						$message = '';
						$options = array();
					}
					if(method_exists($class_name, $method_name)){
						if(!call_user_func_array(array($class_name, $method_name), array($this->params[$field], $options))){ // value of the field is passed as the rule's parameters
							// sounds off the alarm	
								if(!empty($message))
									$this->setError($message, $this->class);
								else
									$this->setError("$field failed $method_name", $this->class);
							}
						}
						else{
							$this->setError(sprintf("Could not call function '%s' to validate field '%s' with the value '%s'",$class_name.$method_name,$field,$this->params[$field]), $this->class);
							// sounds off the alarm	
						}
				}	
									
			}
		}
		
		function setError($message, $class, $type='error'){
			$this->validation_errors[] = array('message' => $message, 'class' => $class, 'type' => $type);
			$this->error_count++;
		}
		
		
		function setZenError($is_admin_side){
			global $messageStack;
			foreach($this->validation_errors as $error)
				if(!$is_admin_side) $messageStack->add($error['class'], $error['message'], $error['type']);
				else $messageStack->add($error['message'], $error['type']);
		}
		
		function run($is_admin_side = false){
			$this->validateFields();
			$this->setZenError($is_admin_side);
			return $this->error_count;
		}
	}		
// set directories to check for function files
$validation_directory = DIR_FS_CATALOG . DIR_WS_CLASSES . 'validation_plugins/';

// Check for new functions in extra_functions directory
$directory_array = array();

if ($dir = @dir($validation_directory)) {
  while ($file = $dir->read()) {
    if (!is_dir($validation_directory . $file)) {
      if (preg_match('/\.php$/', $file) > 0) {
        include($validation_directory.$file);
      }
    }
  }
  $dir->close();
}
