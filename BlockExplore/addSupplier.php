<!DOCTYPE html>
<!-- Add Supplier Info to Table Supplier -->
<?php
		$currentpage="Add Supplier";
		include "pages.php";
		
?>
<html>
	<head>
		<title>Add Supplier</title>
		<link rel="stylesheet" href="index.css">
		<script type = "text/javascript"  src = "verifyInput.js" > </script> 
	</head>
<body>


<?php
	include "header.php";
	$msg = "Add new supplier record to the Supplier Table";

// change the value of $dbuser and $dbpass to your username and password
	include 'connectvars.php'; 
	
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	if (!$conn) {
		die('Could not connect: ' . mysql_error());
	}
	if ($_SERVER["REQUEST_METHOD"] == "POST") {

// Escape user inputs for security
		$sid = mysqli_real_escape_string($conn, $_POST['sid']);
		$sname = mysqli_real_escape_string($conn, $_POST['sname']);
		$city = mysqli_real_escape_string($conn, $_POST['city']);
	
// See if sid is already in the table
		$queryIn = "SELECT * FROM Supplier where sid='$sid' ";
		$resultIn = mysqli_query($conn, $queryIn);
		if (mysqli_num_rows($resultIn)> 0) {
			$msg ="<h2>Can't Add to Table</h2> There is already a supplier with sid $sid<p>";
		} else {
		
		// attempt insert query 
			$query = "INSERT INTO Supplier (sid, sname, city) VALUES ('$sid', '$sname', '$city')";
			if(mysqli_query($conn, $query)){
				$msg =  "Record added successfully.<p>";
			} else{
				echo "ERROR: Could not able to execute $query. " . mysqli_error($conn);
			}
		}
}
// close connection
mysqli_close($conn);

?>
	<section>
    <h2> <?php echo $msg; ?> </h2>

<form method="post" id="addForm">
<fieldset>
	<legend>Supplier Info:</legend>
    <p>
        <label for="sID">Supplier ID:</label>
        <input type="number" min=1 max = 99999 class="required" name="sid" id="sid" title="sid should be numeric">
    </p>
    <p>
        <label for="Name">Supplier Name:</label>
        <input type="text" class="required" name="sname" id="sname">
    </p>

    <p>
        <label for="City">City:</label>
        <input type="text" class="required" name="city" id="city">
</fieldset>

      <p>
        <input type = "submit"  value = "Submit" />
        <input type = "reset"  value = "Clear Form" />
      </p>
</form>
</body>
</html>
