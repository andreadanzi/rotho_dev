<?php
global $theme, $currentModule;

require_once('include/ListView/ListView.php');
require_once('modules/CustomView/CustomView.php');
require_once('include/DatabaseUtil.php');

$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
include_once('modules/Map/lib/utils.inc.php');
require('modules/Map/lib/GeoCoder.inc.php');

$lang = substr($_SESSION['authenticated_user_language'],0,2);

if(!$_REQUEST['show'])
	$_REQUEST['show'] = "Accounts";

if(!$_REQUEST['cluster'])
	$_REQUEST['cluster'] = "Enable";

if(!$_REQUEST['type_or_value'])
	$_REQUEST['type_or_value'] = "type";


?>

<link rel="stylesheet" href="modules/Map/js/closure-library/closure/goog/css/common.css">
<link rel="stylesheet" href="modules/Map/js/closure-library/closure/goog/css/dialog.css">

<script type="text/javascript"
        src="http://maps.googleapis.com/maps/api/js?sensor=false&language=<?php echo $lang?>"></script>

<script src="modules/Map/js/markerclusterer_packed.js" type="text/javascript"></script>   
<script src="modules/Map/js/gm.js" type="text/javascript"></script> 
<!-- <script src="modules/Map/js/gm_packed.js" type="text/javascript"></script>  -->

<script src="modules/Map/js/closure-library/closure/goog/base.js"></script>

<!--20121219 danzi.tn INIZIO -->
<script type="text/javascript" src="include/js/jquery.js"></script>
<script type="text/javascript" src="include/js/jquery.cookie.js"></script>
<script type="text/javascript" src="include/js/jquery.hotkeys.js"></script>
<script type="text/javascript" src="include/js/jquery.jstree.js"></script>

<script src="include/js/jquery_plugins/ui/minified/jquery.ui.core.min.js"></script>
<script src="include/js/jquery_plugins/ui/minified/jquery.ui.position.min.js"></script>
<script src="include/js/jquery_plugins/ui/minified/jquery.ui.widget.min.js"></script>
<script src="include/js/jquery_plugins/ui/minified/jquery.ui.mouse.min.js"></script>
<script src="include/js/jquery_plugins/ui/minified/jquery.ui.button.min.js"></script>
<script src="include/js/jquery_plugins/ui/minified/jquery.ui.draggable.min.js"></script>
<script src="include/js/jquery_plugins/ui/minified/jquery.ui.resizable.min.js"></script>
<script src="include/js/jquery_plugins/ui/minified/jquery.ui.dialog.min.js"></script>
<!-- 20121219 danzi.tn FINE -->


<style>
    .goog-slider-horizontal {
      border: solid 1px #CCCCCC;
      background-color: white;
      position: relative;
      overflow: hidden;
    }

    .goog-slider-thumb {
      position: absolute;
      background-color: #3399CC;
      overflow: hidden;
      width: 10px;
      height: 100%;
      top: 0;
    }
    
</style>


<script type="text/javascript">

function updateFilterCombo(elem)
{
	var module = encodeURIComponent(elem.options[elem.options.selectedIndex].value);
    $("#filterContainer").innerHTML = combos[module];
	
	domElementND = document.getElementById('valueSelND');
	domElementPROD = document.getElementById('valueSelPROD');
	domElement = document.getElementById('valueSel');
	google.maps.event.addDomListener(domElementND, 'click',  function() { updateValueFilterContainer(this);} );
	google.maps.event.addDomListener(domElementPROD, 'click',  function() { updateValueFilterContainer(this);} );
	google.maps.event.addDomListener(domElement, 'click',  function() { updateValueFilterContainer(this);} );
	if(module=='Accounts')
	{
		$("#valueSelContainer").show();
	}
	else
	{
		domElement.checked = false;
		$("#valueSelContainer").hide();
	}
	google.maps.event.trigger(domElement, 'click');
}

