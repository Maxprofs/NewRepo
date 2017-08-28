<?php
include("config.php");
global $product_array;
$product_array=array();
$limits=6;
if(!isset($_GET["page_id"]))
{
	$page_id=0;
}
else
{
	$page_id=$_GET["page_id"];
}
$stmt=$conn->prepare("SELECT COUNT(*) FROM product_table");
	$stmt->bind_result($numbr);
	$stmt->execute();
	
	while($stmt->fetch()) {
		$total_records= $numbr;
		# code...
	}
	//$stmt->close();
	//$conn->close();
	$total_pages=ceil($total_records/6);
$start=$page_id*$limits;
$stmt=$conn->prepare("SELECT * FROM product_table LIMIT ?,? "); 
$stmt->bind_param("ii",$start,$limits);
$stmt->execute();
$res=$stmt->bind_result($idp,$namep,$pricep,$imagep,$categoryp);
if($res==false)
{
  echo "no result binded";
}
while($stmt->fetch())
{
  array_push($product_array, array("id"=>$idp,"name"=>$namep,"price"=>$pricep,"image"=>$imagep,"category"=>$categoryp));
}
?>
<!DOCTYPE html>
<html>
<head>

	<title></title>
	<style type="text/css">
		#main-content .pagination { float: left;
                text-align: right;
                padding: 40px 20 10px 0;
                font-family: Verdana, Arial, Helvetica, sans-serif;
                font-size: 10px;
                }
.pagination a {
                margin: 0 30px 30px;
                padding: 20px 20px;
                }

.pagination a.number {
        border: 1px solid #ddd;
                }

.pagination a.current {
                background: #469400 url('../images/bg-button-green.gif') top left repeat-x !important;
                border-color: #459300 !important;
                color: #fff !important;
                }
        
.pagination a.current:hover {
        text-decoration: underline;
                }
	</style>
	<?php include("new_header.php");?>
