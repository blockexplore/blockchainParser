<!DOCTYPE html>
<!-- Add Part Info to Table Part -->
<?php
		$currentpage="Add Part";
		include "pages.php";
		
?>
<html>
	<head>
		<title>Add Part</title>
		<link rel="stylesheet" href="index.css">
		<script type = "text/javascript"  src = "verifyInput.js" > </script> 
	</head>
<body>


<?php
	include "header.php";
	$msg = "Add new Part record to the Part Table";

// change the value of $dbuser and $dbpass to your username and password
	include 'connectvars.php'; 
	
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	if (!$conn) {
		die('Could not connect: ' . mysql_error());
	}
	if ($_SERVER["REQUEST_METHOD"] == "POST") {

// Escape user inputs for security
		$pid = mysqli_real_escape_string($conn, $_POST['pid']);
		$pname = mysqli_real_escape_string($conn, $_POST['pname']);
		$color = mysqli_real_escape_string($conn, $_POST['color']);
	
// See if pid is already in the table
		$queryIn = "SELECT * FROM Parts where pid='$pid' ";
		$resultIn = mysqli_query($conn, $queryIn);
		if (mysqli_num_rows($resultIn)> 0) {
			$msg ="<h2>Can't Add to Table</h2> There is already a part with pid $pid<p>";
		} else {
		
		// attempt insert query 
			$query = "INSERT INTO Parts (pid, pname, color) VALUES ('$pid', '$pname', '$color')";
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
	<legend>Part Info:</legend>
    <p>
        <label for="sID">Part ID:</label>
        <input type="number" min=1 max = 99999 class="required" name="pid" id="pid" title="pid should be numeric">
    </p>
    <p>
        <label for="Name">Part Name:</label>
        <input type="text" class="required" name="pname" id="pname">
    </p>

    <p>
        <label for="Color">Color:</label>
        <input type="text" class="required" name="color" id="color">
</fieldset>

      <p>
        <input type = "submit"  value = "Submit" />
        <input type = "reset"  value = "Clear Form" />
      </p>
</form>
</body>
</html>