function updateValueFilterContainer(elem)
{
	var chckd = elem.checked;
	var chckId = elem.id;
	if(chckd==true && chckId=='valueSel' )
	{
		$('#cat_prodotti').show('slow');
	}	
	else 
	{
		document.getElementById("valueId").value = 'ND';
		$('#cat_prodotti').hide('slow');
	}
	var moduleElem=document.getElementById("showSel");
	var module = encodeURIComponent(moduleElem.options[moduleElem.options.selectedIndex].value);
	var selFilter = encodeURIComponent(elem.options[elem.options.selectedIndex].value);
}

var combos = new Array();

<?php

global $dbconfig; 
global $adb;
global $app_strings;
define("DB_HOST",$dbconfig['db_server']);
define("DB_USER",$dbconfig['db_username']);
define("DB_PASS",$dbconfig['db_password']);
define("DB_NAME",$dbconfig['db_name']);
define("DB_PORT",$dbconfig['db_port']);

$modules = array("Accounts","Potentials","HelpDesk","Leads","SalesOrder"); // Andrea Danzi aggiunto SalesOrder - 26.03.2012
foreach($modules as $module)
{
        $cv = new CustomView($module);
 	$vid = $cv->getViewId($module);
        $html = $cv->getCustomViewCombo($vid);
	echo "combos['$module'] = '<select class=\"small\" name=\"viewid\">$html</select>';\n";
}


?>

</script>   
<?php

$oCustomView = new CustomView($_REQUEST['show']);
//identify current view
if ($_REQUEST['viewid'])
{
       	$viewid = $_REQUEST['viewid'];
}
else //go to default module view
{
       $viewid = $oCustomView->getViewId($_REQUEST['show']);
}


global $current_user;

//crmv@7634
if(isset($_REQUEST['lv_user_id'])) {
	$_SESSION['lv_user_id'] = $_REQUEST['lv_user_id'];
} else {
	$_REQUEST['lv_user_id'] = $_SESSION['lv_user_id'];
}

// $smarty = new vtigerCRM_Smarty();
$select_assigneduser = getUserOptionsHTML($_REQUEST['lv_user_id'],$currentModule,"");

if( $_REQUEST['lv_user_id'] == "all" || $_REQUEST['lv_user_id'] == "") { // all event (normal rule)
	
} else if ( $_REQUEST['lv_user_id'] == "mine") { // only assigned to me
	$list_where .= " and vtiger_crmentity.smownerid = ".$current_user->id." ";
} else if ( $_REQUEST['lv_user_id'] == "others") { // only assigneto others
	$list_where .= " and vtiger_crmentity.smownerid <> ".$current_user->id." ";
} else { // a selected userid 
	$list_where .= " and vtiger_crmentity.smownerid = ".$_REQUEST['lv_user_id']." ";
}
$where.=$list_where; 
//crmv@7634e
if(isset($where) && $where != '') {
	$_SESSION['export_where'] = $where;
} else {
	unset($_SESSION['export_where']);
}

global $adb;
$result = $adb->query("select  organizationname as name, country, city, code, address, state from vtiger_organizationdetails");
$row = $adb->fetchByAssoc($result);
$gc = new GeoCoder();
$from = $gc->getGeoCode(-1,$row['state'],$row['city'],$row['code'],$row['address'],$row['country']);

echo '<script type="text/javascript">';
        echo "var from = '".$from->latitude.",".$from->longitude."';\n";
        echo "var basePos = new google.maps.LatLng(".$from->latitude.",".$from->longitude.");\n";
        echo "var baseAddress = '".addslashes($row['address'])."';\n";
        echo "var baseName = '".addslashes($row['name'])."';\n";
        echo "var baseCity = '".addslashes($row['city'])."';\n";
        echo "var baseCode = '".addslashes($row['code'])."';\n";
        echo "var baseState = '".addslashes($row['state'])."';\n";
        echo "var baseCountry = '".addslashes($row['country'])."';\n";
