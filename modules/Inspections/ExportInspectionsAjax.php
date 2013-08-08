<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('include/PHPExcel/PHPExcel.php');
global $currentModule, $log,$adb,$root_directory,$tmp_dir;
$modObj = CRMEntity::getInstance($currentModule);
$filename = getTranslatedString('Inspections',$currentModule)."_".date('YmdHis').".xls";
$fname = str_replace('.php', '.xls', __FILE__);
$action = $_REQUEST["action"];
if($action == 'InspectionsAjax')
{
	$ajaxaction = $_REQUEST["ajaxaction"];
	$search_type = $_REQUEST['search_type'];
	$export_data = $_REQUEST['export_data'];
	$export_type = $_REQUEST['export_type'];
	$idstring = rtrim($_REQUEST['idstring'],",");
	if($idstring != '')
	{
		$log->debug("Entering ExportInspectionsAjax ajaxaction=".$ajaxaction);
		if($ajaxaction=='LISTVIEW'){
			$log->debug("Entering ExportInspectionsAjax LISTVIEW(".$export_type.") ");
			echo ':#:EXP_SUCCESS';
			exit();
		}
		elseif ($ajaxaction=='EXPORT') {
		
			$objPHPExcel = new PHPExcel();
			// $objPHPExcel->removeSheetByIndex(0); // remove default sheet
			// PROPERTIES
			$objPHPExcel->getProperties()
				->setCreator("ROTHOCRM CLIENTS+")
				->setLastModifiedBy("ROTHOCRM CLIENTS+")
				->setTitle(getTranslatedString('Inspections',$currentModule)."_".date('YmdHis')); // TODO: report title
			// STYLE
			$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
			$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
			
			$xlsStyle1 = new PHPExcel_Style();
			$xlsStyle2 = new PHPExcel_Style();
			$xlsStyle1_1 = new PHPExcel_Style();
			$xlsStyle1_2 = new PHPExcel_Style();
			$xlsStyle2_1 = new PHPExcel_Style();

			$xlsStyle1->applyFromArray(
				array('font' => array(
					'name' => 'Arial',
					'bold' => true,
					'size' => 11,
					'color' => array( 'rgb' => '000000' )
				),
				'fill' => array(
					'type'  => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array( 'rgb' => 'cecece' ),
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN )
					)
				)
			);
			
			$xlsStyle2->applyFromArray(
				array('font' => array(
					'name' => 'Arial',
					'bold' => false,
					'size' => 10,
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN )
					)
				)
			);
			
			$xlsStyle1_1->applyFromArray(
				array('font' => array(
					'name' => 'Arial',
					'bold' => true,
					'size' => 11,
					'color' => array( 'rgb' => 'FF6600' )
				),
				'fill' => array(
					'type'  => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array( 'rgb' => 'cecece' ),
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN )
					)
				)
			);
			
			
			$xlsStyle1_2->applyFromArray(
				array('font' => array(
					'name' => 'Arial',
					'bold' => true,
					'size' => 11,
					'color' => array( 'rgb' => '000000' )
				),
				'fill' => array(
					'type'  => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array( 'rgb' => 'FFFF00' ),
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN )
					)
				)
			);
			
			
			$xlsStyle2_1->applyFromArray(
				array('font' => array(
					'name' => 'Arial',
					'bold' => true,
					'size' => 10,
					'color' => array( 'rgb' => 'FF6600' )
					)
				)
			);

			
			$log->debug("Entering ExportInspectionsAjax EXPORT");
			$oCustomView = new CustomView($currentModule);
			$viewid = $oCustomView->getViewId($currentModule);
			$log->debug("Entering ExportInspectionsAjax EXPORT after getViewId=".$viewid);
			list($modObj->customview_order_by,$modObj->customview_sort_order) = $oCustomView->getOrderByFilterSQL($viewid);
			$sorder = $modObj->getSortOrder();
			$order_by = $modObj->getOrderBy();
			if(isset($_SESSION['export_where']) && $_SESSION['export_where']!='' && $search_type == 'includesearch'){
				$where =$_SESSION['export_where'];
				$where = ltrim($where,' and');	//crmv@21448
			}
			$query = $modObj->create_export_query($where,$oCustomView,$viewid);
			$params = array();
			if(($search_type == 'withoutsearch' || $search_type == 'includesearch') && $export_data == 'selecteddata'){
				$idstring_expl = explode(";", $_REQUEST['idstring']);
				$list_max_entries_per_page=count($idstring_expl);		
				if(count($idstring_expl) > 0) {
					$query .= " and $modObj->table_name.$modObj->table_index in (" . generateQuestionMarks($idstring_expl) . ')';
					array_push($params, $idstring_expl);
				}
			}
			$log->debug("Exiting ExportInspectionsAjax EXPORT(".$query.") ");
			$result = $adb->pquery($query,$params,true);
			$fields_array = $adb->getFieldsArray($result);
			// $fields_array = array_diff($fields_array,array("user_name"));
			$translated_fields_array = array();
			
			$objPageSetup = new PHPExcel_Worksheet_PageSetup();
			$objPageSetup->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
			$objPageSetup->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
			$objPageSetup->setPrintArea("A1:K15");
			$objPageSetup->setFitToWidth(1);
			
			$count = 0;
			$sheet1 = $objPHPExcel->getActiveSheet ();
			// $sheet1 = new PHPExcel_Worksheet($objPHPExcel, getTranslatedString('Inspections',$currentModule));
			$sheet1->setTitle(getTranslatedString('Inspections',$currentModule));
			$sheet1->getColumnDimension('A')->setWidth(23);
			$sheet1->getColumnDimension('B')->setWidth(20);
			$sheet1->getColumnDimension('C')->setWidth(20);
			$sheet1->getColumnDimension('D')->setWidth(20);
			$sheet1->getColumnDimension('E')->setWidth(20);
			$sheet1->getColumnDimension('F')->setWidth(32);
			$sheet1->getColumnDimension('G')->setWidth(15);
			$sheet1->getColumnDimension('H')->setWidth(20);
			$sheet1->getColumnDimension('I')->setWidth(20);
			$sheet1->getColumnDimension('J')->setWidth(6);
			$sheet1->getColumnDimension('K')->setWidth(50);
			// $objPHPExcel->addSheet($sheet1);
			$sheet1->setSharedStyle($xlsStyle2_1,'A3');
			$sheet1->setCellValue('A3', getTranslatedString('MODULO REVISIONE DPI',$currentModule)); // #FF6600 arancio #FFFF00 giallo
			$sheet1->setSharedStyle($xlsStyle1, 'A6:'.PHPExcel_Cell::stringFromColumnIndex(count($fields_array)-1).'6');
			$sheet1->setSharedStyle($xlsStyle1_1,'B6');
			$sheet1->setSharedStyle($xlsStyle1_2,'E6:J6');
			$sheet1->setSharedStyle($xlsStyle1_1,'K6');
			for($i=0; $i<count($fields_array); $i++) {
				$translated_fields_array[$i] = getTranslatedString($fields_array[$i],$currentModule);
				$sheet1->setCellValue(PHPExcel_Cell::stringFromColumnIndex($count).'6', $translated_fields_array[$i]);
				$count = $count + 1;
			}
			$rowcount=7;
			while($val = $adb->fetchByAssoc($result, -1, false)){
				$dcount = 0;
				foreach ($val as $key => $value){
					// $value = decode_html($value);
					if($key=='inspection_state') {
						$sheet1->setCellValueByColumnAndRow($dcount, $rowcount, getTranslatedString($value,$currentModule));
					} else {
						$sheet1->setCellValueByColumnAndRow($dcount, $rowcount, $value);
					}
					$dcount = $dcount + 1;
				}
				$rowcount++;
			}
			if($rowcount>2) $rowcount=$rowcount-1;
			$sheet1->setSharedStyle($xlsStyle2, 'A7:'.PHPExcel_Cell::stringFromColumnIndex(count($fields_array)-1).$rowcount);
			// firm
			$gdImage = imagecreatefromjpeg('images/rothoblaas.jpg');
			$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
			$objDrawing->setName('Sample image');
			$objDrawing->setDescription('Sample image');
			$objDrawing->setImageResource($gdImage);
			$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
			$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
			$objDrawing->setHeight(70);
			$objDrawing->setCoordinates('I1');
			$objDrawing->setWorksheet($sheet1);
			$sheet1->setCellValueByColumnAndRow($dcount-3, $rowcount+3, "Addetto alla verifica revisione DPI");
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
			$fh=fopen($fname, "rb");
			fpassthru($fh);
		}
	}else
	{
		echo ':#:EXP_FAILURE';
		exit();
	}
} else{
	echo ':#:EXP_FAILURE';
	exit();
}
?>