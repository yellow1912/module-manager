<?php
if(is_array($modules) && count($modules) > 0){ ?>
	<table id="list_modules">
		<tr>
	<?php 
	$keys = array_keys($modules[0]);
	foreach ($keys as $key){ ?>
		<th scope="col"><?php echo $key?></th>
	<?php } ?>
		<th scope="col">Update</th>
		</tr>
	<?php
		foreach($modules as $module){ ?>
		<tr>
		<?php	foreach($module as $field){ ?>
				<td><?php echo $field; ?></td>
	<?php	} ?>
		<td><a href="<?php echo zen_href_link(FILENAME_MODULE_MANAGER,'action=update_module&ID='.$module['ID']) ?>">update</a></td>
		</tr>
	<?php } ?>
	</table>
<?php
}
?>