echo '</script>';

if ($_REQUEST['ids']) //priority to request paramater
	$retValues = getResults($_REQUEST['show'],$_REQUEST['ids']);
else //calculate ids using filters
{
	global $currentModule;
	$extra_ids = null;
	$prod_id = null;
	$map_mindate = null;
	$map_maxdate = null;

	if($_REQUEST['valueId'] && $_REQUEST['valueSel']=='cat_prodotti' && $_REQUEST['valueId']!="ND") $extra_ids =  $_REQUEST['valueId'];
	if($_REQUEST['valueId'] && $_REQUEST['valueSel']=='prodotto' && $_REQUEST['valueId']!="ND") $prod_id =  $_REQUEST['valueId'];
	if($_REQUEST['map_mindate'] && $_REQUEST['map_mindate']!="") $map_mindate =  $_REQUEST['map_mindate'];
	if($_REQUEST['map_maxdate'] && $_REQUEST['map_maxdate']!="") $map_maxdate =  $_REQUEST['map_maxdate'];
	if($viewid)
	{
      		$listquery = getListQuery($_REQUEST['show']);
       	 	$query = $oCustomView->getModifiedCvListQuery($viewid,$listquery,$_REQUEST['show']);
	}else{
		$query = getListQuery($_REQUEST['show']);
	} 
	$query .= $list_where;


        $queryGenerator = new QueryGenerator($_REQUEST['show'], $current_user);


	if ($viewid != "0") {
        	$queryGenerator->initForCustomViewById($viewid);
	} else {
        	$queryGenerator->initForDefaultCustomView();
	}	

	$list_query_2 = $queryGenerator->getQuery();

	$where_2 = $queryGenerator->getConditionalWhere();

	$list_query_2 .= $list_where;
        $res_2 = $adb->query($list_query_2);
        echo "<!-- QUERY_2 IDS  ". $list_query_2 ." -->\n";
        // echo "<!-- WHERE_2 IDS  ". $where ." -->";

        // echo "<!-- QUERY IDS  ". $query ." -->";
	// $list_result = $adb->pquery($query, array());

	while($row = $adb->fetch_array($res_2))
		$ids[] = $row["accountid"];

	
	
	if(count($ids)) 
	{
		$retValues = getResults($_REQUEST['show'],implode(",",$ids),($extra_ids==null?null:$extra_ids),($prod_id==null?null:$prod_id),($map_mindate==null?null:$map_mindate),($map_maxdate==null?null:$map_maxdate)); //retrive map results
		$skippedAccs = getSkippedAccounts(implode(",",$ids));
	}
	else
		$retValues = array();
}

?>
<!--18052012 Andrea INIZIO-->
<table><tbody><tr><td>
<!--18052012 Andrea FINE-->
<div class='moduleName' style='padding: 10px'> <?php echo $app_strings['Tools']; ?> > <?php echo $mod_strings['Maps']; ?> > <a href='index.php?parenttab=Tools&action=index&module=Map&show=<?php echo $_REQUEST['show']; ?>'><?php echo $app_strings[$_REQUEST['show']];?></a></div>
<div style='padding: 5px; margin: 10px; margin-top: 0px; padding-top: 0px;'>



<?php
$customviewcombo_html = $oCustomView->getCustomViewCombo($viewid);
$viewnamedesc = $oCustomView->getCustomViewByCvid($viewid);
$notFound = $retValues["not_found"];
$html_not_found="";
if(False && count($notFound))
{
 	$html_not_found = "\t<ul>\n"; 	
	foreach( $notFound as $key=>$nfitem)
	{	
		$html_not_found .= "\t\t<li>".$nfitem["name"].", ".(empty($nfitem["city"]) ? "<span class='empty'>EMPTY CITY</span>" : $nfitem["city"]) ." ".(empty($nfitem["extra"])? "<span class='empty'>EMPTY ADDRESS</span>":$nfitem["extra"])."</li>\n";
	}
	$html_not_found .= "\t</ul>\n";
}

