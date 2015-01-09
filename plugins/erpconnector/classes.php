<?php
// danzi.tn@20140820 divisione per zero
class log{
	var $start;
	var $stop;
	var $content;
	var $line_termination;
	function log($line_termination="\n"){
		$this->format = $format;
		$this->format_content = $format_content;
		$this->line_termination = $line_termination;
	}
	function get_microtime(){
		list($misec,$sec) = explode(' ', microtime());
		return (int) ((float)$misec + (float)$sec);
	}
	function start(){
		$this->start=$this->get_microtime();
	}
	function stop($string){
		$this->stop=$this->get_microtime();
		$seconds=($this->stop-$this->start)." s ";
		$minutes=(int) ($seconds/60)." m ".($seconds%60)." s ";
		$this->content.="time of $string : $seconds | $minutes  ".$this->line_termination;
	}
	function get_content(){
		return $this->content;
	}
		
}
class importer{
	var $module;
	var $data=Array();
	var $fields=Array();
	var $fields_auto_create=Array();
	var $fields_auto_update=Array();
	var $external_code;
	var $existing_entity;
	var $mapping;
	var $mapping_inverse;
	var $query_get;
	var $object;
	var $sql_file;
	var $sql_file_name;
	var $time_start;
	var $records_updated;
	var $records_created;
	var $create_query;
	var $fields_functions=Array(); //manuele (Funzioni per modificare il singolo campo: es. formattare la data)
	var $mapping_column=Array(); //manuele
	var $created_users = array(); //manuele
	var $table = '';
	function __construct($module,$fields,$external_code,$time_start,$fields_auto_create,$fields_auto_update,$table=''){
		global $root_directory;
		$this->time_start = $time_start;
		$this->records_updated = 0;
		$this->records_created = 0;
		//setto il modulo
		$this->module = $module;
		$this->table = $table;
		$this->object = CRMEntity::getInstance($this->module);
		foreach ($fields as $key=>$item) {
			//manuele - i : Per gestire gli 'as' nei campi della select
			$tmp_key = explode(' as ',$key);
			if($tmp_key[1] != '')
				$key = $tmp_key[1];
			else
				$key = $tmp_key[0];
			//manuele - e
			$fields_real[strtolower($key)] = strtolower($item);
		}
		//manuele i
		if ($this->module == 'Users') {
			$this->object->tab_name = Array('vtiger_users');
			$this->object->tab_name_index = Array('vtiger_users'=>'id');
		}
		//manuele e
		$fields = $fields_real;
		$this->mapping = $fields;
		$this->mapping_inverse = array_flip($fields);
		$this->fields_auto_create = $fields_auto_create;
		$this->fields_auto_update = $fields_auto_update;
		$this->get_fields($fields);
		$this->external_code = strtolower($external_code);
		$this->get_existing_entity();
		$this->sql_file_name_update = $root_directory."plugins/erpconnector/sql/".$this->module."_sql_update.sql";
		@unlink($this->sql_file_name_update);
		$this->sql_file_update = fopen($this->sql_file_name_update , 'w+');
	}
	private function get_fields($fields){
		global $adb,$current_user;
		//setto i campi/tabelle da importare
		$sql = "select tablename,columnname,fieldname,uitype from vtiger_field where fieldname in (".generateQUestionMarks($fields).") and tabid = ?";
		$params = array_values($fields);
		$params[] = getTabid($this->module);
		$res = $adb->pquery($sql,$params);
		if ($res){
			while ($row = $adb->fetchByAssoc($res,-1,false)){
				$this->fields[$row['tablename']][] = $row['columnname'];
				$this->mapping_column[$row['columnname']] = $this->mapping_inverse[$row['fieldname']];
				if ($row['columnname'] == 'parentid' || $row['columnname'] == 'accountid')
					$this->fields_functions[$row['columnname']] = 'getAccExternalCode';
				if ($row['columnname'] == 'smownerid' && ($this->module == 'Accounts' || $this->module == 'Quotes'))
					$this->fields_functions[$row['columnname']] = 'get_smowner';
				if ($row['columnname'] == 'smownerid' && $this->module == 'Contacts')
					$this->fields_functions[$row['columnname']] = 'get_acc_smowner';
				if ($row['columnname'] == 'rating' && $this->module == 'Accounts')
					$this->fields_functions[$row['columnname']] = 'get_rating';
			}
		}
		//aggiungo i campi da importare di default
		if ($this->module != 'Users') {	//manuele
			$this->fields_auto_create['vtiger_crmentity']['createdtime'] = $this->time_start;
			$this->fields_auto_update['vtiger_crmentity']['modifiedtime'] = $this->time_start;
			$this->fields_auto_create['vtiger_crmentity']['modifiedtime'] = $this->time_start;
			$this->fields_auto_create['vtiger_crmentity']['setype'] = $this->module;
		}
	}
	private function get_existing_entity(){
		global $adb;
		$sql = "select tablename from vtiger_field where tabid = ? and fieldname = ?";
		$params[] = getTabid($this->module);
		$params[] = $this->mapping[$this->external_code];
		$res = $adb->pquery($sql,$params);
		if ($res){
			$external_code = $adb->query_result($res,0,'tablename').".".$this->mapping[$this->external_code];
		}
		$qry = getListQuery($this->module,"and ".$external_code." is NOT NULL AND ".$external_code." <> ''");
		//manuele i
		if ($this->module == 'Users')
			$qry = replaceSelectQuery($qry,$this->getkey('full','vtiger_users').",".$external_code);
		else
		//manuele e
			$qry = replaceSelectQuery($qry,$this->getkey('full').",".$external_code);
		
		$res=$adb->query($qry,true);
		while($row=$adb->fetchByAssoc($res,-1,false)){
			//manuele i
			if ($this->module == 'Users')
				$this->existing_entity[$row[$this->mapping[$this->external_code]]] = $row[$this->getkey('','vtiger_users')];
			else
			//manuele e
				$this->existing_entity[$row[$this->mapping[$this->external_code]]] = $row[$this->getkey()];
		}
	}
	private function make_create_files(){
		foreach ($this->object->tab_name_index as $t=>$k){
			$this->sql_file_name_create[$t] = $root_directory."plugins/erpconnector/sql/".$this->module."_sql_create_".$t.".csv";
			@unlink($this->sql_file_name_create[$t]);
			$this->sql_file_create[$t] = fopen($this->sql_file_name_create[$t] , 'w+');			
		}
	}
	private function get_column_create($table_name){
		$create = $this->getcached_create_arr(false);
		foreach ($this->fields as $table => $arr){
			foreach ($arr as $field){
				$create[$table][$field] = '';
			}
			$create[$table][$this->object->tab_name_index[$table]] = '';
		}	
		foreach ($this->object->tab_name_index as $t=>$k){
			if (!$create[$t][$k])
				$create[$t][$k] = '';
		}			
		$sql_number_create = $this->sequence_number();
		if ($sql_number_create[0]){
			$create[$sql_number_create[1]][$sql_number_create[2]] = '';
		}
		return array_keys($create[$table_name]);
	}
	
