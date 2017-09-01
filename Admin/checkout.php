<?php
//session_start();
include("../functions.php");
global $cart,$price1;
$price1=0;
$cart=$_SESSION['cart'];

	if(isset($_GET['d_id']))
	{ 
		deleteFromCart();

	}
	else if(isset($_POST['edit_quant']))
	{
		
		editQuantity();

	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>

	</title>
	<style type="text/css">
		th,td
		{
			font-size: 20px;
		}
		#edit
		{
			
  			 background: url(resources/images/icons/pencil.png) no-repeat;
  			 height: 30px;
  			 width:30px;

		}
	</style>
	<?php
	$page=basename($_SERVER['PHP_SELF']);
	?>
	<?php include("header.php");
	?>
</head>
<body>
<?php include("sidebar.php");
	?>
<div id="container">
<div style="float: right;">
	<a href="index.php"  class="button"><img src="resources/home.png" height="30px" width="30px" title="Home"></a>
	<a href="#"  class=""><img src="resources/store.png" height="30px" width="30px" title="Place Order"></a>
</div>
<div id="main-content">
<form action="checkout.php" method="post">
<table>
						
						<thead >
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
									

<?php global $total_pages,$page_id;?>
				<div class="pagination" style="float: right;">
											<a href="index.php?page_id=<?php echo 0;?>" title="First Page">&laquo; First</a>

											<a href="index.php?page_id=<?php if($page_id==0){echo 0;}else {echo $page_id-1;}?>" title="Previous Page">&laquo; Previous</a>

											<?php for($i=1;$i<=$total_pages;$i++):?>
											<a href="index.php?page_id=<?php echo $i-1;?>" class="number" title="<?php echo $i; ?>"><?php echo $i;?></a>
											<?php endfor;?>

											<a href="index.php?page_id=<?php if($page_id==$total_pages-1){echo $total_pages-1;} else {echo $page_id+1;}?>" title="Next Page">Next &raquo;</a>

											<a href="index.php?page_id=<?php echo $total_pages-1;?>" title="Last Page">Last &raquo;</a>
										</div>
				
					<div class="clear"></div>

									<!-- End .pagination -->
									<div class="clear"></div>
								</td>
							</tr>
						</tfoot>
						<tbody>
<?php global $price1,$cart;
foreach($cart as $key => $value):?>
	<tr>					
						<?php 
						$price1+=$cart[$key]['quantity']*$cart[$key]['price'];
						?>
						<td><input type="checkbox" /></td>
						<td><strong><?php echo $cart[$key]['category'];?></strong></td>
						<td><strong><?php echo $cart[$key]['id'];?></strong></td>
						<td><strong><?php echo $cart[$key]['name'];?></strong></td>
						<td><strong><?php echo $cart[$key]['price'];?></strong></td>
						<td><strong><input type="number" name="quant_field" value="<?php echo $cart[$key]['quantity'];?>"></strong></td>
						<td><img height="150px" width="100px" src='../uploadsnew/<?php echo $cart[$key]['image']; ?>'></td>
						<td>
							<!-- Icons///a href="checkout.php?e_id=<?php //echo $cart[$key]['id'];?>" -->
							<input type="hidden" value="<?php echo $cart[$key]['id'];?>" name="hidden_field">
							 <input type="submit" name="edit_quant" id="edit" title="Edit" value=" ">
							 <a href="checkout.php?d_id=<?php echo $cart[$key]['id'];?>" title="Delete" class="delete_class"><img src="resources/images/icons/cross.png" alt="Delete" /></a> 
							 <a href="#" title="Edit Meta"><img src="resources/images/icons/hammer_screwdriver.png" alt="Edit Meta" /></a>
						</td>
					</tr>
				<?php endforeach; $_SESSION['total_price']=$price1;?>
				</tbody>
						
					</table>
					</form>
					</div>
					</div>


				
</body>
</html>
<!--


	<script type="text/javascript" src="jQuery.js"></script>
				<script type="text/javascript">
				$(document).ready(function()
					{ 
						var htm=" <a href='checkout.php?page_id=0' title='First Page'>&laquo; First</a><a href='checkout.php?page_id=<?//php  if($page_id==0) {echo $page_id;} else{echo $page_id-1;}?>' title='Previous Page'>&laquo; Previous</a>";
						for(var i=1;i<='<?php //echo $total_pages; ?>';i++)
						{
							htm+="<a href='checkout.php?page_id="+(i-1)+"' class='number current' title="+i+">"+i+"</a>";
						}
						htm+="<a href='checkout.php?page_id=<?php // echo $page_id+1;?>' title='Next Page'>Next &raquo;</a><a href='checkout.php?page_id="+(i-2)+"' title='Last Page'>Last &raquo;</a>";
						$(".pagination").html(htm);
					});
				</script>
				-->