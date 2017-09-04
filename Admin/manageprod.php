<?php
global $product;
$product=array();
global $cat_product;
$cat_product=array();

$page=basename($_SERVER['PHP_SELF']);
	include("../functions.php");
	if(isset($_GET['d_id']))
	{
		deleteProduct();
	}
	getAllProducts($page);
	//$start=getNumberOfProducts();
	//$product_array=getCategoryFilter($start);
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Simpla Admin</title>
<?php include("header.php");?>
</head>
	<body><div id="body-wrapper">
	 <!-- Wrapper for the radial gradient background -->
		<?php
		
		include("sidebar.php");
		?>
		<div id="main-content"> <!-- Main Content Section with everything -->
			
			
			
			<!-- Page Head -->
			<h2>Welcome John</h2>
			<p id="page-intro">What would you like to do?</p>
			<div class="clear"></div>
				
				
					<div class="content-box"><!-- Start Content Box -->
				
				<div class="content-box-header">
				
					<h3>Products</h3>
					<div class="clear"></div>
				</div>
				<div class="tab-content default-tab" id="ta"> <!-- This is the target div. id must match the href of this div's tab -->
					
					
					<table>
						
						<thead>
							<tr>
							   <th><input class="check-all" type="checkbox" />
							   </th>
							   <th>Category</th>
							   <th>Product Id</th>
							   <th>Product Name</th>
							   <th>Product Price</th>
							   <th>Product Image</th>
							   <th>Operation</th>
							</tr>
							
						</thead>
					 
						<tfoot>
							<tr>
								<td colspan="6">
									<div class="bulk-actions align-left">
										<select name="dropdown">
											<option value="option1">Choose an action...</option>
											<option value="option2">Edit</option>
											<option value="option3">Delete</option>
										</select>
										<a class="button" href="#">Apply to selected</a>
									</div>
									
									
										
										<div class="pagination">
										<a class="previous" href="#">&laquo; Previous</a>
										<?php for($i=1;$i<=$total_pages;$i++) :?>
										<a href="#"><?php echo $i;?></a>
									<?php endfor;?>
									<a href="#"> &raquo;Next</a>
										</div>
									 
									<div class="clear"></div>
								</td>
							</tr>
						</tfoot>
					 
						<tbody>
						<?php if(isset($_GET['ctgry'])):?>
						<?php foreach($cat_product as $key => $value):?>
							<tr>
								<td><input type="checkbox" /></td>
								<td><strong><?php echo $cat_product[$key]['category'];?></strong></td>
								<td><strong><?php echo $cat_product[$key]['id'];?></strong></td>
								<td><strong><?php echo $cat_product[$key]['name'];?></strong></td>
								<td><strong><?php echo $cat_product[$key]['price'];?></strong></td>
								
								<td><img height="120px" width="120px" src='../uploadsnew/<?php echo $cat_product[$key]['image']; ?>'></td>
								<td>
									<!-- Icons -->
									 <a href="form.php?e_id=<?php echo $cat_product[$key]['id'];?>&page_id=<?php echo $page_id;?>" title="Edit" class="edit_class"><img src="resources/images/icons/pencil.png" alt="Edit" /></a>
									 <a href="manageprod.php?d_id=<?php echo $cat_product[$key]['id'];?>&page_id=<?php echo $page_id;?>" title="Delete" class="delete_class"><img src="resources/images/icons/cross.png" alt="Delete" /></a> 
									 <a href="#" title="Edit Meta"><img src="resources/images/icons/hammer_screwdriver.png" alt="Edit Meta" /></a>
								</td>
							</tr>
						<?php endforeach;?>
						<?php endif;?>
						<!--default-->
						<?php global $product_array;
						foreach($product_array as $key => $value):?>
							<tr>
								<td><input type="checkbox" /></td>
								<td><strong><?php echo $product_array[$key]['category'];?></strong></td>
								<td><strong><?php echo $product_array[$key]['id'];?></strong></td>
								<td><strong><?php echo $product_array[$key]['name'];?></strong></td>
								<td><strong><?php echo $product_array[$key]['price'];?></strong></td>
								
								<td><img height="120px" width="120px" src='../uploadsnew/<?php echo $product_array[$key]['image']; ?>'></td>
								<td>
									<!-- Icons -->
									 <a href="form.php?e_id=<?php echo $product_array[$key]['id'];?>&page_id=<?php echo $page_id;?>" title="Edit" class="edit_class"><img src="resources/images/icons/pencil.png" alt="Edit" /></a>
									 <a href="manageprod.php?d_id=<?php echo $product_array[$key]['id'];?>&page_id=<?php echo $page_id;?>" title="Delete" class="delete_class"><img src="resources/images/icons/cross.png" alt="Delete" /></a> 
									 <a href="#" title="Edit Meta"><img src="resources/images/icons/hammer_screwdriver.png" alt="Edit Meta" /></a>
								</td>
							</tr>
						<?php endforeach;?>
						<!--jfkff-->
						</tbody>
						
					</table>
					
				</div> 
				</div>
				<div class="clear"></div>
				</div>

				<?php include("footer.php");?>
				</div>
				<script type="text/javascript" src="jQuery.js"></script>
				<script type="text/javascript">
				$(document).ready(function()
					{ var htm=" <a href='manageprod.php?page_id=0<?php if(isset($_GET['ctgry'])){echo '&ctgry='.$_GET['ctgry'];}?>' title='First Page'>&laquo; First</a><a href='manageprod.php?page_id=<?php  if($page_id==0) {echo $page_id;} else{echo $page_id-1;}?><?php if(isset($_GET['ctgry'])){echo '&ctgry='.$_GET['ctgry'];}?>' title='Previous Page'>&laquo; Previous</a>";
						for(var i=1;i<='<?php echo $total_pages; ?>';i++)
						{
							htm+="<a href='manageprod.php?page_id="+(i-1)+"<?php if(isset($_GET['ctgry'])){echo '&ctgry='.$_GET['ctgry'];}?>' class='number current' title="+i+">"+i+"</a>";
						}
						htm+="<a href='manageprod.php?page_id=<?php  echo $page_id+1;?><?php if(isset($_GET['ctgry'])){echo '&ctgry='.$_GET['ctgry'];}?>' title='Next Page'>Next &raquo;</a><a href='manageprod.php?<?php if($page_id==($total_pages-1)){echo 'page_id='.($total_pages-1);}else ?>page_id="+(i-2)+"<?php if(isset($_GET['ctgry'])){echo '&ctgry='.$_GET['ctgry'];}?>' title='Last Page'>Last &raquo;</a>";
						$(".pagination").html(htm);
					});
				</script>
				
				</body>
				</html>