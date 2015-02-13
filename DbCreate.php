<?php
echo "Hello world";

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
echo "============". $ep . "================";
$endpoint = $ep;
}


echo "begin database";



$link = mysqli_connect($endpoint,"itmd4515","itmd4515","employees") or die("Error " . mysqli_error($link));
/* check connection */
if (mysqli_connect_errno()) {
printf("Connect failed: %s\n", mysqli_connect_error());
exit();
}
echo "begin database Check END";
/*
$delete_table = 'DELETE TABLE items';
$del_tbl = $link->query($delete_table);
if ($delete_table) {
echo "Table student has been deleted";
}
else {
echo "error!!";
}
/
*/



$create_table = 'CREATE TABLE IF NOT EXISTS USER_INFO
(
id INT NOT NULL AUTO_INCREMENT,
FIRST_NAME VARCHAR(200) NOT NULL,
LAST_NAME VARCHAR(200) NOT NULL,
email VARCHAR(200) NOT NULL,
phone VARCHAR(20) NOT NULL,
filename VARCHAR(255) NOT NULL,
s3rawurl VARCHAR(255) NOT NULL,
s3finishedurl VARCHAR(255),
status INT ,
issubscribed INT,
PRIMARY KEY(id)
)';

$create_tbl = $link->query($create_table);
if ($create_table) {
echo "Table is created or No error returned.";
}
else {
echo "error!!";
}
$link->close();
?>