<?php


$this->title = 'Documentation: How to Sell on Newegg.com or Newegg.ca?';

?>
<header>
    <!--<meta content="Documentation : How to Sell on Newegg.com?" name="title">
    <meta content="Seamlessly connects Shopify Store with Newegg, and synchronizes any products edits (information change) or inventory changes on neweg and Shopify" name="description">
    <meta content="multichannel shopify, multi channel ecommerce newegg shopify, newegg channels api, shopify inventory software." name="tags">

    <meta content="Documentation : How to Sell on Newegg.com?" name="title">
    <meta content="Seamlessly connects Shopify Store with Newegg, and synchronizes any products edits (information change) or inventory changes on neweg and Shopify" name="description">
    <meta content="multichannel shopify, multi channel ecommerce newegg shopify, newegg channels api, shopify inventory software." name="tags">
    <meta property="og:locale" content="en_US"/>

    <meta property="og:type" content="text/html; charset=utf-8"/>
    <meta property="og:title" content="Documentation : How to Sell on Newegg.com?"/>
    <meta property="og:description" content="Seamlessly connects Shopify Store with Newegg, and synchronizes any products edits (information change) or inventory changes on neweg and Shopify"/>
    <meta property="og:url" content="https://apps.shopify.com/newegg-marketplace-integration"/>
    <meta property="og:site_name" content="Shopify Newegg Integration - CedCommerce"/>
    <meta property="og:image" content="https://shopify.cedcommerce.com/integration/frontend/modules/neweggmarketplace/assets/images/newegg/step2.png" />
    <meta name="twitter:card" content="summary"/>
    <meta name="twitter:title" content="Documentation : How to Sell on Newegg.com?"/>
    <meta name="twitter:description" content="Seamlessly connects Shopify Store with Newegg, and synchronizes any products edits (information change) or inventory changes on neweg and Shopify"/>

    <link rel="canonical" href="http://shopify.cedcommerce.com/integration/newegg-marketplace/sell-on-newegg" />-->
</header>
<div class="content-section">
<div class="form new-section">
<div class="jet-pages-heading">
	<h1 class="Jet_Products_style">Shopify Newegg-Integration Documentation :: <span> How to Sell on Newegg.com or Newegg.ca</span></h1>
	<div class="clear"></div>
</div>

<!--main-->
<div class="container-fluid">
    <div class="row">
      <!--left-->
      <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 " id="leftCol">
        <ul class="nav nav-stacked" id="sidebar">
          <li><a href="#sec">Overview</a></li>
          <li><a href="#sec0">Installation</a></li>
          <li><a href="#sec1">Newegg Configuration Setup</a></li>
            <ul class="nav nav-stacked child-out">
              <li><a href="#sec1-1">- Registration</a></li>
              <li><a href="#sec1-2">- Activate Newegg API</a></li>
              <li><a href="#sec1-3">- Product Import Section</a></li>
              <li><a href="#sec1-4">- Newegg - Category Mapping</a></li>
              <li><a href="#sec1-5">- Newegg Attribute Mapping</a></li>
            </ul>


          <li><a href="#sec2">Newegg Configuration Setting</a>
              <ul class="nav nav-stacked child-out">
                  <li><a href="#sec2-1">- Newegg Setting</a></li>
                  <li><a href="#sec2-3">- Cancel Order Setting</a></li>
                   <li><a href="#sec2-3">- Email Subscription Setting</a></li>
              </ul>
          </li>

          <li><a href="#sec4">Configure Products</a>
              <ul class="nav nav-stacked child-out">
                  <li><a href="#sec4-1">- Configure Simple Products</a></li>
                  <li><a href="#sec4-2">- Configure Variant Products</a></li>
              </ul>
          </li>
          
          <li><a href="#sec5">Manage Products</a>
              <ul class="nav nav-stacked child-out">
                  <li><a href="#sec5-1">- Upload Products</a></li>
                  <li><a href="#sec5-2">- Newegg Product Feed</a></li>
              </ul>
          </li>


          <li><a href="#sec6">Order Management</a>
              <ul class="nav nav-stacked child-out">
                  <li><a href="#sec6-2">- Manage Orders</a></li>
                  <li><a href="#sec6-3">- Failed Orders</a></li>
              </ul>
          </li>
          
        
      </div><!--/left-->
      
      <!--right-->
      <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">

        <h2 id="sec">Overview</h2>      
        <p><!-- <a href="https://apps.shopify.com/walmart-marketplace-integration" target="_blank"> -->Newegg Integration<!-- </a> --> app by CedCommerce synchronizes your Shopify Store with Newegg Marketplace. With help of APIs provided by Newegg, the app creates a channel facilitating the synchronization of product inventory and orders, updating products information and  helps you manage the products from your Shopify store itself. This app will help you to sell on Newegg USA or Newegg CANADA.</p>

        <h2 id="sec0">Installation</h2>
        <p>For installing Newegg Shopify Integration app, visit <a href="https://apps.shopify.com/newegg-marketplace-integration" target="_blank"> <b>newegg-marketplace-integration</b> </a>& click GET option (The app will ask permission for approving the data access of their Shopify stores using API).</p>
        
        <hr>
        <p>
          
