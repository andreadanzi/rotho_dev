<?php
// danzi.tn@20160104 passaggio in produzione albero utenti
require_once('include/database/PearDatabase.php');
require_once('include/utils/CommonUtils.php');
require_once('modules/SDK/SDK.php');	//crmv@sdk
require_once('include/utils/db_utils.php');	//crmv@26666


function replaceSelectQueryByProduct($list_query_count,$parameter)

{
	return "SELECT " . $parameter . " FROM (".$list_query_count.") AS TOTSALES";
}

function get_allids_by_product($list_query_count,$ids_to_jump = false){
	require_once('include/ListView/ListView.php');
	global $adb,$app_strings,$list_max_entries_per_page,$currentModule,$current_user, $table_prefix;	//crmv@27096
	$parameter = $table_prefix."_crmentity.crmid";
	if (!$list_query_count)
		return Zend_Json::encode(Array('all_ids'=>false));
	//crmv@27096
	$mod_obj = CRMEntity::getInstance($currentModule);
	$mod_obj->getNonAdminAccessControlQuery($currentModule,$current_user);
	//crmv@27096e
	$query = str_replace("SELECT","SELECT ".$parameter. ",",$list_query_count );
	$query = str_replace("GROUP BY","GROUP BY ".$parameter. ",",$query );
	if ($ids_to_jump){
		$ids_to_jump = array_filter(explode(",",$ids_to_jump));
		$query.=" and crmid not in (".implode(",",$ids_to_jump).")";
	}
	$res = $adb->query($query);
	//crmv@27096
	$all_ids = array();
	if ($res){
		while($row = $adb->fetchByAssoc($res)){
			$all_ids[] = $row['crmid'];
		}
	}
	saveListViewCheck($currentModule,$all_ids);
	return Zend_Json::encode(Array('all_ids'=>implode(';',$all_ids).';'));
	//crmv@27096e
}

function get_navigation_values_by_product($list_query_count,$url_string,$currentModule,$type='',$forusers=false,$viewid = ''){
	//crmv@17613
	global $adb,$app_strings,$list_max_entries_per_page,$current_user;
	$parameter = 'count(*) as cnt';
	if (!$list_query_count)
		return Zend_Json::encode(Array('nav_array'=>Array(),'rec_string'=>''));
	if (!$forusers){
		$mod_obj = CRMEntity::getInstance($currentModule);
		$mod_obj->getNonAdminAccessControlQuery($currentModule,$current_user);
	}
	//crmv@17613 end
	$new_query = replaceSelectQueryByProduct($list_query_count,$parameter); // DA RIVEDERE
	//echo "<!-- NEWQUERY ".$new_query." -->";
	$res = $adb->query($new_query);
	if ($res){
		$noofrows = $adb->query_result($res,0,'cnt');
	}
	//crmv@29617
	if ($viewid != '') {
		$reload_notification_count = checkListNotificationCount($list_query_count,$current_user->id,$viewid,$noofrows);
	}
	//crmv@29617e
	$_REQUEST['noofrows'] = $noofrows;
	$_SESSION["lvs"][$currentModule][$viewid]["noofrows"] = $noofrows;
	if(isPermitted($currentModule,'EditView','') == 'yes')
		$permitted = true;
	else
		$permitted = false;
	if ($noofrows == 0)
		return Zend_Json::encode(Array('nav_array'=>Array(),'rec_string'=>'','permitted'=>$permitted));
	$list_max_entries_per_page=get_selection_options($noofrows,'list');
	$queryMode = (isset($_REQUEST['query']) && $_REQUEST['query'] == 'true');
	$start = ListViewSession::getRequestCurrentPage($currentModule, $list_query_count, $viewid, $queryMode);
	//crmv@15530
	if ($start > ceil($noofrows/$list_max_entries_per_page)){
		$start-=1;
	}
	//crmv@15530 end
	$navigation_array = VT_getSimpleNavigationValues($start,$list_max_entries_per_page,$noofrows);
	$limit_start_rec = ($start-1) * $list_max_entries_per_page;
	$record_string = getRecordRangeMessage($list_max_entries_per_page, $limit_start_rec,$noofrows);
	if ($noofrows >  $list_max_entries_per_page)
		$navigationOutput = getTableHeaderSimpleNavigation($navigation_array,$url_string,$currentModule,$type,$viewid);
	else
		$navigationOutput = Array();
	return Zend_Json::encode(Array('nav_array'=>$navigationOutput,'rec_string'=>$record_string,'permitted'=>$permitted,'reload_notification_count'=>$reload_notification_count));	//crmv@29617
}

