<?php 
	include("../functions.php");
	getCategory();
	 if(isset($_POST['add_category']))
		{

		setCategory();
		
	}
	else if(isset($_GET['e_id']))
	{
		$idtoedit=$_GET['e_id'];
		include("config.php");
		$stmt=$conn->prepare("SELECT * FROM category_table WHERE id=?");
		$stmt->bind_param("i",$idtoedit);
		$stmt->execute();
		$stmt->bind_result($id11,$name11,$parentid11);
		while ($stmt->fetch()) {
			$id12=$id11;
			$name12=$name11;
			$parentid12=$parentid11;
		}
		//echo $parentid12;
		//$stmt->close();
		$stmt=$conn->prepare("SELECT * FROM category_table WHERE id=?");
		$stmt->bind_param("i",$parentid12);
		$stmt->execute();
		$stmt->bind_result($parent_id11,$parent_name11,$parent_parentid11);
		while ($stmt->fetch()) {
			$id13=$parent_id11;
			$name13=$parent_name11;
			$parentid13=$parent_parentid11;
		}
		//echo $name13;
		$stmt->close();
		$conn->close();
		//editCategory($idtoedit);

	}
	else if(isset($_POST['edit_category']))
	{

	}

	
	
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<?php include("new_header.php");?>

</head>
<body>
<?php $page=basename($_SERVER['PHP_SELF']);
include("menubar.php");
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
						<form action="category_add.php" method="post">
							
							<fieldset> 
								<p >
									<label>Category Name</label>
										<input class="text-input small-input" type="text"  name="category_name" value="<?php if(isset($_GET['e_id'])){echo $name12;} else echo " ";?>" /> <span class="input-notification success png_bg">Successful message</span> <!-- Classes for input-notification: success, error, information, attention -->
										
								</p>
								<p >
									<label>Select Parent Category</label>
										<select name="dd_category" id="category-select">
										<option value="<?php if(isset($_GET['e_id'])) echo $name13; else echo " "?>"><?php if(isset($_GET['e_id'])) {echo $name13;} else echo "--Select--";?></option>
											<?php
											 global $category_array;
											 foreach ($category_array as $key => $value) :?>
											 
											<option value="<?php echo $category_array[$key]['name'];?>" data-optionid="<?php echo $category_array[$key]['id'];?>" data-optionparentid="<?php echo $category_array[$key]['parent_id'];?>" ><?php echo $category_array[$key]['name'];?></option>
											<?php endforeach;?>
											
										</select><!-- Classes for input-notification: success, error, information, attention -->
										
								</p>
								<?php if(isset($_GET['e_id']))
								{

								echo "<p>
									<input type='submit' name='edit_category' class='button'>
								</p>";
									} 
									else{
										echo "<p>
									<input type='submit' name='add_category' class='button'>
								</p>";
								}
								?>
								
								</fieldset>
								</form>
					</div>
						</div>

	<?php include("new_footer.php");?>
</body>
</html>