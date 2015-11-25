<?php 
require_once('include/utils/utils.php');
require_once('config.inc.php');
require_once('include/logging.php');

session_start();

if (isset($_SESSION["authenticated_user_id"])) {
    // echo "ok";
    $tree_string = getProductCategoryTree();
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

</style>
<?php 
    echo "<script>\n";
    echo "var json_products = " . $tree_string .";";
    echo "</script>\n";    
    
    $fp = fopen('products.json', 'w+');
    fwrite($fp, $tree_string);
    fclose($fp);
?>
<form>
  <label><input type="radio" name="mode" value="size"> Total Amount</label>
  <label><input type="radio" name="mode" value="count" checked> Number of rows (Count)</label>
</form>
<script src="http://d3js.org/d3.v3.min.js"></script>
<script>

var width = 700,
    height = 800,
    radius = Math.min(width, height) / 2,
    x = d3.scale.linear().range([0, 2 * Math.PI]),
    y = d3.scale.pow().exponent(1.3).domain([0, 1]).range([0, radius]),
    padding = 5,
    color = d3.scale.category20c();

var svg = d3.select("body").append("svg")
    .attr("width", width)
    .attr("height", height)
  .append("g")
    .attr("transform", "translate(" + width / 2 + "," + height * .52 + ")");

var partition = d3.layout.partition()
    .sort(null)
    .size([2 * Math.PI, radius * radius])
    .value(function(d) { return 1; });

var arc = d3.svg.arc()
    .startAngle(function(d) { return d.x; })
    .endAngle(function(d) { return d.x + d.dx; })
    .innerRadius(function(d) { return Math.sqrt(d.y); })
    .outerRadius(function(d) { return Math.sqrt(d.y + d.dy); });

d3.json("products.json", function(error, root) {
  var path = svg.datum(root).selectAll("path")
      .data(partition.nodes)
    .enter().append("path")
      .attr("display", function(d) { return d.depth ? null : "none"; }) // hide inner ring
      .attr("d", arc)
      .style("stroke", "#fff")
      .style("fill", function(d) { return color((d.children ? d : d.parent).name); })
      .style("fill-rule", "evenodd")
      .each(stash);

      
  var text = svg.datum(root).selectAll("text").data(partition.nodes);
  
  text.enter().append("text")
    .style("font-size", "10px")
    .attr("x", function(d) { return d[1]; })
     // Rotate around the center of the text, not the bottom left corner
    .attr("text-anchor", "middle")
     // First translate to the desired point and set the rotation
     // Not sure what the intent of using this.parentNode.getBBox().width was here (?)
    .attr("transform", function(d) { return "translate(" + arc.centroid(d) + ")" + "rotate(" + getAngle(d) + ")"; })                                    
    .attr("dx", "6") // margin
    .attr("dy", ".35em") // vertical-align
    .text(function(d){return d.name})
    .attr("pointer-events","none");
   
  
  
  
  d3.selectAll("input").on("change", function change() {
    var value = this.value === "count"
        ? function(d) { return d.cnt; }
        : function(d) { return d.size; };

    path
        .data(partition.value(value).nodes)
      .transition()
        .duration(1500)
        .attrTween("d", arcTween);
  });
});

// Stash the old values for transition.
function stash(d) {
  d.x0 = d.x;
  d.dx0 = d.dx;
}

// Interpolate the arcs in data space.
function arcTween(a) {
  var i = d3.interpolate({x: a.x0, dx: a.dx0}, a);
  return function(t) {
    var b = i(t);
    a.x0 = b.x;
    a.dx0 = b.dx;
    return arc(b);
  };
}
function getAngle(d) {
    // Offset the angle by 90 deg since the '0' degree axis for arc is Y axis, while
    // for text it is the X axis.
    var thetaDeg = (180 / Math.PI * (arc.startAngle()(d) + arc.endAngle()(d)) / 2 - 90);
    // If we are rotating the text by more than 90 deg, then "flip" it.
    // This is why "text-anchor", "middle" is important, otherwise, this "flip" would
    // a little harder.
    return (thetaDeg > 90) ? thetaDeg - 180 : thetaDeg;
}
d3.select(self.frameElement).style("height", height + "px");

function brightness(rgb) {
  return rgb.r * .299 + rgb.g * .587 + rgb.b * .114;
}
</script>

<?php 
function getProductCategoryTree()
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
    $tree_array = array("name"=>"products", "description"=>"sales order by product category","children" => array());
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