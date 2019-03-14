<!DOCTYPE html>
<?php
		session_start();
		$currentpage="time";
		include "pages.php";
?>
<html>
	<head>
		<title>time</title>
		<link rel="stylesheet" href="index.css">
		<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
		<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
		<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" class="init">
	
			$(document).ready(function() {
				$('table.display').DataTable({searching: false});
			} );

		</script>
	</head>
<body>

<?php
// change the value of $dbuser and $dbpass to your username and password
	include 'connectvars.php'; 
	include 'header.php';	

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	if (!$conn) {
		die('Could not connect: ' . mysql_error());
	}	
?>

<form method="get" id="addForm">
<div style="display: flex">
<fieldset style="width: 50%">

	<legend>Min Time:</legend>
    <p>
        <label for="min">Year</label>
        <input type="number" min=2000 max = 3000 class="required" name="nyear" id="nyear" title="min should be numeric">
    </p>
    <p>
        <label for="min">Month</label>
        <input type="number" min=1 max = 12 class="required" name="nmonth" id="nmonth" title="min should be numeric">
    </p>
    <p>
        <label for="min">Day</label>
        <input type="number" min=1 max = 31 class="required" name="nday" id="nday" title="min should be numeric">
    </p>
    <p>
        <label for="min">Hour</label>
        <input type="number" min=0 max = 23 class="required" name="nhour" id="nhour" title="min should be numeric">
    </p>
    <p>
        <label for="min">Minute</label>
        <input type="number" min=0 max = 59 class="required" name="nmin" id="nmin" title="min should be numeric">
    </p>
    <p>
        <label for="min">Second</label>
        <input type="number" min=0 max = 59 class="required" name="nsec" id="nsec" title="min should be numeric">
    </p>
</fieldset>
<fieldset style="width: 50%">
	<legend>Max Time:</legend>
    <p>
        <label for="min">Year</label>
        <input type="number" min=2000 max = 3000 class="required" name="xyear" id="xyear" title="min should be numeric">
    </p>
    <p>
        <label for="min">Month</label>
        <input type="number" min=1 max = 12 class="required" name="xmonth" id="xmonth" title="min should be numeric">
    </p>
    <p>
        <label for="min">Day</label>
        <input type="number" min=1 max = 31 class="required" name="xday" id="xday" title="min should be numeric">
    </p>
    <p>
        <label for="min">Hour</label>
        <input type="number" min=0 max = 23 class="required" name="xhour" id="xhour" title="min should be numeric">
    </p>
    <p>
        <label for="min">Minute</label>
        <input type="number" min=0 max = 59 class="required" name="xmin" id="xmin" title="min should be numeric">
    </p>
    <p>
        <label for="min">Second</label>
        <input type="number" min=0 max = 59 class="required" name="xsec" id="xsec" title="min should be numeric">
    </p>

</fieldset>
</div>
      <p style="width: 100%">
        <input type = "submit"  value = "Submit" />
        <input type = "reset"  value = "Clear Form" />
      </p>
</form>

<?php


	$nyear = mysqli_real_escape_string($conn, $_GET['nyear']);
	$nmonth = mysqli_real_escape_string($conn, $_GET['nmonth']);
	$nday = mysqli_real_escape_string($conn, $_GET['nday']);
	$nhour = mysqli_real_escape_string($conn, $_GET['nhour']);
	$nmin = mysqli_real_escape_string($conn, $_GET['nmin']);
	$nsec = mysqli_real_escape_string($conn, $_GET['nsec']);
	$xyear = mysqli_real_escape_string($conn, $_GET['xyear']);
	$xmonth = mysqli_real_escape_string($conn, $_GET['xmonth']);
	$xday = mysqli_real_escape_string($conn, $_GET['xday']);
	$xhour = mysqli_real_escape_string($conn, $_GET['xhour']);
	$xmin = mysqli_real_escape_string($conn, $_GET['xmin']);
	$xsec = mysqli_real_escape_string($conn, $_GET['xsec']);


// query to select all information from supplier table

	if($nyear==NULL){
		$nyear=2000;
	} 
	if($nmonth==NULL){
		$nmonth=1;
	}
	if($nday==NULL){
		$nday=1;
	}
	if($nhour==NULL){
		$nhour=0;
	} 
	if($nmin==NULL){
		$nmin=0;
	}
	if($nsec==NULL){
		$nsec=0;
	}
	if($xyear==NULL){
		$xyear=3000;
	} 
	if($xmonth==NULL){
		$xmonth=12;
	}
	if($xday==NULL){
		$xday=31;
	}
	if($xhour==NULL){
		$xhour=23;
	} 
	if($xmin==NULL){
		$xmin=59;
	}
	if($xsec==NULL){
		$xsec=59;
	}



 	$query = "SELECT transHash, time FROM TransTable where time>=\"$nyear-$nmonth-$nday $nhour:$nmin:$nsec\" and time<=\"$xyear-$xmonth-$xday $xhour:$xmin:$xsec\" order by time";

	
	
// Get results from query
	$result = mysqli_query($conn, $query);
	if (!$result) {
		die("Query to show fields from table failed");
	}
// get number of columns in table	
	$fields_num = mysqli_num_fields($result);
	echo "<h1>Transactions:</h1>";
	echo "<table id='t01' border='1' class=\"display\" style=\"width:100%\"><thead><tr>";
	
// printing table headers
	for($i=0; $i<$fields_num; $i++) {	
		$field = mysqli_fetch_field($result);	
		echo "<th><b>$field->name</b></th>";
	}
	echo "</tr></thead><tbody>\n";
	while($row = mysqli_fetch_row($result)) {	
		echo "<tr>";	
		// $row is array... foreach( .. ) puts every element
		// of $row to $cell variable
		$i=0;	
		foreach($row as $cell){
			if($i!=0){
				echo "<td>$cell</td>";
			}
			else{
				echo "<td><a href='transHash.php?hash=" . $cell . "'>$cell</a></td>";
			}
			$i++;
		}		
			
		echo "</tr>\n";
	}
	echo "</tbody><tfoot><tr>";
	for($i=0; $i<$fields_num; $i++) {	
		$field = mysqli_fetch_field($result);	
		echo "<th><b>$field->name</b></th>";
	}
	echo "</tr></tfoot></table>\n";
	mysqli_free_result($result);
	mysqli_close($conn);
?>



</body>

</html>

	
