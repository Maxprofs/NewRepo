<?php
session_start();
global $cart;
$cart=array();
$cart=$_SESSION['cart'];
echo "<pre>";
print_r($cart);
echo "</pre>";
	include("config.php");
	
	//$stmt=$conn->prepare("SELECT COUNT(*) FROM new_products_table");
	//$stmt->bind_result($numbr);
	//$stmt->execute();
	
	//while($stmt->fetch()) {
		//$total_records= $numbr;
		# code...
	//}
	//$stmt->close();
	//$conn->close();
	$total_records=count($cart);
	$total_pages=ceil($total_records/4);
	$limits=4;
	echo $total_pages;
	
//
?>
<!DOCTYPE html>
<html>
<head>
	<title>

	</title>
	<?php include("header.php");
	?>
</head>
<body>
<div id="main-content">

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
										

										
									</div> <!-- End .pagination -->
									<div class="clear"></div>
								</td>
							</tr>
						</tfoot>
					 
						<tbody>
<?php foreach($cart as $key => $value):?>
	<tr>
						<td><input type="checkbox" /></td>
						<td><strong><?php echo $cart[$key]['category'];?></strong></td>
						<td><strong><?php echo $cart[$key]['id'];?></strong></td>
						<td><strong><?php echo $cart[$key]['name'];?></strong></td>
						<td><strong><?php echo $cart[$key]['price'];?></strong></td>
						<td><strong><?php echo $cart[$key]['quantity'];?></strong></td>
						<td><img height="70px" width="70px" src='../uploads/<?php echo $cart[$key]['image']; ?>'></td>
						<td>
							<!-- Icons -->
							 <a href="form.php?e_id=<?php echo $cart[$key]['id'];?>" title="Edit" class="edit_class"><img src="resources/images/icons/pencil.png" alt="Edit" /></a>
							 <a href="manageprod.php?d_id=<?php echo $cart[$key]['id'];?>" title="Delete" class="delete_class"><img src="resources/images/icons/cross.png" alt="Delete" /></a> 
							 <a href="#" title="Edit Meta"><img src="resources/images/icons/hammer_screwdriver.png" alt="Edit Meta" /></a>
						</td>
					</tr>
				<?php endforeach;?>
				</tbody>
						
					</table>
					</div>
					<script type="text/javascript" src="jQuery.js"></script>
				<script type="text/javascript">
				$(document).ready(function()
					{ var htm=" <a href='checkout.php?page_id=0' title='First Page'>&laquo; First</a><a href='checkout.php?page_id=<?php  if($page_id==0) {echo $page_id;} else{echo $page_id-1;}?>' title='Previous Page'>&laquo; Previous</a>";
						for(var i=1;i<='<?php echo $total_pages; ?>';i++)
						{
							htm+="<a href='checkout.php?page_id="+(i-1)+"' class='number current' title="+i+">"+i+"</a>";
						}
						htm+="<a href='checkout.php?page_id=<?php  echo $page_id+1;?>' title='Next Page'>Next &raquo;</a><a href='checkout.php?page_id="+(i-2)+"' title='Last Page'>Last &raquo;</a>";
						$(".pagination").html(htm);
					});
				</script>
</body>
</html>