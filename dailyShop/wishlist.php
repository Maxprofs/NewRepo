<?php
include("../functions.php");
 global $wish_array;
$wish_array=array();
$wish_array=$_SESSION['wishlist'];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    <title>Daily Shop | Wishlist Page</title>
    
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
 <?php include("menubar.php");?>
  <!-- catg header banner section -->
  <section id="aa-catg-head-banner">
   <img src="img/fashion/fashion-header-bg-8.jpg" alt="fashion img">
   <div class="aa-catg-head-banner-area">
     <div class="container">
      <div class="aa-catg-head-banner-content">
        <h2>Wishlist Page</h2>
        <ol class="breadcrumb">
          <li><a href="UserPageIndex.php">Home</a></li>                   
          <li class="active">Wishlist</li>
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
           <div class="cart-view-table aa-wishlist-table">
             <form action="">
               <div class="table-responsive">
                  <table class="table">
                    <thead>
                      <tr>
                        <th></th>
                        <th></th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Stock Status</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php global $wish_array;
              
                    foreach ($wish_array as $key => $value):
                    ?>
                      <tr>
                        <td><a class="remove" href="#"><fa class="fa fa-close"></fa></a></td>
                        <td><a href="#"><img src="../uploadsnew/<?php echo $wish_array[$key]['image'];?>" alt="img"></a></td>
                        <td><a class="aa-cart-title" href="#"><?php echo $wish_array[$key]['name'];?></a></td>
                        <td><?php echo $wish_array[$key]['price'];?></td>
                        <td>In Stock</td>
                        <td><a href="#" name="add_to_cart_btn" class="aa-add-to-cart-btn">Add To Cart</a></td>
                      </tr>
                    <?php endforeach;?>
                                      
                      </tbody>
                  </table>
                </div>
             </form>             
           </div>
         </div>
       </div>
     </div>
   </div>
 </section>
 <!-- / Cart view section -->


 <?php include("new_footer.php");?>
  </body>
</html>