<?
require_once('modules/Campaigns/Campaigns.php');
class CampaignsRotho extends Campaigns {
	
	var $list_fields = Array(
					'Campaign Name'=>Array('campaign'=>'campaignname'),
					'Codice corso'=>Array('campaignscf'=>'cf_742'),
					'Titolo'=>Array('campaignscf'=>'cf_743'),
					'Data corso'=>Array('campaignscf'=>'cf_745')
				);

	var $list_fields_name = Array(
					'Campaign Name'=>'campaignname',
					'Codice corso'=>'cf_742',
					'Titolo'=>'cf_743',
					'Data corso'=>'cf_745'					
				     );	  			

	
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'crmid';
	var $default_sort_order = 'DESC';
	
	function getDetailedStatisticRelatedLists() {
		global $adb;
		global $table_prefix;
		$labels = array();
		$result = $adb->query("SELECT label,name FROM ".$table_prefix."_relatedlists WHERE tabid = 26 AND related_tabid = 0 AND name LIKE 'get_statistics_%' ORDER BY sequence");
		while($row=$adb->fetchByAssoc($result)) {
			$labels[$row['name']] = Array('label'=>getTranslatedString($row['label'],'Campaigns'),'count'=>0);
			$method = $row['name'];
			//make the count for every relatedlist
			$return_value = $this->$method($this->id, getTabId('Campaigns'), 0,false,true);
			$method = str_replace("get_statistics_",'',$method);
			$method = str_replace("_",' ',$method);			
			if ($_SESSION[$method."_listquery"] != ''){
				$res = $adb->query($_SESSION[$method."_listquery"]);
				if ($res){
					$labels[$row['name']]['count'] = $adb->num_rows($res);
				}
			}
		}
		return $labels;
	}	
}
?>