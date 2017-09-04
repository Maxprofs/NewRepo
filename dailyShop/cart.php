<?php
//session_start();
include("../functions.php");
global $price1,$new;
$price1=0;

$cart=$_SESSION['cart'];

  if(isset($_GET['d_id']))
  { 
    deleteFromCart();

  }
  else if(isset($_POST['update_cart']))
  {
    //echo 3213621;
    editQuantity();

  }
 
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    <title>Daily Shop | Cart Page</title>
    
    <!-- Font awesome -->
    <?php include("new_header.php");?>

  </head>
  <body>
   
   <!-- wpf loader Two -->
    <div id="wpf-loader-two">          
      <div class="wpf-loader-two-inner">
        <span>Loading</span>
      </div>
    </div> 
    <!-- / wpf loader Two -->       
 <!-- SCROLL TOP BUTTON -->
    <a class="scrollToTop" href="#"><i class="fa fa-chevron-up"></i></a>
  <!-- END SCROLL TOP BUTTON -->
  <?php include("menubar.php");?>

 
  <!-- catg header banner section -->
  <section id="aa-catg-head-banner">
   <img src="img/fashion/fashion-header-bg-8.jpg" alt="fashion img">
   <div class="aa-catg-head-banner-area">
     <div class="container">
      <div class="aa-catg-head-banner-content">
        <h2>Cart Page</h2>
        <ol class="breadcrumb">
          <li><a href="UserPageIndex.php">Home</a></li>                       
          <li class="active">Cart</li>
        </ol>
      </div>
     </div>
   </div>
  </section>
  <!-- / catg header banner section -->

 <!-- Cart view section -->
 <section id="cart-view">
   <div class="container">
     <div class="row">
       <div class="col-md-12">
         <div class="cart-view-area">
           <div class="cart-view-table">
             <form action="cart.php" method="post">
               <div class="table-responsive">
                  <table class="table">
                    <thead>
                      <tr>
                        <th></th>
                        <th></th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php global $cart;
                    $Subtotal=0;
                    $Grandtotal=0;
                    $c=0;
                    foreach ($cart as $key => $value):
                    $total=$cart[$key]['price']* $cart[$key]['quantity'];
                  $Subtotal=$Subtotal+$cart[$key]['price'];
                  $Grandtotal=$Grandtotal+$total;
                  $c++;
                      ?>
                      
                      <tr>
                        <td><a class="remove" href="cart.php?d_id=<?php echo $cart[$key]['id'];?>"><fa class="fa fa-close"></fa></a></td>
                        <td><a href="#"><img src="../uploadsnew/<?php echo $cart[$key]['image'];?>" alt="img"></a></td>
                        <td><a class="aa-cart-title" href="#"><?php echo $cart[$key]['name'];?></a></td>
                        <td><?php echo $cart[$key]['price'];?></td>
                        <td><input name="u_quantity[]" class="aa-cart-quantity" type="number" value="<?php echo $cart[$key]['quantity'];?>"></td>
                        <input type="hidden" name="ids[]" value="<?php echo $cart[$key]['id'];?>">
                        <td><?php echo $total?></td>

                      </tr>
                    <?php endforeach; $_SESSION['total_products']=$c;?>:
                      
                      <tr>
                        <td colspan="6" class="aa-cart-view-bottom">
                          <div class="aa-cart-coupon">
                            <input class="aa-coupon-code" type="text" placeholder="Coupon">
                            <input class="aa-cart-view-btn" type="submit" value="Apply Coupon">
                          
                          </div>
                            <input name="update_cart" class="aa-cart-view-btn" type="submit" value="Update Cart">
                        </td>
                      </tr>
                      </tbody>
                  </table>
                </div>

             </form>
             <!-- Cart Total view -->
             <div class="cart-view-total">
               <h4>Cart Totals</h4>
               <table class="aa-totals-table">
                 <tbody>
                   <tr>
                     <th>Subtotal</th>
                     <td><?php echo $Subtotal;?></td>
                   </tr>
                   <tr>
                     <th>Total</th>
                     <td><?php echo $Grandtotal;
                     ?></td>
                   </tr>
                 </tbody>
               </table>
               <a href="checkout.php" class="aa-cart-view-btn">Proceed to Checkout</a>
             </div>
           </div>
         </div>
       </div>
     </div>
   </div>
 </section>
 <!-- / Cart view section -->




  <!-- footer -->  
  <?php include("new_footer.php");?>

  </body>
</html>