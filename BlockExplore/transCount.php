<!DOCTYPE html>
<?php
		session_start();
		$currentpage="transCount";
		include "pages.php";
?>
<html>
	<head>
		<title>transCount</title>
		<link rel="stylesheet" href="index.css">
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
	<legend>Transactions:</legend>
    <p>
        <label for="min">Min Transactions</label>
        <input type="number" min=1 max = 99999 class="required" name="min" id="min" title="min should be numeric">
    </p>
    <p>
        <label for="max">Max Transactions</label>
        <input type="number" min=1 max = 99999 class="required" name="max" id="max" title="max should be numeric">
    </p>

</fieldset>

      <p>
        <input type = "submit"  value = "Submit" />
        <input type = "reset"  value = "Clear Form" />
      </p>
</form>

<?php


	$min = mysqli_real_escape_string($conn, $_GET['min']);
	$max = mysqli_real_escape_string($conn, $_GET['max']);

// query to select all information from supplier table
	if($min==NULL){
		$min = 1;
	}
	if($max==NULL){
		$query = "SELECT * FROM Block where transCount>=$min order by transCount";
	}else{
		$query = "SELECT * FROM Block where transCount>=$min AND transCount<=$max order by transCount";
	}
	
	
// Get results from query
	$result = mysqli_query($conn, $query);
	if (!$result) {
		die("Query to show fields from table failed");
	}
// get number of columns in table	
	$fields_num = mysqli_num_fields($result);
	echo "<h1>Blocks:</h1>";
	echo "<table id='t01' border='1'><tr>";
	
// printing table headers
	for($i=0; $i<$fields_num; $i++) {	
		$field = mysqli_fetch_field($result);	
		echo "<td><b>$field->name</b></td>";
	}
	echo "</tr>\n";
	while($row = mysqli_fetch_row($result)) {	
		echo "<tr>";	
		// $row is array... foreach( .. ) puts every element
		// of $row to $cell variable	
		foreach($row as $cell)		
			echo "<td>$cell</td>";	
		echo "</tr>\n";
	}

	mysqli_free_result($result);
	mysqli_close($conn);
?>



</body>

</html>

	