	//mycrmv@rotho
	public function convert_data($date){
		$gg = substr($date,-2);
		$mm = substr($date,4,-2);
		$aa = substr($date,0,4);
		$date_ok = $aa.'-'.$mm.'-'.$gg;
		return $date_ok;
	}
	//mycrmv@rotho e
	//mycrmv@rotho
	public function getUseridByName($user_name){
		global $adb;
		$sql_user = "SELECT id FROM vtiger_users WHERE user_name = ?";
		$resultid = $adb->pquery($sql_user,Array($user_name));
		if ($resultid){
			$userid = $adb->query_result($resultid,0,'id');
		}else{
			$userid = 1; //se non trova id mette admin di default
		}
		return $userid;
	}
	//mycrmv@rotho e
	
	//mycrmv@rotho
	public function getAccountidByExtcode($customer_no){
		global $adb;
		$sql = "SELECT accountid FROM vtiger_account
						inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid
						where external_code = ? and deleted = 0";
		$ress = $adb->pquery($sql,Array($customer_no));
		if ($ress){
			$accountid = $adb->query_result($ress,0,'accountid');
		}else{
			$accountid = 0;
		}
		return $accountid;
	}
	//mycrmv@rotho e
	
	//mycrmv@rotho
	public function go_pickvalue($status_value,$modulename,$field_i='',$field_final,$classification1_path=''){
		require_once('include/utils/utils.php');
		include_once('vtlib/Vtiger/Module.php');
		global $root_directory,$adb;
		
		$module = Vtiger_Module::getInstance($modulename);
		if ($modulename == 'Quotes' && $field_i = 'proposal_status'){
			if($classification1_path != ''){
				$status_value = 'Respinta';
			}
			else{
				if ($status_value == '1'){
					$status_value = 'Aperta';
				}elseif ($status_value == '2'){
					$status_value = 'Accettata';
				}elseif ($status_value == '3'){
					$status_value = 'Chiusa';
				}else{
					$status_value = 'Respinta';
				}
			}
		}
		$value = array($status_value);
		$field = Vtiger_Field::getInstance($field_final,$module);
		$sql="select $field_final from vtiger_$field_final where $field_final = ?";
		$res=$adb->pquery($sql,array($value_to_add));
		if($adb->num_rows($res)){
//			return false;
			return $status_value;
		}
		else {
			//$field->setPicklistValues($value);
			//return true;
			return $status_value;
		}
//		return $status_value;
	}
	//mycrmv@rotho e
	//mycrmv@rotho
		public function set_totals_quotes($ext_code){
		global $adb;
		$sql = 'select detail_netprice,detail_quantity from erp_temp_crm_customerproposal where proposal_number = ?';
		$res = $adb->pquery($sql,Array($ext_code));
		while ($rows = $adb->fetchByAssoc($res,-1,false)){
			$tot_save += $rows['detail_netprice']*$rows['detail_quantity'];
		}
		return $tot_save;
//		$update = 'update vtiger_salesorder set total = ?, subtotal = ? where salesorderid = ?';
//		$result = $adb->pquery($update,Array($tot_save,$tot_save,$so_id));
	}
	
