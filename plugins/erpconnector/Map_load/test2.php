<?php
$myServer = "10.88.105.14";
$myUser = "sa";
$myPass = "semsql111";
$myDB = "vte40_387";

//connection to the database
$dbhandle = mssql_connect($myServer, $myUser, $myPass)
  or die("Couldn't connect to SQL Server on $myServer");

//select a database to work with
$selected = mssql_select_db($myDB, $dbhandle)
  or die("Couldn't open database $myDB");

//declare the SQL statement that will query the database
$query = "select condizioni_prezzo 
			from vte40_387.dbo.vtiger_condizioni_prezzo 
			inner join	vte40_387.dbo.vtiger_role2picklist on vte40_387.dbo.vtiger_role2picklist.picklistvalueid = vte40_387.dbo.vtiger_condizioni_prezzo.picklist_valueid
			and roleid = 'H2'  
			order by condizioni_prezzo asc";

//execute the SQL query and return records
$result = mssql_query($query);

$numRows = mssql_num_rows($result);
echo "<h1>" . $numRows . " Row" . ($numRows == 1 ? "" : "s") . " Returned </h1>";

//display the results
while($row = mssql_fetch_array($result))
{
  echo "<li>" . $row["id"] . $row["name"] . $row["year"] . "</li>";
}
//close the connection
mssql_close($dbhandle);
?> 