// danzi.tn@20140411 update product category
function getProductCategoryTree()
{
	global $adb, $table_prefix;
	$tree_string="";

	$query = "SELECT DISTINCT class3 as categorycode, class1 as parentlevel1, class2 as parentlevel2, class_desc3 as categorydescr, class_desc1, class_desc2
	FROM erp_temp_crm_classificazioni
	JOIN {$table_prefix}_products ON {$table_prefix}_products.product_cat = erp_temp_crm_classificazioni.class3
	JOIN {$table_prefix}_crmentity ON {$table_prefix}_crmentity.crmid  = {$table_prefix}_products.productid AND {$table_prefix}_crmentity.deleted = 0
	ORDER BY parentlevel1 ASC, parentlevel2 ASC, categorycode ASC ";

	$result = $adb->query($query);
	$i_count = 0;
	$i_count1 = 0;
	$i_count2 = 0;
	$i_count3 = 0;
	$s_level1 = "x96x";
	$s_level2 = "x96x";
	$s_level3 = "x96x";
	while($row=$adb->fetchByAssoc($result))
	{
		if($i_count1==0) $tree_string.="<ul>\n";
		if($row['parentlevel1']!=$s_level1)
		{
			if($i_count1>0) $tree_string.="\t\t\t</ul>\n\t\t</li>\n\t</ul>\n\t</li>\n";
			$i_count2=0;
			$s_level1=$row['parentlevel1'];
			$s_desclevel1=$row['class_desc1'];
			$tree_string.="\t<li title=\"".$s_desclevel1."\"  id=\"".$s_level1."\"><a title=\"".$s_desclevel1."\" href=\"#\">".$s_level1." (".$s_desclevel1.")</a>\n";
			$i_count1++;
		}
		if($i_count2==0) $tree_string.="\t<ul>\n";
		if($row['parentlevel2']!=$s_level2)
		{
			if($i_count2>0) $tree_string.="\t\t\t</ul>\n\t\t</li>\n";
			$i_count3=0;
			$s_level2=$row['parentlevel2'];
			$s_desclevel2=$row['class_desc2'];
			$tree_string.="\t\t<li title=\"".$s_desclevel2."\" id=\"".$s_level2."\"><a title=\"".$s_desclevel2."\"  href=\"#\">".$s_level2." (".$s_desclevel2.")</a>\n";
			$i_count2++;
		}
		if($i_count3==0) $tree_string.="\t\t\t<ul>\n";
		$tree_string.="\t\t\t\t<li title=\"".$row['categorydescr']."\" id=\"".$row['categorycode']."\"><a title=\"".$row['categorydescr']."\" href=\"#\">".$row['categorycode']." (".$row['categorydescr'].")</a></li>\n";
		$i_count3++;
	}
	 $tree_string.="\t\t\t</ul>\n\t\t</li>\n\t</ul>\n\t</li>\n";
	 $tree_string.="</ul>\n	";
	return $tree_string;
}