	public function set_totals($ext_code){
		global $adb;
		$sql = 'select fatturato_netto,quantita from erp_temp_crm_ordini where ordine_numero = ?';
		$res = $adb->pquery($sql,Array($ext_code));
		while ($rows = $adb->fetchByAssoc($res,-1,false)){
			$tot_save += $rows['fatturato_netto'];
		}
		return $tot_save;
//		$update = 'update vtiger_salesorder set total = ?, subtotal = ? where salesorderid = ?';
//		$result = $adb->pquery($update,Array($tot_save,$tot_save,$so_id));
	}
	//mycrmv@rotho e
	public function go($query){
		global $adb;
		//crmv@18206
		$rows=array();
		//crmv@18206e
		$res = $adb->query($query);		
		if ($res){
			$this->make_create_files();			
			$records = $adb->num_rows($res);
			while ($row = $adb->fetchByAssoc($res,-1,false)){
//print_r($row); die;
				//mycrmv@rotho
				if ($this->module == 'Quotes'){
					$row['proposal_date'] = self::convert_data(trim($row['proposal_date']));
					//$row['agent_number'] = self::getUseridByName(trim($row['agent_number']));
					//$row['customer_number'] = self::getAccountidByExtcode(trim($row['customer_number']));
					$row['proposal_status'] = self::go_pickvalue(trim($row['proposal_status']),$this->module,'proposal_status','quote_status',$row['classification1_path']);
					$row['total'] = (float)self::set_totals_quotes($row['proposal_number']);
					$row['subtotal'] = (float)self::set_totals_quotes($row['proposal_number']);
				}
				elseif ($this->module == 'SalesOrder'){
					$row['total'] = (float)self::set_totals($row['ordine_numero_key']);
					$row['subtotal'] = (float)self::set_totals($row['ordine_numero_key']);
					$row['stato'] = 'Delivered';
					
					$cliente = $row['cliente_fatt_az'];
					/*
					$sql = "SELECT accountid FROM vtiger_account
							inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid
							where external_code = ? and deleted = 0";
					$res1 = $adb->pquery($sql,Array($cliente));
					if ($res1){
						$row['cliente_fatt_az'] = $adb->query_result($res1,0,'accountid');
					}
					*/

					$assegnatario = $row['cliente_fatt_ass'];
					$sql1 = "SELECT smownerid FROM vtiger_account
							inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid
							where external_code = ? and deleted = 0";
					$res2 = $adb->pquery($sql1,Array($assegnatario));
					if ($res2){
						$row['cliente_fatt_ass'] = $adb->query_result($res2,0,'smownerid');
					}else{
						$row['cliente_fatt_ass'] = 1;
					}
					$row['ordine_data'] = self::convert_data(trim($row['ordine_data']));
					$row['mese'] = substr($row['mese'],-2).'-'.substr($row['mese'],0,4);
				}
				//mycrmv@rotho e
				array_walk_recursive(&$row,'str_format');
				
				//mycrmv@30336
				if ($this->module == 'Accounts') {
					//check ext_code
					if ($row['base_number'] != '') {
						$qry_ext_code = "SELECT * FROM vtiger_account
										 JOIN vtiger_accountscf ON vtiger_account.accountid = vtiger_accountscf.accountid
										 JOIN vtiger_crmentity ON crmid = vtiger_account.accountid 
										 WHERE external_code = '".$row['base_number']."' AND deleted = 0";						
						$res_ext_code = $adb->query($qry_ext_code);
						$rows_ext_code = $adb->num_rows($res_ext_code);							
					}else {
						$rows_ext_code = 0;
					}
					if ($rows_ext_code > 0 && $row['base_number'] != '') {
						$id = $adb->query_result($res_ext_code,0,'accountid');
                        //danzi.tn@20140821 esiste in VTE ed ha un codice semiramis, quindi US (Update key is Semiramis) se non esiste
						$sem_importflag = $adb->query_result($res_ext_code,0,'sem_importflag');	
                        // danzi.tn@20141223 if(empty($sem_importflag)) {
                            $this->fields_auto_update['vtiger_account']['sem_importflag'] = 'US';  	
                            $this->fields_auto_update['vtiger_account']['sem_importdate'] = $this->time_start;                            
                        // }
						$this->update($row,$id);
						if( $row['base_number'] =='21442' || $row['base_number'] == '29574' || $row['base_number'] =='LV100')
						{
							echo "====== danzi.tn@20141223 begin\n";
							echo "base_number=".$row['base_number']."\n";
							echo "base_name=".$row['base_name']."\n";
							echo "agent_name=".$row['agent_name']."\n";
							echo "agent_number=".$row['agent_number']."\n";
							echo "new_category_desc=".$row['new_category_desc']."\n";
							echo "====== danzi.tn@20141223 end\n";
						}
						$rows_updated[] = $row[$this->external_code];
					}else {
						// danzi.tn@20130730 - check base_crmnumber
						if ($row['base_crmnumber'] != '') {
							$qry_base_crmnumber = "SELECT * FROM vtiger_account
										 JOIN vtiger_accountscf ON vtiger_account.accountid = vtiger_accountscf.accountid
										 JOIN vtiger_crmentity ON crmid = vtiger_account.accountid 
										 WHERE account_no = '".$row['base_crmnumber']."' AND deleted = 0";
							$res_base_crmnumber = $adb->query($qry_base_crmnumber);
							$rows_base_crmnumber = $adb->num_rows($res_base_crmnumber);	
						} else {
							$rows_base_crmnumber = 0;
						}
						if ($rows_base_crmnumber > 0 && $row['base_crmnumber'] != '') {
							$id = $adb->query_result($res_base_crmnumber,0,'accountid');
                            $sem_importflag = $adb->query_result($res_base_crmnumber,0,'sem_importflag');	
                            // danzi.tn@20141223 if(empty($sem_importflag)) {
                                //danzi.tn@20140821 esiste in VTE ma non ha un codice semiramis, quindi UB (Update key is BASE CRM NUMBER)
                                $this->fields_auto_update['vtiger_account']['sem_importflag'] = 'UB';
                                $this->fields_auto_update['vtiger_account']['sem_importdate'] = $this->time_start;
                                //danzi.tn@20140821e     
                            // danzi.tn@20141223 }
							$this->update($row,$id);
							$rows_updated[] = $row[$this->external_code];
						} else { // danzi.tn@20130730e
							//check partita iva
							if ($row['finance_localtaxid'] != '') {
								$qry_piva = "SELECT * FROM vtiger_account 
											JOIN vtiger_accountscf ON vtiger_account.accountid = vtiger_accountscf.accountid
											 JOIN vtiger_crmentity ON crmid = vtiger_account.accountid 
											 WHERE cf_751 = '".$row['finance_localtaxid']."' AND (external_code IS NULL OR external_code = '') AND deleted = 0";									 																				
								$res_piva = $adb->query($qry_piva);
								$rows_piva = $adb->num_rows($res_piva);
							}else {
								$rows_piva = 0;
							}
							if ($rows_piva > 0 && $row['finance_localtaxid'] != '' ) {
								$id = $adb->query_result($res_piva,0,'accountid');
                                $sem_importflag = $adb->query_result($res_piva,0,'sem_importflag');	
                                // danzi.tn@20141223 if(empty($sem_importflag)) {
                                    //danzi.tn@20140821 esiste in VTE ma non ha un codice semiramis, quindi UV (Update key is Vat)
                                    $this->fields_auto_update['vtiger_account']['sem_importflag'] = 'UV';
                                    $this->fields_auto_update['vtiger_account']['sem_importdate'] = $this->time_start;
                                    //danzi.tn@20140821e
                                // danzi.tn@20141223 }
								$this->update($row,$id);
								$rows_updated[] = $row[$this->external_code];
							} else {
								//check cf
								if ($row['finance_suppltaxid'] != '') {
									$qry_cf = "SELECT * FROM vtiger_account
											 JOIN vtiger_accountscf ON vtiger_account.accountid = vtiger_accountscf.accountid
										 JOIN vtiger_crmentity ON crmid = vtiger_account.accountid 
											 WHERE cf_750 = '".$row['finance_suppltaxid']."' AND (external_code IS NULL OR external_code = '') AND deleted = 0";									
									$res_cf = $adb->query($qry_cf);
									$rows_cf = $adb->num_rows($res_cf);
								} else {
									$rows_cf = 0;
								}
								if ($rows_cf > 0 && $row['finance_suppltaxid'] != '') {
									$id = $adb->query_result($res_cf,0,'accountid');
                                    $sem_importflag = $adb->query_result($res_cf,0,'sem_importflag');	
                                    // danzi.tn@20141223 if(empty($sem_importflag)) {
                                        //danzi.tn@20140821 esiste in VTE ma non ha un codice semiramis, quindi UF (Update key is Fiscal Code)
                                        $this->fields_auto_update['vtiger_account']['sem_importflag'] = 'UF';
                                        $this->fields_auto_update['vtiger_account']['sem_importdate'] = $this->time_start;
                                        //danzi.tn@20140821e
                                    // danzi.tn@20141223 }
									$this->update($row,$id);
									$rows_updated[] = $row[$this->external_code];
								} else {
									//danzi.tn@20140821 non esiste quindi bisogna anche inserire sem_importflag 'IN' (insert new) e sem_importdate a oggi
                                    //inoltre bisogna mettere 'cf_770' = 'Import Semiramis'
                                    $this->fields_auto_create['vtiger_account']['sem_importflag'] = 'IN';
                                    $this->fields_auto_create['vtiger_account']['sem_importdate'] = $this->time_start;
                                    $this->fields_auto_create['vtiger_accountscf']['cf_770'] = 'Import Semiramis';
                                    //danzi.tn@20140821e
									$this->create($row);
								}
							}
						}
					}
				} elseif ($this->module == 'Vendors') {
					$this->check_vendors($row,$rows_updated);
				} else {
					if ($this->existing_entity[$row[$this->external_code]] != ''){
						$this->update($row,$this->existing_entity[$row[$this->external_code]]);
						//mycrmv@rotho
						if ($this->module == 'SalesOrder'){
							$this->set_invetoryproducts($row,$this->existing_entity[$row[$this->external_code]]);
						} 
						elseif ($this->module == 'Quotes'){
							$this->set_invetoryproducts_quotes($row,$this->existing_entity[$row[$this->external_code]]);
						}
						//mycrmv@rotho e
					}
					else {
						$this->create($row);
					}
					//mycrmv@rotho
				}
				
				//mycrmv@30336e
				if ($this->module == 'SalesOrder'){
					$this->set_invetoryproducts($row,$this->existing_entity[$row[$this->external_code]]);
				}
				elseif ($this->module == 'Quotes'){
					$this->set_invetoryproducts_quotes($row,$this->existing_entity[$row[$this->external_code]]);
				}
				//mycrmv@rotho e
				$rows[]=$row[$this->external_code];
			}
			
			$this->close_files();
			$this->execute();
			//$this->delete_files();
			//crmv@18206
//			return Array('records_created'=>$this->records_created,'records_updated'=>$this->records_updated);
			return Array('records_created'=>$this->records_created,'records_updated'=>$this->records_updated,'external_code_rows'=>$rows,'upd_ext_codes' => $rows_updated);
			//crmv@18206e
		}
	}
	
