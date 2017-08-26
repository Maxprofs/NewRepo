<?php
session_start();
global $cart;
$cart=array();
global $product1;
$product1=array();
global $c_product;
$c_product=array();

if(isset($_POST['p_id']))
				{
					$p=$_POST['p_id'];
					include("config.php");
					$stmt=$conn->prepare("SELECT * FROM new_products_table WHERE id=?");

					if ( false === $stmt ) {
						return false;
					}
					$stmt->bind_param("s",$p);

					if ( false === $params) {
						return false;
					}
					$stmt->execute();

					if ( false === $execute) {
						return false;
					}
					$stmt->bind_result($id12,$name12,$price12,$quantity12,$image12,$category12);
					while($stmt->fetch())
					{
						masterFun($id12,$name12,$price12,$image12,$category12,$cart);
					}
					
					$stmt->close();
					$conn->close();
					echo json_encode($p);
					//header("loacation:index.php");
				}
				else if (isset($_POST['match_category'])) 
				{
					$catg_to_show=$_POST['p_category'];
					echo $catg_to_show;
					include("config.php");
					$stmt=$conn->prepare("SELECT * FROM new_products_table WHERE category=?");
					$stmt->bind_param("s",$catg_to_show);
					$stmt->bind_result($id14,$name14,$price14,$quantity14,$image14,$category14);
					$stmt->execute();
					while($stmt->fetch())
					{
						array_push($product1,array("id"=>$id14,"name"=>$name14,"price"=>$price14,"image"=>$image14,"quantity"=>$quantity14,"category"=>$category14));
					}
					$stmt->close();
					$conn->close();
					//$_SESSION['prod']=$product1;
					//$product1=$_SESSION['prod'];
					//header("location:index.php");
					//echo json_encode(array("prod"=>$product1));
				}

				else
				{
				include("config.php");
				$stmt=$conn->prepare("SELECT * FROM new_products_table");
				$stmt->bind_result($id11,$name11,$price11,$quantity11,$image11,$category11);
				$stmt->execute();
				while($stmt->fetch())
				{
					array_push($product1,array("id"=>$id11,"name"=>$name11,"price"=>$price11,"image"=>$image11,"quantity"=>$quantity11,"category"=>$category11));
				}
				$stmt->close();
				$conn->close();

				}


function masterFun($id_c,$name_c,$price_c,$image_c,$category_c,$cart)
{
	if(isset($_SESSION['cart']))
	{
		$cart=$_SESSION['cart'];
		if(isExist($id_c,$cart))
		{
			$cart=updateProd($id_c,$cart);
			$_SESSION['cart']=$cart;
			$cart=$_SESSION['cart'];
			echo json_encode(array("arraycart"=>$cart));
		}
		else
		{
		array_push($cart,array("id"=>$id_c,"name"=>$name_c,"price"=>$price_c,"image"=>$image_c,"category"=>$category_c,"quantity"=>1));
		$_SESSION['cart']=$cart;
		$cart=$_SESSION['cart'];
		echo json_encode(array("arraycart"=>$cart));
		}
	}
	else
	{
		array_push($cart,array("id"=>$id_c,"name"=>$name_c,"price"=>$price_c,"image"=>$image_c,"category"=>$category_c,"quantity"=>1));
		$_SESSION['cart']=$cart;
		$cart=$_SESSION['cart'];
		echo json_encode(array("arraycart"=>$cart));
	}
}
function isExist($id_c,$cart)
{
	foreach($cart as $key=>$value)
	{
		if($id_c==$cart[$key]['id'])
		{
			return true;
		}
	}
	return false;
}
function updateProd($id_c,$cart)
{
	foreach($cart as $key=>$value)
	{
		if($id_c==$cart[$key]['id'])
		{
			$cart[$key]['quantity']=$cart[$key]['quantity']+1;
		}
	}
	return $cart;

}

//by default


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
	$page=basename($_SERVER['PHP_SELF']);
	?>
	<!-- Wrapper for the radial gradient background -->
		<?php
		include("sidebar.php");
		?>
		 <!-- End #sidebar -->
		
		<div id="main-content">
		 <!-- Main Content Section with everything -->
		 <div style="float:rigth;">
		 <a href="checkout.php"  class="small-input"><img src="store.png" height="30px" width="30px" title="Store"></a>
			
		 <form action="index.php" method="post">
		 	 <p>
									<label>Select Category</label>              
									<select name="p_category" class="small-input">
										<option class="cat" value="<?php if (isset($_POST['match_category'])) echo $_POST['p_category'];?>" ><?php if (isset($_POST['match_category'])) echo $_POST['p_category'];
										else echo "--Select--";?></option>
										<option class="cat" value="sports">Sports</option>
										<option class="cat" value="automobiles">Automobiles</option>
										<option class="cat" value="electronics">Electronics</option>
										<option class="cat" value="fashion">Fashion</option>
										<option class="cat" value="cosmetics">Cosmetics</option>
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
		<?php  foreach ($product1 as $key => $value) :?>
			<div id="<?php echo $product1[$key]['id']; ?>" class="product">

				<img src="../uploads/<?php echo $product1[$key]['image']; ?>" width="64px" height="64px">
				<h3 class="title"><a href="#"><?php echo $product1[$key]['name']; ?></a></h3>
				<h3><?php echo $product1[$key]['price']; ?></h3><br>
				<span><?php echo $product1[$key]['id']; ?></span>
				<a class="add-to-cart" name="add_to_cart" data-productid="<?php echo $product1[$key]['id']; ?>">Add To Cart</a>
			</div>
		<?php endforeach; ?>	
		</div>

				
					<div class="clear"></div>
					
				</div> <!-- End .content-box-header -->
				
				 <!-- End .content-box-content -->
				
			</div> 
			
	
			<div class="clear"></div>
			
			<script type="text/javascript" src="jQuery.js"></script>
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
<!--

		$(".small-input").change(function(){
								
								var s_value=
									 $(this).val(); 
									 //alert(s_value);
									$.ajax({
									  method: "POST",
									  url: "index.php",
									  data: {drop_category:s_value},
									  dataType:"json"
									})
									.done(function(msg)
									{
										alert(msg.prod);
									});
							});

							-->
