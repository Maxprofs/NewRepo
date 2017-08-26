<?php 
	include("config.php");
	global $conn,$stmt,$category_array;
	$category_array=array();
	getCategory();
	 if(isset($_POST['add_category']))
		{

		if($_POST['dd_category']==" ")
		{
			echo "no category selected";
			include("config.php");
			$c_name=$_POST['category_name'];
			$c_parent_id=0;
			put_asParentCategory($c_name,$c_parent_id);
			
		}
		else
		{
			echo $_POST['dd_category'];
			$c_name1=$_POST['category_name'];
			$category_selected=$_POST['dd_category'];
			makeSubCategory($category_selected,$c_name1);
		}
	}
	
	function getCategory()
	{
		global $conn,$stmt,$category_array;
		$stmt=$conn->prepare("SELECT * FROM category_table");
		$stmt->bind_result($id,$name,$parentid);
		$stmt->execute();
		while ($stmt->fetch()) 
		{
			# code...

			array_push($category_array,array("id"=>$id,"name"=>$name,"parent_id"=>$parentid));
		}
		$stmt->close();
		$conn->close();
		//print_r($category_array);
	}
	function put_asParentCategory($c_name,$c_parent_id)
	{
		global $conn,$stmt;

		$stmt=$conn->prepare("INSERT INTO category_table (category,parent_id) VALUES(?,?)");
		$stmt->bind_param("si",$c_name,$c_parent_id)	;
		$stmt->execute();
		if(false==$stmt)
		{
			echo "not inserted";
		}
		$stmt->close();
		$conn->close();
	}

	function makeSubCategory($category_selected,$c_name1)
	{
		include("config.php");
		$dd_selected=$category_selected;
		$new_category=$c_name1;
		echo $new_category;
		$stmt=$conn->prepare("SELECT * FROM category_table WHERE category=?");
		if($stmt==false)
		{
			echo "error";
		}
		$stmt->bind_param("s",$dd_selected);
		$stmt->execute();
		$stmt->bind_result($sub_cat_id,$ab,$cd);
		while($stmt->fetch())
		{
			$cat_parent_id=$sub_cat_id;
		}
		$stmt->close();
		$stmt=$conn->prepare("INSERT INTO category_table (category,parent_id) VALUES(?,?)");
		$stmt->bind_param("si",$new_category,$cat_parent_id);
		$stmt->execute();
		$stmt->close();
		$conn->close();
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<?php include("header.php");?>

</head>
<body>
<?php $page=basename($_SERVER['PHP_SELF']);
include("sidebar.php");
?>

	<div id="main-content"> <!-- Main Content Section with everything -->
			
			<h2>Welcome John</h2>
			<p id="page-intro">What would you like to do?</p>
			<div class="clear"></div>
					<div class="content-box"><!-- Start Content Box -->
				
						<div class="content-box-header">
						<h3>Add New Category</h3>
						<div class="clear"></div>
						</div>
						<form action="category_form.php" method="post">
							
							<fieldset> 
								<p >
									<label>Category Name</label>
										<input class="text-input small-input" type="text"  name="category_name"/> <span class="input-notification success png_bg">Successful message</span> <!-- Classes for input-notification: success, error, information, attention -->
										
								</p>
								<p >
									<label>Select Parent Category</label>
										<select name="dd_category" id="category-select">
										<option value=" ">--Select--</option>
											<?php
											 global $category_array;
											 foreach ($category_array as $key => $value) :?>
											 
											<option value="<?php echo $category_array[$key]['name'];?>" data-optionid="<?php echo $category_array[$key]['id'];?>" data-optionparentid="<?php echo $category_array[$key]['parent_id'];?>" ><?php echo $category_array[$key]['name'];?></option>
											<?php endforeach;?>
											
										</select><!-- Classes for input-notification: success, error, information, attention -->
										
								</p>
								<p>
									<input type="submit" name="add_category" class="button">
								</p>
								</fieldset>
								</form>
					</div>
						</div>

	<?php include("footer.php");?>
</body>
</html>