echo "
<div style='width:auto;display:block;'>
<table width='100%' cellspacing='0' cellpadding='0' border='0' class='small'>
<tbody>
<tr>
<td>
<form name='map-form' action='index.php' method='GET'> 
<table width='900px' class='small'>
<tbody>
<tr>
<td class='dvInnerHeader' colspan='5'>
	<div style='float:left;font-weight:bold;'>
		<b>&nbsp;". $mod_strings['Show']. " " .$app_strings[$_REQUEST['show']] ." (".count($retValues["results"])." " .$mod_strings['Results'] .")</b>
	</div>
        <!-- QUERY Map ".$retValues["query"] ." -->
</td>
</tr>
<tr style='height:25px'>
<td class='dvtCellLabel' align='right'>
<span style='font-weight: bold ; font-size: 110%; margin-left: 10px'>{$app_strings['LBL_MODULE']}:</span>
</td>
<td class='dvtCellInfo'>
<select class='small' onchange='updateFilterCombo(this)' id='showSel' name='show'>
<option value='Accounts' ".($_REQUEST['show']=='Accounts'?'selected="selected"':'').">{$app_strings['Accounts']}</option>
<!-- 22052012 <option value='HelpDesk' ".($_REQUEST['show']=='HelpDesk'?'selected="selected"':'').">{$app_strings['HelpDesk']}</option> -->
<!-- 22052012 <option value='Potentials' ".($_REQUEST['show']=='Potentials'?'selected="selected"':'').">{$app_strings['Potentials']}</option> -->
<!-- 22052012 <option value='Leads' ".($_REQUEST['show']=='Leads'?'selected="selected"':'').">{$app_strings['Leads']}</option> -->
<!-- 22052012 <option value='SalesOrder' ".($_REQUEST['show']=='SalesOrder'?'selected="selected"':'').">{$app_strings['SalesOrder']}</option> -->
</select>
</td>
<td class='dvtCellLabel' align='right'>
<span style='font-weight: bold ; font-size: 110%'>{$app_strings['LBL_VIEW']}</span>
</td>
<td class='dvtCellInfo'>
<span id='filterContainer' style='float:left;'><select class='small' id='viewid' name='viewid' style='width:60px;'>";
echo $customviewcombo_html;
echo "</select></span><span id='ownerFlt' style='float:left;'> ";
echo $select_assigneduser ."</span>

</td>
<td class='dvtCellInfo' rowspan=2>
<input class='crmbutton small create' type='submit' value='{$mod_strings['Show']}'/>
</td>
</tr>
<tr style='height:25px'>

<td class='dvtCellLabel' align='right'>
<span style='font-weight: bold ; font-size: 110%; margin-left: 10px'>Valore per:</span>
</td>
<td class='dvtCellInfo'>
<span id='valueSelContainer'>

<input class='small' type='radio' class='small' id='valueSelND'  name='valueSel' value='ND' ".($_REQUEST['valueSel']=='ND'?'checked="checked"':'')." />Nessuno 
<input class='small' type='radio' class='small' id='valueSel'  name='valueSel' value='cat_prodotti' ".($_REQUEST['valueSel']=='cat_prodotti'?'checked="checked"':'')." /> Categorie
<input class='small' type='radio' class='small' id='valueSelPROD'  name='valueSel' value='prodotto' ".($_REQUEST['valueSel']=='prodotto'?'checked="checked"':'')." /> Prodotto
</span>

</td>
<td class='dvtCellLabel' align='right'>
<span style='font-weight: bold ; font-size: 110%; margin-left: 10px'>Filtri Valore:</span> 
</td>
<td class='dvtCellInfo'>
<span id='valueFilterContainer'>
<input  class='small'  type='text' name='valueId' id='valueId' value='".($_REQUEST['valueId']?$_REQUEST['valueId']:'ND')."' maxlength='50' size='12'  />
</span>

