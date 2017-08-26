<?php
	session_start();
	$user="john";
	$date=date('Y/m/d H:i:s');
	$t_price=$_SESSION['total_price'];
	global $cart;
	$cart=array();
	$cart=$_SESSION['cart'];
	include("config.php");
	$stmt=$conn->prepare("INSERT INTO order_table (user_name,order_date,order_price,order_data) VALUES(?,?,?,?)");
	$stmt->bind_param("ssis",$user,$date,$t_price,$cart);
	$stmt->execute();
	$stmt->close();
	$conn->close();
	unset($_SESSION['cart']);
	unset($_SESSION['total_price']);
	session_destroy();
	header("location:index.php");
?>