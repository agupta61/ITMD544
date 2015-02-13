<?php
//image compression function
function LoadPng($imgname)
{
/* Attempt to open */
$im = @imagecreatefrompng($imgname);
/* See if it failed */
if(!$im)
{
/* Create a black image */
$im = imagecreatetruecolor(50, 30);
$bgc = imagecolorallocate($im, 255, 255, 255);
$tc = imagecolorallocate($im, 0, 0, 0);
imagefilledrectangle($im, 0, 0, 50, 30, $bgc);
/* Output an error message */
imagestring($im, 1, 5, 5, 'Error loading ' . $imgname, $tc);
}
error_log( " image Compressed!");
return $im;
}
/*************************************************************************/

require 'vendor/autoload.php';
use Aws\Sqs\SqsClient;  
use Aws\Rds\RdsClient;
use Aws\S3\S3Client;
use Aws\Sns\SnsClient;
/*
If you instantiate a new client for Amazon Simple Storage Service (S3) with
no parameters or configuration, the AWS SDK for PHP will look for access keys
in the AWS_ACCESS_KEY_ID and AWS_SECRET_KEY environment variables.
For more information about this interface to Amazon S3, see:
http://docs.aws.amazon.com/aws-sdk-php-2/guide/latest/service-s3.html#creating-a-client
*/
$s3client = S3Client::factory();
$snsclient = SnsClient::factory(array(
'region' => 'us-east-1'
));		

$sqsclient = SqsClient::factory(array(
'region' => 'us-east-1'
));

$count=0;//initial value of count
$stopper=1000;
/***********************************************************************/

//infinite Loop starts here
while(1>0){
/**********************************initiate sqs handler evertime*************************************/

error_log("inside loop count: $count"); 
if ($count>$stopper){
error_log("sleep for one minutes!!!");
sleep(60);//sleep for one minutes.
$stopper=$stopper+100;
error_log("stopper is set to $stopper"); 
}
if ($count>1000000){
$count=0;
break;

}
$sqsresult = $sqsclient->receiveMessage(array(
// QueueUrl is required
'QueueUrl' => 'https://sqs.us-east-1.amazonaws.com/730379203140/FA5QUEUE',
'MaxNumberOfMessages' => 1,
'VisibilityTimeout' => 30,
// 'WaitTimeSeconds' => 60,
));
error_log("got the sqsresult!"); 
$SqsURL = 'https://sqs.us-east-1.amazonaws.com/730379203140/FA5QUEUE';
$messageBody = "";
$receiptHandle = "";
try { //to avoid invalid Receipt handler
foreach ($sqsresult->getPath('Messages/*/Body') as $messageBody) {
// Do something with the message
echo $messageBody ."\n";

$rdsclient = RdsClient::factory(array(
'region' => 'us-east-1'
));
$rdsresult = $rdsclient->describeDBInstances(array(
'DBInstanceIdentifier' => 'ambujsql',
));
$endpoint = "";
foreach ($rdsresult->getPath('DBInstances/*/Endpoint/Address') as $ep) {
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
error_log( "DATABASE CONNECTED"); 



$link->real_query("SELECT s3rawurl FROM USER_INFO where id=$messageBody");
$res = $link->use_result();
error_log( "GETTING THE IMAGE URL");

while ($row = $res->fetch_assoc()) {
echo " url = " . $row['s3rawurl'] . "\n";
   $imgurl=$row['s3rawurl'];
}
//Update the status to 0-started
if (!($stmt = $link->prepare("UPDATE USER_INFO SET STATUS=0 WHERE id=?"))) {
echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
} 
  $stmt->bind_param("i",$messageBody);

if (!$stmt->execute()) {
echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
} 
printf("%d Row Updated.\n", $stmt->affected_rows);
/* explicit close recommended */
$stmt->close();

// processing of image and upload the image to s3 bucket and update the row
/****************************************************************/

//Update the status to 1-in process

if (!($stmt = $link->prepare("UPDATE USER_INFO SET STATUS=1 WHERE id=?"))) {
echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
} 
  $stmt->bind_param("i",$messageBody);

if (!$stmt->execute()) {
echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
} 
printf("%d Row Updated.\n", $stmt->affected_rows);
/* explicit close recommended */
$stmt->close();

//image compression starts

error_log("raw url $imgurl");
$cmpimg = LoadPng($imgurl);
//store image to upload folder
$value=rand();
$file_name="thmb$value.png";
imagepng($cmpimg,"/var/www/html/uploads/$file_name");
$add="/var/www/html/uploads/$file_name";
//find thumbnail in uploads folder and upload it to S3 bucket
$s3result = $s3client->putObject(array(
    'Bucket'       =>'ambujbucket',
    'Key'          => $file_name,
    'SourceFile'   => $add,
    'ACL'          => 'public-read',
    'Metadata'     => array(
        'param1' => 'value 1',
        'param2' => 'value 2'
    )
));
//Update the status to 2-complete
$uploadURL= $s3result['ObjectURL'];
if (!($stmt = $link->prepare("UPDATE USER_INFO SET STATUS=2,s3finishedurl=? WHERE id=?"))) {
echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
} 
  $stmt->bind_param("si",$uploadURL,$messageBody);

if (!$stmt->execute()) {
echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
} 
printf("%d Row Updated.\n", $stmt->affected_rows);
/* explicit close recommended */
$stmt->close();
$link->close();
}// inner Loop ends here
}//try ends here
catch (Exception $e) { 
        echo 'Caught Exception: ',  $e->getMessage(), "\n"; 
    } 


error_log(" after Inner loop receiptHandle ");
try{
foreach ($sqsresult->getPath('Messages/*/ReceiptHandle') as $receiptHandle) {
error_log("Got the receiptHandle ");
$sqsresult = $sqsclient->deleteMessage(array(
// QueueUrl is required
'QueueUrl' => $SqsURL,
// ReceiptHandle is required
'ReceiptHandle' => $receiptHandle,
));
error_log("Message has been deleted!");
/**************************Publish the topic to get to notify the end point uses*************************/
// Get topic attributes
$snsresult = $snsclient->publish(array(
  	'TopicArn' => 'arn:aws:sns:us-east-1:730379203140:ImageDone',
  	'Message' => "See your image this link. $uploadURL",
  	'Subject' => 'Congratulations image processed.!',
  	'MessageStructure' => 'string',
  	));


error_log("User has been notified!");
}
}
catch (Exception $e) { 
        echo 'Caught Exception: ',  $e->getMessage(), "\n"; 
		error_log("Caught Exception");
    } 
	$count=$count+1;

error_log("END of PAGE!");


}//end infinite loop

echo "count 1000000 reached";
?>