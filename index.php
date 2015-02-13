<!DOCTYPE html>
<html lang="en">
<head>
<h1><center> Welcome to Image Processing Technologies </center></h1>
	<meta charset="UTF-8">
	<title>Final Assignment</title>
	<style type = "text/css">
	#table{
	color: grey;
	background-color: #DCDCDC;
	}</style>
</head>

<body background="http://wallruru.com/wp-content/uploads/2014/08/Green-Background-10.jpg">
<table>
<tr>

<td>
<form ENCTYPE="multipart/form-data" action="gallery.php" method="post">
<p><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</label></p>
</form>
</td>


<td>
<form ENCTYPE="multipart/form-data" action="result.php" method="post">

<fieldset id = "table">
<legend><h2><font color="black">Enter Details</h2></legend>
<p><label>First Name: <input type="text" name="first_name_field" placeholder="enter first name"></label></p>
<p><label>Last Name: <input type="text" name="last_name_field" placeholder="enter last name"></label></p>
<p><label>Phone Number: <input type="tel" name="phone_field" placeholder="enter phone number"></label></p>
<p><label>Email Address: <input type="email" name="email_field" placeholder="enter email address"></label></p>
</fieldset>
<br>
<fieldset id = "table">
<legend><h2><font color="black">Provide an Image below</h2></legend>
Upload an Image file: <INPUT NAME="file_up" TYPE="file">
</fieldset>
<br>
<input type="submit" value="Upload Image">
</form>
</td>

<td>
<form ENCTYPE="multipart/form-data" action="gallery.php" method="post">
<p><label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</label></p>
</form>
</td>


<td>
<form ENCTYPE="multipart/form-data" action="gallery.php" method="post">
<fieldset id = "table">
<legend><h2><font color="black">See your gallery</h2></legend>
<p><label>Email Address: <input type="email" name="email_field" placeholder="enter email address"></label></p>
<br>
<input type="submit" value="Show my gallery">
</fieldset>
<br><br><br><br><br><br><br><br><br><br><br><br>
</form>
</td>
</tr>
</table>

</body>
</html>
