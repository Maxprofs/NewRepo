<?php
//session_start();
include("../functions.php");

getCategory();
if(isset($_POST['add_product']))

{
	addProduct();
	
}
 else if(isset($_GET['e_id']))

{
	include("config.php");
	$idtoedit=$_GET['e_id'];
	include("config.php");
	$stmt=$conn->prepare(" SELECT * FROM product_table  WHERE id=?");
	$stmt->bind_param("s",$idtoedit);
	$stmt->execute();
	$stmt->bind_result($id2,$name2,$price2,$quantity2,$image2,$category2);

	while($stmt->fetch()) {

	    $id11= $id2; 
	    $name11= $name2;
	    $price11 = $price2;
	    $quantity11 = $quantity2;
	    $image11= $image2;
	    $category11 = $category2;
	}
				 $stmt->close();
   		         $conn->close();

}
	



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>add product</title>
<?php include("new_header.php");?>
</head>
<body><div id="body-wrapper">
 <!-- End #sidebar -->
		<?php
	$page=basename($_SERVER['PHP_SELF']);
	?>
		<?php include("menubar.php");?>
		
		<div id="main-content"> <!-- Main Content Section with everything -->
			
			<h2>Welcome John</h2>
			<p id="page-intro">What would you like to do?</p>
			<div class="clear"></div>
					<div class="content-box"><!-- Start Content Box -->
				
				<div class="content-box-header">
					<h3>Add New Product</h3>
					<div class="clear"></div>
					</div>

					<div class="tab-content1" id="t">
					
						<form action="add_product.php" method="post" enctype="multipart/form-data">
							
							<fieldset> <!-- Set class to "column-left" or "column-right" on fieldsets to divide the form into columns -->
								
								<p >
									<label>Product Id</label>
										<input class="text-input small-input" type="text" id="idp" name="p_id" value="<?php if(isset($_GET['e_id'])){echo $id11;}  ?>"/> <span class="input-notification success png_bg">Successful message</span> <!-- Classes for input-notification: success, error, information, attention -->
										<br /><small>A small description of the field</small>
								</p>
								
								<p>
									<label>Product Name</label>
									<input class="text-input medium-input datepicker" type="text" id="namep" name="p_name" value="<?php if(isset($_GET['e_id'])){echo $name11;}  ?>"/> <span class="input-notification error png_bg">Error message</span>
								</p>
								
								<p>
									<label>Product Price</label>
									<input class="text-input medium-input datepicker" type="text" id="pricep" name="p_price" value="<?php if(isset($_GET['e_id'])){echo $price11;}  ?>"/>
								</p>
								
								<p>
									<label>Product Image</label>
									<input type="file" id="imagep" name="p_image" value="<?php if(isset($_GET['e_id'])){echo $image11;}  ?>"/>
								</p>
								<p>
									<label>Select Category</label>              
									<select name="p_category" class="small-input" value=" ">
										<option value="<?php if(isset($_GET['e_id'])){echo $category11;}  ?>"><?php if(isset($_GET['e_id'])){echo $category11;}  else echo"--Select--";?></option>
										<?php
											 global $category_array;
											 foreach ($category_array as $key => $value) :?>
											 
											<option value="<?php echo $category_array[$key]['name'];?>" data-optionid="<?php echo $category_array[$key]['id'];?>" data-optionparentid="<?php echo $category_array[$key]['parent_id'];?>" ><?php echo $category_array[$key]['name'];?></option>
											<?php endforeach;?>
									</select> 
								</p>
								<?php if(isset($_SESSION['message'])):?>
							<div class="notification success png_bg">
								<a href="#" class="close"><img src="resources/images/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>
								<div>
								Success notification. Product has been added successfuly
								</div>
								</div>
						<?php unset($_SESSION['message']); endif;?>
						<?php if(isset($_SESSION['error'])):?>
							<div class="notification error png_bg">
								<a href="#" class="close"><img src="resources/images/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>
								<div>
									Error notification. Something went wrong;
								</div>
							</div>
							<?php unset($_SESSION['error']); endif;?>
							<?php if(isset($_GET['e_id'])){
										echo "<input type='hidden' name='hidden_eid' value='".$_GET['e_id']."'>";
												}
										?>
								<?php if(isset($_GET['e_id'])){
										echo "<p>
									<input class='button' type='submit' value='UPDATE' name='edit_product'/>
								</p>";

									} 
									else{
										echo "<p>

									<input class='button' type='submit' value='Add Product' name='add_product'/>
								</p>";
									} 
								
								?>
								
							</fieldset>
							
							<!-- End .clear -->
							
						</form>
						
						
					</div>
						</div>
						</div>
				
					<?php include("new_footer.php");?>
				</div>
				
				
				
				</body>
				</html>
			