<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of txhe License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
include_once('config.php');
require_once('include/logging.php');
require_once('data/SugarBean.php');
require_once('include/utils/utils.php');
require_once('user_privileges/default_module_view.php');

class HelpDesk extends CRMEntity {
	var $log;
	var $db;
	var $table_name;
	var $table_index= 'ticketid';
	var $tab_name = Array();
	var $tab_name_index = Array();
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array();
	
	var $column_fields = Array();
	//Pavani: Assign value to entity_table
        var $entity_table;

	var $sortby_fields = Array('title','status','priority','crmid','firstname','smownerid','parent_id'); //crmv@7214s

	var $list_fields = Array(
					//Module Sequence Numbering
					//'Ticket ID'=>Array('crmentity'=>'crmid'),
					'Ticket No'=>Array('troubletickets'=>'ticket_no'),
					// END
					'Subject'=>Array('troubletickets'=>'title'),	  			
					'Related to'=>Array('troubletickets'=>'parent_id'),	  			
					'Status'=>Array('troubletickets'=>'status'),
					'Priority'=>Array('troubletickets'=>'priority'),
					'Assigned To'=>Array('crmentity'=>'smownerid')
				);

	var $list_fields_name = Array(
					//'Ticket ID'=>'',
					'Ticket No'=>'ticket_no',
					'Subject'=>'ticket_title',	  			
					'Related to'=>'parent_id',	  			
					'Status'=>'ticketstatus',
					'Priority'=>'ticketpriorities',
					'Assigned To'=>'assigned_user_id'
				     );

	var $list_link_field= 'ticket_title';
			
	var $range_fields = Array(
				        'ticketid',
					'title',
			        	'firstname',
				        'lastname',
			        	'parent_id',
			        	'productid',
			        	'productname',
			        	'priority',
			        	'severity',
				        'status',
			        	'category',
					'description',
					'solution',
					'modifiedtime',
					'createdtime'
				);
	var $search_fields = Array();
	var $search_fields_name = Array(
		'Ticket No' => 'ticket_no',
		'Title'=>'ticket_title',
		);
	//Specify Required fields
    var $required_fields =  array();
    
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('assigned_user_id', 'createdtime', 'modifiedtime', 'ticket_title', 'update_log');

    //Added these variables which are used as default order by and sortorder in ListView
    var $default_order_by = 'modifiedtime';
    var $default_sort_order = 'DESC';
	//crmv@10759
	var $search_base_field = 'ticket_title';
	//crmv@10759 e
	
	//var $groupTable = Array('vtiger_ticketgrouprelation','ticketid');
	
	//crmv@2043m
	var $waitForResponseStatus = 'Wait For Response';
	var $answeredByCustomerStatus = 'Open';
	//crmv@2043me

