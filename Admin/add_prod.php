<?php
session_start();
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
	$execute=$stmt->execute();
	if($execute==false)
		{
			$_SESSION['error']="gfg";
		}
		else{
			$_SESSION['message']="gfg";
		}
	$stmt->close();
	$conn->close();
	header("location:form.php");

}
else if(isset($_POST['edit_product']))
	{
	include("config.php");
	$hiddenidtoedit=$_POST['hidden_eid'];
	$id3= $_POST['p_id'];
	$name3= $_POST['p_name'];
	$price3= $_POST['p_price'];
	$quantity3= $_POST['p_quantity'];
	$category3= $_POST['p_category'];
				   
		 if(isset($_FILES['p_image']))
		 {
			$tempn=$_FILES['p_image']['tmp_name'];
			$nm1=$_FILES['p_image']['name'];
		 	if(move_uploaded_file($tempn,"../uploads/".$nm1))
		 	{
		 		echo "image uploaded successfully";
				$image3=$nm1;
		
			}
		 }
	
	$stmt=$conn->prepare(" UPDATE new_products_table  SET id=?,name=?,price=?,qty=?,image=?,category=? WHERE id=?");
	$stmt->bind_param("ssiisss",$id3,$name3,$price3,$quantity3,$image3,$category3,$hiddenidtoedit);
	$stmt->execute();
	$stmt->close();
	$conn->close();
	header("location:manageprod.php");
}

?>