//crm@7634
// return picklist on user array (for listview)
function getUserTreeOptionsHTML($selected_user_id,$module_name,$parenttab) {

	global $current_user,$app_strings;
	$is_admin = is_admin($current_user);
	$tab_id = getTabid($module_name);
	require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	//crmv@28496
	if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tab_id] == 3)
	{
		$users_array = get_user_array(FALSE, "Active", $current_user->id,'private','Read');
	}
	else
	{
		$users_array = get_user_array(FALSE, "Active", $current_user->id,'','Read');
	}
	//crmv@28496e
	//crmv@18592
	$htmlStr = "<select name='lv_user_id' class='small' id='lv_user_id' onChange='showDefaultTreeCustomView(null,\"$module_name\",\"$parenttab\", \"{$_REQUEST['folderid']}\");'>"; // crmv@30967
	//crmv@18592e
	if($selected_user_id == all)
		$htmlStr .= "<option value='all' selected>".$app_strings['LBL_ASSIGNED_TO_ALL']."</option>'";
	else    $htmlStr .= "<option value='all' >".$app_strings['LBL_ASSIGNED_TO_ALL']."</option>";

	if($selected_user_id == mine)
		$htmlStr .= "<option value='mine' selected>".$app_strings['LBL_ASSIGNED_TO_ME']."</option>";
	else    $htmlStr .= "<option value='mine' >".$app_strings['LBL_ASSIGNED_TO_ME']."</option>";

	if($selected_user_id == others)
		$htmlStr .= "<option value='others' selected>".$app_strings['LBL_ASSIGNED_TO_OTHERS']."</option>";
	else	$htmlStr .= "<option value='others' >".$app_strings['LBL_ASSIGNED_TO_OTHERS']."</option>";

	foreach($users_array as $id=>$username)
	{
		if($id == $selected_user_id)
			$htmlStr .= "<option value='".$id."' selected>".$username."</option>";
		else
			$htmlStr .= "<option value='".$id."' >".$username."</option>";
	}

	$htmlStr .= "</select>";
	return $htmlStr;
}
//crm@7634e

//danzi.tn@20150825 returns ul tree on user array (for listview)
class AgentTree {
    public $item;
    public $children;
    public $qty = 0;
    public $active = false;
}

class Agents {
    protected $db;
    public function __construct($adb) {
        $this->db = $adb;
    }

    public function country_array( $module_name=null, $language=null) {
        global $log, $table_prefix,  $current_language, $currentModule;
        if( $language==null  ) $language = $current_language;
        if( $module_name==null  ) $module_name = $currentModule;
        $items = array();
        $sql = "SELECT
                    c.bill_country as country_code,
                    l.trans_label as country_label
                FROM
                    ".$table_prefix."_bill_country c
                    JOIN sdk_language l on convert(varchar(10) , l.label) = c.bill_country and l.module='".$module_name."' and l.language = '".$language."'";
        $wsresult = $this->db->query($sql);
        while($row = $this->db->fetchByAssoc($wsresult)){
            $item = (object) $row;
            $items[] = $item;
        }
        return $items;
    }

    public function agent_array($parent = null, $module_name=null, $country=null) {
        global $log, $table_prefix;
        $child_val = 0;
        $items = array();

        $sql = "select DISTINCT
                u.id as id,
                u.user_name as agente,
                u.first_name ,
                u.last_name,
                u.erp_code,
                u.agent_cod_capoarea,
                u.status,
                count(ue.crmid) as cnt,
                p.user_name as parent,
                p.first_name as parent_first_name,
                p.last_name as parent_last_name,
                p.status as parent_status,
                p.erp_code as  parent_erp_code
                from
                ".$table_prefix."_users u ";
        if(isset($country) && $country !=null && !empty($country)) {
            $sql = $sql . " left join ".$table_prefix."_crmentity ue on ue.smownerid = u.id and ue.deleted = 0 and ue.setype = 'Accounts'
                            left join ".$table_prefix."_accountbillads acc on acc.accountaddressid = ue.crmid ";
        } else {
             $sql = $sql . " left join ".$table_prefix."_crmentity ue on ue.smownerid = u.id and ue.deleted = 0 and ue.setype = 'Accounts' ";
        }
        $sql = $sql . " left join ".$table_prefix."_users p on p.erp_code = u.agent_cod_capoarea and p.erp_code <>'' and p.erp_code IS NOT NULL ";
        $sql = $sql . " group by
                u.id,
                u.user_name ,
                u.first_name ,
                u.last_name,
                u.erp_code,
                u.agent_cod_capoarea,
                u.status,
                p.user_name ,
                p.first_name,
                p.last_name,
                p.status,
                p.erp_code";
        $sql = $sql . " HAVING  u.erp_code <>'' AND p.erp_code " . (empty($parent)?" IS NULL": " ='".$parent."' ");
        // $sql = "select * from ".$table_prefix."_view_agents_parentchild pc WHERE  pc.erp_code <>'' and pc.parent_erp_code " . (empty($parent)?" IS NULL": " ='".$parent."' ");
        $wsresult = $this->db->query($sql);
        while($row = $this->db->fetchByAssoc($wsresult)){
            $result = new AgentTree();
            $result->item = (object) $row;
            $result->qty = $result->item->cnt;
            $result->active = $result->item->status == 'Active' ? true : false;
            $child_array = $this->agent_array($result->item->erp_code, $module_name);
            foreach($child_array as $child ) {
                $result->qty += $child->qty;
                $result->active = $result->active || $child->active;
            }
            if( $result->qty ==0) continue;
            if( !$result->active ) continue;
            if(sizeof($child_array) == 0 ) {
                array_push($items, $result);
            } else {
                $result->children = $child_array;
                array_push($items, $result);
            }
        }
        return $items;
    }

