<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Simpla Admin</title>
<?php include("header.php");?>
</head>
<body><div id="body-wrapper">
 <!-- End #sidebar -->
		<?php
	$page=basename($_SERVER['PHP_SELF']);
	?>
		<?php include("sidebar.php");?>
		
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
					
						<form action="add_prod.php" method="post" enctype="multipart/form-data">
							
							<fieldset> <!-- Set class to "column-left" or "column-right" on fieldsets to divide the form into columns -->
								
								<p >
									<label>Product Id</label>
										<input class="text-input small-input" type="text" id="idp" name="p_id" /> <span class="input-notification success png_bg">Successful message</span> <!-- Classes for input-notification: success, error, information, attention -->
										<br /><small>A small description of the field</small>
								</p>
								
								<p>
									<label>Product Name</label>
									<input class="text-input medium-input datepicker" type="text" id="namep" name="p_name" /> <span class="input-notification error png_bg">Error message</span>
								</p>
								
								<p>
									<label>Product Price</label>
									<input class="text-input medium-input datepicker" type="text" id="pricep" name="p_price" />
								</p>
								<p>
									<label>Product Quantity</label>
									<input class="text-input medium-input datepicker" type="text" id="quantityp" name="p_quantity" />
								</p>
								<p>
									<label>Product Image</label>
									<input type="file" id="imagep" name="p_image" />
								</p>
								<p>
									<label>Select Category</label>              
									<select name="p_category" class="small-input">
										<option value=" ">--Select--</option>
										<option value="sports">Sports</option>
										<option value="automobiles">Automobiles</option>
										<option value="electronics">Electronics</option>
										<option value="cosmetics">Cosmetics</option>
									</select> 
								</p>
							
								
								<p>
									<input class="button" type="submit" value="Add Product" name="add_product"/>
								</p>
								
							</fieldset>
							
							<!-- End .clear -->
							
						</form>
						
					</div>
						</div>
						</div>
				
					<?php include("footer.php");?>
				</div>
				
				
				
				</body>
				</html>