</td>
</tr> 

<!-- ricerca per data -->
<tr style='height:25px'>

<td class='dvtCellLabel' align='right'>
<span style='font-weight: bold ; font-size: 110%; margin-left: 10px'>Filtra dalla data:</span>
</td>
<td class='dvtCellInfo'>

<table cellspacing='0' cellpadding='0' border='0' style='float:left;'>
		<tbody><tr>
		<td>
		<input type='text' value='".($_REQUEST['map_mindate']?$_REQUEST['map_mindate']:'')."' maxlength='10' size='11' style='border:1px solid #bababa;' id='jscal_field_map_mindate' tabindex='' name='map_mindate'>
		</td>
				<td style='padding-right:2px;'>
		<img id='jscal_trigger_map_mindate' src='themes/rothosofted/images/btnL3Calendar.gif'>
		</td>
				<td>
										
		</td>
		</tr>
												
		<tr>
		<td colspan='2'>
					<font size='1'><em old='(yyyy-mm-dd)'>(yyyy-mm-dd)</em></font>
				
		</td>
		</tr>
		</tbody>
	</table>
		<script id='massedit_calendar_map_mindate' type='text/javascript'>
			Calendar.setup ({
				inputField : \"jscal_field_map_mindate\", ifFormat : \"%Y-%m-%d\", showsTime : false, button : \"jscal_trigger_map_mindate\", singleClick : true, step : 1
			})
		</script>

<input type=\"image\" align=\"absmiddle\" style=\"cursor:hand;cursor:pointer;float:left;\" onclick=\"this.form.map_mindate.value=''; this.form.map_mindate.value='';return false;\" language=\"javascript\" title=\"Pulisci\" alt=\"Pulisci\" src=\"themes/rothosofted/images/clear_field.gif\" tabindex=\"\">

</td>
<td class='dvtCellLabel' align='right'>

<span style='font-weight: bold ; font-size: 110%; margin-left: 10px'>Alla data:</span> 
</td>
<td class='dvtCellInfo' align='left'>

<table cellspacing='0' cellpadding='0' border='0' style='float: left;' >
		<tbody><tr>
		<td>
		<input type='text' value='".($_REQUEST['map_maxdate']?$_REQUEST['map_maxdate']:'')."' maxlength='10' size='11' style='border:1px solid #bababa;' id='jscal_field_map_maxdate' tabindex='' name='map_maxdate'>
		</td>
				<td style='padding-right:2px;'>
		<img id='jscal_trigger_map_maxdate' src='themes/rothosofted/images/btnL3Calendar.gif'>
		</td>
				<td>
										
		</td>
		</tr>
												
		<tr>
		<td colspan='2'>
					<font size='1'><em old='(yyyy-mm-dd)'>(yyyy-mm-dd)</em></font>
				
		</td>
		</tr>
		</tbody>
	</table>
		<script id='massedit_calendar_map_maxdate' type='text/javascript'>
			Calendar.setup ({
				inputField : \"jscal_field_map_maxdate\", ifFormat : \"%Y-%m-%d\", showsTime : false, button : \"jscal_trigger_map_maxdate\", singleClick : true, step : 1
			})
		</script>

<input type=\"image\" align=\"absmiddle\" style=\"cursor:hand;cursor:pointer;float:left;\" onclick=\"this.form.map_maxdate.value=''; this.form.map_maxdate.value='';return false;\" language=\"javascript\" title=\"Pulisci\" alt=\"Pulisci\" src=\"themes/rothosofted/images/clear_field.gif\" tabindex=\"\">

</td>
</tr> 

<tr style='height:25px'>

