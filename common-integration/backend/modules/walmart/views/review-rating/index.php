<?php 

 ?>
 <h1>Recent Reviews</h1>
 <div id="reviews">
 <div class="contents">
 <?php 
 if(isset($data['reviews']) && !empty($data['reviews'])){
 	$i=0;
 	foreach ($data['reviews'] as $key => $value) {
 		$i++;

	 	 echo '<figcaption>
	      <strong><a href="https://'.$value['shop_domain'].'" itemprop="author" rel="external">'.$value['shop_name'] . '</a></strong>
	    <span class="review-datepublished">posted <time data-local="time-ago" datetime="' . $value['created_at'].'">' . $value['created_at'].'</time></span>
	    <meta content="' . $value['created_at'].'" itemprop="datePublished">
	  </figcaption>
	  <span class="appcard-rating-star appcard-rating-star-1" data-review-type="star"></span>
	  <meta content="'.$i.'" itemprop="reviewRating">
	  <blockquote itemprop="reviewBody"><p>'.$value['body'].'
	<br> </p></blockquote>';
	 	}

	 	echo "</div>";
 }
 else{
 	echo '<span class="review-datepublished">'.$data['no_content'].'</span>';
 }



 ?>


 
