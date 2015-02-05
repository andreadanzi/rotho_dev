<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
// require_once('include/PHPExcel/PHPExcel.php');
require_once('include/PHPExcel/PHPExcel.php');
global $log,$adb;
$filename = "Accounts_Custom_ListView.xls";
$fname = str_replace('ExportListViewByProductAjax.php', $filename, __FILE__);
$action = $_REQUEST["action"];

if($action == 'AccountsAjax')
{
	$listquery = $_SESSION['Accounts_listquery'];
	$ajaxaction = $_REQUEST["ajaxaction"];
	$search_type = $_REQUEST['search_type'];
	$export_data = $_REQUEST['export_data'];
	$export_type = $_REQUEST['export_type'];
	$idstring = rtrim($_REQUEST['idstring'],",");
	if($listquery != '')
	{
		$log->debug("Entering ExportListViewByProductAjax.php ajaxaction=".$ajaxaction);
		if ($ajaxaction=='EXPORT') {
			// echo $listquery;
			$objPHPExcel = new PHPExcel();
			// $objPHPExcel->removeSheetByIndex(0); // remove default sheet
			// PROPERTIES
			$objPHPExcel->getProperties()
				->setCreator("ROTHOCRM CLIENTS+")
				->setLastModifiedBy("ROTHOCRM CLIENTS+")
				->setTitle("Accounts_".date('YmdHis')); // TODO: report title
			// STYLE
			$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
			$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
			
			$xlsStyle1 = new PHPExcel_Style();
			
			$xlsStyle1->applyFromArray(
				array('font' => array(
					'name' => 'Arial',
					'bold' => true,
					'size' => 12,
					'color' => array( 'rgb' => '0000FF' )
				),
			));
			
			$log->debug("Entering ExportListViewByProductAjax.php EXPORT (".$listquery.")");
			
			$result = $adb->query($listquery);
			$fields_array = $adb->getFieldsArray($result);
			// $fields_array = array_diff($fields_array,array("user_name"));
			$translated_fields_array = array();
			
			$objPageSetup = new PHPExcel_Worksheet_PageSetup();
			$objPageSetup->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
			$objPageSetup->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
			$objPageSetup->setFitToWidth(1);
			// preparazione delle colonne
			$count = 0;
			$sheet1 = $objPHPExcel->getActiveSheet();					
			$rowcount=2;
			while($val = $adb->fetchByAssoc($result, -1, false)){
				if( $rowcount==2 ) {
					$sheet1->setSharedStyle($xlsStyle1, 'A1:'.PHPExcel_Cell::stringFromColumnIndex(count($val)).'1');
					foreach($val as $key=>$value) {
						$sheet1->setCellValueByColumnAndRow($count, 1, $key);
						$count = $count + 1;
					}
				}
				$dcount = 0;
				foreach ($val as $key => $value){
					$value = decode_html($value);
					//check for strings that looks like numbers (starting with 0)
					if (is_numeric($value) && substr(strval($value), 0, 1) == '0' && !preg_match('/[,.]/', $value)) { // crmv@30385
						$sheet1->setCellValueExplicitByColumnAndRow($dcount, $rowcount, $value, PHPExcel_Cell_DataType::TYPE_STRING);
					// crmv@38798 - currency fields
					} elseif (preg_match('/([€$]) (-?[0-9.,]+)/', $value, $matches)) {
						$symbol = $matches[1];
						$value = $matches[2];
						$sheet1->setCellValueExplicitByColumnAndRow($dcount, $rowcount, $value, PHPExcel_Cell_DataType::TYPE_NUMERIC);
						if ($symbol == '$') {
							$numberFormat = PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE;
						} else {
							$numberFormat = PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE;
						}
						$sheet1->getStyleBycolumnAndRow($dcount, $rowcount)->getNumberFormat()->setFormatCode($numberFormat);
					} else {
						$sheet1->setCellValueByColumnAndRow($dcount, $rowcount, $value);
					}
					$dcount = $dcount + 1;
				}
				$rowcount++;
			}
			
			$sheet1->setPageSetup($objPageSetup);
			
			// firma e
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save($fname);
			if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
			{
				header("Pragma: public");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			}
			header("Content-Type: application/vnd.ms-excel");
			header("Content-Length: ".@filesize($fname));
			header('Content-disposition: attachment; filename="'.$filename.'"');
			// $fh=fopen($fname, "rb");
			// fpassthru($fh);
			
			/*
			
			// Redirect output to a client’s web browser (Excel5)
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$filename.'"');
			header('Cache-Control: max-age=0');
			// If you're serving to IE 9, then the following may be needed
			header('Cache-Control: max-age=1');

			// If you're serving to IE over SSL, then the following may be needed
			header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');*/
			echo 'modules/Accounts/'.$filename;
			exit();
		}
	}else
	{
		echo ':#:EXP_FAILURE LISTQUERY';
		exit();
	}
} else{
	echo ':#:EXP_FAILURE ACCOUNTSAJAX';
	exit();
}
?>