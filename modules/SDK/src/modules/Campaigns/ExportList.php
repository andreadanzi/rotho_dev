<?php
require_once('include/utils/utils.php');

$operation = vtlib_purify($_REQUEST['operation']);
$record = vtlib_purify($_REQUEST['record']);
$ret_arr = Array(
	'success'=>false,
	'message'=>getTranslatedString('LBL_EXPORT_ERROR','Campaigns'),
);
if ($operation == 'get_related_lists'){
	$object = CRMEntity::getInstance('Campaigns');
	$object->id = $record;
	$relatedlists = $object->getDetailedStatisticRelatedLists();
	$ret_arr['relatedlists'] = $relatedlists;
	$ret_arr['success'] = true;
	require_once('Smarty_setup.php');
	$smarty = new vtigerCRM_Smarty();
	$smarty->assign("RELATEDLISTS",$relatedlists);
	$smarty->assign("RECORD",$record);
	$ret_arr['html'] = $smarty->fetch("modules/SDK/src/modules/Campaigns/ExportList.tpl");
	unset($ret_arr['message']);
	echo Zend_Json::encode($ret_arr);
	exit;
}
elseif($operation != 'export_lists'){
	echo Zend_Json::encode($ret_arr);
	exit;
}
$list_to_export = Zend_Json::decode($_REQUEST['relatedlists_to_export']);

$campaign_name = reset(getEntityName('Campaigns',$record));

global $php_max_execution_time,$table_prefix;
set_time_limit($php_max_execution_time);

// crmv@30385 - cambio classe php per scrivere i xls - tutto il file
require_once('include/PHPExcel/PHPExcel.php');
require_once("modules/Reports/ReportRun.php");
require_once("modules/Reports/Reports.php");

global $tmp_dir, $root_directory, $mod_strings, $app_strings; // crmv@29686
$fname = tempnam($root_directory.$tmp_dir, "merge2.xls");
$objPHPExcel = new PHPExcel();
$objPHPExcel->removeSheetByIndex(0); // remove default sheet

$objPHPExcel->getProperties()
	->setCreator("VTE CRM")
	->setLastModifiedBy("VTE CRM")
	->setTitle("Report"); // TODO: report title

$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

$xlsStyle1 = new PHPExcel_Style();
$xlsStyle2 = new PHPExcel_Style();

$xlsStyle1->applyFromArray(
	array('font' => array(
		'name' => 'Arial',
		'bold' => true,
		'size' => 12,
		'color' => array( 'rgb' => '0000FF' )
	),
));

$xlsStyle2->applyFromArray(
	array('font' => array(
		'name' => 'Arial',
		'bold' => true,
		'size' => 11,
	),
));
$campaignid = vtlib_purify($_REQUEST['record']);
$focus = CRMEntity::getInstance('Campaigns');
foreach ($list_to_export as $label=>$method){
	if (!method_exists($focus,$method)){
		continue;
	}
	$return_value = $focus->$method($campaignid, getTabId('Campaigns'), 0,false,true);
	$method_backup = $method;
	$method = str_replace("get_statistics_",'',$method);
	$method = str_replace("_",' ',$method);
	global $list_max_entries_per_page;
	$list_max_entries_per_page_backup = $list_max_entries_per_page;
	$totalrows = $list_max_entries_per_page;
	if ($_SESSION[$method."_listquery"] != ''){
		global $adb;
		$res = $adb->query($_SESSION[$method."_listquery"]);
		if ($res){
			$totalrows = $adb->num_rows($res);
		}
	}
	if ($totalrows > $list_max_entries_per_page){
		$list_max_entries_per_page = $totalrows+1;
	}
	$return_value = $focus->$method_backup($campaignid, getTabId('Campaigns'), 0);
	$list_max_entries_per_page = $list_max_entries_per_page_backup;
	$count = 0;
	$sheet1 = new PHPExcel_Worksheet($objPHPExcel,$label);
	$objPHPExcel->addSheet($sheet1);
	// crmv@29686e
	$sheet1->setSharedStyle($xlsStyle1, 'A1:'.PHPExcel_Cell::stringFromColumnIndex(count($return_value['header'])).'1');
	foreach($return_value['header'] as $value) {
		$sheet1->setCellValueByColumnAndRow($count, 1, $value);
		$count = $count + 1;
	}
	if (!empty($return_value['entries'])){
		$rowcount=2;
		foreach($return_value['entries'] as $key=>$array_value)
		{
			$dcount = 0;
			foreach($array_value as $hdr=>$value)
			{
	
				$value = strip_tags(decode_html($value));
	
				//crmv@29016
				//check for strings that looks like numbers (starting with 0)
				if (is_numeric($value) && substr(strval($value), 0, 1) == '0' && !preg_match('/[,.]/', $value)) { // crmv@30385
					$sheet1->setCellValueExplicitByColumnAndRow($dcount, $rowcount, $value, PHPExcel_Cell_DataType::TYPE_STRING);
				} else {
					$sheet1->setCellValueByColumnAndRow($dcount, $rowcount, $value);
				}
				//crmv@29016e
				$dcount = $dcount + 1;
			}
			$rowcount++;
		}
	}
}
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // replace with Excel2007 and change extension to xlsx for the new format
$objWriter->save($fname);


if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
{
	header("Pragma: public");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
}
header("Content-Type: application/vnd.ms-excel");
header("Content-Length: ".@filesize($fname));
header('Content-disposition: attachment; filename="'.$campaign_name.' '.date('YmdHis').'"');
$fh=fopen($fname, "rb");
fpassthru($fh);
?>