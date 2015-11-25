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
<!-- saved from url=(0061)http://mbostock.github.io/d3/talk/20111116/bar-hierarchy.html -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <link type="text/css" rel="stylesheet" href="./bar-hierarchy_files/style.css">
    <style type="text/css">

svg {
  font-size: 14px;
}

rect.background {
  fill: none;
  pointer-events: all;
}

.axis {
  shape-rendering: crispEdges;
}

.axis path, .axis line {
  fill: none;
  stroke: #000;
  stroke-width: .5px;
}

    </style>
  </head>
<?php 
    $fp = fopen('products.json', 'w+');
    fwrite($fp, $tree_string);
    fclose($fp);
?>

<form>

</form>
<body>
    <script type="text/javascript" src="./bar-hierarchy_files/d3.js"></script>
    <script src="http://d3js.org/d3.v3.min.js"></script>
    <script src="http://d3js.org/d3.v3.layout.min.js"></script>
    
<script type="text/javascript">

var m = [80, 160, 0, 160], // top right bottom left
    w = 1280 - m[1] - m[3], // width
    h = 800 - m[0] - m[2], // height
    x = d3.scale.linear().range([0, w]),
    y = 25, // bar height
    z = d3.scale.ordinal().range(["steelblue", "#aaa"]); // bar color

var hierarchy = d3.layout.partition()
    .value(function(d) { return d.cnt; });

var xAxis = d3.svg.axis()
    .scale(x)
    .orient("top");

var svg = d3.select("body").append("svg:svg")
    .attr("width", w + m[1] + m[3])
    .attr("height", h + m[0] + m[2])
  .append("svg:g")
    .attr("transform", "translate(" + m[3] + "," + m[0] + ")");

svg.append("svg:rect")
    .attr("class", "background")
    .attr("width", w)
    .attr("height", h)
    .on("click", up);

svg.append("svg:g")
    .attr("class", "x axis");

svg.append("svg:g")
    .attr("class", "y axis")
  .append("svg:line")
    .attr("y1", "100%");

d3.json("products.json", function(root) {
  hierarchy.nodes(root);
  x.domain([0, root.value]).nice();
  down(root, 0);
});

function down(d, i) {
  if (!d.children || this.__transition__) return;
  var duration = d3.event && d3.event.altKey ? 7500 : 750,
      delay = duration / d.children.length;

  // Mark any currently-displayed bars as exiting.
  var exit = svg.selectAll(".enter").attr("class", "exit");

  // Entering nodes immediately obscure the clicked-on bar, so hide it.
  exit.selectAll("rect").filter(function(p) { return p === d; })
      .style("fill-opacity", 1e-6);

  // Enter the new bars for the clicked-on data.
  // Per above, entering bars are immediately visible.
  var enter = bar(d)
      .attr("transform", stack(i))
      .style("opacity", 1);

  // Have the text fade-in, even though the bars are visible.
  // Color the bars as parents; they will fade to children if appropriate.
  enter.select("text").style("fill-opacity", 1e-6);
  enter.select("rect").style("fill", z(true));

  // Update the x-scale domain.
  x.domain([0, d3.max(d.children, function(d) { return d.value; })]).nice();

  // Update the x-axis.
  svg.selectAll(".x.axis").transition()
      .duration(duration)
      .call(xAxis);

  // Transition entering bars to their new position.
  var enterTransition = enter.transition()
      .duration(duration)
      .delay(function(d, i) { return i * delay; })
      .attr("transform", function(d, i) { return "translate(0," + y * i * 1.2 + ")"; });

  // Transition entering text.
  enterTransition.select("text").style("fill-opacity", 1);

  // Transition entering rects to the new x-scale.
  enterTransition.select("rect")
      .attr("width", function(d) { return x(d.value); })
      .style("fill", function(d) { return z(!!d.children); });

  // Transition exiting bars to fade out.
  var exitTransition = exit.transition()
      .duration(duration)
      .style("opacity", 1e-6)
      .remove();

  // Transition exiting bars to the new x-scale.
  exitTransition.selectAll("rect").attr("width", function(d) { return x(d.value); });

  // Rebind the current node to the background.
  svg.select(".background").data([d]).transition().duration(duration * 2); d.index = i;
}

function up(d) {
  if (!d.parent || this.__transition__) return;
  var duration = d3.event && d3.event.altKey ? 7500 : 750,
      delay = duration / d.children.length;

  // Mark any currently-displayed bars as exiting.
  var exit = svg.selectAll(".enter").attr("class", "exit");

  // Enter the new bars for the clicked-on data's parent.
  var enter = bar(d.parent)
      .attr("transform", function(d, i) { return "translate(0," + y * i * 1.2 + ")"; })
      .style("opacity", 1e-6);

  // Color the bars as appropriate.
  // Exiting nodes will obscure the parent bar, so hide it.
  enter.select("rect")
      .style("fill", function(d) { return z(!!d.children); })
    .filter(function(p) { return p === d; })
      .style("fill-opacity", 1e-6);

  // Update the x-scale domain.
  x.domain([0, d3.max(d.parent.children, function(d) { return d.value; })]).nice();

  // Update the x-axis.
  svg.selectAll(".x.axis").transition()
      .duration(duration * 2)
      .call(xAxis);

  // Transition entering bars to fade in over the full duration.
  var enterTransition = enter.transition()
      .duration(duration * 2)
      .style("opacity", 1);

  // Transition entering rects to the new x-scale.
  // When the entering parent rect is done, make it visible!
  enterTransition.select("rect")
      .attr("width", function(d) { return x(d.value); })
      .each("end", function(p) { if (p === d) d3.select(this).style("fill-opacity", null); });

  // Transition exiting bars to the parent's position.
  var exitTransition = exit.selectAll("g").transition()
      .duration(duration)
      .delay(function(d, i) { return i * delay; })
      .attr("transform", stack(d.index));

  // Transition exiting text to fade out.
  exitTransition.select("text")
      .style("fill-opacity", 1e-6);

  // Transition exiting rects to the new scale and fade to parent color.
  exitTransition.select("rect")
      .attr("width", function(d) { return x(d.value); })
      .style("fill", z(true));

  // Remove exiting nodes when the last child has finished transitioning.
  exit.transition().duration(duration * 2).remove();

  // Rebind the current parent to the background.
  svg.select(".background").data([d.parent]).transition().duration(duration * 2);
}

// Creates a set of bars for the given data node, at the specified index.
function bar(d) {
  var bar = svg.insert("svg:g", ".y.axis")
      .attr("class", "enter")
      .attr("transform", "translate(0,5)")
    .selectAll("g")
      .data(d.children)
    .enter().append("svg:g")
      .style("cursor", function(d) { return !d.children ? null : "pointer"; })
      .on("click", down);

  bar.append("svg:text")
      .attr("x", -6)
      .attr("y", y / 2)
      .attr("dy", ".35em")
      .attr("text-anchor", "end")
      .text(function(d) { return d.name; });

  bar.append("svg:rect")
      .attr("width", function(d) { return x(d.value); })
      .attr("height", y);

  return bar;
}

// A stateful closure for stacking bars horizontally.
function stack(i) {
  var x0 = 0;
  return function(d) {
    var tx = "translate(" + x0 + "," + y * i * 1.2 + ")";
    x0 += x(d.value);
    return tx;
  };
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

</body></html>