<td class='dvtCellLabel' align='right'>
<span style='font-weight: bold ; font-size: 110%; margin-left: 10px'>{$mod_strings['Lookup']}:</span>
</td>
<td class='dvtCellInfo'>
<input id='type1' type='radio' name='type_or_value' value='type' ".($_REQUEST['type_or_value']=='type'?'checked="true"':'')."/> {$mod_strings['Show_type']} <input id='type2' type='radio' name='type_or_value' value='value_and_type' ".($_REQUEST['type_or_value']=='value_and_type'?'checked="true"':'')." /> {$mod_strings['Show_value']} <input id='type3' type='radio' name='type_or_value' value='value' ".($_REQUEST['type_or_value']=='value'?'checked="true"':'')." /> solo Valore
</td>
<td class='dvtCellLabel' align='right'>
<span style='font-weight: bold ; font-size: 110%; margin-left: 10px'>{$mod_strings['Clustering']}:</span> 
</td>
<td class='dvtCellInfo'>
<input id='clust1' type='radio' value='Enable' name='cluster' ".($_REQUEST['cluster']=='Enable'?'checked="true"':'')."/>{$mod_strings['Enable']}
<input id='clust2' type='radio' value='Disable' name='cluster' ".($_REQUEST['cluster']=='Disable'?'checked="true"':'')."/>{$mod_strings['Disable']}
</td>
<td class='dvtCellInfo'>

</td>
</tr>

<tr style='height:25px'>

<td class='dvtCellLabel' align='right'>
<span style='font-weight: bold ; font-size: 110%; margin-left: 10px'>Filtra valori:</span>
</td>
<td class='dvtCellInfo'>

<div tabindex='0' aria-valuenow='0' aria-valuemax='100' aria-valuemin='0' role='slider' id='slider' class='goog-slider goog-slider-horizontal' style='width: 255px; height: 11px; float: left'>
<div style='left: 190px;' class='goog-slider-thumb'></div>
</div>

</td>
<td class='dvtCellLabel' align='right'>
<span style='font-weight: bold ; font-size: 110%; margin-left: 10px'>superiori a:</span> 
</td>
<td class='dvtCellInfo'>

<div id='slider-value' style='float: left; margin-left: 5px;'><span style='font-weight: bold; color:#D6FF2F;'>0 k</span></div>

</td>
<td class='dvtCellInfo'>
</td>
</tr>
</tbody>
</table>
<input type='hidden' name='module' value='Map'/>
<input type='hidden' name='action' value='index'/>
<input type='hidden' name='parenttab' value='Tools'/>
</form>
</td>
</tr>
</tbody>
</table></div>"
?>
</div>

<input type='hidden' name='accfrom' id='accfrom' value='ND'/>
<input type='hidden' name='accto' id='accto' value='ND'/>
<input type='hidden' name='gmfrom' id='gmfrom' value='xxx'/>
<input type='hidden' name='gmto' id='gmto' value='yyy'/>
	

<div style='float: left;  padding-bottom:70px;'>
	<div id="map_canvas" style="margin-left: 10px; margin-right: 10px; width: 900px; height: 500px;  border: 1px solid black;  padding-bottom:40px; float: left"></div>
	<div>
	<input type="button" class="crmbutton small delete" style='margin-left: 10px' value="<?php echo $mod_strings['Clear directions'] ?>" onClick="restore();"/><br/>
	
	<div id="desc" name="desc" style="padding-top: 20px; float: left"></div><br/><br/><br/>
	<div id="route"> </div>
	</div>
</div>
<!--18052012 Andrea INIZIO-->
</td><td valign="top"><div id="cat_prodotti"><b>Filtri per categoria prodotto</b>
<!--18052012 Andrea FINE -->
<!--18052012 Andrea INIZIO-->
<?php
echo "<div id='categorytree'>"; 
echo getProductCategoryTree();
echo "</div>";
?>
</div></td></tr></tbody></table> 
<?php echo $html_not_found; ?>
<!--18052012 Andrea FINE -->
<script type="text/javascript">
<?php echo "var module='".$_REQUEST['show']."';\n"; ?>