</head>
<body>
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
<div id="main-content">
<div class="container">
<section id="aa-slider">
    <div class="aa-slider-area">
      <div id="sequence" class="seq">
        <div class="seq-screen">
          <ul class="seq-canvas">
            <!-- single slide item -->
            <li>
              <div class="seq-model">
                <img data-seq src="img/slider/1.jpg" alt="Men slide img" />
              </div>
              <div class="seq-title">
               <span data-seq>Save Up to 75% Off</span>                
                <h2 data-seq>Men Collection</h2>                
                <p data-seq>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Minus, illum.</p>
                <a data-seq href="#" class="aa-shop-now-btn aa-secondary-btn">SHOP NOW</a>
              </div>
            </li>
            <!-- single slide item -->
            <li>
              <div class="seq-model">
                <img data-seq src="img/slider/2.jpg" alt="Wristwatch slide img" />
              </div>
              <div class="seq-title">
                <span data-seq>Save Up to 40% Off</span>                
                <h2 data-seq>Wristwatch Collection</h2>                
                <p data-seq>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Minus, illum.</p>
                <a data-seq href="#" class="aa-shop-now-btn aa-secondary-btn">SHOP NOW</a>
              </div>
            </li>
            <!-- single slide item -->
            <li>
              <div class="seq-model">
                <img data-seq src="img/slider/3.jpg" alt="Women Jeans slide img" />
              </div>
              <div class="seq-title">
                <span data-seq>Save Up to 75% Off</span>                
                <h2 data-seq>Jeans Collection</h2>                
                <p data-seq>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Minus, illum.</p>
                <a data-seq href="#" class="aa-shop-now-btn aa-secondary-btn">SHOP NOW</a>
              </div>
            </li>
            <!-- single slide item -->           
            <li>
              <div class="seq-model">
                <img data-seq src="img/slider/4.jpg" alt="Shoes slide img" />
              </div>
              <div class="seq-title">
                <span data-seq>Save Up to 75% Off</span>                
                <h2 data-seq>Exclusive Shoes</h2>                
                <p data-seq>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Minus, illum.</p>
                <a data-seq href="#" class="aa-shop-now-btn aa-secondary-btn">SHOP NOW</a>
              </div>
            </li>
            <!-- single slide item -->  
             <li>
              <div class="seq-model">
                <img data-seq src="img/slider/5.jpg" alt="Male Female slide img" />
              </div>
              <div class="seq-title">
                <span data-seq>Save Up to 50% Off</span>                
                <h2 data-seq>Best Collection</h2>                
                <p data-seq>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Minus, illum.</p>
                <a data-seq href="#" class="aa-shop-now-btn aa-secondary-btn">SHOP NOW</a>
              </div>
            </li>                   
          </ul>
        </div>
        <!-- slider navigation btn -->
        <fieldset class="seq-nav" aria-controls="sequence" aria-label="Slider buttons">
          <a type="button" class="seq-prev" aria-label="Previous"><span class="fa fa-angle-left"></span></a>
          <a type="button" class="seq-next" aria-label="Next"><span class="fa fa-angle-right"></span></a>
        </fieldset>
      </div>
    </div>
  </section>
      <div class="tab-pane fade1" id="allproducts">

                      <ul class="aa-product-catg">
                      
                        <?php global $product_array;
                         //print_r($product_array);
                        foreach ($product_array as $key => $value): ?>
                         
                        <li>
                          <figure>
                            <a class="aa-product-img" href="#"><img height="250px" width="200px" src="../uploadsnew/<?php echo $product_array[$key]['image'];?>" alt="polo shirt img"></a>
                            <a class="aa-add-card-btn"href="#"><span class="fa fa-shopping-cart"></span>Add To Cart</a>
                            <figcaption>
                              <h4 class="aa-product-title"><a href="#"><?php echo $product_array[$key]['name'];?></a></h4>
                              <span class="aa-product-price"><?php echo $product_array[$key]['price'];?></span><span class="aa-product-price"><del><?php echo $product_array[$key]['price']*7;?></del></span>
                            </figcaption>
                          </figure>                         
                          <div class="aa-product-hvr-content">
                            <a href="#" data-toggle="tooltip" data-placement="top" title="Add to Wishlist"><span class="fa fa-heart-o"></span></a>
                            <a href="#" data-toggle="tooltip" data-placement="top" title="Compare"><span class="fa fa-exchange"></span></a>
                            <a href="#" data-toggle2="tooltip" data-placement="top" title="Quick View" data-toggle="modal" data-target="#quick-view-modal"><span class="fa fa-search"></span></a>                            
                          </div>
                          <!-- product badge -->
                          <span class="aa-badge aa-sale" href="#">SALE!</span>
                        </li>
                      <?php endforeach;?>
                       
                        <!-- start single product item -->
                              
                      </ul>
                    </div>
                   
                    
					</div>
 <div class="pagination">
                    	
                    </div> 
                    </div>
					<?php include("new_footer.php");?>
					<script type="text/javascript" src="jQuery.js"></script>
					<script type="text/javascript">
						$(document).ready(function()
							{
								 var htm="<h2> <a href='products.php?page_id=0' title='First Page'>&laquo; First</a><a href='products.php?page_id=<?php  if($page_id==0) {echo $page_id;} else{echo $page_id-1;}?>' title='Previous Page'>&laquo; Previous<span class='slick-prev slick-arrow'></span></a>";
						for(var i=1;i<='<?php echo $total_pages; ?>';i++)
						{
							htm+="<a href='products.php?page_id="+(i-1)+"' class='number current' title="+i+">"+i+"</a>";
						}
						htm+="<a href='products.php?page_id=<?php  echo $page_id+1;?>'  title='Next Page'>Next &raquo;<span class='slick-next slick-arrow'></span></a><a href='products.php?page_id="+(i-2)+"' title='Last Page'>Last &raquo;</a></h2>";
						$(".pagination").html(htm);
							});
					</script>
</body>


</html>