	/**	Constructor which will set the column_fields in this object
	 */
	function HelpDesk() 
	{
		global $table_prefix;
		$this->table_name = $table_prefix."_troubletickets";
		$this->tab_name = Array($table_prefix.'_crmentity',$table_prefix.'_troubletickets',$table_prefix.'_ticketcf');
		$this->tab_name_index = Array($table_prefix.'_crmentity'=>'crmid',$table_prefix.'_troubletickets'=>'ticketid',$table_prefix.'_ticketcf'=>'ticketid',$table_prefix.'_ticketcomments'=>'ticketid');
		$this->customFieldTable = Array($table_prefix.'_ticketcf', 'ticketid');
	    $this->entity_table = $table_prefix."_crmentity";
        $this->search_fields = Array(
		//'Ticket ID' => Array($table_prefix.'_crmentity'=>'crmid'),
		'Ticket No' =>Array($table_prefix.'_troubletickets'=>'ticket_no'),
		'Title' => Array($table_prefix.'_troubletickets'=>'title')
		);
		$this->log =LoggerManager::getLogger('helpdesk');
		$this->log->debug("Entering HelpDesk() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('HelpDesk');
		$this->log->debug("Exiting HelpDesk method ...");
	}

	function save_module($module)
	{
		//crmv@27146
		//Inserting into Ticket Comment Table
		if(isset($_REQUEST['action']) && $_REQUEST['action'] != 'MassEditSave'){
			$this->insertIntoTicketCommentTable($table_prefix."_ticketcomments",'HelpDesk');
		}
		//Inserting into vtiger_attachments
		//$this->insertIntoAttachment($this->id,'HelpDesk');
		//crmv@27146e
		
		/* commento altrimenti passa per la save_related_module sia qui che nella CRMEntity
		$return_action = $_REQUEST['return_action'];
		$for_module = $_REQUEST['return_module'];
		$for_crmid  = $_REQUEST['return_id'];
		if ($return_action && $for_module && $for_crmid) {
			if ($for_module != 'Accounts' && $for_module != 'Contacts' && $for_module != 'Products') {
				$on_focus = CRMEntity::getInstance($for_module);
				$on_focus->save_related_module($for_module, $for_crmid, $module, $this->id);
			}
		}
		*/
	}

	/** Function to insert values in vtiger_ticketcomments  for the specified tablename and  module
  	  * @param $table_name -- table name:: Type varchar
  	  * @param $module -- module:: Type varchar
 	 */	
	function insertIntoTicketCommentTable($table_name, $module)
	{
		global $log;
		$log->info("in insertIntoTicketCommentTable  ".$table_name."    module is  ".$module);
       	global $adb;
       	global $table_prefix;
		global $current_user;

        $current_time = $adb->formatDate(date('Y-m-d H:i:s'), true);
		if($this->column_fields['assigned_user_id'] != '')
			$ownertype = 'user';
		else
			$ownertype = 'customer';

		if($this->column_fields['comments'] != '')
			$comment = $this->column_fields['comments'];
		else
			$comment = $_REQUEST['comments'];
		
		if($comment != '')
		{
			$comid = $adb->getUniqueID($table_prefix.'_ticketcomments');
			$sql = "insert into ".$table_prefix."_ticketcomments values(?,?,?,?,?,?)";	
	        	$params = array($comid, $this->id, from_html($comment), $current_user->id, $ownertype, $current_time);
			$adb->pquery($sql, $params);
		}
	}


	/**
	 *      This function is used to add the vtiger_at".$table_prefix."nts. This will call the function uploadAndSaveFile which will upload the attachment into the server and save that attachment information in the database.
	 *      @param int $id  - entity id to which the vtiger_files to be uploaded
	 *      @param string $module  - the current module name
	*/
	function insertIntoAttachment($id,$module)
	{
		global $log, $adb;
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");
		
		$file_saved = false;

		foreach($_FILES as $fileindex => $files)
		{
			if($files['name'] != '' && $files['size'] > 0)
			{
				$files['original_name'] = vtlib_purify($_REQUEST[$fileindex.'_hidden']);
				$file_saved = $this->uploadAndSaveFile($id,$module,$files);
			}
		}

		$log->debug("Exiting from insertIntoAttachment($id,$module) method.");
	}
	
	
	/**
	 * Function to get sort order
	 * return string  $sorder    - sortorder string either 'ASC' or 'DESC'
	 */
	function getSortOrder()
	{
		global $log,$currentModule;
		$log->debug("Entering getSortOrder() method ...");
		$use_default_order_by = '';
		//default listview sorting
		if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
				$use_default_sort_order = $this->default_sort_order;
		}
		//crmv default listview customview sorting
		if ($this->customview_sort_order != '' && $use_default_sort_order != $this->customview_sort_order)
				$use_default_sort_order = $this->customview_sort_order;		
		if(isset($_REQUEST['sorder']))
			$sorder = $this->db->sql_escape_string($_REQUEST['sorder']);
		elseif ($_REQUEST['override_orderby'] == 'true')
			$sorder = $use_default_sort_order;
		else
			$sorder = (($_SESSION[$currentModule.'_SORT_ORDER'] != '')?($_SESSION[$currentModule.'_SORT_ORDER']):($use_default_sort_order));

		$log->debug("Exiting getSortOrder method ...");
		return $sorder;
	}

	/**
	 * Function to get order by
	 * return string  $order_by    - fieldname(eg: 'campaignname')
	 */
	function getOrderBy()
	{
		global $log,$currentModule;
		$log->debug("Entering getOrderBy() method ...");
		$use_default_order_by = '';
		//default listview sorting
		if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
				$use_default_order_by = $this->default_order_by;
		}
		//crmv default listview customview sorting
		if ($this->customview_order_by != '' && $use_default_order_by != $this->customview_order_by)
				$use_default_order_by = $this->customview_order_by;
		if (isset($_REQUEST['order_by']))
			$order_by = $this->db->sql_escape_string($_REQUEST['order_by']);
		elseif ($_REQUEST['override_orderby'] == 'true')
			$order_by = $use_default_order_by;
		else	
			$order_by = (($_SESSION[$currentModule.'_ORDER_BY'] != '')?($_SESSION[$currentModule.'_ORDER_BY']):($use_default_order_by));

		$log->debug("Exiting getOrderBy method ...");
		return $order_by;
	}

	/**     Function to form the query to get the list of activities
         *      @param  int $id - ticket id
	 *	@return array - return an array which will be returned from the function GetRelatedList
        **/
	function get_activities($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
		$log->debug("Entering get_activities(".$id.") method ...");

		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance('Activity');
        vtlib_setup_modulevars($related_module, $other);		
		$singular_modname = vtlib_toSingular($related_module);
		
		$parenttab = getParentTab();
		
		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;
		
		$button = '';

		$button .= '<input type="hidden" name="activity_mode">';
		
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_TODO', $related_module) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Task\";' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_TODO', $related_module) ."'>&nbsp;";
				$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_EVENT', $related_module) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Events\";' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_EVENT', $related_module) ."'>";
			}
		}

		$query = "SELECT case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name," .
					" ".$table_prefix."_activity.*, ".$table_prefix."_cntactivityrel.contactid, ".$table_prefix."_contactdetails.lastname, ".$table_prefix."_contactdetails.firstname," .
					" ".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid, ".$table_prefix."_crmentity.modifiedtime," .
					" ".$table_prefix."_seactivityrel.crmid as parent_id " .
					" from ".$table_prefix."_activity inner join ".$table_prefix."_seactivityrel on ".$table_prefix."_seactivityrel.activityid=".$table_prefix."_activity.activityid" .
					" inner join ".$table_prefix."_activitycf on ".$table_prefix."_activitycf.activityid = ".$table_prefix."_activity.activityid" .
					" inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_activity.activityid" .
					" left join ".$table_prefix."_cntactivityrel on ".$table_prefix."_cntactivityrel.activityid = ".$table_prefix."_activity.activityid " .
					" left join ".$table_prefix."_contactdetails on ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_cntactivityrel.contactid" .
					" left outer join ".$table_prefix."_recurringevents on ".$table_prefix."_recurringevents.activityid=".$table_prefix."_activity.activityid" .
					" left join ".$table_prefix."_users on ".$table_prefix."_users.id=".$table_prefix."_crmentity.smownerid" .
					" left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid=".$table_prefix."_crmentity.smownerid" .
					" where ".$table_prefix."_seactivityrel.crmid=".$id." and ".$table_prefix."_crmentity.deleted=0 and (activitytype NOT IN ('Emails'))" .
							" AND ( ".$table_prefix."_activity.status is NULL OR ".$table_prefix."_activity.status != 'Completed' )" .
							" and ( ".$table_prefix."_activity.eventstatus is NULL OR ".$table_prefix."_activity.eventstatus != 'Held') ";
	