	// danzi.tn@20140602 gestione Vendors
	private function check_vendors($row,&$rows_updated) {
		global $adb;
		//check codice fornitore semiramis
		if ($row['supplier_number'] != '') {
			// se il codice fornitore non è vuoto allora si verifica se ci sono già Vendor con lo stesso codice
			$qry_ext_code = "SELECT vtiger_vendor.vendorid from vtiger_vendor
							JOIN vtiger_vendorcf ON vtiger_vendorcf.vendorid = vtiger_vendor.vendorid
							JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_vendor.vendorid AND vtiger_crmentity.deleted = 0
							WHERE vtiger_vendorcf.cf_1115 = '".$row['supplier_number']."'";						
			$res_ext_code = $adb->query($qry_ext_code);
			$rows_ext_code = $adb->num_rows($res_ext_code);							
		} else {
			// echo "Nothing Found for SUPPLIER_NUMBER  ".$row['supplier_number']."\n" ;
			$rows_ext_code = 0;
		}
		if ($rows_ext_code > 0 && $row['supplier_number'] != '') {
			$id = $adb->query_result($res_ext_code,0,'vendorid');	
			// echo "Found SUPPLIER_NUMBER  with id ".$id."\n" ;
			$this->update($row,$id);
			$rows_updated[] = $row[$this->external_code];
		}else {
			// danzi.tn@20140602 - check partita IVA
			if ($row['finance_taxidcee'] != '') {
				// se la partita iva internazionale non è vuota allora si verifica se ci sono già Vendor con la stessa partita IVA
				$qry_piva = "SELECT vtiger_vendor.vendorid  from vtiger_vendor
							JOIN vtiger_vendorcf ON vtiger_vendorcf.vendorid = vtiger_vendor.vendorid
							JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_vendor.vendorid AND vtiger_crmentity.deleted = 0 
							WHERE vtiger_vendor.vendor_vat_code = '".$row['finance_taxidcee']."'";
				$res_piva = $adb->query($qry_piva);
				$rows_piva = $adb->num_rows($res_piva);
			}else {
				$rows_piva = 0;
				// echo "Nothing Found for FINANCE_TAXIDCEE  ".$row['finance_taxidcee']."\n" ;
			}
			if ($rows_piva > 0 && $row['finance_localtaxid'] != '' ) {
				$id = $adb->query_result($res_piva,0,'vendorid');
				// echo "Found FINANCE_TAXIDCEE  with id ".$id."\n" ;
				$this->update($row,$id);
				$rows_updated[] = $row[$this->external_code];
			} else {
				// danzi.tn@20140602 - check codice Fiscale
				if ($row['finance_suppltaxid'] != '') {
					// se il codice fiscale non è vuoto allora si verifica se ci sono già Vendor con lo stesso codice
					$qry_cf = "SELECT vtiger_vendor.vendorid  from vtiger_vendor
							JOIN vtiger_vendorcf ON vtiger_vendorcf.vendorid = vtiger_vendor.vendorid
							JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_vendor.vendorid AND vtiger_crmentity.deleted = 0
							WHERE vtiger_vendor.vendor_fiscal_code = '".$row['finance_suppltaxid']."'";									
					$res_cf = $adb->query($qry_cf);
					$rows_cf = $adb->num_rows($res_cf);
				} else {
					$rows_cf = 0;
				}
				if ($rows_cf > 0 && $row['finance_suppltaxid'] != '') {
					$id = $adb->query_result($res_cf,0,'vendorid');
					// echo "Found FINANCE_SUPPLTAXID  with id ".$id."\n" ;
					$this->update($row,$id);
					$rows_updated[] = $row[$this->external_code];
				} else {
					// echo "Nothing Found for FINANCE_SUPPLTAXID  ".$row['finance_suppltaxid']."\n" ;
					//alla fine se non è stato trovato nulla allora vuol dire che non c'è e deve essere creato
					$this->create($row);
				}
			}
		}
	}
	
