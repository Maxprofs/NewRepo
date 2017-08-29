<?php
$servername="localhost";
$username="kashifak";
$password="password";
$dbname="kashifak_mydb2";

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