<?php

//Make short Variable names
$firstName = $_REQUEST['first_name_field'];
$lastName = $_REQUEST['last_name_field'];
$phone = $_REQUEST['phone_field'];
$email = $_REQUEST['email_field'];
$subs=1;
$doSubscribe=0;

?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>RESULT PAGE</title>
		<style>
		.box {
			border: 1px solid #444;
			background-color: #ccc;
			margin: 20px 20px;
			padding: 5px 20px;
		}
	</style>
</head>
<body background="http://wallruru.com/wp-content/uploads/2014/08/Green-Background-10.jpg" >
	<div style="text-align:center;">
		
		<p>Welcome to S3 bucket</p>
	</div>
	<hr><br>
	
	<div class="box">
	<h2>Personal Information</h2>
		<p><strong>First Name:</strong> <?php echo htmlentities($firstName) ?></p>
		<p><strong>Last Name:</strong> <?php echo htmlentities($lastName) ?></p>
		<p><strong>Phone Number:</strong> <?php echo htmlentities($phone) ?></p>
		<p><strong>Email Address:</strong> <?php echo htmlentities($email) ?></p>

	</div>
	<div class="box">
	<h2>Image uploaded in  Bucket</h2>
	
<?Php
require 'vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\Sns\SnsClient;
$client = S3Client::factory();
//$bucket = uniqid("php-sdk-sample-", true);
//echo "Creating bucket named {$bucket}\n";
/*$result = $client->createBucket(array(
    'Bucket' => $bucket
));
// Wait until the bucket is created
$client->waitUntilBucketExists(array('Bucket' => $bucket));*/

//echo "Uploading the file \n";
$file_upload="true";
$file_up_size=$_FILES['file_up'][size];
//echo $_FILES[file_up][name];
if ($_FILES[file_up][size]>250000){$msg=$msg."Your uploaded file size is more than 250KB
 so please reduce the file size and then upload.<BR>";
$file_upload="false";}

if (!($_FILES[file_up][type] =="image/jpeg" OR $_FILES[file_up][type] =="image/gif" OR $_FILES[file_up][type] =="image/png"))
{$msg=$msg."Your uploaded file must be of JPG or GIF. Other file types are not allowed<BR>";
$file_upload="false";}

$file_name=$_FILES[file_up][name];
$add="/var/www/html/uploads/$file_name"; // the path with the file name where the file will be stored
/*$path="/var/www/html/uploads/";*/
if($file_upload=="true"){

if(move_uploaded_file ($_FILES[file_up][tmp_name], $add)){
//echo "File successfully uploaded";
}else{echo "Failed to upload file Contact Site admin to fix the problem";}

}else{echo $msg;}
// Upload a file.
$result = $client->putObject(array(
    'Bucket'       =>'ambujbucket',
    'Key'          => $file_name,
    'SourceFile'   => $add,
    'ACL'          => 'public-read',
    'Metadata'     => array(
        'param1' => 'value 1',
        'param2' => 'value 2'
    )
));

// Print the URL to the object.
  //  echo "Downloading that same object:\n";
/*$result = $client->getObject(array(
    'Bucket' => $bucket,
    'Key'    => $file_name
))*/
$uploadURL= $result['ObjectURL'];
?>


<img src="<?php echo $uploadURL?>" alt="picture"/>
<?php
echo "INSERTION INTO TABLE";

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

$link = mysqli_connect($endpoint,"itmd4515","itmd4515","employees") or die("Error " . mysqli_error($link));
/* check connection */
if (mysqli_connect_errno()) {
printf("Connect failed: %s\n", mysqli_connect_error());
exit();
}
echo "DATABASE CONNECTED";
/**************Check for Already Subscribed*****************************************************/
try{
$link->real_query("SELECT * FROM USER_INFO where email='$email'");
$res = $link->use_result();
while ($row = $res->fetch_assoc()) {
echo " issubscribed = " . $row['issubscribed'] . "\n";
     $doSubscribe=$row['issubscribed'];
   break;
}
}
catch (Exception $e) {         
		echo "No Email Found!";
    }

$link->close();
if($doSubscribe<1){
/***********************Subscribe the user with the topic*************************/
$snsclient = SnsClient::factory(array(
'region' => 'us-east-1'
));	
echo 'Email is going to be subscribed.';
$snsresult = $snsclient->subscribe(array(
    // TopicArn is required
    'TopicArn' => 'arn:aws:sns:us-east-1:730379203140:ImageDone',
    // Protocol is required
    'Protocol' => 'email',
    'Endpoint' => "$email",
));
echo 'Done!';
}
/************************************************************************************************/

/*$sql = "INSERT INTO USER_INFO (FIRST_NAME, LAST_NAME,email,phone,filename,s3rawurl) VALUES ($firstName,$lastName,$email,$phone,$file_name,$uploadURL)";
if ($link->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
*/
$link = mysqli_connect($endpoint,"itmd4515","itmd4515","employees") or die("Error " . mysqli_error($link));
/* check connection */
if (mysqli_connect_errno()) {
printf("Connect failed: %s\n", mysqli_connect_error());
exit();
}
echo "DATABASE CONNECTED AGAIN";

if (!($stmt = $link->prepare("INSERT INTO USER_INFO (FIRST_NAME, LAST_NAME,email,phone,filename,s3rawurl,issubscribed) VALUES (?,?,?,?,?,?,?)"))) {
echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
} 
  $stmt->bind_param("ssssssi",$firstName,$lastName,$email,$phone,$file_name,$uploadURL,$subs);
  /*$stmt->bind_param('s', $firstName);
  $stmt->bind_param('s', $lastName);
  $stmt->bind_param('s', $email);
  $stmt->bind_param('s', $phone);
  $stmt->bind_param('s', $file_name);
  $stmt->bind_param('s', $uploadURL); */

if (!$stmt->execute()) {
echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
} 
printf("%d Row inserted.\n", $stmt->affected_rows);
/* explicit close recommended */
$stmt->close();
$link->close();
$link = mysqli_connect($endpoint,"itmd4515","itmd4515","employees") or die("Error " . mysqli_error($link));
/* check connection */
if (mysqli_connect_errno()) {
printf("Connect failed: %s\n", mysqli_connect_error());
exit();
}
echo "DATABASE CONNECTED AGAIN";
$link->real_query("SELECT * FROM USER_INFO where status is null");
$res1 = $link->use_result();
echo "Result set order...\n";
while ($row = $res1->fetch_assoc()) {
echo " id = " . $row['id'] . "\n";
   $rowid=$row['id'];
}
$link->close();
?>


<?php


require 'vendor/autoload.php';
//http://docs.aws.amazon.com/aws-sdk-php/guide/latest/service-sqs.html
use Aws\Sqs\SqsClient;
$client = SqsClient::factory(array(
'region' => 'us-east-1'
));

$result = $client->getQueueUrl(array(
// QueueName is required
'QueueName' => 'FA5QUEUE',
// 'QueueOwnerAWSAccountId' => '',
));

//echo $result['QueueUrl'];

//$queueUrl = $result->get('QueueUrl');

$queueUrl = $result->get('QueueUrl');

//echo  $queueUrl

$messagevalue=
$client->sendMessage(array(
'QueueUrl' => $queueUrl,
'MessageBody' => $rowid,
));

echo 'Message has been sent to SQS.';

?>




	</div>
</body>
</html>
