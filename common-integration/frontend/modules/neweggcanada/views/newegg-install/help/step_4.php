<?php
use yii\helpers\Html;
use yii\base\view;
?>
<style>
.fixed-container-body-class {
    padding-top: 0;
}
	.image-edit {
  box-shadow: 0 2px 15px 0 rgba(78, 68, 137, 0.3);
  height: auto;
  margin-bottom: 20px;
  margin-top: 20px;
  padding: 15px;
  width: 100%;
}
</style>
<div class="page-content jet-install">
	<div class="container">
		<div class="row">
			<div class="col-lg-offset-2 col-md-offset-2 col-lg-8 col-md-8 col-sm-12 col-xs-12">
				<div class="content-section">
					<div class="form new-section">
						<h3 id="sec3">Newegg Category Mapping</h3>
						<br>
				        <p>
				           On the left side of the screen is <b>Product type (Shopify)</b> and on right side <b>Newegg Category Name</b>. Map appropriate <b>Shopify product types</b> with <b>newegg.com categories</b>. Now that mapping is done, HERE, you are not required to map categories time and again.
				        </p>  
				        <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggcanada/assets/images/newegg/category-mapping.png" alt="map-category-newegg"/>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>