	//mycrmv@rotho	//mycrmv@2707m
	public function set_invetoryproducts_quotes($row,$external_code){
		global $adb;

		$sql = "SELECT detail_number,detail_item,detail_quantity,detail_netprice,sconto1,sconto2
					FROM erp_temp_crm_customerproposal
					WHERE proposal_number = ? ORDER BY detail_number";
		$res = $adb->pquery($sql,Array($row['proposal_number']));
		
		$del = "DELETE FROM vtiger_inventoryproductrel WHERE id = $this->id";
		$adb->query($del);
		
		while ($rows = $adb->fetchByAssoc($res,-1,false)){
			$q_inventory = "SELECT productid
							FROM vtiger_products INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_products.productid
							WHERE base_no = ? AND deleted = 0";
			$res1 = $adb->pquery($q_inventory,Array($rows['detail_item']));
			$productid = $adb->query_result($res1,0,'productid');
			$rows['detail_item'] = $productid;
			$rows['lineitemid'] = $adb->getUniqueID('vtiger_inventoryproductrel');
			$erp_discount = array_filter(array($rows['sconto1'],$rows['sconto2']));
			if (!empty($erp_discount)) {
				$erp_discount = implode(' + ',$erp_discount);
			} else {
				$erp_discount = '';
			}
			$insert = "INSERT INTO vtiger_inventoryproductrel (id,productid,sequence_no,quantity,listprice,lineitem_id,erp_discount) values (?,?,?,?,?,?,?)";
			$adb->pquery($insert,Array($this->id,$rows['detail_item'],$rows['detail_number'],$rows['detail_quantity'],$rows['detail_netprice'],$rows['lineitemid'],$erp_discount));
			$rows = array();
		}
	}
	
