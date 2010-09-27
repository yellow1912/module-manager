<?php
class RITemplate extends template_func{
	var $view;
  var $path;
  var $css_path;
  var $base;
  var $data = array();
  var $admin = false;
  
  function RITemplate($admin = false){
  	$this->admin = $admin;
  	if($this->admin){
  		if(empty($this->base)) $this->base = preg_replace('/\.php/','',substr(strrchr($_SERVER['PHP_SELF'],'/'),1),1);
  	}
  	else 
  		if(empty($base)) $this->base = $_GET['main_page'];
  }
  
//  
//  function loadAdminCss(){
//  	if($this->_checkPath(DIR_FS_ADMIN.$this->css_path));
//  	echo '<link rel="stylesheet" type="text/css" href="'.$this->css_path.'">';
//  }
  
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
  
  function render(){
  	if(empty($this->view)){
  		if(!isset($_GET['action']) || empty($_GET['action']))
  			$this->view .= 'index';
  		else
  			$this->view .= $_GET['action'];
  		$this->view .= '.php';
  	}
  	
  	if($this->admin)
			$this->path = DIR_FS_ADMIN."includes/templates/template_default/templates/".$this->base.'/';
  	else
	  	$this->path = $this->get_template_dir($this->view, DIR_WS_TEMPLATE, $this->base, 'templates/'.$this->base).'/';
 
  	if($this->_checkPath($this->path.$this->view)){
  		foreach($this->data as $element)
    	    extract($element, EXTR_SKIP);
  		ob_start();
  		require_once($this->path.$this->view);
  		$out = ob_get_clean();
  		print $out;
  	}
  	// error output
  	else{
  		echo "Render error, file not found(".$this->path.$this->view.")";
  	}
  }
}