//crmv@8398 		
			$query.=getCalendarSql();
//crmv@8398e
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_activities method ...");		

		return $return_value;
	}

	/**     Function to get the Ticket History information as in array format
	 *	@param int $ticketid - ticket id
	 *	@return array - return an array with title and the ticket history informations in the following format
							array(	
								header=>array('0'=>'title'),
								entries=>array('0'=>'info1','1'=>'info2',etc.,)
							     )
	 */
	function get_ticket_history($ticketid)
	{
		global $log, $adb;
		global $table_prefix;
		$log->debug("Entering into get_ticket_history($ticketid) method ...");

		$query="select title,update_log from ".$table_prefix."_troubletickets where ticketid=?";
		$result=$adb->pquery($query, array($ticketid));
		$update_log = $adb->query_result($result,0,"update_log");

		$splitval = explode('--//--',trim($update_log,'--//--'));

		$header[] = $adb->query_result($result,0,"title");

		$return_value = Array('header'=>$header,'entries'=>$splitval);

		$log->debug("Exiting from get_ticket_history($ticketid) method ...");

		return $return_value;
	}


	/**	Function to get the ticket comments as a array
	 *	@param  int   $ticketid - ticketid
	 *	@return array $output - array(	
						[$i][comments]    => comments
						[$i][owner]       => name of the user or customer who made the comment
						[$i][createdtime] => the comment created time
					     ) 
				where $i = 0,1,..n which are all made for the ticket
	**/
	function get_ticket_comments_list($ticketid)
	{
		global $log;
		global $table_prefix;
		$log->debug("Entering get_ticket_comments_list(".$ticketid.") method ...");
		 $sql = "select * from ".$table_prefix."_ticketcomments where ticketid=? order by createdtime DESC";
		 $result = $this->db->pquery($sql, array($ticketid));
		 $noofrows = $this->db->num_rows($result);
		 for($i=0;$i<$noofrows;$i++)
		 {
			 $ownerid = $this->db->query_result($result,$i,"ownerid");
			 $ownertype = $this->db->query_result($result,$i,"ownertype");
			 if($ownertype == 'user')
				 $name = getUserName($ownerid);
			 elseif($ownertype == 'customer')
			 {
				 $sql1 = 'select * from '.$table_prefix.'_portalinfo where id=?';
				 $name = $this->db->query_result($this->db->pquery($sql1, array($ownerid)),0,'user_name');
			 }

			 $output[$i]['comments'] = nl2br($this->db->query_result($result,$i,"comments"));
			 $output[$i]['owner'] = $name;
			 $output[$i]['createdtime'] = $this->db->query_result($result,$i,"createdtime");
		 }
		$log->debug("Exiting get_ticket_comments_list method ...");
		 return $output;
	 }
		
	/**	Function to form the query which will give the list of tickets based on customername and id ie., contactname and contactid
	 *	@param  string $user_name - name of the customer ie., contact name
	 *	@param  int    $id	 - contact id 
	 * 	@return array  - return an array which will be returned from the function process_list_query
	**/
	function get_user_tickets_list($user_name,$id,$where='',$match='')
	{
		global $log;
		global $table_prefix;
		$log->debug("Entering get_user_tickets_list(".$user_name.",".$id.",".$where.",".$match.") method ...");

		$this->db->println("where ==> ".$where);

		$query = "select ".$table_prefix."_crmentity.crmid, ".$table_prefix."_troubletickets.*, ".$table_prefix."_crmentity.description, ".$table_prefix."_crmentity.smownerid, ".$table_prefix."_crmentity.createdtime, ".$table_prefix."_crmentity.modifiedtime, ".$table_prefix."_contactdetails.firstname, ".$table_prefix."_contactdetails.lastname, ".$table_prefix."_products.productid, ".$table_prefix."_products.productname, ".$table_prefix."_ticketcf.* 
			from ".$table_prefix."_troubletickets 
			inner join ".$table_prefix."_ticketcf on ".$table_prefix."_ticketcf.ticketid = ".$table_prefix."_troubletickets.ticketid 
			inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_troubletickets.ticketid 
			left join ".$table_prefix."_contactdetails on ".$table_prefix."_troubletickets.parent_id=".$table_prefix."_contactdetails.contactid 
			left join ".$table_prefix."_products on ".$table_prefix."_products.productid = ".$table_prefix."_troubletickets.product_id 
			left join ".$table_prefix."_users on ".$table_prefix."_crmentity.smownerid=".$table_prefix."_users.id 
			where ".$table_prefix."_crmentity.deleted=0 and ".$table_prefix."_contactdetails.email='".$user_name."' and ".$table_prefix."_troubletickets.parent_id = '".$id."'";

		if(trim($where) != '')
		{
			if($match == 'all' || $match == '')
			{
				$join = " and ";
			}
			elseif($match == 'any')
			{
				$join = " or ";
			}
			$where = explode("&&&",$where);
			$count = count($where);
			$count --;
			$where_conditions = "";
			foreach($where as $key => $value)
			{
				$this->db->println('key : '.$key.'...........value : '.$value);
				$val = explode(" = ",$value);
				$this->db->println('val0 : '.$val[0].'...........val1 : '.$val[1]);
				if($val[0] == $table_prefix.'_troubletickets.title')
				{
					$where_conditions .= $val[0]."  ".$val[1];
					if($count != $key) 	$where_conditions .= $join;
				}
				elseif($val[1] != '' && $val[1] != 'Any')
				{
					$where_conditions .= $val[0]." = ".$val[1];
					if($count != $key)	$where_conditions .= $join;
				}
			}
			if($where_conditions != '')
				$where_conditions = " and ( ".$where_conditions." ) ";

			$query .= $where_conditions;
			$this->db->println("where condition for customer portal tickets search : ".$where_conditions);
		}

		$query .= " order by ".$table_prefix."_crmentity.crmid desc";
		$log->debug("Exiting get_user_tickets_list method ...");
		return $this->process_list_query($query);
	}

	/**	Function to process the list query and return the result with number of rows
	 *	@param  string $query - query 
	 *	@return array  $response - array(	list           => array(   
											$i => array(key => val)   
									       ),
							row_count      => '',
							next_offset    => '',
							previous_offset	=>''		 
						)
		where $i=0,1,..n & key = ticketid, title, firstname, ..etc(range_fields) & val = value of the key from db retrieved row 
	**/
	function process_list_query($query)
	{
		global $log;
		$log->debug("Entering process_list_query(".$query.") method ...");
	  
   		$result =& $this->db->query($query,true,"Error retrieving $this->object_name list: ");
		$list = Array();
	        $rows_found =  $this->db->getRowCount($result);
        	if($rows_found != 0)
	        {
			$ticket = Array();
			for($index = 0 , $row = $this->db->fetchByAssoc($result, $index); $row && $index <$rows_found;$index++, $row = $this->db->fetchByAssoc($result, $index))
			{
		                foreach($this->range_fields as $columnName)
                		{
		                	if (isset($row[$columnName])) 
					{
			                	$ticket[$columnName] = $row[$columnName];
                    			}
		                       	else     
				        {   
		                        	$ticket[$columnName] = "";
			                }   
	     			}	
    		                $list[] = $ticket;
                	}
        	}   

		$response = Array();
	        $response['list'] = $list;
        	$response['row_count'] = $rows_found;
	        $response['next_offset'] = $next_offset;
        	$response['previous_offset'] = $previous_offset;

		$log->debug("Exiting process_list_query method ...");
	        return $response;
	}

	/**	Function to get the HelpDesk field labels in caps letters without space
	 *	@return array $mergeflds - array(	key => val	)    where   key=0,1,2..n & val = ASSIGNEDTO,RELATEDTO, .,etc
	**/
	function getColumnNames_Hd()
	{
		global $log,$current_user;
		global $table_prefix;
		$log->debug("Entering getColumnNames_Hd() method ...");
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
		{
			$sql1 = "select fieldlabel from ".$table_prefix."_field where tabid=13 and block <> 30 and ".$table_prefix."_field.uitype <> '61' and ".$table_prefix."_field.presence in (0,2)";
			$params1 = array();
		}else
		{
			$profileList = getCurrentUserProfileList();
			$sql1 = "select ".$table_prefix."_field.fieldid,fieldlabel from ".$table_prefix."_field inner join ".$table_prefix."_def_org_field on ".$table_prefix."_def_org_field.fieldid=".$table_prefix."_field.fieldid where ".$table_prefix."_field.tabid=13 and ".$table_prefix."_field.block <> 30 and ".$table_prefix."_field.uitype <> '61' and ".$table_prefix."_field.displaytype in (1,2,3,4) and ".$table_prefix."_def_org_field.visible=0 and ".$table_prefix."_field.presence in (0,2)";
			$params1 = array();
		    $sql1.=" AND EXISTS(SELECT * FROM ".$table_prefix."_profile2field WHERE ".$table_prefix."_profile2field.fieldid = ".$table_prefix."_field.fieldid ";
		        if (count($profileList) > 0) {
			  	 	$sql1.=" AND ".$table_prefix."_profile2field.profileid IN (". generateQuestionMarks($profileList) .") ";
			  	 	array_push($params1, $profileList);
			} 			  
		    $sql1.=" AND ".$table_prefix."_profile2field.visible = 0) "; 			
		}
		$result = $this->db->pquery($sql1, $params1);
		$numRows = $this->db->num_rows($result);
		for($i=0; $i < $numRows;$i++)
		{
			$custom_fields[$i] = $this->db->query_result($result,$i,"fieldlabel");
			$custom_fields[$i] = ereg_replace(" ","",$custom_fields[$i]);
			$custom_fields[$i] = strtoupper($custom_fields[$i]);
		}
		$mergeflds = $custom_fields;
		$log->debug("Exiting getColumnNames_Hd method ...");
		return $mergeflds;
	}

	/**     Function to get the list of comments for the given ticket id
	 *      @param  int  $ticketid - Ticket id
	 *      @return list $list - return the list of comments and comment informations as a html output where as these comments and comments informations will be formed in div tag.
	**/
	function getCommentInformation($ticketid)
	{
		global $log;
		global $table_prefix;
		$log->debug("Entering getCommentInformation(".$ticketid.") method ...");
		global $adb;
		global $mod_strings, $default_charset;
		$sql = "select * from ".$table_prefix."_ticketcomments where ticketid=?";
		$result = $adb->pquery($sql, array($ticketid));
		$noofrows = $adb->num_rows($result);

		//In ajax save we should not add this div
		if($_REQUEST['action'] != 'HelpDeskAjax')
		{
			$list .= '<div id="comments_div" style="overflow: auto;height:200px;width:100%;">';
			$enddiv = '</div>';
		}
		for($i=0;$i<$noofrows;$i++)
		{
			if($adb->query_result($result,$i,'comments') != '')
			{
				//this div is to display the comment
				$comment = $adb->query_result($result,$i,'comments');
				// Asha: Fix for ticket #4478 . Need to escape html tags during ajax save.
				if($_REQUEST['action'] == 'HelpDeskAjax') {
					$comment = htmlentities($comment, ENT_QUOTES, $default_charset);
				}
				$list .= '<div valign="top" style="width:99%;padding-top:10px;" class="dataField">';
				$list .= make_clickable(nl2br($comment));

				$list .= '</div>';

				//this div is to display the author and time
				$list .= '<div valign="top" style="width:99%;border-bottom:1px dotted #CCCCCC;padding-bottom:5px;" class="dataLabel"><font color=darkred>';
				$list .= $mod_strings['LBL_AUTHOR'].' : ';

				if($adb->query_result($result,$i,'ownertype') == 'user')
					$list .= getUserName($adb->query_result($result,$i,'ownerid'));
				elseif($adb->query_result($result,$i,'ownertype') == 'customer') {
					$contactid = $adb->query_result($result,$i,'ownerid');
					$list .= getContactName($contactid);
				}

				$list .= ' on '.$adb->query_result($result,$i,'createdtime').' &nbsp;';

				$list .= '</font></div>';
			}
		}
		
		$list .= $enddiv;

		$log->debug("Exiting getCommentInformation method ...");
		return $list;
	}

	/**     Function to get the Customer Name who has made comment to the ticket from the customer portal
	 *      @param  int    $id   - Ticket id
	 *      @return string $customername - The contact name
	**/
	function getCustomerName($id)
	{
		global $log;
		$log->debug("Entering getCustomerName(".$id.") method ...");
        	global $adb;
        	global $table_prefix;
	        $sql = "select * from ".$table_prefix."_portalinfo inner join ".$table_prefix."_troubletickets on ".$table_prefix."_troubletickets.parent_id = ".$table_prefix."_portalinfo.id where ".$table_prefix."_troubletickets.ticketid=?";
        	$result = $adb->pquery($sql, array($id));
	        $customername = $adb->query_result($result,0,'user_name');
		$log->debug("Exiting getCustomerName method ...");
        	return $customername;
	}
	//Pavani: Function to create, export query for helpdesk module
        /** Function to export the ticket records in CSV Format
        * @param reference variable - where condition is passed when the query is executed
        * Returns Export Tickets Query.
        */
    function create_export_query($where)
        {
                global $log;
                global $current_user;
                global $table_prefix;
                $log->debug("Entering create_export_query(".$where.") method ...");

                include("include/utils/ExportUtils.php");

                //To get the Permitted fields query and the permitted fields list
                $sql = getPermittedFieldsQuery("HelpDesk", "detail_view");
                $fields_list = getFieldsListFromQuery($sql);
				//Ticket changes--5198
				//crmv@15981
				$fields_list = 	str_replace(','.$table_prefix.'_ticketcomments.comments as "Add Comment"',' ',$fields_list);
				//crmv@15981 end

                $query = "SELECT $fields_list,case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name
                       FROM ".$this->entity_table. "
				INNER JOIN ".$table_prefix."_troubletickets
					ON ".$table_prefix."_troubletickets.ticketid =".$table_prefix."_crmentity.crmid
				LEFT JOIN ".$table_prefix."_crmentity ".$table_prefix."_crmentityRelatedTo
					ON ".$table_prefix."_crmentityRelatedTo.crmid = ".$table_prefix."_troubletickets.parent_id
				LEFT JOIN ".$table_prefix."_account
					ON ".$table_prefix."_account.accountid = ".$table_prefix."_troubletickets.parent_id
				LEFT JOIN ".$table_prefix."_contactdetails
					ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_troubletickets.parent_id
				LEFT JOIN ".$table_prefix."_ticketcf
					ON ".$table_prefix."_ticketcf.ticketid=".$table_prefix."_troubletickets.ticketid
				LEFT JOIN ".$table_prefix."_groups
					ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users
					ON ".$table_prefix."_users.id=".$table_prefix."_crmentity.smownerid and ".$table_prefix."_users.status='Active'
				LEFT JOIN ".$table_prefix."_seattachmentsrel
					ON ".$table_prefix."_seattachmentsrel.crmid =".$table_prefix."_troubletickets.ticketid
				LEFT JOIN ".$table_prefix."_attachments
					ON ".$table_prefix."_attachments.attachmentsid=".$table_prefix."_seattachmentsrel.attachmentsid
				LEFT JOIN ".$table_prefix."_products
					ON ".$table_prefix."_products.productid=".$table_prefix."_troubletickets.product_id";
				//end
				$query .= $this->getNonAdminAccessControlQuery('HelpDesk',$current_user);
				$where_auto = " ".$table_prefix."_crmentity.deleted=0";
		
				if($where != '') $query .= " WHERE ($where) AND $where_auto";
				else $query .= " WHERE $where_auto";
		
				$query = $this->listQueryNonAdminChange($query, 'HelpDesk');
				return $query;
                $log->debug("Exiting create_export_query method ...");
                return $query;
        }

	
	/**	Function used to get the Activity History
	 *	@param	int	$id - ticket id to which we want to display the activity history
	 *	@return  array	- return an array which will be returned from the function getHistory
	 */
	function get_history($id)
	{
		global $log;
		global $table_prefix;
		$log->debug("Entering get_history(".$id.") method ...");
		$query = "SELECT ".$table_prefix."_crmentity.crmid, ".$table_prefix."_activity.activityid, ".$table_prefix."_activity.subject, ".$table_prefix."_activity.status, ".$table_prefix."_activity.eventstatus, ".$table_prefix."_activity.date_start, ".$table_prefix."_activity.due_date,".$table_prefix."_activity.time_start,".$table_prefix."_activity.time_end,".$table_prefix."_activity.activitytype, ".$table_prefix."_troubletickets.ticketid, ".$table_prefix."_troubletickets.title, ".$table_prefix."_crmentity.modifiedtime,".$table_prefix."_crmentity.createdtime, ".$table_prefix."_crmentity.description,
case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name
				from ".$table_prefix."_activity
				inner join ".$table_prefix."_activitycf on ".$table_prefix."_activitycf.activityid= ".$table_prefix."_activity.activityid
				inner join ".$table_prefix."_seactivityrel on ".$table_prefix."_seactivityrel.activityid= ".$table_prefix."_activity.activityid
				inner join ".$table_prefix."_troubletickets on ".$table_prefix."_troubletickets.ticketid = ".$table_prefix."_seactivityrel.crmid
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_activity.activityid
                                left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid=".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users on ".$table_prefix."_users.id=".$table_prefix."_crmentity.smownerid
				where (".$table_prefix."_activity.activitytype != 'Emails')
				and (".$table_prefix."_activity.status = 'Completed' or ".$table_prefix."_activity.status = 'Deferred' or (".$table_prefix."_activity.eventstatus = 'Held' and ".$table_prefix."_activity.eventstatus != ''))
				and ".$table_prefix."_seactivityrel.crmid=".$id."
                                and ".$table_prefix."_crmentity.deleted = 0";
//crmv@8398 			
		$query.=getCalendarSql('history');
//crmv@8398e	
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php
		$log->debug("Entering get_history method ...");
		return getHistory('HelpDesk',$query,$id);
	}

	/** Function to get the update ticket history for the specified ticketid
	  * @param $id -- $ticketid:: Type Integer 
	 */	
	function constructUpdateLog($focus, $mode, $assigned_group_name, $assigntype)
	{
		global $adb;
		global $current_user,$mod_strings;
		global $table_prefix;
		if($mode != 'edit')//this will be updated when we create new ticket
		{
			$updatelog = $mod_strings["Ticket created. Assigned to"];

			if(!empty($assigned_group_name) && $assigntype == 'T')
			{
				$updatelog .= " ".$mod_strings['group']." ".(is_array($assigned_group_name)? $assigned_group_name[0] : $assigned_group_name);
			}
			elseif($focus->column_fields['assigned_user_id'] != '')
			{
				$updatelog .= " ".$mod_strings['user']." ".getUserName($focus->column_fields['assigned_user_id']);
			}
			else
			{
				$updatelog .= " ".$mod_strings['user']." ".getUserName($current_user->id);
			}

			$fldvalue = date("l dS F Y h:i:s A")." ".$mod_strings['by']." ".$current_user->user_name;
			$updatelog .= " -- ".$fldvalue."--//--";
		}
		else
		{
			$ticketid = $focus->id;

			//First retrieve the existing information
			$tktresult = $adb->pquery("select * from ".$table_prefix."_troubletickets where ticketid=?", array($ticketid));
			$crmresult = $adb->pquery("select * from ".$table_prefix."_crmentity where crmid=?", array($ticketid));

			$updatelog = decode_html($adb->query_result($tktresult,0,"update_log"));

			$old_owner_id = $adb->query_result($crmresult,0,"smownerid");
			$old_status = $adb->query_result($tktresult,0,"status");
			$old_priority = $adb->query_result($tktresult,0,"priority");
			$old_severity = $adb->query_result($tktresult,0,"severity");
			$old_category = $adb->query_result($tktresult,0,"category");

			//crmv@19664
			//Assigned to change log
			if($focus->column_fields['assigned_user_id'] != $old_owner_id)
			{
				$owner_name = getOwnerName($focus->column_fields['assigned_user_id']);
				if($assigntype == 'T')
					$updatelog .= ' '.$mod_strings['Transferred to group'].' '.$owner_name.'\.';
				else
					$updatelog .= ' '.$mod_strings['Transferred to user'].' '.decode_html($owner_name).'\.'; // Need to decode UTF characters which are migrated from versions < 5.0.4.
			}
			//Status change log
			if($old_status != $focus->column_fields['ticketstatus'] && $focus->column_fields['ticketstatus'] != '')
			{
				$updatelog .= ' '.$mod_strings['Status Changed to'].' '.$focus->column_fields['ticketstatus'].'\.';
			}
			//Priority change log
			if($old_priority != $focus->column_fields['ticketpriorities'] && $focus->column_fields['ticketpriorities'] != '')
			{
				$updatelog .= ' '.$mod_strings['Priority Changed to'].' '.$focus->column_fields['ticketpriorities'].'\.';
			}
			//Severity change log
			if($old_severity != $focus->column_fields['ticketseverities'] && $focus->column_fields['ticketseverities'] != '')
			{
				$updatelog .= ' '.$mod_strings['Severity Changed to'].' '.$focus->column_fields['ticketseverities'].'\.';
			}
			//Category change log
			if($old_category != $focus->column_fields['ticketcategories'] && $focus->column_fields['ticketcategories'] != '')
			{
				$updatelog .= ' '.$mod_strings['Category Changed to'].' '.$focus->column_fields['ticketcategories'].'\.';
			}

			$updatelog .= ' -- '.date("l dS F Y h:i:s A")." ".$mod_strings['by']." ".$current_user->user_name.'--//--';
			//crmv@19664e
		}
		return $updatelog;
	}