	public function set_invetoryproducts($row,$external_code){
		global $adb;

		$sql = "SELECT fatturato_netto,ordine_riga,articolo_code,quantita,sconto1,sconto2
					FROM erp_temp_crm_ordini
					WHERE ordine_numero = ? order by ordine_riga";
		$res = $adb->pquery($sql,Array($row['ordine_numero_key']));
		
		$del = "DELETE FROM vtiger_inventoryproductrel WHERE id = $this->id";
		$adb->query($del);
		
		while ($rows = $adb->fetchByAssoc($res,-1,false)){
			$q_inventory = "SELECT productid
							FROM vtiger_products INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_products.productid
							WHERE base_no = ? AND deleted = 0";
			$res1 = $adb->pquery($q_inventory,Array($rows['articolo_code']));
			$productid = $adb->query_result($res1,0,'productid');
			$rows['articolo_code'] = $productid;
			$rows['lineitemid'] = $adb->getUniqueID('vtiger_inventoryproductrel');
			//$params[] = $rows;
			$erp_discount = array_filter(array($rows['sconto1'],$rows['sconto2']));
			if (!empty($erp_discount)) {
				$erp_discount = implode(' + ',$erp_discount);
			} else {
				$erp_discount = '';
			}
			$insert = "INSERT INTO vtiger_inventoryproductrel (id,productid,sequence_no,quantity,listprice,lineitem_id,erp_discount) values (?,?,?,?,?,?,?)";
            // danzi.tn@20140820 divisione per zero
            $list_price = 0.0;
            if( !empty($rows['quantita']) && $rows['quantita'] !=0  ) {
                $list_price = ($rows['fatturato_netto']/$rows['quantita']);
            }
            // danzi.tn@20140820
			$adb->pquery($insert,Array($this->id,$rows['articolo_code'],$rows['ordine_riga'],$rows['quantita'],$list_price,$rows['lineitemid'],$erp_discount));
		}
	}
	//mycrmv@rotho e	//mycrmv@2707me
	
