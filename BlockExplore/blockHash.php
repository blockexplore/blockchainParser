<!DOCTYPE html>
<?php
		session_start();
		$currentpage="blockHash";
		include "pages.php";
?>
<html>
	<head>
		<title>blockHash</title>
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
<fieldset>
	<legend>blockHash:</legend>
    <p>
        <label for="hash">blockHash</label>
        <input type="text" class="required" name="hash" id="hash">
    </p>


</fieldset>

      <p>
        <input type = "submit"  value = "Submit" />
        <input type = "reset"  value = "Clear Form" />
      </p>
</form>

<?php


	$hash = mysqli_real_escape_string($conn, $_GET['hash']);
	

// query to select all information from supplier table

	if($hash==NULL){
		$hash = 0;
		$query = "SELECT * FROM BlockTable where 1";
	}else{
		$hash="\"$hash%\"";
		$query = "SELECT * FROM BlockTable where blockHash like $hash";
	}
	

	
	
// Get results from query
	$result = mysqli_query($conn, $query);
	if (!$result) {
		die("Query to show fields from table failed");
	}
// get number of columns in table	
	$fields_num = mysqli_num_fields($result);
	echo "<h1>Blocks:</h1>";
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
				echo "<td><a href='searchByBlock.php?hash=" . $cell . "'>$cell</a></td>";
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

	