/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered 
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	function transferRelatedRecords($module, $transferEntityIds, $entityId) {
		global $adb,$log;
		global $table_prefix;
		$log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");
		
		$rel_table_arr = Array("Activities"=>$table_prefix."_seactivityrel","Attachments"=>$table_prefix."_seattachmentsrel","Documents"=>$table_prefix."_senotesrel");
		
		$tbl_field_arr = Array($table_prefix."_seactivityrel"=>"activityid",$table_prefix."_seattachmentsrel"=>"attachmentsid",$table_prefix."_senotesrel"=>"notesid");	
		
		$entity_tbl_field_arr = Array($table_prefix."_seactivityrel"=>"crmid",$table_prefix."_seattachmentsrel"=>"crmid",$table_prefix."_senotesrel"=>"crmid");	
		
		foreach($transferEntityIds as $transferId) {
			foreach($rel_table_arr as $rel_module=>$rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result =  $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
						" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)",
						array($transferId,$entityId));
				$res_cnt = $adb->num_rows($sel_result);
				if($res_cnt > 0) {
					for($i=0;$i<$res_cnt;$i++) {
						$id_field_value = $adb->query_result($sel_result,$i,$id_field);
						$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?", 
							array($entityId,$transferId,$id_field_value));	
					}

				}				
			}
		}
		//crmv@15526
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		//crmv@15526 end		
		$log->debug("Exiting transferRelatedRecords...");
	}
	
	/*
	 * Function to get the secondary query part of a report 
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule){
		$query = $this->getRelationQuery($module,$secmodule,$table_prefix."_troubletickets","ticketid");
		global $table_prefix;
		$query .=" left join ".$table_prefix."_crmentity ".$table_prefix."_crmentityHelpDesk on ".$table_prefix."_crmentityHelpDesk.crmid=".$table_prefix."_troubletickets.ticketid and ".$table_prefix."_crmentityHelpDesk.deleted=0 
				left join ".$table_prefix."_ticketcf on ".$table_prefix."_ticketcf.ticketid = ".$table_prefix."_troubletickets.ticketid
				left join ".$table_prefix."_crmentity ".$table_prefix."_crmentityRelHelpDesk on ".$table_prefix."_crmentityRelHelpDesk.crmid = ".$table_prefix."_troubletickets.parent_id
				left join ".$table_prefix."_account ".$table_prefix."_accountRelHelpDesk on ".$table_prefix."_accountRelHelpDesk.accountid=".$table_prefix."_crmentityRelHelpDesk.crmid 
				left join ".$table_prefix."_contactdetails ".substr($table_prefix.'_contactdetailsRelHelpDesk',0,29)." on ".substr($table_prefix.'_contactdetailsRelHelpDesk',0,29).".contactid= ".$table_prefix."_crmentityRelHelpDesk.crmid
				left join ".$table_prefix."_products ".$table_prefix."_productsRel on ".$table_prefix."_productsRel.productid = ".$table_prefix."_troubletickets.product_id 
				left join ".$table_prefix."_groups ".$table_prefix."_groupsHelpDesk on ".$table_prefix."_groupsHelpDesk.groupid = ".$table_prefix."_crmentityHelpDesk.smownerid
				left join ".$table_prefix."_users ".$table_prefix."_usersHelpDesk on ".$table_prefix."_usersHelpDesk.id = ".$table_prefix."_crmentityHelpDesk.smownerid"; 

		return $query;
	}

	/*
	 * Function to get the relation tables for related modules 
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		global $table_prefix;
		$rel_tables = array (
			"Calendar" => array($table_prefix."_seactivityrel"=>array("crmid","activityid"),$table_prefix."_troubletickets"=>"ticketid"),
			"Documents" => array($table_prefix."_senotesrel"=>array("crmid","notesid"),$table_prefix."_troubletickets"=>"ticketid"),
			"Products" => array($table_prefix."_troubletickets"=>array("ticketid","product_id")),
			"Services" => array($table_prefix."_crmentityrel"=>array("crmid","relcrmid"),$table_prefix."_troubletickets"=>"ticketid"),
		);
		return $rel_tables[$secmodule];
	}
	
	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		global $table_prefix;
		if(empty($return_module) || empty($return_id)) return;
		
		if($return_module == 'Contacts' || $return_module == 'Accounts') {
			$sql = 'UPDATE '.$table_prefix.'_troubletickets SET parent_id=0 WHERE ticketid=?';
			$this->db->pquery($sql, array($id));
			$se_sql= 'DELETE FROM '.$table_prefix.'_seticketsrel WHERE ticketid=?';
			$this->db->pquery($se_sql, array($id));
		} elseif($return_module == 'Products') {
			$sql = 'UPDATE '.$table_prefix.'_troubletickets SET product_id=0 WHERE ticketid=?';
			$this->db->pquery($sql, array($id));
		} else {
			$sql = 'DELETE FROM '.$table_prefix.'_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}
	function get_timecards($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
		$log->debug("Entering get_timecards(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);		
		$singular_modname = vtlib_toSingular($related_module);
		
		$parenttab = getParentTab();
		
		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;
		
		$button = "<input title='".getTranslatedString('LBL_ViewTC',$related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=HelpDesk&action=chatHDTicket&record=$id&return_module=HelpDesk&return_action=DetailView&return_id=$id&hdticketcomments=yes&parenttab=$parenttab','tcview','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_ViewTC','Timecards')."'>&nbsp;";
		
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module,$related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module,$related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname,$related_module) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname,$related_module) ."'>&nbsp;";
			}
		} 

		$query = "SELECT case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name," .
					" ".$table_prefix."_timecards.*, ".$table_prefix."_troubletickets.ticket_no, ".$table_prefix."_troubletickets.parent_id, ".$table_prefix."_troubletickets.priority," .
					"  ".$table_prefix."_troubletickets.severity, ".$table_prefix."_troubletickets.status, ".$table_prefix."_troubletickets.category, ".$table_prefix."_troubletickets.title," .
					"  ".$table_prefix."_products.*, ".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid, ".$table_prefix."_crmentity.modifiedtime" .
					" from ".$table_prefix."_timecards" .
					" inner join ".$table_prefix."_timecardscf on ".$table_prefix."_timecardscf.timecardsid = ".$table_prefix."_timecards.timecardsid" .
					" inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_timecards.timecardsid" .
					" inner join ".$table_prefix."_troubletickets on ".$table_prefix."_troubletickets.ticketid = ".$table_prefix."_timecards.ticket_id " .
					" left join ".$table_prefix."_products on ".$table_prefix."_products.productid = ".$table_prefix."_timecards.product_id" .
					" left join ".$table_prefix."_users on ".$table_prefix."_users.id=".$table_prefix."_crmentity.smownerid" .
					" left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid=".$table_prefix."_crmentity.smownerid" .
					" where ".$table_prefix."_timecards.ticket_id=$id and ".$table_prefix."_crmentity.deleted=0 ";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_timecards method ...");		
		return $return_value;
	}
	function save_related_module($module, $crmid, $with_module, $with_crmid) {
	    global $adb,$log;
	    global $table_prefix;
		if(!is_array($with_crmid)) $with_crmid = Array($with_crmid);
		if($with_module == 'Timecards') {	
			$with_crmids=implode(',',$with_crmid);
		    $adb->pquery("UPDATE ".$table_prefix."_timecards set ticket_id=? where timecardsid in (?)",Array($crmid, $with_crmids));
			//crmv@29617
			if(!is_array($with_crmid)) $with_crmid = Array($with_crmid);
			foreach($with_crmid as $relcrmid) {
				if ($crmid != $relcrmid) {
					$obj = CRMEntity::getInstance('ModNotifications');
					$obj->saveRelatedModuleNotification($crmid, $module, $relcrmid, $with_module);
				}
			}
			//crmv@29617e
		} else {
		    parent::save_related_module($module, $crmid, $with_module, $with_crmid);
		}
	}
}
?>