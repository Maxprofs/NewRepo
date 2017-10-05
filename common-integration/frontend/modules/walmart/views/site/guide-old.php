<?php
/* @var $this yii\web\View */
use app\models\JetOrderDetail;
use app\models\JetOrderImportError;
use app\models\JetProduct;

$this->title = 'How to Sell on Jet';

?>
<div class="jet-pages-heading">
	<h1 class="Jet_Products_style">Shopify Walmart Marketplace Integration Documentation :: <span> How to Sell on Walmart Marketplace</span></h1>
	<div class="clear"></div>
</div>

<!--main-->
<div class="container1">
    <div class="row">
      <!--left-->
      <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 ced-wal-guide-left" id="leftCol">
        <ul class="nav nav-stacked" id="sidebar">
          <li><a href="#sec">Overview</a></li>
          <li><a href="#sec0">Installation</a></li>
          <li><a href="#sec1">Walmart Configuration Setup</a></li>
          <li><a href="#sec1-1">Product Import Section</a></li>
          <li><a href="#sec2">Walmart Category Mapping</a></li>
           <li><a href="#sec2-1">Walmart Attribute Mapping</a></li>
          <li><a href="#sec3">Manage Product</a></li>
          <li><a href="#sec4">Manage Orders</a></li>
          <li><a href="#sec4-2">Carrier-Mapping</a></li>
        </ul>
      </div><!--/left-->
      
      <!--right-->
      <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12 ced-wal-guide-right">
      <h2 id="sec">Overview</h2>
        <p><a href="https://apps.shopify.com/walmart-marketplace-integration" target="_blank">Walmart Integration</a> app by CedCommerce synchronizes Shopify Store with Walmart. With help of APIs provided by Walmart, the app creates a channel facilitating the synchronization of product inventory and orders, updating products information and  helps you manage the products from your Shopify store itself.</p>
        <h2 id="sec0">Installation</h2>
        <p>For installing Walmart Shopify Integration app, visit <a href="https://apps.shopify.com/walmart-marketplace-integration" target="_blank"><b>walmart-marketplace-integration</b></a>  & click GET option (The app will ask permission for approving the data access of their Shopify stores using API).</p>
        
        <hr>
        <p>
       		
	        <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/install-walmart-app.png" />
	        After that, click INSTALL APP option (If access of different API levels is granted, the process of seamless integration of your Shopify store with Walmart begins).
        </p>
        
        <h2 id="sec1">Walmart Configuration Setup</h2>
        <p>To successfully integrate your Shopify Store with Walmart and start selling on it, few settings are required to be configured. </p><p>
            <span class="applicable">After clicking on “Continue” button on the Walmart Shopify integration app, a configuration pop-up gets displayed. </span><span>Here, you are required to enter <b>WALMART API DETAILS</b> i.e. <b>Walmart Consumer Id</b>, <b>API Secret Key</b> and <b>Channel Type Id</b>. Thereafter, Click VALIDATE button.</span>
            <p>
            In order to obtain <b>Walmart Consumer Id, API Secret Key and Channel Type Id </b> the merchant needs to login to his Walmart Seller Panel. Click on the Settings icon > API option.
            </p>
        </p>    
            <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/get-walmart-api-1.png" alt="configuration-settings-new"/>
        <p>
        	Copy the <b>“Consumer ID” </b>, click on the <b>“Regenerate Key”</b> button to regenerate the secret key and copy the “Consumer Channel Type Id” from your Walmart seller panel one by one and paste these keys in the Configuration settings of the app.
        </p>    
            <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/get-walmart-api-2.png" alt="configuration-settings-new1" />
        <p>    
            When you click on the <b>“Regenerate Key”</b> button then, a popup appears. Click <b>“Yes, Regenerate Key”</b> button, a new Secret Key is generated.
            <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/get-walmart-api-3.png" alt="live-api"/>
             After that copy <b>“Consumer ID”, “Secret Key” and “Consumer Channel Type Id” </b> one by one, then paste these in the respective fields of the Walmart Shopify Integration app’s configuration settings.
            <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/get-walmart-api-4.png" alt="live-api"/>
             Now that Shopify store is integrated with Walmart, importing products on Walmart from Shopify is the second step to start selling on Walmart.
            <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/walmart-config-setup.png" alt="live-api"/>
          
        </p>
        <hr>
        
       <h2 id="sec1-1">Product Import Section</h2>
        <p>
           After successful Walmart Configuration settings, Product Import pop-up is displayed. Click '<b>IMPORT PRODUCTS</b>' option to import all products from Shopify store to Walmart Integration app. (See below).
		</p>
			<img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/walmart-import-product.png" alt="import-product" />
         <p>
           <h2>WALMART INTEGRATION DASHBOARD</h2>
           After proper installation of Walmart Integration App, dashboard of admin panel of Shopify store (like below) is displayed. From here, you can easily manage the listing of products on Walmart by making use of Walmart Shopify Integration App tools. 
		</p>
			<img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/walmart-dashboard.png" alt="import-product" />       
        <hr> 
        
        <h2 id="sec2">WALMART CATEGORY MAPPING</h2>
        <p>
        	To sell on Walmart, map your Shopify Product Type with Walmart Category.</p>
          <p> Note: You are highly recommended to map all the Product Types with Walmart category at once.   </p>
          <p>To map the categories:</p>
        <p>
            Go to top menu & click <b>Manage Products > Map Category</b>. 
        </p>
        <p>
            Following screen appears.
        </p>
        <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/walmart-category-mapping.png" alt="map-category-jet"/>
        <p>
           On the left side of the screen is Product type (Shopify) and on right side WALMART CATEGORY NAME.</p> 
          <p>-<b>Map appropriate Shopify product type category with Walmart category.</b> </p>
          <p>-<b> To prevent entering tax code repetitively for a particular product type having largest number of products, enter the tax code here only once. </b></p>  
          <p><b>Note:</b></p>
          <p>If you want to sell products belonging to single product-category of Walmart (Although, you’ve divided the products into multiple categories but for Walmart they all belong to same product type category.<p>
          <p>Then you can define a single Universal Tax code for your products.</p>

          <p><b>To do this:</b></p>
          <p>
          Click <b>Settings</b> on the Dashboard And, Enter <b>Product Tax Code</b> (in the Product Settings Section), Now, you’re no longer required to enter Tax Code for the products again.  
          </p>
          <h2 id="sec2-1">WALMART ATTRIBUTE MAPPING</h2>
           <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/walmart-attribute-mapping.png" alt="map-attribute-jet"/>
           <p>Now that Category Mapping is done, Map Your products attribute on the Shopify Store with the respective Product attributes by Walmart. </p>
           <b>Note: Doing this step saves you from individually mapping attributes for all the products.</b>

        </p> 

        <h2 id="sec3">MANAGE PRODUCTS</h2>
        <p>
          After mapping categories, you are now all set to upload products on Walmart by clicking on <b>Manage Products > Upload Products</b> from top menu and continue with further processes.  
                <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/walmart-manage-product.png" alt="shopify-product-listing"/>
                To view the errors in products, click on the orange color <b>exclamation(!)</b> icon. A popup listing the error will appear.
             <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/walmart-product-error.png" alt="attribute-nonvariant"/>
           To edit the product, click on the blue color plus <b>(+)</b> icon. A popup will appear from where the product can be edited. 
            <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/walmart-product-update.png" alt="attribute-variant-new"/>
            For further details and guidance, you can add us on skype - <b>live:cedcommerce</b>, and get in touch with our development team or raise a ticket on our support - <b>live:support_35785</b>.
        </p>
        <h4>Product Tax Code:</h4>
          <p>For the product type that has largest number of products at your store, you can define the Tax Code here to prevent you from entering the tax code for products of THAT product types repetitively. </p>
          <h4> Product Custom pricing: </h4>
          <p>To manipulate the pricing of Products being offered at Walmart, <b>Select Yes</b>.<br> </p>
          <p>Further, there are TWO ways to manipulate the pricing – Fixed Price & Fixed Percentage<br> </p>

          <p><b>Fixed Pricing:</b> Through this you can increase the price of all the products by FIXED amount.<br>
          <b>Fixed Percentage:</b> Through this you can increase the price of all the products by FIXED percentage.</p>   
         <h4> Remove Free Shipping From All Products:</h4>
         <p> Select Yes to remove Value Shipping (free shipping) from all the products. Now, Standard Shipping charges will be applicable.<p>

          <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/custom-price-walmart.png" alt="shopify-order-listing"/>
        <h2 id="sec4">MANAGE ORDER:</h2>
        <p>
         Once the orders are placed for your products on Walmart, they are fetched to your stores with the help of <b>CRON’s</b> running at regular intervals.
          <h3 id="sec4-0">Sales Order:</h3>
          <p>To see the no of orders received from Walmart:</p>
          <b>Click Manage Orders> Click Sales Order (See in the image below)</b>
                <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/walmart-sales-order.png" alt="shopify-order-listing"/>
                <b>Also, From this panel, You can manually FETCH orders and SYNCHRONIZE them with your Shopify stores</b>
          <h3 id="sec4-1">Failed Orders:</h3>
          <p>To see the number of failed orders from your store,</p>
          <b>Click Manage Orders>Click Sales Order (See in the image below)</b>
                <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/walmart-failed-order.png" alt="shopify-order-listing"/>
          <h3 id="sec4-2">CARRIER MAPPING:</h3>
          <p>Once all the products are uploaded at Walmart, it is time to Map your shipment carrier as well.</p>
          <b>Click Carrier Mapping> Click Add Mapping (under action bar)> Choose your Carrier (See in the image below)</b>
                <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/walmart-guide/walmart-Carrier-Mapping.png" alt="shopify-order-listing"/>

          <p>There it is everything that was required has been done. Now just wait for orders to pour in. Happy hunting!</p>
          
        </div><!--/right-->
        <div class="clear"></div>

        
    </div><!--/row-->
</div><!--/container-->

<?php $this->registerCssFile(Yii::$app->request->baseUrl."/css/setup-styles.css"); ?>
 <?php $this->registerJsFile(Yii::$app->request->baseUrl.'/js/setup-scripts.js'); ?>