<!--          <img class="image-edit" src="--><?//= Yii::$app->request->baseUrl; ?><!--/images/walmart-guide/install-walmart-app.png" />-->
          After that, click INSTALL APP option (If access of different API levels is granted, the process of seamless integration of your Shopify store with Newegg begins).
        </p>
        <hr>
        
        <h2 id="sec1"><b>Newegg Configuration Setup</b></h2>
        <p>
            <span class="applicable">To successfully integrate your Shopify Store with Newegg.com and start selling, few settings are required to be configured.</span>
        </p>   
        <h3 id="sec1-1">Registration</h3> 
            <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/newegg-guide/Step1.png" alt="registration-step"/>
        <p>
       <h3 id="sec1-2">Test Newegg Api</h3>
              <p>To successfully integrate your Shopify Store with Newegg and start selling on it, few settings are required to be configured. </p>

              <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/newegg-guide/step2.png" alt="registration-step2" />
              As we are providing integration for Newegg USA and Newegg Canada both, first you need to select whether you want integration for Newegg Canada or Newegg USA.</br>
              <ol>
                <li>First select the desired option- <b>US or CANADA</b></li>
                <li>Fill your <b>Seller Id</b></li>
                <li>Fill the <b>Newegg Authorisation key</b></li>
                <li>Fill the <b>Newegg Secret key</b></li>
                <li>Click on <b>Select as default store.</b></li>
                <li>Finally submit your details by clicking on <b>Submit</b></li>
              </ol>
           	<p>
            In order to obtain <b>Newegg Seller Id, Authorization key and Secret Key </b> the merchant needs to login to his Newegg Seller Panel.
            </p>

              
        <hr>
          
          <h3 id="sec1-3">Product Import Section</h3>
            <br>
                <p>
                  This step enables you to import products to your Newegg integration app.
            </p>
            <p>
              Here you get three options:
            </p>
                 <ol>
                   <li><b>All Products -</b> If you want to import all products to upload</li>
                   <li><b>Published Products -</b> If you want to import published products only</li>
                   <li><b>Select Products to import -</b> If you want import only some selected products</li>
                 </ol>
                  <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/newegg-guide/step3.png" alt="product upload"/>
            
                 </br>
                 <p>Whenever you select any of them you will get another subcategory in whch you can see the status of your products and further can update it from your shopify store.-</p>
                 <ol>
                   <li>Total Product(s) Available</li>
                   <li>Product(s) not having "Sku"</li>
                   <li>Product(s) having Duplicate "Sku"</li>
                   <li>Product(s) not having "Product Type"</li>
                   <li>Product(s) Ready To Import</li>
                 </ol>
                 <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/newegg-guide/step3.2.png" alt="product upload category"/>
                <p>If there is some kind of error in some particular category of product, you will also see a view button before it by clicking on which you will able to correct it from your shopify store. By clicking on view button, you will redirect to your shopify store and from there you can edit your details.</p>
                <p><b>Note : </b>Always remember that app will import only those products which have 2 important attributes-
                <ol>
                  <li>SKU</li>
                  <li>Product Type</li>
                </ol> 
                </p>
            <hr>
                
                <h3 id="sec1-4">Newegg - Category Mapping</h3>
                <p>After importing products on app, now it is turn to perform category mapping. It is one of the important step as you cant see your products on app until you perform it. You will only see a product count. Always try to perform proper category mapping as Newegg has so many restriction about category mapping.</br>
                If you are selling some products, so they must belog some category so that they can be searched on some particular marketplace. For example- You are selling a shirt, then it will be assigned in following category- </br>
                <b>Apparel  =>  Mens Casual Shirts </b></p>
                <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/newegg-guide/step4.png" alt="map-category-newegg"/>
                <p><h3><u>Note-</u></h3>  <b>Newegg has a policy that if you are already selling your products in some category, you can't change the category of your products. Once your products have been uploaded on Newegg, they will registered your products with the provided details. So in any case you are changing it, they will give you an error message. If you really need this then you can contact to marketing team of Newegg or us. We will contact them on your behalf.</b></p>
                <hr>

            <h3 id="sec1-5">Newegg Attribute Mapping</h3>
        
                <p>
                    If you have variant products then you must have to perform attribute mapping. By variant product means- same product with different attributes. For example you are selling a shoe, then it must has various size and colors. So these are variants.
                <br>
                <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/newegg-guide/step5.png" alt="newegg-attribute-mapping"/>
                
       <h2 id="sec2"><b>Newegg Configuration Setting</b></h2>
       <h3 id="sec2-1">Newegg Setting</h3>
       <p>
        <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/newegg-guide/newegg_configuration.png" alt="newegg_configuration"/>
       </p>
      <hr>
      
      <h3 id="sec2-3">Cancel Order Setting</h3>
       <p>
        <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/newegg-guide/cancelsection.png" alt="newegg-order-section"/>
       </p>

       <h3 id="sec2-3">Email Subscription Setting</h3>
       <p>
        <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/newegg-guide/email_setting.png" alt="newegg-order-section"/>
       </p>



       <h2 id="sec4">Configure Products</h2>
        <p>
          Now that categories are mapped, products are required to be uploaded on Newegg.com for sales. 
          <ul>
            <li>In Newegg-Integration app,<br><i>Go to the top menu and Click <b>PRODUCTS -> <a href="<?= Yii::$app->request->baseUrl; ?>/neweggmarketplace/neweggproduct/index" target="_self">MANAGE PRODUCTS</a></b>.</i></li>
            <li>
                You can see all the Shopify store products are listed on our app.<br>Here all the different columns listed are self explanatory. See below. 
            </li>    
                <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/newegg-guide/Manage_product.png" alt="product upload"/>
            <li style="font-family:verdana">
                <b>
                    NOTE: 
                    <p>1) All the products that have valid SKU(s) will be retrieved from Shopify store. If there is any product with no SKU, it will be not listed on Newegg Shopify app.</p>
                    <p>2) Each variant must have unique SKU for every product. If any product have duplicate SKU, then it might be conflicting SKU at the product upload time.<p>
                    <p>3) Each product must have positive inventory quantity of products. If the product quantity is negative or zero then products will not be uploaded on Newegg.com</p>
                    <p>4) Product price must be assigned to each product. You can update product price by admin panel of Shopify.</p>
                </b>                
            </li>
            <li>
                <p>Now to update any information of products,<br><i>Click <b>EDIT</b> icon of the product</i>. You can see many editable and non editable fields.</p>
            </li>
            <h3 id="sec4-1">Configure Simple Products (Non Variant)</h3>
            <hr />

            <li>
	            <p> These products which are SOLO and do not have any other variations (Size, Color etc.) are called Simple Products (Non Variant).  
	             <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/edit-simple.png" alt="attribute-nonvariant"/>
	            As visible in the image above, since <b>GAME ACCESSORIES</b> is child category and the product uploaded under this has NO variations, all the products can be uploaded directly.<br>
              For Example, Baseball bat under games accessories doesn’t have any variation thus it is Simple Product.
	            </p>
            </li>

            <li>
	            <h3 id="sec4-2">Configure Variant Products</h3><hr />
	            <p> These products have different variations. There exist ONE parent product category and rest of the products are children.
		            <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/edit.png" alt="attribute-variant-new"/>
		            	The attributes of the products are updated, again, as per Newegg.com’s category. As Variant Product is selected, it shows variable attribute listings.
		            <br/>
	            </p>           
            </li>
             
	       </li>
         </ul>
        
        <hr>
       
         
        <h2 id="sec5">Manage Products</h2>
        	<p>After all the products are properly edited. Means products have their Barcode (UPC, ISBN-10,ISBN-13,GTIN-14) or ASIN, Quantity and Attribute values, Price information, products can be uploaded at once.<br>Go to top menu, Click <b>Manage Products -><a href="<?= Yii::$app->request->baseUrl; ?>/neweggmarketplace/neweggproduct/index" target="_self"> Upload Products</a></b><br>All the products that uploaded till now are listed here.<br>Tick all the check boxes on the leftmost column, thereafter Select <b>Upload </b> and Click <b>SUBMIT</b>.</p>
	        <p><b>Upload:</b> It <b>uploads</b> the new products to Newegg.com or <b>update</b> the existing products information.</p>
	        	            
	        </p>
        
        <h3 id="sec5-1">Upload Products</h3>
         <p>
          <b>Select the product, choose upload and Click Submit.</b> 
         	<img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/newegg-guide/uploadproduct.png" alt="manage-product" />
			Upon selecting and uploading the products, the next page appears with display STATUS BAR.STATUS BAR shows Number of products successfully uploaded and Number of Products having ERRORS.Furthermore, Errors can occur due to <b>DUPLICATE</b> SKU(s) or Barcode or ASIN, <b>MISSING</b> Image,<b>INVALID</b> Quantity and <b>IMPROPER</b>  mapping of Shopify variant options with Newegg.com’s attributes. For <b>batch</b> upload, errors are displayed for each product if any product has incomplete information. To describe the error for each products in brief, see the image below.
		 </p>
			<img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/status-bar.png" alt="status_bar"/>
        	<p>
        		If product has all the information valid, it gets uploaded on Newegg.com and STATUS changes from <b>"Not uploaded"</b> to <b>"Uploaded"</b> or <b>ERROR ICON</b> gets displayed in action column.
        	</p>
        	<!-- <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/guide/after_upload.png" alt="after_upload" /> -->

        <hr>
        <h3 id="sec5-2">Newegg Product Feed</h3>
        <p>
        <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/product-feed.png" alt="status_bar"/>
        </p>
        
        <hr>
        <hr>
         
         <h2 id="sec6">Order Management</h2>
         	<p>
         		Under order management section, all the details of Newegg orders, whether it's Acknowledged, shipped,returned or refunded are stored.
         	</p>
         	
            <b>Note: Only the READY orders will be imported onto Newegg Shopify Integration app.</b>
            <p>If any new orders are created on Newegg.com, they are instantly fetched by our app. Same order is generated for the Shopify store, for merchants to view view details and fulfill them easily in their native order processing system.<br>To view all the imported orders from our app.<br>        
            
            <h3 id="sec6-2">Manage Orders</h3>
            <p>
                Newegg orders can be viewed under sales order area in the app. To check order details, 
                <i>Go to top menu <b>Orders -><a href="<?= Yii::$app->request->baseUrl; ?>/neweeggmarketplace/neweggorderdetail/index" target="_self">Sales Order</a>.</b></i>
                <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/sales-order.png" alt="order-listing"/>
            </p>
            
            <h3 id="sec6-3">Failed Orders</h3>
            <p>
                In the case quantity is not available in Shopify store or product SKU doesn’t exist) app does not fetch order from Newegg.com. For checking details of failed orders<br> Open <b>Orders -> <a href="<?= Yii::$app->request->baseUrl; ?>/neweggmarketplace/neweggorderimporterror/index" target="_self">Failed Order</a></b> section.
                <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/failed-orders.png" alt="failed-orders-list" />
                
            </p>

        <hr>
   
                  <h3 id="sec6-7">Email Notifications:</h3>
            <p>
               To alert you for all the happenings of your product on Newegg.com, you can enable the subscription for each of the desired action i.e. alerts when New order is received or, when the order is rejected, or when the order contains error etc.
            </p>
            <b>To do this</b>
            <p>Go to <b>Admin</b> Settings->Choose the desired alerts by ticking against the events</p>
            <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/emailsubscription.png" alt="emailsubscription"/>
        </div><!--/right-->
    </div><!--/row-->
</div><!--/container-->
</div>
</div>

 <?php $this->registerCssFile(Yii::$app->request->baseUrl."/frontend/modules/neweggmarketplace/assets/css/setup-styles.css"); ?>
 <?php $this->registerJsFile(Yii::$app->request->baseUrl.'/frontend/modules/neweggmarketplace/assets/js/setup-scripts.js'); ?>
