<style>
.error{
background: none repeat scroll 0 0 #DFDFDF;
    color: #000000;
    font-family: Helvetica;
    font-size: 150px;
    line-height: 2;
    padding: 50px 0;
    text-align: center;
   }
.error p{
 font-size: 54px;
}
</style>



<div class="error">
<?php if(is_array($exception)) { ?>
<p class="lead"><?php echo $exception['statusCode'];?></p>
<?php echo $error;?>
<?php }else{ ?>
<p class="lead"><?php echo $exception->statusCode;?></p>
Not Found !
<?php } ?>
</div>


