<?php
class RITemplate{
	var $view;
  var $path;
  var $css_path;
  var $template;
  var $data = array();
  var $admin = false;
  
  function RITemplate($admin = false, $plugin = '', $base = '', $template = ''){
  	$this->admin = $admin;
  	if($this->admin){
  		$root = empty($plugin) ? DIR_FS_ADMIN."includes/templates/" : DIR_FS_CATALOG."includes/plugins/$plugin/admin/templates/";
  		$this->template = empty($template) ? 'template_default' : $template;
  		$this->base = empty($base) ? preg_replace('/\.php/','',substr(strrchr($_SERVER['PHP_SELF'],'/'),1),1) : $base;
  	}
  	else{ 
  		$root = empty($plugin) ? DIR_FS_CATALOG."includes/templates/" : DIR_FS_CATALOG."includes/plugins/$plugin/catalog/templates/";
  		$this->template = empty($template) ? DIR_WS_TEMPLATE : $template;
  		$this->base = empty($base) ? $_GET['main_page'] : $base;
  	}
  	$this->path = $root."{template}/{t}/{$this->base}/";
  }
  
  function setOpt($plugin = '', $base = '', $template = ''){
  	if($this->admin){
  		$root = empty($plugin) ? DIR_FS_ADMIN."includes/templates/" : DIR_FS_CATALOG."includes/plugins/$plugin/admin/templates/";
  	}
  	else{ 
  		$root = empty($plugin) ? DIR_FS_CATALOG."includes/templates/" : DIR_FS_CATALOG."includes/plugins/$plugin/catalog/templates/";
  	}
  	$base = empty($base) ? $this->base : $base;
  	if(!empty($template)) $this->template = $template;
  	$this->path = $root."{template}/{t}/$base/";
  }
    
  function _checkPath($path){
  	if(file_exists($path))
  		return true;
  	return false;
	}
	
  function setView($view){
  	$this->view = $view;
  }

  function set($one, $two = null) {
    $data = null;
    if (is_array($one)) {
      if (is_array($two)) {
        $data = array_combine($one, $two);
      } else {
        $data = $one;
      }
    } else {
      $data = array($one => $two);
    }
		if ($data == null) {
    	return false;
    }
	 foreach($this->data as $key=>$value)
     	if(key($this->data[$key]) == key($data))
     		unset($this->data[$key]);

     	$this->data[] = $data;
	}

	function setByReference($one, &$two = null) {
    $data = null;
    if (is_array($one)) {
      if (is_array($two)) {
        $data = array_combine($one, $two);
      } else {
        $data = $one;
      }
    } else {
      $data = array($one => $two);
    }
		if ($data == null) {
    	return false;
    }
	 foreach($this->data as $key=>$value)
     	if(key($this->data[$key]) == key($data))
     		unset($this->data[$key]);

     	$this->data[] = $data;
	}
	
  function setArray($array){
		foreach($array as $element){
			$this->set($element);
		}
  }
  
  
  // admin css
  // $this->css_path = "includes/templates/template_default/css/".$this->base.'.css';
  
  function get($t, $view){
  	if(file_exists($template_file = str_replace(array("{template}", "{t}"), array($this->template, $t), $this->path).$view))
  		return $template_file;
  	else 
  		return str_replace(array("{template}", "{t}"), array("template_default", $t), $this->path).$view;
  }
  
  function render(){
  	if(empty($this->view)){
  		if(!isset($_GET['action']) || empty($_GET['action']))
  			$this->view .= 'index';
  		else
  			$this->view .= $_GET['action'];
  		$this->view .= '.php';
  	}
 
  	$path = $this->get("templates", $this->view);
  	
  	if($this->_checkPath($path)){
  		foreach($this->data as $element)
    	    extract($element, EXTR_SKIP);
  		ob_start();
  		require_once($path);
  		$out = ob_get_clean();
  		print $out;
  	}
  	// error output
  	else{
  		echo "Render error, file not found(".$path.")";
  	}
  }
}
