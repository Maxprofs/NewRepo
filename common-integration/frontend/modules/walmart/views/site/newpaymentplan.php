
<style>
	.glyphicon{
		margin-left:-2%;
	}
	.black-background {
	    background: #002666 none repeat scroll 0 0;
	    color: #ffffff;
	    margin-top: 3px;
	}
	.white-background{
		background: #fff;
	}
	.content-area{
		position: relative;
	}
	.red-text{
		color: #fff;
		font-size: 90px!important;
		font-weight: bold;
		margin: 0;
	}
	.black-text {
  color: #cc0000;
  font-size: 90px!important;
  font-weight: bold;
  margin: 0;
  position: relative;
  text-align: center;
  top: 0;
}
	.bfs-discount{
		padding: 30px 0 0 0;
	}
	.bfs-discount .first {
		border-right: 10px solid #cc0000;
		font-size: 125px;
		font-weight: bold;
		font-family: 'jet_regular';
		display: inline-block;
		margin: 0 10px 0 0;
		line-height: 100px;
		padding-right: 10px;
	}
	.bfs-discount .second{
		display: inline-block;
		margin: 0;
		line-height: 100%;
	}
	.bfs-discount .second .txt-block {
		display: block;
		font-size: 55px;
		font-weight: bold;
		font-family: 'jet_regular';
		line-height: 50px;
	}	
	.bfs-discount .third {
  color: red;
  font-family: "jet_regular";
  font-size: 24px;
  margin: 40px 0 0;
  padding: 30px 0;
  text-transform: uppercase;
}
	.bfs-button-col{
		padding: 20px 0 50px;
	}
	.bfs-pricing-col {
		padding-top: 30px;
		padding-bottom: 30px;
	}
	.bfs-price .bfs-dollar {
		font-size: 80px;
		line-height: 100%;
		font-weight: bold;
	}
	.bfs-pricing-col > p {
		font-size: 20px;
	}
	.bfs-btn {
		background: transparent none repeat scroll 0 0;
		border: 3px solid #CC0000;
		border-radius: 50px;
		color: #CC0000;
		font-size: 24px;
		font-weight: bold;
		max-width: 300px;
		padding: 15px;
		text-transform: uppercase;
		transition: all 0.3s linear 0s;
		width: 100%;
	}
	.bfs-btn:hover {
		background: #CC0000;
		color: #ffffff;
		transition: all 0.3s linear 0s;
	}
	del.bfs-price{
		color: #D90102;
	}
	span.bfs-price{
		color: #D90102;
	}
	.third::before, .third::after {
  background-image: url("../frontend/modules/walmart/assets/images/star.png");
  background-repeat: repeat-x;
  content: "";
  display: block;
  height: 14px;
}

	/*==================================================
	=            MEDIA QUARIES BLACK-FRIDAY            =
	==================================================*/
	@media only screen and (max-width: 640px){
		.bfs-discount .first {
			font-size: 65px;
			line-height: 50px;
		}
		.bfs-discount .second .txt-block {
			font-size: 30px;
			line-height: 25px;
		}
		.bfs-discount .third {
			font-size: 14px;
		}
		.red-text ,
		.black-text {
			font-size: 46px !important;
		}
		.black-text {
			top: -5px;
		}
		.bfs-button-col {
			padding: 20px 0;
		}
	}
	
	
	/*=====  End of MEDIA QUARIES BLACK-FRIDAY  ======*/
	
</style>

<div class="black-friday-wrapper">
	<div class="row">
		<div class="col-lg-10 col-lg-offset-1 col-md-10 col-md-offset-1 col-sm-12 col-xs-12">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="black-background content-area text-center">
						<div class="bfs-discount">
							<p class="first">FLAT</p>
							<p class="second"><span class="txt-block">25%</span> <span class="txt-block">OFF</span></p>
							<p class="third">pay for 9 months & enjoy for 12.</p>
						</div>
						<h1 class="red-text">Happy</h1>
					</div>
				</div>
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="text-center">
						<div class="white-background bfs-content-area">
							<h1 class="black-text">Independence Day</h1>
						</div>
						<div class="row">
							<div class="bfs-pricing">
								<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 bfs-pricing-col first">
									<img src="<?= Yii::$app->request->getBaseUrl()?>/frontend/modules/walmart/assets/images/then.png" class="img-responsive center-block" alt="">
									<!-- <p>Then,</p>
									<p class="bfs-price"><del class="bfs-dollar">$360</del>/year</p> -->
								</div>
								<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 bfs-pricing-col second">
									<img src="<?= Yii::$app->request->getBaseUrl()?>/frontend/modules/walmart/assets/images/now.png" class="img-responsive center-block" alt="">
								<!-- 	<p>Now,</p>
								<p class="bfs-price"><span class="bfs-dollar">$270</span>/year</p> -->
							</div>
						</div>
						<div class="col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2 col-xs-12">
							<div class="bfs-button-col text-center">
								<a href="<?= Yii::$app->request->getBaseUrl().'/walmart/site/paymentplan?plan=3' ?>">
									<button class="bfs-btn">activate plan</button>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
<script type="text/javascript">
	function changeImage(id){
		j$('div.carousel-inner div').removeClass('active');
		if(id=='test_api'){
			j$('div.carousel-inner div#login').addClass('active');
		}else if(id=='activate_api'){
			j$('div.carousel-inner div#filled-api').addClass('active');
		}else if(id=='live_api'){
			j$('div.carousel-inner div#live').addClass('active');
		}
	}
	
</script>