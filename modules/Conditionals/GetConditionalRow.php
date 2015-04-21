<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is crmvillage.biz.
 * Portions created by crmvillage.biz are Copyright (C) crmvillage.biz.
 * All Rights Reserved.
 *******************************************************************************/
require_once('include/utils/utils.php');
$module = $_REQUEST['conditional_module'];
$chk_fieldname = $_REQUEST['chk_fieldname'];
$chk_criteria_id = $_REQUEST['chk_criteria_id'];
$chk_field_value = $_REQUEST['chk_field_value'];
$count = $_REQUEST['rowCnt'];
//danzi.tn@20150421 aggiunto il criterio n 7 not includes
global $mod_strings,$app_strings,$table_prefix;
$fields_list = array();
$fields_columnnames = array();

$sql = "SELECT b.blocklabel, f.fieldlabel, f.uitype, f.columnname, f.fieldname, t.tabid
		FROM ".$table_prefix."_field f
		INNER JOIN ".$table_prefix."_tab t ON f.tabid = t.tabid
		INNER JOIN ".$table_prefix."_blocks b ON b.blockid = f.block
		AND t.name = '".$module."'
		ORDER BY b.sequence, f.sequence";
$result = $adb->query($sql);

$num_rows = $adb->num_rows($result);
for ($k = 0; $k < $num_rows; $k++)
{
	$blocklabel = $adb->query_result($result, $k, 'blocklabel');
	$field_name_key = $adb->query_result($result, $k, 'fieldlabel');
	$field_name = "";
					
	if ($app_strings[$field_name_key])
		$field_name = $app_strings[$field_name_key];
	elseif ($mod_strings[$field_name_key])
		$field_name = $mod_strings[$field_name_key];
	else
	{
		$field_name = $field_name_key;
	}
	
	$fields_list[$blocklabel][] = $field_name_key;
	$fields_columnnames[$blocklabel][] = $adb->query_result($result, $k, 'fieldname');
}
?>
<td align="left" width="20%">
	<select name="field<?php echo $count; ?>">
		<?php
		foreach($fields_columnnames as $name_block => $block) {
			$options .=  '<optgroup class="select" label="'.getTranslatedString($name_block,$module).'">';
			foreach($block as $key => $field) {
				$options .=  '<option value="'.$field.'"';
				if ($field == $chk_fieldname) $options .= 'selected';
				$options .= '>'.getTranslatedString($fields_list[$name_block][$key],$module).'</option>';
			}
			$options .= '</optgroup>';
		}
		echo $options;
		?>
	</select>
</td>
<td align="left" width="20%">
	<select name="criteria_id<?php echo $count; ?>">
		<option value="0" <?php if ($chk_criteria_id == "0") echo 'selected';?> ><?php echo $mod_strings['LBL_CRITERIA_VALUE_LESS_EQUAL']; ?></option>
		<option value="1" <?php if ($chk_criteria_id == "1") echo 'selected';?> ><?php echo $mod_strings['LBL_CRITERIA_VALUE_LESS_THAN']; ?></option>
		<option value="2" <?php if ($chk_criteria_id == "2") echo 'selected';?> ><?php echo $mod_strings['LBL_CRITERIA_VALUE_MORE_EQUAL']; ?></option>
		<option value="3" <?php if ($chk_criteria_id == "3") echo 'selected';?> ><?php echo $mod_strings['LBL_CRITERIA_VALUE_MORE_THAN']; ?></option>
		<option value="4" <?php if ($chk_criteria_id == "4") echo 'selected';?> ><?php echo $mod_strings['LBL_CRITERIA_VALUE_EQUAL']; ?></option>
		<option value="5" <?php if ($chk_criteria_id == "5") echo 'selected';?> ><?php echo $mod_strings['LBL_CRITERIA_VALUE_NOT_EQUAL']; ?></option>
		<option value="6" <?php if ($chk_criteria_id == "6") echo 'selected';?> ><?php echo $mod_strings['LBL_CRITERIA_VALUE_INCLUDES']; ?></option>
		<option value="7" <?php if ($chk_criteria_id == "7") echo 'selected';?> ><?php echo $mod_strings['LBL_CRITERIA_VALUE_NOT_INCLUDES']; ?></option>
	</select>
</td>
<td align="left" width="60%">
	<?php echo $mod_strings['LBL_CRITERIA_VALUE_NAME']; ?>
	<input type="text" value="<?php if ($chk_field_value != "") echo $chk_field_value;?>" name="field_value<?php echo $count; ?>">
	<input type="hidden" id="deleted<?php echo $count; ?>" name="deleted<?php echo $count; ?>" value="0">
	<a href="javascript:;" onclick="deleteRow(<?php echo $count; ?>);"><img src="modules/com_vtiger_workflow/resources/remove.png" border="0"></a>
</td>