goog.require('goog.ui.Dialog');
goog.require('goog.ui.Slider');
goog.require('goog.ui.Component');
var map, sliderTimer, fusiontables_layer, slider;
var fusion_value = 0;
var entityCircle;
<?php echo "var clusterRequest='".$_REQUEST['cluster']."';\n"; ?>
<?php echo "var type_or_valueRequest='".$_REQUEST['type_or_value']."';\n"; ?>
<?php echo "var valueSelRequest='".$_REQUEST['valueSel']."';\n"; ?>
var markerCluster;
var local_markersArray = [];
var local_circleArray = [];
var directionDisplay;
var directionsService = new google.maps.DirectionsService();
var head_lbl = '<?php echo $mod_strings['Head office'] ?>' ;
var from_lbl = '<?php echo $mod_strings['From'] ?>' ; 
var to_lbl = '<?php echo $mod_strings['To'] ?>';
var direction_lbl = '<?php echo $mod_strings['Direction'] ?>';
var reload_lbl = '<?php echo $mod_strings['Reload'] ?>';
var baseDesc = '<span style="font-weight: bold; font-size: 110%">'+baseName+'</span><br/><br/>'+baseAddress+'<br/>'+baseCode+'<br/>'+baseCity+' (' + baseState + ')<br/>'+baseCountry+'<br/><span style="float: right"><a href="index.php?module=Map&file=update&action=MapAjax&id=-1&show='+module+'">'+reload_lbl+'</a></span>';
<?php
if( count($retValues["results"])==0 )
{
  if($from->latitude || $from->longitude)
  {
      echo "var home_center = new google.maps.LatLng(".$from->latitude.",".$from->longitude.");\n";
  }
}
else
{
    $results = $retValues["results"];
    foreach($results as $key=>$retPoint)
    { 
      echo "var home_center = new google.maps.LatLng(".$retPoint["lat"].",".$retPoint["lng"].");\n";
      break;
    }
}
?>


<?php
	printResultLayer($retValues["results"],$retValues["not_found"]);
?>

google.maps.event.addDomListener(window, 'load', initialize);

