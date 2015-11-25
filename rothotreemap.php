<?php 
require_once('include/utils/utils.php');
require_once('config.inc.php');
require_once('include/logging.php');

session_start();

if (isset($_SESSION["authenticated_user_id"])) {
    // echo "ok";
    $mode = "size";
    $accountid = 0;
    if(isset($_REQUEST['mode'])) {
        $mode = $_REQUEST['mode'];
    }
    if(isset($_REQUEST['record'])) {
        $accountid = $_REQUEST['record'];
    }
    $tree_string = getProductCategoryTree($accountid);
} else {
    $_SESSION['lastpage'] = array($_SERVER['QUERY_STRING']);
	require('modules/Users/Authenticate.php');
	die();
}

?>
<!DOCTYPE html>
<meta charset="utf-8">
<style>

body {
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  margin: auto;
  position: relative;
  width: 960px;
}

form {
  position: absolute;
  right: 10px;
  top: 10px;
}

.node {
  border: solid 1px white;
  font: 10px sans-serif;
  line-height: 12px;
  overflow: hidden;
  position: absolute;
  text-indent: 2px;
}

</style>
<?php 
   
    
    $fp = fopen('products.json', 'w+');
    fwrite($fp, $tree_string);
    fclose($fp);
?>
<form>
  <label><input type="radio" name="mode" value="size" checked> Size</label>
  <label><input type="radio" name="mode" value="count"> Count</label>
</form>
<script src="http://d3js.org/d3.v3.min.js"></script>
<script>

var margin = {top: 40, right: 10, bottom: 10, left: 10},
    width = 960 - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom;

var color = d3.scale.category20c();

var treemap = d3.layout.treemap()
    .size([width, height])
    .sticky(true)
    .value(function(d) { return d.size; });

var div = d3.select("body").append("div")
    .style("position", "relative")
    .style("width", (width + margin.left + margin.right) + "px")
    .style("height", (height + margin.top + margin.bottom) + "px")
    .style("left", margin.left + "px")
    .style("top", margin.top + "px");

d3.json("products.json", function(error, root) {
  var node = div.datum(root).selectAll(".node")
      .data(treemap.nodes)
    .enter().append("div")
      .attr("class", "node")
      .attr("title", function(d) { return d.name + ": " + d.description; })
      .call(position)
      .style("background", function(d) { return d.children ? color(d.name) : null; })
      .text(function(d) { return d.children ? null : d.name; });
   
   
   
  d3.selectAll("input").on("change", function change() {
    var value = this.value === "count"
        ? function(d) { return d.cnt; }
        : function(d) { return d.size; };

    node
        .data(treemap.value(value).nodes)
      .transition()
        .duration(1500)
        .call(position);
  });
});

function position() {
  this.style("left", function(d) { return d.x + "px"; })
      .style("top", function(d) { return d.y + "px"; })
      .style("width", function(d) { return Math.max(0, d.dx - 1) + "px"; })
      .style("height", function(d) { return Math.max(0, d.dy - 1) + "px"; });
}

</script>


<?php 
function getProductCategoryTree($accountid)
{
    global $adb;
    $tree_string="";
    $query = "SELECT DISTINCT 
            class3 as categorycode, 
            class1 as parentlevel1, 
            class2 as parentlevel2, 
            class_desc3 as categorydescr, 
            class_desc1,
             class_desc2 ,
            sum(CASE WHEN vtiger_salesorder.salesorderid IS NULL THEN 0 ELSE vtiger_inventoryproductrel.listprice*vtiger_inventoryproductrel.quantity END) as sunburst_size,
            count(CASE WHEN vtiger_salesorder.salesorderid IS NULL THEN 0 ELSE 1 END) as sunburst_count
            FROM erp_temp_crm_classificazioni 
            JOIN vtiger_products ON vtiger_products.product_cat = erp_temp_crm_classificazioni.class3
            JOIN vtiger_crmentity ON vtiger_crmentity.crmid  = vtiger_products.productid AND vtiger_crmentity.deleted = 0
            LEFT JOIN vtiger_inventoryproductrel on vtiger_inventoryproductrel.productid = vtiger_products.productid
            LEFT JOIN vtiger_salesorder on vtiger_salesorder.salesorderid = vtiger_inventoryproductrel.id  
            LEFT JOIN vtiger_crmentity vtiger_crmentity_sales on vtiger_crmentity_sales.crmid  = vtiger_salesorder.salesorderid and vtiger_crmentity_sales.deleted = 0
            ".($accountid>0 ? " WHERE vtiger_salesorder.accountid = {$accountid} " : "")."           
           GROUP BY class3 , class1, class2, class_desc3, class_desc1, class_desc2
            ORDER BY parentlevel1 ASC, parentlevel2 ASC, categorycode ASC ";
    $result = $adb->query($query);
    $i_count = 0;
    $i_count1 = 0;
    $i_count2 = 0;
    $i_count3 = 0;
    $s_level1 = "x96x";
    $s_level2 = "x96x";
    $s_level3 = "x96x";
    $tree_array = array("name"=>"products", "description"=>"SALES ORDERS","children" => array());
    $tree_array_1 = array();
    $tree_array_2 = array();
    while($row=$adb->fetchByAssoc($result))
    {
        $tree_array_3 = array("name"=>$row['categorycode'], "description"=>$row['categorydescr'],"size" =>$row["sunburst_size"], "cnt" =>$row["sunburst_count"] );
        if($row['parentlevel1']!=$s_level1)
        {
            $i_count2=0;
            $s_level1=$row['parentlevel1'];
            $s_desclevel1=$row['class_desc1'];
            if( $i_count1 > 0) array_push($tree_array["children"], $tree_array_1);
            $tree_array_1 = array("name"=>$s_level1, "description"=>$s_desclevel1,"children" => array());
            $i_count1++;
        } 
        if($row['parentlevel2']!=$s_level2)
        {
            $i_count3=0;
            $s_level2=$row['parentlevel2'];
            $s_desclevel2=$row['class_desc2'];
            if($i_count2 > 0) array_push($tree_array_1["children"], $tree_array_2);
            $tree_array_2 = array("name"=>$s_level2, "description"=>$s_desclevel2,"children" => array());
            $i_count2++;
        }        
        array_push($tree_array_2["children"], $tree_array_3);
        $i_count++;
    }
    array_push($tree_array_1["children"], $tree_array_2);
    array_push($tree_array["children"], $tree_array_1);
    return indent(json_encode($tree_array));
}

/**
 * Indents a flat JSON string to make it more human-readable.
 *
 * @param string $json The original JSON string to process.
 *
 * @return string Indented version of the original JSON string.
 */
function indent($json) {

    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;

        // If this character is the end of an element,
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }

        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element,
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }

            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }

        $prevChar = $char;
    }

    return $result;
}
?>