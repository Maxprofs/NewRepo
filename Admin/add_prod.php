<?php
if(isset($_POST['add_product']))
{
	include("config.php");
	$id=$_POST['p_id'];
	$name=$_POST['p_name'];
	$price=$_POST['p_price'];
	$quantity=$_POST['p_quantity'];
	$category=$_POST['p_category'];
	
	if(isset($_FILES['p_image']))
	{

		$temp=$_FILES['p_image']['tmp_name'];
		$nm=$_FILES['p_image']['name'];
		if(move_uploaded_file($temp,"../uploads/".$nm))
		{
			$image=$nm;
			
		}
	}
$stmt=$conn->prepare("INSERT INTO new_products_table (id ,name,price,qty,image,category) VALUES(?,?,?,?,?,?)");
	$stmt->bind_param("ssiiss",$id,$name,$price,$quantity,$image,$category);
	$stmt->execute();
	$stmt->close();
	$conn->close();
	header("location:form.php");

}
?>