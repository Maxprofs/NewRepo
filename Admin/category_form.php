<?php 
	include("../functions.php");
	$category_array=getCategory();
	
	 if(isset($_POST['add_category'])|| isset($_POST['edit_category']))
		{
			setCategory();
		}
	else if(isset($_GET['e_id']))
	{
		editCategory();	

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
								<p ><?php global $name_c_update,$name_c_p_update;
								?>
									<label>Category Name</label>
										<input class="text-input small-input" type="text"  name="category_name" value="<?php if(isset($_GET['e_id'])){echo $name_c_update;} else echo " ";?>" /> <span class="input-notification success png_bg">Successful message</span> <!-- Classes for input-notification: success, error, information, attention -->
										
								</p>
								<p >
									<label>Select Parent Category</label>
										<select name="dd_category" id="category-select">
										<option value="<?php if(isset($_GET['e_id'])) echo $name_c_p_update; else echo " "?>"><?php if(isset($_GET['e_id'])) {echo $name_c_p_update;} else echo "--Select--";?></option>
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

	<?php //include("footer.php");?>
</body>
</html>