	private function close_files(){
		foreach ($this->object->tab_name_index as $t=>$k){
			fclose($this->sql_file_create[$t]);
		}
		fclose($this->sql_file_update);
	}
	private function delete_files(){
		foreach ($this->object->tab_name_index as $t=>$k){
			@unlink($this->sql_file_name_create[$t]);
		}
		@unlink($this->sql_file_name_update);
	}
	private function getcached_update_arr() {
		if ($this->update_arr)
			return $this->update_arr;
		if(is_array($this->fields_auto_update)) {
			foreach ($this->fields_auto_update as $table => $arr){
					foreach ($arr as $field=>$def_value){
						$this->update_arr[$table][$field] = $def_value;
					}			
			}
			return $this->update_arr;
		}
	}	
	private function update($data,$id){
		global $adb;
		$update = $this->getcached_update_arr();		
		foreach ($this->fields as $table => $arr){
			foreach ($arr as $field){
				if ($this->module == 'Accounts' && (in_array($field,array('bill_city','bill_code','bill_country','bill_state','bill_street')))) {	//mycrmv@2707m
					continue;
				}
//				$update[$table][$field] = $data[$this->mapping_inverse[$field]];
				$update[$table][$field] = $data[$this->mapping_column[$field]];
				//mycrmv@rotho
				
				//manuele - i
				if ($this->fields_functions[$field] != '') {
					$mapping_column_field = $this->mapping_column[$field];
					if (in_array($field,array('zona','cf_799','user_name','last_name','first_name')))
						$update[$table][$field] = $this->fields_functions[$field]($data);
					else{
						$update[$table][$field] = $this->fields_functions[$field]($data[$mapping_column_field]);
					}
				}
				//manuele - e
				//mycrmv@rotho
				if(in_array($field,array('total','subtotal'))){
					$update[$table]['total'] = $data['total'];
					$update[$table]['subtotal'] = $data['subtotal'];
				}//mycrmv@rotho e
			}
		}
		foreach ($update as $table=>$arr){
			array_walk($arr,'sanitize_array_sql');
			$sql  = "update $table set ";
			$first = true;
			foreach ($arr as $field=>$value){
				if (!$first)
					$sql .=",";
				$sql .=" $field = $value";
				$first = false;
			}
			$sql.=" where ".$this->getkey('full',$table)." = $id ;\n";			
			fwrite($this->sql_file_update,$sql);
		}
		$this->id = $id;
		$this->records_updated++;
	}
	private function getkey($mode = '',$table = false){
		if (!$table)
			$table = 'vtiger_crmentity';
		if ($mode == 'full')
			return $table.".".$this->object->tab_name_index[$table];
		else
			return $this->object->tab_name_index[$table];
	}
	private function getcached_create_arr($data = true) {
		if ($this->create_arr)
			return $this->create_arr;
		foreach ($this->fields_auto_create as $table => $arr){
				foreach ($arr as $field=>$def_value){
					if (!$data) 
						$def_value = '';
					$this->create_arr[$table][$field] = $def_value;
				}			
		}
		return $this->create_arr; 
	}	
	private function create($data){
		global $adb,$current_user;
		//manuele i
		if ($this->module == 'Users')
			$id = $adb->getUniqueID('vtiger_users');
		else
		//manuele e
			$id = $adb->getUniqueID('vtiger_crmentity');
		$create = $this->getcached_create_arr();
		foreach ($this->fields as $table => $arr){
			foreach ($arr as $field){
//				$create[$table][$field] = $data[$this->mapping_inverse[$field]];
				$create[$table][$field] = $data[$this->mapping_column[$field]];
				//manuele - i
				if ($this->fields_functions[$field] != '') {
					$mapping_column_field = $this->mapping_column[$field];
					if (in_array($field,array('zona','cf_799','user_name','last_name','first_name')))
						$create[$table][$field] = $this->fields_functions[$field]($data);
					else
						$create[$table][$field] = $this->fields_functions[$field]($data[$mapping_column_field]);
				}
				//manuele - e
				//mycrmv@rotho
				if(in_array($field,array('total','subtotal'))){
					$create[$table]['total'] = $data['total'];
					$create[$table]['subtotal'] = $data['subtotal'];
				}//mycrmv@rotho e
			}
			$create[$table][$this->object->tab_name_index[$table]] = $id;
		}
		foreach ($this->object->tab_name_index as $t=>$k){
			if (!$create[$t][$k])
				$create[$t][$k] = $id;
		}		
		$sql_number_create = $this->sequence_number();
		//manuele i
		if ($this->module != 'Users') {
			if ($sql_number_create[0]){
				$create[$sql_number_create[1]][$sql_number_create[2]] = $this->object->setModuleSeqNumber("increment",$this->module);
			}
		}
		else {
			fwrite($this->sql_file_update,"insert into vtiger_user2role values ($id,'H5');\n");
			$this->created_users[] = $id;
		}
		//manuele e
		foreach ($this->object->tab_name_index as $t=>$k){
			$this->insert_into_create_file($t,$create);
		}
		$this->id = $id; //mycrmv@rotho
		$this->records_created++;	
	}
	private function insert_into_create_file($table,$create){
		global $adb;
		
		if ($adb->isMySQL()){
			fputcsv($this->sql_file_create[$table],$create[$table]);
		}		
		else{
			array_walk($create[$table],'sanitize_array_sql');
//			if (!$this->create_query[$table]){
//				$this->create_query[$table] = true;
//				fwrite($this->sql_file_create[$table],"insert into $table (".implode(",",array_keys($create[$table])).") values ");
//				fwrite($this->sql_file_create[$table],"(".implode(",",$create[$table]).") ");
//			}
//			else {	
//				fwrite($this->sql_file_create[$table],",(".implode(",",$create[$table]).") ");
//			}	
			fwrite($this->sql_file_create[$table],"insert into $table (".implode(",",array_keys($create[$table])).") values ");
			fwrite($this->sql_file_create[$table],"(".implode(",",$create[$table]).")\n");		
		}
	}
	private function sequence_number(){
		if ($this->sequence_number)
			return $this->sequence_number;
		global $adb;
		$sql = "select tablename,columnname from vtiger_field where tabid = ? and uitype = 4";
		$res = $adb->pquery($sql,Array(getTabid($this->module)));
		if ($res){
			$this->sequence_number = Array(true,$adb->query_result($res,0,'tablename'),$adb->query_result($res,0,'columnname'));
			
		}
		else
			$this->sequence_number = Array(false);
		return 	$this->sequence_number;
	}
	private function execute(){
		global $dbconfig,$adb,$root_directory;
		if ($adb->isMySQL()){
			//faccio le create
			foreach ($this->object->tab_name_index as $t=>$k){
				$this->load_csv_mysql($t);
			}
			//faccio le insert
			$update_file = fopen($this->sql_file_name_update,'r');
			while(!feof($update_file)){
//				echo "\n".fgets($update_file);
				$sql = fgets($update_file);
				if ($sql!="")
					$adb->query($sql,true);
			}
			fclose($update_file);
			//manuele i
			if ($this->module == 'Users') {
				$crypt_type = 'MD5';
				$new_pwd = 'readytec';
				include_once('modules/Users/Users.php');
				require_once('modules/Users/CreateUserPrivilegeFile.php');
				foreach($this->created_users as $userid) {
					$user = new Users();
					$user->retrieve_entity_info($userid,'Users');
					$encrypted_password = $user->encrypt_password($new_pwd, $crypt_type);
					$user_hash = strtolower(md5($new_pwd));
					$query = "UPDATE vtiger_users SET user_password=?, user_hash=?, crypt_type=? where id=?";
					$adb->pquery($query,array($encrypted_password, $user_hash, $crypt_type, $userid));
					
					createUserPrivilegesfile($userid);
					createUserSharingPrivilegesfile($userid);
				}
			}
			//manuele e
			//uso la exec
//			$string = "mysql -u ".$dbconfig['db_username']." --password=".$dbconfig['db_password']." ".$dbconfig['db_name']." < ".$this->sql_file_name_update;
//			exec($string);
		}
		else{
			//faccio le create
			foreach ($this->object->tab_name_index as $t=>$k){
				// danzi.tn@20131206 - nel caso dei Products , tab_name_index contiene anche vtiger_seproductsrel e vtiger_producttaxrel che non sono in vtiger_field e che non possono essere create solo con id
				if($this->module=="Products" && ($t=="vtiger_seproductsrel" ||$t=="vtiger_producttaxrel" )) {
					echo "skipping ".$t."\n";
					continue;
				}
				// danzi.tn@20131206e
				$create_file = fopen($this->sql_file_name_create[$t],'r');
				while(!feof($create_file)){
					$sql = fgets($create_file);
					if ($sql!="")
						$adb->query($sql,true);
				}
				fclose($create_file);
			}			
			//faccio le insert
			$update_file = fopen($this->sql_file_name_update,'r');
			while(!feof($update_file)){
				$sql = fgets($update_file);
				if ($sql!="")
					$adb->query($sql,true);
			}
			fclose($update_file);			
		}
	}
	function load_csv_mysql($table){
		global $adb,$root_directory;
		$fields = $this->get_column_create($table);
		$sql_load = "LOAD DATA LOCAL INFILE ? INTO TABLE $table FIELDS ESCAPED BY ? TERMINATED BY ? OPTIONALLY ENCLOSED BY ? LINES TERMINATED BY ? (".implode(",",$fields).")";
		$params[] = $root_directory.$this->sql_file_name_create[$table];
		$params[] = "\\";
		$params[] = ",";
		$params[] = '"';
		$params[] = "\n";
		$adb->pquery($sql_load,$params,true);
	}
	function load_csv_mssql($table){
		global $adb,$root_directory;
		$fields = $this->get_column_create($table);
		$sql_load = "BULK INSERT $table FROM ? WITH (FIELDTERMINATOR = ?,ROWTERMINATOR = ?) (".implode(",",$fields).")";
		$params[] = $root_directory.$this->sql_file_name_create[$table];
		$params[] = ",";
		$params[] = "\n";
		$adb->pquery($sql_load,$params,true);
	}
}
?>