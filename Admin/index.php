<?php
global $page,$start;
include("../functions.php");
$category_array=getCategory();
$page=basename($_SERVER['PHP_SELF']);

if(isset($_POST['p_id']) )
{
	//getAllProducts($page);
	addtoCart();
}
else if(isset($_POST['match_category']))
{
	getAllProducts($page);
}	
else
{
	getAllProducts($page);
}		
?>
	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Simpla Admin</title>
<link href="style22.css" type="text/css" rel="stylesheet">
<?php include("header.php");?>
</head>
	<body><div id="body-wrapper"> 
	<?php
	
	?>
	<!-- Wrapper for the radial gradient background -->
		<?php
		include("sidebar.php");
		?>
		 <!-- End #sidebar -->
		
		<div id="main-content">
		 <!-- Main Content Section with everything -->
		 <div style="float:rigth;">
		 <a href="checkout.php"  class="small-input"><img src="resources/store.png" height="30px" width="30px" title="Store"></a>
			
		 <form action="index.php" method="post">
		 	 <p>
									<label>Select Category</label>              
									<select name="p_category" class="small-input">
										<option class="cat" value="<?php if (isset($_POST['match_category'])) echo $_POST['p_category'];?>" ><?php if (isset($_POST['match_category'])) echo $_POST['p_category'];
										else echo "--Select--";?></option>
										<?php global $category_array; 
										foreach ($category_array as $key => $value):?> 
										
										<option class="cat" value="<?php echo $category_array[$key]['name'];?>"><?php echo $category_array[$key]['name'];?></option>
									<?php endforeach;?>
									</select> 
									<input type="submit" name="match_category" value="SHOW" class="button">
								</p>
		 </form>
		
								</div>
		 
			
			
			<!-- Page Head -->
			<h2>Welcome John</h2>
			<p id="page-intro">What would you like to do?</p>
			
			<!-- End .shortcut-buttons-set -->
			
			<div class="clear"></div> <!-- End .clear -->
			
			
					
					<h3>Add Products to cart</h3>
					<div id="main">
		<div id="products">
		<?php global $product_array;  
		foreach ($product_array as $key => $value) :?>
			<div id="<?php echo $product_array[$key]['id']; ?>" class="product">

				<img src="../uploadsnew/<?php echo $product_array[$key]['image']; ?>" width="120px" height="120px">
				<h3 class="title"><a href="#"><?php echo $product_array[$key]['name']; ?></a></h3>
				<h3><?php echo $product_array[$key]['price']; ?></h3><br>
				<span><?php echo $product_array[$key]['id']; ?></span>
				<a class="add-to-cart" name="add_to_cart" data-productid="<?php echo $product_array[$key]['id']; ?>">Add To Cart</a>
			</div>
		<?php endforeach; ?>	
		</div>
		<?php global $total_pages,$page_id;
		//echo $total_pages."<br>".$page_id; ?>
				<div class="pagination" style="float: right;">
											<a href="index.php?page_id=<?php echo 0;?><?php if(isset($_POST['match_category'])){echo '&ctgry='.$_POST['p_category'];} ?><?php if(isset($_GET['ctgry'])){echo '&ctgry='.$_GET['ctgry'];} ?>" title="First Page">&laquo; First</a>

											<a href="index.php?page_id=<?php if($page_id==0){echo 0;}else {echo $page_id-1;}?><?php if(isset($_POST['match_category'])){echo '&ctgry='.$_POST['p_category'];} ?><?php if(isset($_GET['ctgry'])){echo '&ctgry='.$_GET['ctgry'];} ?>" title="Previous Page">&laquo; Previous</a>

											<?php for($i=1;$i<=$total_pages;$i++):?>
											<a href="index.php?page_id=<?php echo $i-1;?><?php if(isset($_POST['match_category'])){echo '&ctgry='.$_POST['p_category'];} ?><?php if(isset($_GET['ctgry'])){echo '&ctgry='.$_GET['ctgry'];} ?>" class="number" title="<?php echo $i; ?>"><?php echo $i;?></a>
											<?php endfor;?>

											<a href="index.php?page_id=<?php if($page_id==$total_pages-1){echo $total_pages-1;} else {echo $page_id+1;}?><?php if(isset($_POST['match_category'])){echo '&ctgry='.$_POST['p_category'];} ?><?php if(isset($_GET['ctgry'])){echo '&ctgry='.$_GET['ctgry'];} ?>" title="Next Page">Next &raquo;</a>

											<a href="index.php?page_id=<?php echo $total_pages-1;?><?php if(isset($_POST['match_category'])){echo '&ctgry='.$_POST['p_category'];} ?><?php if(isset($_GET['ctgry'])){echo '&ctgry='.$_GET['ctgry'];} ?>" title="Last Page">Last &raquo;</a>
										</div>
				
					<div class="clear"></div>
					
				</div> <!-- End .content-box-header -->
				
				 <!-- End .content-box-content -->
				
			</div> 
			
	
			<div class="clear"></div>
			
			<script type="text/javascript" src="resources/jQuery.js"></script>
				<script type="text/javascript">
					
					$(document).ready(function()
						{

							$(".add-to-cart").click(function()
								{
									
									var pid=
									 $(this).data("productid"); 
									 //alert(pid);
									$.ajax({
									  method: "POST",
									  url: "index.php",
									  data: { p_id:pid },
									  dataType:"json"
									})
									 

								});
						
							
						});
				</script>
			<!-- Start Notifications -->
			
			
			<!-- End Notifications -->
			
			<?php include("footer.php");?><!-- End #footer -->
			
		</div> <!-- End #main-content -->
	</div></body>
</html>
