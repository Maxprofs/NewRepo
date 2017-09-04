<?php
	include("../functions.php");
	//global $orders_array;
	//$orders_array=array();
	$page=basename($_SERVER['PHP_SELF']);
	$orders_array= getAllProducts($page);
	//print_r($orders_array);

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
				
					<h3>Orders</h3>
					<div class="clear"></div>
				</div>
				<div class="tab-content default-tab" id="ta"> <!-- This is the target div. id must match the href of this div's tab -->
					
					
					<table>
						
						<thead>
							<tr>
							   <th><input class="check-all" type="checkbox" />
							   </th>
							   <th>User Email</th>
							   <th>Products</th>
							   <th>Date of order</th>
							   <th>Total Price</th>
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
										<a class="button" href="manage_orders.php?<?php if(!empty($_GET['delete_checklist'])):?><?php  foreach ($_GET['delete_checklist'] as $key=>$value) {echo '&delete_checklist[]='
                  .$value;
                  
                }?><?php endif; ?>">Apply to selected</a>
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
						
						<!--default-->
						<?php global $orders_array; 

						foreach($orders_array as $key => $value):?>
							<tr>
								<td><input class="checkbox" type="checkbox" name="delete_checklist[]"/></td>
								<td><strong><?php echo $orders_array[$key]['email'];?></strong></td>

								<td><strong><?php echo $orders_array[$key]['orders'];?></strong></td>
								<td><strong><?php echo $orders_array[$key]['date'];?></strong></td>
								
								<td><strong><?php echo $orders_array[$key]['price'];?></strong></td>
								
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
				
				
				</body>
				</html>