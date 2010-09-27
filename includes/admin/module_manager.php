<?php
/**
 * Module Manager
 * @Version: 
 * @Authour: yellow1912
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */ 

require('includes/application_top.php');
require_once(DIR_WS_CLASSES.'module_manager.php');
$module_manager = new module_manager();

require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'ri_template.php');
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'ri_utility.php');
$ri_template = new RITemplate(true);

switch($_GET['action']){
	case 'list_modules':
		$modules = $module_manager->list_modules();
		$ri_template->set('modules', $modules);
	break;
	case 'update_module':
		$module_manager->update_module($_GET['ID']);
		$ri_template->setView('index.php');
	break;
	case 'install_modules':
		$modules = $module_manager->list_modules();
		$installed_modules = array();
		if(is_array($modules))
		foreach($modules as $module){
			$installed_modules[] = $module['module_code'];
		}
		$new_modules = $module_manager->get_new_modules($installed_modules);

		foreach($new_modules as $new_module){
			$module_manager->set_module($new_module);
			$module_manager->upgrade_module();
		}
		$ri_template->set('counter', count($new_modules));
	break;
}
		
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script language="javascript" src="../js/dynamic_input_field.js"></script>
<style type='text/css'>
.submit_link {
 color: #0000ff;
 background-color: transparent;
 text-decoration: none;
 border: none;
}
</style>

</head>
<body onLoad="init()">
<!-- header //-->
<div class="header_area">
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
</div>
<!-- header_eof //-->

<fieldset>
	<legend>Basic Functions</legend>
	List all modules: <a href="<?php echo zen_href_link(FILENAME_MODULE_MANAGER,'action=list_modules'); ?>">Click here</a><br />
	Install new modules: <a href="<?php echo zen_href_link(FILENAME_MODULE_MANAGER,'action=install_modules'); ?>">Click here</a>
</fieldset>
<?php $ri_template->render(); ?>

<!-- footer //-->
<div class="footer-area">
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
</div>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>