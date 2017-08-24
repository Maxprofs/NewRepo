<?php
global $product;
$product=array();
if(isset($_GET['d_id']))
{
	$idtodel=$_GET['d_id'];
	include("config.php");
	$stmt=$conn->prepare(" DELETE FROM new_products_table WHERE id=?");
	$stmt->bind_param("s",$idtodel);
	$stmt->execute();
	$stmt->close();
	$conn->close();
}



include("config.php");
$stmt=$conn->prepare("SELECT * FROM new_products_table");
$stmt->bind_result($id1,$name1,$price1,$quantity1,$image1,$category1);
$stmt->execute();
while($stmt->fetch())
{
	array_push($product,array("id"=>$id1,"name"=>$name1,"price"=>$price1,"image"=>$image1,"quantity"=>$quantity1,"category"=>$category1));
}

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
		$page=basename($_SERVER['PHP_SELF']);
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
							   <th>Product Quantity</th>
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
										<a href="#" title="First Page">&laquo; First</a><a href="#" title="Previous Page">&laquo; Previous</a>
										<a href="#" class="number" title="1">1</a>
										<a href="#" class="number" title="2">2</a>
										<a href="#" class="number current" title="3">3</a>
										<a href="#" class="number" title="4">4</a>
										<a href="#" title="Next Page">Next &raquo;</a><a href="#" title="Last Page">Last &raquo;</a>
									</div> <!-- End .pagination -->
									<div class="clear"></div>
								</td>
							</tr>
						</tfoot>
					 
						<tbody>
						<?php foreach($product as $key => $value):?>
							<tr>
								<td><input type="checkbox" /></td>
								<td><strong><?php echo $product[$key]['category'];?></strong></td>
								<td><strong><?php echo $product[$key]['id'];?></strong></td>
								<td><strong><?php echo $product[$key]['name'];?></strong></td>
								<td><strong><?php echo $product[$key]['price'];?></strong></td>
								<td><strong><?php echo $product[$key]['quantity'];?></strong></td>
								<td><img height="70px" width="70px" src='../uploads/<?php echo $product[$key]['image']; ?>'></td>
								<td>
									<!-- Icons -->
									 <a href="form.php?e_id=<?php echo $product[$key]['id'];?>" title="Edit" class="edit_class"><img src="resources/images/icons/pencil.png" alt="Edit" /></a>
									 <a href="manageprod.php?d_id=<?php echo $product[$key]['id'];?>" title="Delete" class="delete_class"><img src="resources/images/icons/cross.png" alt="Delete" /></a> 
									 <a href="#" title="Edit Meta"><img src="resources/images/icons/hammer_screwdriver.png" alt="Edit Meta" /></a>
								</td>
							</tr>
						<?php endforeach;?>
						
						</tbody>
						
					</table>
					
				</div> 
				</div>
				
				</div>
				<?php include("footer.php");?>
				</div>

				
				</body>
				</html>