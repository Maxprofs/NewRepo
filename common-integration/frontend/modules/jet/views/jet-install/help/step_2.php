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
						 <h3 id="sec1">Test Api Details</h3>
						 	<br>
					        <p>
					            <span class="applicable">The Jet Integration app configuration process begins with entering the set of API’s – exactly four; <b>API User, Secret, Merchant ID & Fulfillment Node Id</b> – which you got from Jet.com into Test Api Setup panel. </span>
					        </p>    

					            <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/guide/configuration-settings-new1.png" alt="configuration-settings-new1" />
					        <p>  
					        <br>  
					             <p>
					        	And how to get APIs, let’s see:
						        </p>  
						        <ul>
						        	<li>
						        		<p>1. First, you need to login into your partner.jet.com account</p>
						        	</li>
						        	<li>
						        		<p>2. Now that you’re logged in, </p><p>Click API (Here you can get 3 (of 4 required) IDs – API User, Secret & Merchant ID)> Copy the IDs> Paste them onto Test Api Setup panel.</p>
						        		<img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/test-api.png" alt="test-api"/>
						        	</li>
						        	<br>
						        	<li>
						        		<p>3. Now, It’s the time for 4th ID – Fulfillment Node ID.</p>
						        		<p>To get this, Click Fulfillment Node ID > Copy the code > Paste it onto Test Api Setup panel</p>
						        		 <img class="image-edit" height="340px" width="685px" src="<?= Yii::$app->request->baseUrl; ?>/images/fulfillment-node.png" alt="fulfillment node"/>
						        		 <p>3. Now,That the test API step is fulfilled,Let's Proceed for Live API (Click Next) </p>
						        	</li>
						        </ul> 
					        </p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
