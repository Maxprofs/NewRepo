<?php
$servername="localhost";
$username="kashifak";
$password="password";
$dbname="kashifak_mydb1";

//stablish connection
$conn = new mysqli($servername, $username, $password, $dbname);
if($conn->connect_error)
{
	die("Data Base Error");

}
else
{
//echo "connection successfull";
}
?>
