<?php

//Make short Variable names
$email = $_REQUEST['email_field'];


?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Gallery</title>
		<style>
		.box {
			border: 1px solid #444;
			background-color: #ccc;
			margin: 20px 20px;
			padding: 5px 20px;
		}
	</style>
</head>
<body background="http://wallruru.com/wp-content/uploads/2014/08/Green-Background-10.jpg">
	<div style="text-align:center;">
		
		<p>Welcome to Your Gallery <a href="index.php">Home</a></p>
	</div>
	<hr><br>

	<div class="box">
	<h2>Images from Bucket</h2>
	
<?Php
require 'vendor/autoload.php';

use Aws\Rds\RdsClient;
$client = RdsClient::factory(array(
'region' => 'us-east-1'
));
$result = $client->describeDBInstances(array(
'DBInstanceIdentifier' => 'ambujsql',
));
$endpoint = "";
foreach ($result->getPath('DBInstances/*/Endpoint/Address') as $ep) {
// Do something with the message
//echo "============". $ep . "================";
$endpoint = $ep;
}

$link = mysqli_connect($endpoint,"itmd4515","itmd4515","employees") or die("Error " . mysqli_error($link));
/* check connection */
if (mysqli_connect_errno()) {
printf("Connect failed: %s\n", mysqli_connect_error());
exit();
}
//echo "DATABASE CONNECTED";

$link->real_query("SELECT * FROM USER_INFO WHERE email ='$email'");
$res = $link->use_result();
echo "<h1>Your images!</h1>";
echo "<table border=1 width=100%>";
echo "<tr>";
echo "<td><center><h3>Original Image</h3></center></td><td><center><h3>Thumb Nails</h3></center></td>";
echo "</tr>";

while ($row = $res->fetch_assoc()) {
echo "<tr>";
echo " <td><img src =\" " . $row['s3rawurl'] . "\" /></td><td><img src =\"" .$row['s3finishedurl'] . "\"/></td>";
echo "</tr>";
}
echo "</table>";
$link->close();


?>




	</div>
</body>
</html>