</script>
<!--18052012 Andrea INIZIO -->
<script type="text/javascript" class="source below"> 
$(function () {
	$("#categorytree")
		.jstree({ "plugins" : ["themes","html_data","ui"] })
		// 1) if using the UI plugin bind to select_node
		.bind("select_node.jstree", function (event, data) { 
			// `data.rslt.obj` is the jquery extended node that was clicked
			document.getElementById("valueId").value = data.rslt.obj.attr("id");
			// alert(data.rslt.obj.attr("id"));
		})
		// 2) if not using the UI plugin - the Anchor tags work as expected
		//    so if the anchor has a HREF attirbute - the page will be changed
		//    you can actually prevent the default, etc (normal jquery usage)
		.delegate("a", "click", function (event, data) { event.preventDefault(); });

	if(valueSelRequest && valueSelRequest=='cat_prodotti') $('#cat_prodotti').show()
	else $('#cat_prodotti').hide();


	var indirizzo = $( "#indirizzo" ),
            citta = $( "#citta" ),
            provincia = $( "#provincia" ),
            cap = $( "#cap" ),
            stato = $( "#stato" ),
            do_geocode = $( "#do_geocode" ),
            latlon_geocode = $( "#latlon_geocode" ),
            entity_id = $( "#entity_id" ),
            allFields = $( [] ).add( indirizzo ).add( citta ).add( provincia ).add( cap ).add( stato ).add( do_geocode ),
            tips = $( ".validateTips" );
 
        function updateTips( t ) {
            tips.text( t ).addClass( "ui-state-highlight" );
            setTimeout(function() {
                tips.removeClass( "ui-state-highlight", 1500 );
            }, 500 );
	}


	function codeAddress() {
		var address = indirizzo.val()+ ' ' + cap.val() + ' ' + citta.val() + ' ' + provincia.val() + ' ' + stato.val();
		geocoder.geocode( { 'address': address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				ll = results[0].geometry.location;
				coords.lat = ll.lat();
				coords.lng = ll.lng();
				latlon_geocode.val(ll.toString());
				$( "#dialog-map" ).dialog( "open" );
				showmap();
			} else {
				alert('Geocode was not successful for the following reason: ' + status);
			}
		});
	}
 
        function checkLength( o, n, min, max ) {
            if ( o.val().length > max || o.val().length < min ) {
                o.addClass( "ui-state-error" );
                updateTips( "Length of " + n + " must be between " +
                    min + " and " + max + "." );
                return false;
            } else {
                return true;
            }
        }
 
        $( "#dialog-map" ).dialog({
            autoOpen: false,
            height: 400,
            width: 600,
            modal: true,
            resizeStop: function(event, ui) {google.maps.event.trigger(gmap, 'resize')  },
            buttons: {
                "Accept the address": function() {
			var ok_address = indirizzo.val()+ ' ' + cap.val() + ' ' + citta.val() + ' ' + provincia.val() + ' ' + stato.val();
			var ok_id = entity_id.val();
			alert("For id=" + ok_id + " new address is " + ok_address);
                   	$( this ).dialog( "close" );
			
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            },
            close: function() {
                
            },
	    open: function(event, ui) {google.maps.event.trigger(gmap, 'resize'); }  
        });

        $( "#dialog-form" ).dialog({
            autoOpen: false,
            height: 400,
            width: 350,
            modal: true,
            buttons: {
                "Update the address": function() {
                    var bValid = true;
                    allFields.removeClass( "ui-state-error" );
 
                    bValid = bValid && checkLength( citta, "citta", 3, 64 );
                    bValid = bValid && checkLength( stato, "stato", 2, 16 );
 			
                    bGeocoded = false;
                    if ( bValid ) {
			if(  do_geocode.attr('checked') == true )
			{
				initializeGmap();
				codeAddress();
			}
                        $( this ).dialog( "close" );
                    }
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            },
            close: function() {
                allFields.removeClass( "ui-state-error" );
            }
        });
 });


</script>
<!--18052012 Andrea FINE -->


<!--2012.12.19 danzi.tn INIZIO -->
<div id="dialog-form" title="Aggiornare Indirizzo">
    <form>
    <fieldset>
        <label for="indirizzo">Indirizzo</label>
        <input type="text" name="indirizzo" id="indirizzo" class="text ui-widget-content ui-corner-all" />
		<br/>
        <label for="citta">Citt&agrave;</label>
        <input type="text" name="citta" id="citta" class="text ui-widget-content ui-corner-all" />
		<br/>
        <label for="provincia">Provincia</label>
        <input type="text" name="provincia" id="provincia" class="text ui-widget-content ui-corner-all" />
		<br/>
        <label for="cap">CAP</label>
        <input type="text" name="cap" id="cap" class="text ui-widget-content ui-corner-all" />
		<br/>
        <label for="stato">Stato</label>
        <input type="text" name="stato" id="stato" class="text ui-widget-content ui-corner-all" />
		<br/>
        <label for="do_geocode">Geocode</label>
		<input type="checkbox" name="do_geocode" id="do_geocode" value="" />
	<input type="hidden" name="latlon_geocode" id="latlon_geocode" value="" />
	<input type="hidden" name="entity_id" id="entity_id" value="" />
    </fieldset>
    </form>
</div>

<div id="dialog-map" title="Indirizzo su mappa">
	<div id="map-address"></div>
	<div id="gmap_canvas"  style="width:100%;height:100%;"></div>
</div>
<!--2012.12.19 danzi.tn FINE -->
