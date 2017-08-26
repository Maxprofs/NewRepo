<?php
global $product;
$product=array();
global $cat_product;
$cat_product=array();
//
	include("config.php");
	if(isset($_GET['page_id']))
	{
		$page_id=$_GET['page_id'];
	}
	else
	{
		$page_id=0;
	}
	
	$stmt=$conn->prepare("SELECT COUNT(*) FROM new_products_table");
	$stmt->bind_result($numbr);
	$stmt->execute();
	
	while($stmt->fetch()) {
		$total_records= $numbr;
		# code...
	}
	$stmt->close();
	$conn->close();
	$total_pages=ceil($total_records/4);
	$limits=4;
	$start=$page_id*$limits;
	echo $total_pages;

//
if(isset($_GET['d_id']))
{
	$idtodel=$_GET['d_id'];
	//$page_to_return=$_GET['page_id'];
	include("config.php");
	$stmt=$conn->prepare(" DELETE FROM new_products_table WHERE id=?");
	$stmt->bind_param("s",$idtodel);
	$stmt->execute();
	$stmt->close();
	$conn->close();
}
else if(isset($_GET['ctgry']))
{
	$Category=$_GET['ctgry'];
	//echo $_GET['ctgry'];
	include("config.php");
$stmt=$conn->prepare("SELECT * FROM new_products_table WHERE category=?");
$stmt->bind_param("s",$Category);

$stmt->bind_result($id13,$name13,$price13,$quantity13,$image13,$category13);
$stmt->execute();

while($stmt->fetch())
{
	array_push($cat_product,array("id"=>$id13,"name"=>$name13,"price"=>$price13,"image"=>$image13,"quantity"=>$quantity13,"category"=>$category13));
	
}
$stmt->close();
$conn->close();

}
?>
<?php if(!isset($_GET['ctgry']))
{
	include("config.php");
$stmt=$conn->prepare("SELECT * FROM new_products_table LIMIT ?,?");
$stmt->bind_param("ii",$start,$limits);
$stmt->bind_result($id1,$name1,$price1,$quantity1,$image1,$category1);
$stmt->execute();
while($stmt->fetch())
{
	array_push($product,array("id"=>$id1,"name"=>$name1,"price"=>$price1,"image"=>$image1,"quantity"=>$quantity1,"category"=>$category1));
}
$stmt->close();
$conn->close();
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
								<td><strong><?php echo $cat_product[$key]['quantity'];?></strong></td>
								<td><img height="90px" width="90px" src='../uploads/<?php echo $cat_product[$key]['image']; ?>'></td>
								<td>
									<!-- Icons -->
									 <a href="form.php?e_id=<?php echo $cat_product[$key]['id'];?>" title="Edit" class="edit_class"><img src="resources/images/icons/pencil.png" alt="Edit" /></a>
									 <a href="manageprod.php?d_id=<?php echo $cat_product[$key]['id'];?>" title="Delete" class="delete_class"><img src="resources/images/icons/cross.png" alt="Delete" /></a> 
									 <a href="#" title="Edit Meta"><img src="resources/images/icons/hammer_screwdriver.png" alt="Edit Meta" /></a>
								</td>
							</tr>
						<?php endforeach;?>
						<?php endif;?>
						<!--default-->
						<?php foreach($product as $key => $value):?>
							<tr>
								<td><input type="checkbox" /></td>
								<td><strong><?php echo $product[$key]['category'];?></strong></td>
								<td><strong><?php echo $product[$key]['id'];?></strong></td>
								<td><strong><?php echo $product[$key]['name'];?></strong></td>
								<td><strong><?php echo $product[$key]['price'];?></strong></td>
								<td><strong><?php echo $product[$key]['quantity'];?></strong></td>
								<td><img height="90px" width="90px" src='../uploads/<?php echo $product[$key]['image']; ?>'></td>
								<td>
									<!-- Icons -->
									 <a href="form.php?e_id=<?php echo $product[$key]['id'];?>" title="Edit" class="edit_class"><img src="resources/images/icons/pencil.png" alt="Edit" /></a>
									 <a href="manageprod.php?d_id=<?php echo $product[$key]['id'];?>&page_id=<?php echo $page_id;?>" title="Delete" class="delete_class"><img src="resources/images/icons/cross.png" alt="Delete" /></a> 
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
					{ var htm=" <a href='manageprod.php?page_id=0' title='First Page'>&laquo; First</a><a href='manageprod.php?page_id=<?php  if($page_id==0) {echo $page_id;} else{echo $page_id-1;}?>' title='Previous Page'>&laquo; Previous</a>";
						for(var i=1;i<='<?php echo $total_pages; ?>';i++)
						{
							htm+="<a href='manageprod.php?page_id="+(i-1)+"' class='number current' title="+i+">"+i+"</a>";
						}
						htm+="<a href='manageprod.php?page_id=<?php  echo $page_id+1;?>' title='Next Page'>Next &raquo;</a><a href='manageprod.php?page_id="+(i-2)+"' title='Last Page'>Last &raquo;</a>";
						$(".pagination").html(htm);
					});
				</script>
				
				</body>
				</html>