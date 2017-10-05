<?php


$this->title = 'How to Sell on Jet';

?>
<div class="content-section">
<div class="form new-section">
<div class="jet-pages-heading">
	<h1 class="Jet_Products_style">Shopify Jet-Integration Documentation :: <span> How to Sell on Jet.com </span></h1>
	<div class="clear"></div>
</div>

<!--main-->
<div class="container-fluid">
    <div class="row">
      <!--left-->
      <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 " id="leftCol">
        <ul class="nav nav-stacked" id="sidebar">
          <li><a href="#sec0">Installation</a>
          </li>
          <li><a href="#sec1">Jet Configuration Setup</a>
            <ul class="nav nav-stacked child-out">
              <li><a href="#sec1-1">- Registration</a></li>
              <li><a href="#sec1-2">- Activate Test API</a></li>
              <li><a href="#sec1-3">- Activate Live API</a></li>
              <li><a href="#sec1-4">- Product Import Section</a></li>
              <li><a href="#sec1-5">- Jet- Category Mapping</a></li>
              <li><a href="#sec1-6">- Jet Attribute Mapping</a></li>
            </ul>
          </li>
          <li><a href="#sec2">Home</a>
          </li>
          <li><a href="#sec3">Products</a>
              <ul class="nav nav-stacked child-out">
                  <li><a href="#sec3-1">- Map Category</a></li>
                  <li><a href="#sec3-2">- Map Jet Attribute</a></li>
                  <li><a href="#sec3-3">- Manage Products</a></li>
                  <li><a href="#sec3-4">- Jet Repricing</a></li>
                  <li><a href="#sec3-5">- Listing on Jet</a></li>
              </ul>
          </li>
          <li><a href="#sec4">Export/Import</a>
              <ul class="nav nav-stacked child-out">
                  <li><a href="#sec4-1">- Product Update</a></li>
                  <li><a href="#sec4-2">- Product Archive/Unarchive</a></li>
              </ul>
          </li>
          
          <li><a href="#sec5">Orders</a>
              <ul class="nav nav-stacked child-out">
                  <li><a href="#sec5-1">- Sales Orders</a></li>
                  <li><a href="#sec5-2">- Failed Orders</a></li>
                  <li><a href="#sec5-3">- Return Orders</a></li>
                  <li><a href="#sec5-4">- Refund Orders</a></li>
              </ul>
          </li>
          <li><a href="#sec6">Settings</a>
              <ul class="nav nav-stacked child-out">
                  <li><a href="#sec6-1">- API Configuration</a></li>
                  <li><a href="#sec6-2">- Return Configuration</a></li>
                  <li><a href="#sec6-3">- Product Configuration</a></li>
                  <li><a href="#sec6-4">- Order Configuration</a></li>
                  <li><a href="#sec6-5">- Email Subscription Settings</a></li>
              </ul>
          </li>
          <li><a href="#sec7">Documentation</a>
              <ul class="nav nav-stacked child-out">
                  <li><a href="#sec7-1">- Pricing</a></li>
                  <li><a href="#sec7-2">- Support</a></li>
                  <li><a href="#sec7-3">- Documentation</a></li>
                  <li><a href="#sec7-4">- Quick Toors</a></li>
                  <li><a href="#sec7-5">- Reports</a></li>
                  <li><a href="#sec7-6">- Logout</a></li>
              </ul>
          </li>
          
         
      </div><!--/left-->
      
      <!--right-->
      <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
        <h2 id="sec0"><b>Installation</b></h2>
        <p>Numerous Shopify store owners want to sell their products on Jet.com. So here is an amazing <a href="https://apps.shopify.com/jet-integration" target="_self"><b>Jet Integration</b></a> app for them. You can install this app in few easy steps.</p>
        
        <hr>
        <p>
       		 You can install Jet <a href="https://apps.shopify.com/jet-integration" target="_self"><b>Jet Integration</b></a> from Shopify app store. For that,Click <b>GET</b> option.(The app will ask permission for the approval of the data access of their Shopify stores using API).
	      <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/guide/install-app.png" /> 
    <!-- <iframe width="100%" height="445" style="align-items: center; padding: 10px;" src="https://www.youtube.com/embed/vvRPjZ5nDhc">
    </iframe> -->
	        As visible in the above screen, Click <b>INSTALL APP</b> option.(If granted access of different API levels, the process seamless integration of store with Jet.com begins.)</br>
	    </p>
        <hr>
        
        <h2 id="sec1">Jet Configuration Setup</h2>
        <p>
            <span class="applicable">To successfully integrate your Shopify Store with Jet.com and start selling, few settings are required to be configured.</span>
        </p>   
        <h2 id="sec1-1">Registration</h2> 
        <p>
        	This is the first step after installation of the app. The basic information like your NAME, CONTACT, EMAIL is already filled in that particular section. You just need to choose your shipping source and from where you had heard about us. Then just choose an option that you agree to our terms and conditions.
            Click next to move on next step
        </p>
            <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/reg.png') ?>" alt="Registration "/>
        <p>
       <h3 id="sec1-2">Test Api Details</h3>
              <br>
                  <p>
                      <span class="applicable">The Jet Integration app configuration process begins with entering the set of API’s – exactly four; <b>API User, Secret, Merchant ID & Fulfillment Node Id</b> – which you got from Jet.com into Test Api Setup panel. To get API keys you must have a partner panel on Jet. Just login in your account and get details.</span>
                  </p>    

                      <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/reg2.png') ?>" alt="Test API details "/><p>  
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
                        <p>3. It’s the time for 4th ID – Fulfillment Node ID.</p>
                        <p>To get this, Click Fulfillment Node ID > Copy the code > Paste it onto Test Api Setup panel</p>
                         <img class="image-edit" height="340px" width="685px" src="<?= Yii::$app->request->baseUrl; ?>/images/fulfillment-node.png" alt="fulfillment node"/>
                      </li>
                    </ul> 
                  </p>
          <h3 id="sec1-3">Live Api Details</h3>
            <br>
            <ul>
            <li>
                      <p>In this Step, you are required to get the <b>LIVE API DETAILS</b>, and PASTE them onto Live Api Setup Panel.</p>
                      <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/reg3.png') ?>" alt="Live API details "/>
                    </li>
                    <br>
                    <li>
                      <p><b>To do this:</b></p>
                      <p>Click at DASHBOARD (on Jet partner panel). Here you get API User, Secret, and Merchant ID.</p>
                      <p>Copy (them) > Paste them on Cedcommerce Live Api Setup Panel > And, Click Next</p>
                      <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/live-api.png" alt="live-api"/>
                    </li>
                  </ul>
                  <p><b>Note:</b> Now that you’ve integrated your Shopify store with Jet.com, you need to import product from your store to the Cedcommerce Jet Integration app.</p>

          <h3 id="sec1-4">Product Import Section</h3>
            <br>
                <p>
                  This step enables you to import products to your Jet integration app.
            </p>
            <p>
              Here you get three options:
            </p>
            <p><b>All products: </b>It enables you to import all the Shopify store products in the app.</p>
            <p><b>Published Products: </b>It enables you to import only those products which are available at your shopify store’s front-end.</p>
            <p><b>Custom Products Selection: </b>It enables you to import only those products which you want upload from your Shopify store on the app.</p>
            <p>In each option you have choice to select products under these categories</p>
            <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/reg4-1.png') ?>" alt="Products import "/><p> 

            <ul>
              <li>
                <p><b>1. Total Products</b></p>
              </li>
              <li>
                <p><b>2. Products having SKU</b></p>
              </li>
              <li>
                <p><b>3. Products not having SKU</b></p>
              </li>
              <li>
                <p><b>4. Products not having Product Type</b></p>
              </li>
              <li>
                <br>
                <p><b>A. Haven't defined Product types for your store products ?!</b></p> 
                    <p>i). login to your shopify store and visit product section then click the products which you want to define product types for</p>
                    <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/reg4.png') ?>" alt="Products import "/><p> 
                    <br>
                    <p>
                      ii). Too long, Right?! No problem,<br>You can define all product types at once with bulk product edit.
                    </p>
                    <p><b>Visit product section > Select All Products > Click Edit Products > Click Add Fields > Select Product type</b></p> 
                    <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/guide/bulk-edit-products.png" alt="bulk-edit-products"/>
                    <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/guide/bulk-edit-product-type.png" alt="bulk-edit-product-type"/>  
                </li>    
              <li>
                <br>
                <p><b>. Products Ready To import = Total Products - Products not having SKU</b></p>
              </li>
            </ul>
            <p>After choosing your option ALL PRODUCTS/PUBLISHED PRODUCTS</p>
            <p>Click <b>Start Import</b></p>
            <img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/guide/import-product.png" alt="import-product" />
            <hr>
            <h3 id="sec1-5">Jet Category Mapping</h3>
            <br>
                <p>
                   Correct category mapping is very important so that he/she find the relevant product in respective category.
			       Jet maps product in category and then subcategory. It means- </br><b>PARENT -> CHILD -> GRANDCHILD.</b></br></br>The chances of getting order increases if you have mapped your product in correct category.</br>1) Just map your products and then click on NEXT.</br>2) After submittion you will proceed to next step
                </p>  
                <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/reg5.png') ?>" alt="Category Mapping "/><p> 

            <h3 id="sec1-6">Jet Attribute Mapping</h3>
            <br>
                <p>
                    Choose jet attributes that you want to map with your product variant options. If you have variant products in your store you need to perform <b>ATTRIBUTE MAPPING</b>
        		    Variant Products mean, products having various attributes like color, size, metal etc. 
                </p>
                <br>
                <p>For example take Engagement Ring as a shopify product type : </p>
                <p>Now, in order to transfer correct information of your products on jet, you need to map jet attributes with your product variant options (attributes) . Like-</br> Map <b>"Size-Free Text" attribute of jet</b> with <b>"Size" variant option of your engagement ring (product type)</b> and <b>jet's "Color"</b> with <b>engagement ring's "Color"</b>.
                </p>
                <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/reg6.png') ?>" alt="Attribute Mapping "/><p> 
            <hr>
                
       <h2 id="sec2"><b>Home</b></h2>
        <p>
            Your homepage will show you each and every detail of your products and orders. <b>We provide real time synchronisation</b> So whenever you will perform any change, it will be shown on your dashboard at the same moment. Your dashboard will provide you following information-
            <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/dashboard.png') ?>" alt="Dashboard"/>
            <ol>
            	<b>
            	<li>It will show you the progress in your revenue</li>
            	<li>Information about your products</li>
            	<li>Information about your orders</li>
            	<li>Latest features of app</li>
            	<li>Updates about your inventory</li>
            	</b>
            </ol></br>
            <h4>We provide various options so that you can contact  us easily and always keep in touch with us.</h4>
            <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/Keepconnect.png') ?>" alt="Connect with us"/>
            <ol>
            	<b><li>You can submit your issue via ticket.</li>
            	<li>You can send us mail.</li>
            	<li>You can add on SKYPE with us.</li></b>
            </ol>
            Our support will  always be available to handle your query.

        </p>   
       
         
        <h2 id="sec3"><b>Products</b></h2>
        	<p>Go to top menu, Click <b>Manage Products ->
        	<a href="<?= Yii::$app->request->baseUrl; ?>/jetproduct/index" target="_self"> 
        	<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/pro.png') ?>" alt="Products"/>

        	Upload Products</a></b><br>All the products that uploaded till now are listed here.<br>Tick all the check boxes on the leftmost column, thereafter Select <b>Upload / Archive / Unarchive</b> and Click <b>SUBMIT</b>.</p>
	        <p><b>Upload:</b> It <b>uploads</b> the new products to Jet.com or <b>update</b> the existing products information.</p>
	        <p><b>Archive:</b>If for any reason, the products are NOT required to be uploaded on Jet, choosing Archive PREVENTS products to go LIVE on Jet.com.</p>
	        <p><b>UnArchive:</b> If the archived products are needed to go LIVE, choose Unarchive the products.</p>
	            
	        </p>
        
        <h3 id="sec3-1">Map Category</h3>
         <p>
          <b>Map the products in correct category so that they can sell more.</b> 
         	<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/cat1.png') ?>" alt="Category Mapping"/>
			Whenever customer will search any product on jet it will be shown within some category. So correct category mapping is very important so that he/she find the relevant product in respective category.
			Jet maps product in category and then subcategory. It means- </br><b>PARENT -> CHILD -> GRANDCHILD.</b></br></br>The chances of getting order increases if you have mapped your product in correct category.</br>1) Just map your products and then click on SUBMIT.</br>2) after submission you will redirect to manage products section.</br><b><h4><u>Note-</u></h4>You can search product on jet and then find correct category to map your products.</b></br>
		 
        	It will be shown that Jet Categories are mapped successfully with Product Type.
        	</p>

  
        <hr>
        <h3 id="sec3-2">Map Jet Attributes</h3>
        	<p>
        		If you have variant products in your store you need to perform <b>ATTRIBUTE MAPPING</b>
        		Variant Products mean, products having various attributes like color, size, metal etc. It is necessary to do correct attribute mapping because <b>if it is not done your products will not be published on jet.</b> Jet provides you attribute so that you can correctly map your shopify attributes with jet attributes.</br><br>1) Just map your product with correct attribute</br>2) Click on save button and you are done.</br>
        		<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/Attribute.png') ?>" alt="Attribute Mapping"/>
            </p>
            
         <hr>
         <h2 id="sec3-3">Manage Products</h2>   
         	<p>
            	This is the most important section. With the help of this section you will be able to manage your products, config your products and many more. Here, you can see all the details about your products also you can edit them.
            </p>
             <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/product11.png') ?>" alt="Manage Product"/>
             <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/product1.png') ?>" alt="Manage Product"/>
             <p>
             	<h4>
             	  <b>You have various option in this section-</b>
             	</h4></br>
             	  <b>1. Sync Products With App-</b> Sync your store's product on app as well as on Jet.</br></br>
             	  <b>2. Reset Filter-</b> If you had added any filter, anywhere in this section,it will be reset.</br></br>
             	  <b>3. View More-</b> By choosing this option, you would be able to perform different actions-</br>  
             	  <ol><b>
             	    <li>Get the status of products that are uploaded on Jet.</li>
             	    <li>Validate your products as per Jet requirements.It means, this simpally tells you that what are necessary requirements you need to publish your products on Jet.com.</li>
             	    <li>Sync your product's inventory, price etc to jet means whatever updation you had performed will be sync to jet.</li></b>
             	    </ol></br></br>
             	<h4>
             	<b>Select Bulk Action-</b>
             	</h4>
             	  You can perform various action on all your products like -</br>
             	  <ol><li><b>UPLOAD ALL YOUR PRODUCTS ON JET</b></li><li><b>UPLOAD THE INVENTORY OF YOUR PRODUCTS</b></li><li><b>UPLOAD THE PRICE OF YOUR PRODUCTS</b></li><li><b>ARCHIVE YOUR PRODUCTS</b></li><li><b>UNARCHIVE YOUR PRODUCTS</b></li>
             	  </ol></br></br>
             	  <h4><b>Some improtant points</b></h4>
             	   <ol> 
                    <li>All the products that have valid SKU(s) will be retrieved from Shopify store. If there is any product with no SKU, it will be not listed on Jet Shopify app.</li>
                    <li>Each variant must have unique SKU for every product. If any product have duplicate SKU, then it might be conflicting SKU at the product upload time.</li>
                    <li>To upload any products on Jet.com, products must have either Barcode (UPC, ISBN-10,GTIN-14,ISBN-13) or ASIN or MPN(mfr part number) with it. You can add or Update the Barcode(UPC, ISBN-10,ISBN-13,GTIN-14) from Shopify admin panel’s product section.</li>
                    <li>If any merchant has ASIN then he/she can enter ASIN for each product on EDIT from app panel.</li>
                    <li>Each product must have positive inventory quantity of products. If the product quantity is negative or zero then products will not be uploaded on Jet.com</li>
                    <li>Product price must be assigned to each product. You can update product price by admin panel of Shopify.</li>                
            </ol>
             </p>

              <h3 id="sec3-4">Jet Repricing</h3>
              <p>
              	<b>It is a new feature through which you can customise your product's price according to your competitor price on Jet and can view in this section</b>
              </p>
               <p>
               	This section includes these terms-
               	<ol>
               		<li><b>MIN PRICE-</b> Minimum cost on which you can sell your product so you never face any loss</li>
               		<li><b>CURRENT PRICE IN APP-</b> Current price of your product on Jet</li>
               		<li><b>MAX PRICE-</b> Maximum price of your product so that your price should not be more then your competitor price.</li>
               		<li><b>BID PRICE-</b> It is the amount which can be add in your MIN PRICE or can be deducted from your MAX PRICE.</li>
               	</ol>
               	To enable this feature go to <b>SETTINGS</b> and then enable <b>REPRICER</b> by choosing YES option.<b>Remember when you are using this feature you can not use Update Price Feature</b>
               	<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/Repricing2.png') ?>" alt="Repricing Setup"/>
               	After this go to <b>Products</b> then<b> Manage Product.</b> You can see an extra icon there.By clicking on this icon you would be able to get use this feature.
               	<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/Repricing3.png') ?>" alt="Manage Price"/>
               	Here, you can insert the amount according to your convinience.
               	<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/Repricing3.png') ?>" alt="Manage Price"/>
               	In this feature, we just perform simple calculation-</br>
               	<h3><b>Example-</b></h3>
               		<p>Price of your product is $20 and your competitor price is $18.Your Maximum Price can be $21 and Minimum price can be $15.In this case you can choose your BID PRICE = $3 or $2</p>
               		<h4><b> Min Price +  Bid Price = $15 + $3 = $18</b></h4>
               		<h4><b> Max Price -  Bid Price = $21 - $3 = $18 </b></h4></br>
                    <h4>Now you can see the changes in <b>JET REPRICING</b> feature</h4>
                    <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/Repricing.png') ?>" alt="Repricing"/>
                </p>

                <h3 id="sec3-5">Listing On Jet</h3>
                <p>
                This option provides you the list of those products which are listed on jet. It also provides you the status of those products and also gives you the facility to prform different actions on those products.
                <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/Listing on jet.png') ?>" alt="Listing on jet"/>
                <ol>
                	<li><b>Export selected sku's csv-</b> You can export the csv of selected skus that you need.</li>
                	<li><b>Products uploaded from other API provider-</b> You can export the csv of those products which are uploaded by some other app or API provider</li>
                	<li><b>Export all SKU csv-</b> You can export the csv of each product.</li>
                </ol>
                </p>


                <h2 id="sec4"><b>Export/Import</b></h2>
                <p>
                	In ths section you are able to update your product's detail through a csv. You just need to perform 3 steps-
                	<ol>
                		<li>Export the CSV of products</li>
                		<li>Perform changes</li>
                		<li>Import updated CSV</li>
                	</ol>
                </p>
                <h3 id="sec4-1">Product Update</h3>
                <p> 
                	If you want to perform any changes in your product's detail like you want to update price of products, inventory of products, barcode of products or any onther detail, you can do with this option.
                	<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/import_export.png') ?>" alt="import-export"/>
                	<b>First Export the csv file from Jet-</b>
                	<ol>
                	<li>Just select the attributes you want to update in  your csv and then click on <b>EXPORT.</b></li> 
                	<li>A csv of products with attributes you have selected, will be download on your system. You just need to update the information and then save it.</li>
                	</ol>
                	<b>Import the csv file on jet-</b>
                	<ol>
                		<li>Just go to same option <b>Listing On Jet</b></li>
                		<li>Browse your updated file and then click on <b>IMPORT</b></li>
                	</ol></br>
                	<h4>Your updated file would be successfully uploaded on jet.</h4>
                </p></br>

                <h3 id="sec4-2">Product Archive/Unarchive</h3>
                <p>
                	You must aware about terms <b>ARCHIVE</b> and <b>UNARCHIVE</b>
                	<ol> 
                	<li><b>Archive-</b> When you mark your product as Archive, it means it will not be published on Jet. If you don't want to sell your products then you need to archive them.</li>
                	<li><b>Unarchive-</b> If you want to again sell your products on jet,just Unarchive your products which has been archived previously.</li>
                	</ol>
                	<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/arc_unarc.png') ?>" alt="Archive_Unarchive"/>
                     With the help of this option you are able to <b>Archive</b> or <b>Unarchive</b> your products in bulk through csv.
                     <h4><b>To Archive your products-</b></h4>
                     <ol>
                       <b><li>Go to Import csv to Archive products</li>
                       <li>Browse the file (you can download the sample csv file from jet partner panel)</li>
                       <li>In this file, put the sku of those products you want to archive on jet</li>
                       <li>Click on Submit</li></b>
                     </ol>
                     It will show you message that you had made changes successfully.
                     <h4><b>In the same way if you want to unarchive your products-</b></h4>
                     <ol>
                     	<b><li>Go to Import csv to Unarchive products</li>
                     	<li>Browse the file (you can download the sample csv file from jet partner panel)</li>
                       <li>In this file, put the sku of those products you want to unarchive on jet</li>
                       <li>Click on Submit</li></b>
                     </ol>
                     It will also show you message that you had performed changes successfully.
                </p>

         <hr>
        
         
         <h2 id="sec5"><b>Orders</b></h2>
         	<p>
         		Here you will get the information about your orders.
         	<b>Note: Only the READY orders will be imported onto Jet Shopify Integration app.</b>
            <p>If any new orders are created on Jet.com, they are instantly fetched by our app. Same order is generated for the Shopify store for merchants, to view details and fulfill them easily in their native order processing system.<br>To view all the imported orders from our app.</p><br>

            <h3 id="sec5-1">Sales Orders</h3>
            <p>
                Jet orders can be viewed under <b>Sales Order</b> . To check order details, 
                
            </p>
            <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/salesOr.png') ?>" alt="Sales Order"/>
            You can also export csv file of your orders from here.
            

            <h3 id="sec5-2">Failed Orders</h3>
            <p>
            	All the failed orders can be viewed from here-
            	<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/failed.png') ?>" alt="Failed Order"/>
            	If you are not able to complete your order, it will be listed on <b>Failed Order Section.</b></br>
            	<b>Note: 
            	<ol>
            	  <li>Continuous cancellation of orders can be a reason to declare you as bad merchant and because of this your shop will be unauthorised by Jet</li>
            	  <li>We don't fetch orders of those products whose sku are not available on our app.</li>
            	  <li>If you have earned some revenue and your trail has been expired, in this case you  need to purchase our app otherwise we could not be able to fetch your orders</li></b>
            	</ol>
            </p>
            
            <h3 id="sec5-3">Return Orders</h3>
            <p>
                if the customer is not satisfy with the product, in this case he can return the product. For this he need to cantact with jet. Jet returns can be viewed in return order area of the app.
                click<b> Orders ->Return Order</b><br>
                Here all the returns are listed. 
                <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/return.png') ?>" alt="Return Order"/>

                Here you can see 3 status-
                <ol>
                	<li><b>Created -</b>the return is created on jet</li>
                	<li><b>Inprogress- </b>Processing on return</li>
                	<li><b>Complete by merchant- </b>Accepted by jet</li>
                </ol>
            </p>
            
            <h3 id="sec5-4">Refund Orders</h3>
            <p>
                If the customer is not satisfy from the product he can also contact directly to merchant.Jet refunds can be viewed in refund order area of the app. 
                Click<b> Orders ->Refund Order</b><br>
                Here all the refunds are listed.
                <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/refund.png') ?>" alt="Refund Order"/>
                </p>

                <h2 id="sec6"><b>Settings</b></h2>
                <p>With the help of this section you are able to adjust the settings of your panel. Also you can see the details and use various features.
                
                </p>

                <h3 id="sec6-1">API Configuration</h3>
                <p>
                	You can see your API details from here. These details are associated with Jet.com and provide you a unique identity. These details are necessary for us also .
                	<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/API.png') ?>" alt="API Details "/>
                </p>

                <h3 id="sec6-2">Return Configuration</h3>
                <p>
                This section contains the details of location from where all the orders will be returned. 
                If customer wants to return the product then he will try to contact on this address. So it is necessary to mention these details very clearly.
                	<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/Return.png') ?>" alt="Return Details "/>
                </p>
                 <h3 id="sec6-3">Product Configuration</h3>
                <p>
                With the help of this option you can perform some changes on your product's price and inventory. This section contains some useful features which can be useful to <b> Sell More On Jet</b>
                <img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/Products.png') ?>" alt="Product Details "/>
                <ol>
                	<li><b>Repricer-</b>This feature is helpful to set your price according to your competitor price. With the help of BID AMOUNT you are able to set correct price of your product. Kindly see the Products section in doc --><b>JET REPRICING</b></li></br>
                	<li><b>Update Price</b>
                	<p>
                		You can update the price of your all products with the help of this feature. There are 2 options available here-
                		<ul>
                			<li><b><u>Price update in FIXED AMOUNT</u></b>
                			<p>You can add any amount in your all products.</br>
                			Just choose the option FIXED AMOUNT and after this put your price there, you want to increase in your all products. Click on SAVE
                			<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/FixedP.png') ?>" alt="Fixed Price "/>
                			</p></li></br>
                			<li><b><u>Price update in %age</u></b>
                			<p>
                			If you want to increase price of your products in %age then you can choose this option.</br>
                			Just choose the option %age AMOUNT and after this put the %age price you want to increase in all your products. Click on SAVE
                			<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/percent.png') ?>" alt="Percent Price "/>
                			</p></li></br>
                		</ul>
                	</p>
                	</li>
                	<li><b>Threshold Product Inventory</b></li>
                	<p>
                		This feature is basically used for the inventory of your products.</br>
                		If you want to set a limit for your product's inventory below which it will be show <b>OUT OF STOCK</b> then you can set this limit from here.You just need to enter any value below which it will show the notification.
                		<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/Threshold.png') ?>" alt="Threshold limit "/>
                		<b>As given in image the THRESHOLD LIMIT is set to 5, it means below 5 it will show OUT OF STOCK.</b>
                	</p>
                </ol>
                </p>
                <h3 id="sec6-4">Order Configuration</h3>
                <p>Jet will suspend your account if orders are cancelled so many times. The chances of cancelling order increases when you don't have time for continuously update yourself about your all products.</br>  If you want to automatically cancel orders when product inventory is out of stock or sku is not available or order is not acknowledged within 2 hours, you can use this feature.
                	<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/OrderCon.png') ?>" alt="Order Configuration "/>
                </p>
            
                <h3 id="sec6-5">Email Subscription Settings</h3>
                <p>
                	Sometimes it is necessary, if you are performing some action or any other process continues/finishes,you need to get some notification about that. But sometimes the unwanted notifications about useless action also can irritate you.</br> So this feature will help you to get notification about those actions which you want.You can choose the options about which you want to get notification.
                	<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/emails.png') ?>" alt="Emails "/>
                </p>
                <hr>

                <h2 id="sec7"><b>Documentation</b></h2>
         	       <p>
         		      Here you will get all kind of support and information 
         	       </p>
         	       <h3 id="sec7-1">Pricing</h3>
                      <p>
                      	In this option you can get the pricing plan of our app. We provide 3 plans-</br>
                      	<ol>
                      		<li>Monthly plan</li>
                      		<li>Half yearly plan</li>
                      		<li>Annually plan</li>
                      	</ol>
                      	<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/Pricing.png') ?>" alt="Pricing Plan"/>
                      </p>

                      <h3 id="sec7-3">Support</h3>
                      <p>
                      	In this option you will get different ways through which you can communicate with us and share your problem. You can also get various information about company,blogs and many more-</br>
                      	<ol>
                      		<li><b>Cedcommerce Store -</b> You can purchase various apps, extensions, plugins from here.</li>
                      		<li><b>Support center home-</b> We at CedCommerce aim to provide the best possible solutions to you for your problems as we consider Customer Satisfaction of utmost importance. In order to avail our 24*7 support services you need to raise a ticket which is a very simple process. Just create a ticket and submit it. Our support team comprising of highly qualified professionals will provide you assistance for all your queries.</li>
                      		<li><b>Knowledgebase -</b> You can get the details about jet integration app and marketplace from here. </li>
                      		<li><b>Open a new ticket- </b> You can submit your issue via ticket and get support from our team.</li>
                      		<li><b>Support App -</b> It helps you to stay in touch with CedCommerce technical staff on your finger tips. The CedCommerce Support mobile app is one stop solution for all your support resource needs. You can search about CedCommerce related products through online mobile friendly contents. Any customer can raise tickets related to their products.</li>
                      		<li><b>Blog -</b> You can update yourself with various blogs written by our marketing team which will help you to understand you the marketplace and other marketing techniques so that you can sell more.</li>
                      	</ol>
                      	<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/Support.png') ?>" alt="Support "/>
                      </p>
            <h3 id="sec7-3">Documentation</h3>
            <p>
                You can get the details of each and every section your app from this option and also get useful details that will hel you to sell more on jet.
            </p>
        	
                  
        <h3 id="sec7-4">Quick Tours</h3>
        	
        	<p>
        		You will get a quick tour of your dashboard by choosing this option like -
        		<ol>
        			<li>Revenue of your store</li>
        			<li>Product's information</li>
        			<li>Order's information</li>
        			<li>Latest updates</li>
        		</ol>
        		So you would be able to get information of the main points of your dashboard. Just click on next to view more or you can quit also.
        		<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/Quick_tour.png') ?>" alt="Quick tour "/>
        	</p>

        	<h3 id="sec7-5">Reports</h3>
        	
        	<p>
        		In this section you will get the report of your performance-
        		<ol>
        			<li>Your top 10 selling products - So that you come to know that which of your products are selling most and you can always aware about their inventory <li>
        			<li>On your revenue and order basis it will show the performance of company that how much you are going better then previous months.</li>
        		</ol>
        		<img class="image-edit" src="<?= Yii::getAlias('@jetbasepath/assets/images/guide/reports.png') ?>" alt="Reports "/>
        	</p>

        	<h3 id="sec7-6">Logout</h3>
        	
        	<p>
        		You will come out of the app. 
        	</p>
        
        	<p>
          Go to top, Click<b> Orders -> <a href="<?= Yii::$app->request->baseUrl; ?>/jetorderimporterror/index" target="_self">Failed Order</a></b> Order and Click Cancel order option. And fill Order Item ID and Merchant Order ID details.
        		<img class="image-edit" src="<?= Yii::$app->request->baseUrl; ?>/images/guide/order-cancellation.png" alt="order-cancellation"/>
        	</p>
        	</div><!--/right-->
    </div><!--/row-->
</div><!--/container-->
</div>
</div>

 <?php $this->registerCssFile(Yii::$app->request->baseUrl."/css/setup-styles.css"); ?>
 <?php $this->registerJsFile(Yii::$app->request->baseUrl.'/js/setup-scripts.js'); ?>
?>