    public function show_agent_array($array,$selected_users_ids=null){
        $output = '<ul>';
        foreach ($array as  $key => $mixedValue) {
            $output .= '<li active="'.$mixedValue->active.'" title="'.$mixedValue->item->first_name.' ' .$mixedValue->item->last_name.'" id="'.$mixedValue->item->id.'"><a title="'.$mixedValue->item->first_name.' ' .$mixedValue->item->last_name.'" href="#">'.$mixedValue->item->first_name.' ' .$mixedValue->item->last_name. ' (' . $mixedValue->item->agente .' '.$mixedValue->item->cnt . '/'.$mixedValue->qty.' )' . '</a>';
            if (!empty($mixedValue->children)) {
                $output .=  $this->show_agent_array($mixedValue->children) ;
            }
            $output .='</li>';
        }
        $output .= '</ul>';
        return $output;
    }

    public function show_country_array($array,$selected_country=null){
        $output = '<option value="">';
        $output .= '---';
        $output .= '</option>';
        foreach ($array as  $key => $mixedValue) {
            $selected = (!empty($selected_country) && $selected_country == $mixedValue->country_code) ? " selected " : "";
            $output .= '<option value="'.$mixedValue->country_code.'" '.$selected.' >';
            $output .= $mixedValue->country_label. ' (' .$mixedValue->country_code.' )';
            $output .= '</option>';
        }
        return $output;
    }
}

function getUserTreeHTML($selected_users_ids,$module_name,$parenttab,$selected_country=null) {
    global $adb;
    $agents = new Agents($adb);
    // se l'utente è amministratore, si parte con parent = parent_erp_code = null, altrimenti si parte con il proprio erp_code
    $agent_array = $agents->agent_array(null,$module_name,$selected_country);
    return $agents->show_agent_array($agent_array,$selected_users_ids );
}


function getCountryListHTML($selected_country,$module_name,$parenttab) {
    global $adb;
    $agents = new Agents($adb);
    // se l'utente è amministratore, si parte con parent = parent_erp_code = null, altrimenti si parte con il proprio erp_code
    $country_array = $agents->country_array($module_name);
    return $agents->show_country_array($country_array,$selected_country);
}

function getUserTreeAndCountryListHTML($selected_users_ids,$selected_country,$module_name,$parenttab) {
    global $adb;
    $agents = new Agents($adb);
    // se l'utente è amministratore, si parte con parent = parent_erp_code = null, altrimenti si parte con il proprio erp_code
    $agent_array = $agents->agent_array(null,$module_name,$selected_country);
    $country_array = $agents->country_array($module_name);
    return array('users'=>$agents->show_agent_array($agent_array,$selected_users_ids ), "countries"=>$agents->show_country_array($country_array,$selected_country));
}

function getDisplaySelectedUser($selected_users_ids,$module_name,$parenttab) {
     global $adb, $table_prefix;
     $selected_users_ids_display = "";
     if(!empty($selected_users_ids) && $selected_users_ids != '') {
        $user_ids_array = explode(",",$selected_users_ids);
        $sql = "select first_name, last_name from ".$table_prefix."_view_agents_parentchild pc WHERE  pc.id = " . $user_ids_array[0];
        $wsresult = $adb->query($sql);
        while($row = $adb->fetch_array($wsresult)){
            $selected_users_ids_display = $row['first_name']. ' ' . $row['last_name'];
        }
     }
     return $selected_users_ids_display;
}
//danzi.tn@20150825e
?>
