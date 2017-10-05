<?php

$this->title = 'Documentation: How to Sell on Walmart.com?';

?>
<div class="content-section">
    <div class="form new-section">
        <div class="jet-pages-heading">
            <h1 class="Jet_Products_style">Shopify Walmart-Integration Documentation :: <span> How to Sell on Walmart.com </span>
            </h1>
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
                        <li><a href="#sec1">Walmart Configuration Setup</a></li>
                        <ul class="nav nav-stacked child-out">
                            <li><a href="#sec1-1">- Registration</a></li>
                            <li><a href="#sec1-2">- Activate Walmart API</a></li>
                            <li><a href="#sec1-3">- Product Import Section</a></li>
                            <li><a href="#sec1-4">- Walmart - Category Mapping</a></li>
                            <li><a href="#sec1-5">- Walmart Attribute Mapping</a></li>
                        </ul>


                        <li><a href="#sec2">Walmart Configuration Setting</a>
                            <ul class="nav nav-stacked child-out">
                                <li><a href="#sec2-1">- Walmart Setting</a></li>
                                <li><a href="#sec2-2">- Walmart Return Location</a></li>
                                <li><a href="#sec2-3">- Walmart Order</a></li>
                                <li><a href="#sec2-4">- Product Setting</a></li>
                                <li><a href="#sec2-5">- Email Subscription Setting</a></li>
                            </ul>
                        </li>

                        <li><a href="#sec4">Configure Products</a>
                            <ul class="nav nav-stacked child-out">
                                <li><a href="#sec4-1">- Configure Simple Products</a></li>
                                <li><a href="#sec4-2">- Configure Variant Products</a></li>
                                <li><a href="#sec4-3">- Promotional Pricing</a></li>
                                <li><a href="#sec4-4">- Shipping Exception</a></li>
                            </ul>
                        </li>

                        <li><a href="#sec5">Manage Products</a>
                            <ul class="nav nav-stacked child-out">
                                <li><a href="#sec5-1">- Upload Products</a></li>
                                <li><a href="#sec5-2">- Walmart Product Feed</a></li>
                                <li><a href="#sec5-3">- Walmart TaxCode</a></li>
                                <li><a href="#sec5-4">- Repricing</a></li>

                            </ul>
                        </li>

                        <li><a href="#sec6">Order Management</a>
                            <ul class="nav nav-stacked child-out">
                                <li><a href="#sec6-2">- Manage Orders</a></li>
                                <li><a href="#sec6-3">- Failed Orders</a></li>
                            </ul>
                        </li>
                        <li><a href="#sec7">Import/Export</a>
                            <ul class="nav nav-stacked child-out">
                                <li><a href="#sec7-1">- Product Update</a></li>
                                <li><a href="#sec7-2">- Price, Inventory and Barcode Update</a></li>
                                <li><a href="#sec7-3">- Retire Products</a></li>
                            </ul>
                        </li>
                        <li><a href="#sec8">Report Section</a>
                            <ul class="nav nav-stacked child-out">
                                <li><a href="#sec8-1">- Low stock report</a></li>
                                <li><a href="#sec8-2">- Sales by SKU</a></li>
                            </ul>
                        </li>
                    </ul>

                </div><!--/left-->

                <!--right-->
                <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">

                    <h2 id="sec"><u>Overview</u></h2>
                    <p><a href="https://apps.shopify.com/walmart-marketplace-integration" target="_blank">Walmart
                            Integration</a> app by CedCommerce synchronizes Shopify Store with Walmart. With the
                        help of
                        APIs provided by Walmart, the app creates a channel facilitating the synchronization of
                        product
                        inventory and orders, updating product's information and helps you manage the products from
                        your
                        Shopify store itself.</p>

                    <h2 id="sec0"><u>Installation</u></h2>
                    <p>For installing Walmart Shopify Integration app, visit <a
                                href="https://apps.shopify.com/walmart-marketplace-integration" target="_blank"><b>walmart-marketplace-integration</b></a>
                        & click GET option (The app will ask permission for approving the data access of their
                        Shopify
                        stores using API).</p>

                    <hr>
                    <p>
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/install-walmart-app.png"/>
                        After that, click INSTALL APP option (If access of different API levels is granted, the
                        process
                        of seamless integration of your Shopify store with Walmart begins).
                    </p>
                    <hr>

                    <h2 id="sec1"><u>Walmart Configuration Setup</u></h2>
                    <p>
                        <span class="applicable">To successfully integrate your Shopify Store with Walmart.com and to Start Selling On Walmart.com, few settings are required to be configured. </span>
                    </p>
                    <h2 id="sec1-1"><u>Registration</u></h2>
                    <p>
                        <span class="applicable">The first step of configuration process is the Registration step. In this merchant need to enter his personal details like: name, company address, revenue, e-mail, contact number, etc. </span>
                    </p>
                    <img class="image-edit"
                         src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/step1.png"
                         alt="registration-step"/>
                    <p>
                    <h3 id="sec1-2"><u>Walmart Api Activation</u></h3>
                    <p>To successfully integrate your Shopify Store with Walmart and start selling on it, few
                        settings are required to be configured. </p>
                    <p>

                    <h3>
                        How to get Walmart Api Credentials ?
                    </h3>
                    <p>

                        In order to obtain <b>Walmart Consumer Id and API Secret Key</b>
                    </p>
                    <p>
                        The merchant needs to login to his Walmart Seller Panel. Click on the Settings icon -> API
                        option.
                    </p>
                    <p>
                        <img class="image-edit"
                             src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/walmart-guide/api/api1.png"
                             alt="configuration-settings-new"/>
                    </p>
                    <p>
                        Copy the <b>“Consumer ID” </b>, click on the <b>“Regenerate Key”</b> button to regenerate
                        the <b>Secret key</b> and copy that and one by
                        one and paste these keys in the Api Configuration step of the app.
                    </p>
                    <img class="image-edit"
                         src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/walmart-guide/api/api2.png"
                         alt="configuration-settings-new1"/>
                    <p>
                        When you click on the <b>“Regenerate Key”</b> button then, a popup appears.
                        <img class="image-edit"
                             src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/walmart-guide/api/api3.png"
                             alt="live-api"/>

                        Click <b>“Yes,
                            Regenerate Key”</b> button, a new Secret Key is generated.

                        <img class="image-edit"
                             src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/walmart-guide/api/api4.png"
                             alt="live-api"/>

                        After that copy <b>“Consumer ID” and “Secret Key”  </b> one by
                        one, then paste these in the respective fields of the Walmart Shopify Integration app’s
                        configuration step.

                        <img class="image-edit"
                             src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/walmart-guide/api/api-step2.png"
                             alt="live-api"/>
                        Now that Shopify store is integrated with Walmart, importing products on our Walmart-shopify app from
                        Shopify is the next step to start selling on Walmart.

                    </p>
                    <hr>

                    <h3 id="sec1-3"><u>Product Import Section</u></h3>
                    <br>
                    <p>
                        This step allows you to import your shopify products to our Shopify-Walmart Integration app.
                    </p>
                    <img class="image-edit"
                         src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/step3-dropdown.png"
                         alt="import-product"/>

                    <p>
                        Here you get three options:
                    </p>
                    <p><b>All products: </b>It enables you to import all the Shopify store products our the app.</p>
                    <p><b>Published products: </b>It enables you to import only those products which are available
                        at
                        your shopify store’s front-end.</p>
                    <p><b>Select products to import: </b>It enables you to import only those product that you want.
                        A
                        table will be displayed listing all the products you can select any product to import.</p>
                    <img class="image-edit"
                         src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/step-3-selected.png"
                         alt="import-product"/>
                    <p>
                        Select Product by selecting the checkbox and then click on "Start Import" and when selected products are imported sucessfully.
                    </p>

                    <p>After selecting "All products" you will get the status of</p>
                    <ul>
                        <li>
                            <p><b>1. Total Product(s) Available</b></p>
                        </li>
                        <li>
                            <p><b>2. Product(s) not having SKU</b></p>
                        </li>
                        <li>
                            <p><b>3. Product(s) not having Duplicate SKU</b></p>
                        </li>
                        <li>
                            <p><b>4. Product(s) not having Product Type SKU</b></p>
                            <img class="image-edit"
                                 src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/step3-select.png"
                                 alt="import-product"/>
                        </li>
                        <li>
                            <p>If you have products that do not have Product type, you can click on <b>View</b>, you
                                will be re-directed to the Shopify Store.</p>
                            <p> Login to your shopify store and visit product section then click the products for
                                which you want to define product types</p>
                            <img class="image-edit"
                                 src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/product-type.png"
                                 alt="shopify-product-type"/>
                            <br>
                            <p> Too long, Right?! No problem,<br>You can define all product types at once with
                                bulk
                                product edit.
                            </p>
                            <p><b>Visit product section > Select All Products > Click Edit Products > Click Add
                                    Fields >
                                    Select Product type</b></p>
                            <img class="image-edit"
                                 src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/bulk-edit-products.png"
                                 alt="bulk-edit-products"/>
                            <img class="image-edit"
                                 src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/bulk-edit-product-type.png"
                                 alt="bulk-edit-product-type"/>
                        </li>
                        <li>
                            <br>
                            <p><b>5. Product(s) Ready To import = Total Product(s) - Product(s) not having SKU</b>
                            </p>
                        </li>
                    </ul>
                    <p>Now Click on <b>Start Import</b></p>
                    <!-- <img class="image-edit" src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/step3-dropdown.png" alt="import-product" />-->
                    <!--<img class="image-edit" src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/step3-select.png" alt="import-product" />-->
                    <img class="image-edit"
                         src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/step3.png"
                         alt="import-product"/>
                    <hr>
                    <h3 id="sec1-4"><u>Walmart Category Mapping</u></h3>
                    <br>
                    <p>
                        On the left side of the screen is <b>Product type (Shopify)</b> and on right side <b>Walmart
                            Category Name</b>. Map appropriate <b>Shopify product types</b> with <b>Walmart.com
                            categories</b>. Thereafter, provide appropriate <b>Taxcode </b>to each of the Product
                        Type.
                        Now that mapping is done, click NEXT.
                    </p>
                    <img class="image-edit"
                         src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/step4.png"
                         alt="map-category-walmart"/>
                    <h3 id="sec1-5"><u>Walmart Attribute Mapping</u></h3>
                    <br>
                    <p>
                        Choose Walmart attributes that you want to map with your product variant options.
                    </p>
                    <p>Take Electric Ride-On Car as a shopify product type for example: </p>
                    <p>Now, in order to transfer correct information of your products on walmart, you need to map
                        walmart attributes with your product variant options (attributes) .</p>
                    <img class="image-edit"
                         src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/step5.png"
                         alt="walmart-attribute-mapping"/>

                    <h2 id="sec2"><u>Walmart Configuration Setting</u></h2>
                    <h3 id="sec2-1"><u>Walmart API Setting</u></h3>
                    <p> All the information that you had entered while Configuration Step-2 is saved here. If in any
                        case you want to change the Keys you can edit them from here and Save it.
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/walmart_setting.png"
                             alt="walmart-attribute-mapping"/>
                    </p>
                    <hr>
                    <h3 id="sec2-2"><u>Walmart Return Location</u></h3>
                    <img class="image-edit"
                         src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/walmart_return_location.png"
                         alt="walmart-attribute-mapping"/>
                    <h3 id="sec2-3"><u>Walmart Order</u></h3>
                    <p> Here you have two option-
                        <ol>
                        	<li><b>Order Sync</b> - If you don't want to sync oredr on  shopify automatically then you can set this option at 'NO'. When you will disable this option your walmart orders will not be synced to shopify.</li>
                        	<img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/walmart_order.png"
                             alt="walmart-attribute-mapping"/>

                        	<li><b>Allow Partial Order</b> - By using this option you will be able to ship order partially. Let us suppose you have an order for 5 quantity. But you have only 3 quantity, then in this case you will be able to ship 3 quantity and 2 quantity will be auto cancel.</li>
                        	<img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/walmart_order.png"
                             alt="walmart-attribute-mapping"/>
                        </ol>
                        
                    </p>
                    <h3 id="sec2-4"><u>Walmart Product Settings</u></h3>
                    <p><b>Product Taxcode</b></p>
                    <p> In Walmart it is necessary to provide tax code to all your products. You can search the tax code for your products from here. If you have similar type of products and you want to set the same taxcode to all of your products, you can do this from here. Paste the taxcode here and Save it, this will change the taxcode of all products the with the taxcode provided here. 
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/product_taxcode.png"
                             alt="walmart-attribute-mapping"/>
                    </p>
                    <p><b><ul><li>Product Import</li></ul></b></p>
                    <p> Here is the option to import the products from your Shopify store to the app.
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/product_import.png"
                             alt="walmart-attribute-mapping"/>
                    </p>
                    <p><b><ul><li>Product Custom Pricing</li></ul></b></p>
                    <p> Sometimes you want to change the price scheme for all your products. ou can do this by using PRODUCT CUSTOM PRICING feature. If you want increase or decrease the price of all the products by same amount or percentage then you can do it from here. You just need to follow <br>
                    <ol>
                    	<li>Set the option on Yes.</li>
                    	<li>Choose the option that you want to increase price or decrease price.</li>
                    	<li>Now choose, you want to change price by fixed amount or by percentage. The price will be vary according to it.</li>
                    	<li>Now put the value according which you want to perform change.</li>
                    </ol> </br>
                    <b>Example : </b>Suppose you want to increase price of your product and you had chosne FIXED BY PERCENTAGE and put the value 23, it means your product's price will be increased by 23%.

                     <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/product_price.png"
                             alt="walmart-attribute-mapping"/>
                    </p>
                    <p><b><ul><li>Free Shipping</li></ul></b></p>

                    <p>
                        Sometimes it is not feasible for you to provide free shipping from all your orders, you have to include some shipping price on your order. If you want to remove free shipping from all the products, you can set the setting to YES from here and Save it.
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/free_shipping.png"
                             alt="walmart-attribute-mapping"/>
                    </p>
                    <p><b><ul><li>Enable Advance Attribute</li></ul></b></p>
                    <p>
                        If you want to send optional attributes along with required attributes to Walmart then you
                        can Enable it from here by setting it 'YES'.
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/attribute_add.png"
                             alt="walmart-attribute-mapping"/>
                    </p>
                    <p><b><ul><li>Threshold Product Inventory</li></ul></b>
                        Sometimes it is difficult to manage inventory. If you are not able to manage your inventory on Walmart it could be the reason for the cancellation of order. You can set a notification bell for your inventory.
                        This feature provide you the facilty to set a threshold limit for your products. If the quantity of your product is going down according to threshold limit then you will see a notification on dashboad also the quantity of product will be show OUT OF STOCK on walmart.
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/inventory_low.png"
                             alt="walmart-attribute-mapping"/>
                    </p>
                    <h3 id="sec2-5"><u>Email Subscription Setting</u></h3>
                    <p>
                        To alert you for all the happenings of your product on walmart.com, you can enable the
                        subscription for each of the desired action i.e. alerts when New order is received or, when
                        the
                        order is rejected, or when the order contains error etc.
                    </p>
                    <b>To do this</b>
                    <p>Go to <b>Admin</b> Settings->Choose the desired alerts by ticking against the events</p>
                    <p>
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/email_setting.png"
                             alt="walmart-attribute-mapping"/>
                    </p>

                    <h2 id="sec4"><u>Configure Products</u></h2>
                    <p>
                        Now that categories and attributes are mapped, all the settings are completed now its time
                        to upload the product on Walmart.com for sale.
                        <ul>
                            <li>In Walmart-Integration app,<br>Go to the top menu and Click <b>PRODUCTS -> <a
                                            href="<?= Yii::$app->request->baseUrl; ?>/walmart/walmartproduct/index"
                                            target="_self">MANAGE PRODUCTS</a></b>.
                            </li>
                            <li>
                                You can see all the Shopify store products that you imported are listed over
                                here.<br>Here
                                all the different columns listed are self explanatory. See below.
                            </li>
                            <img class="image-edit"
                                 src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/ConfigProducts.png"
                                 alt="shopify-product-listing"/>
                    <p>In the above image you can see 7 buttons highlighted.<br><br>
                        <ol class="order-list-ced"><li><b>Upload Products: </b>By clicking on this option you will be able to upload all the products on Walmart.com.</li>
                        <li><b>Update Price: </b>If you had updated price of any product of the product then click on "Update Price" it will
                        sync with your product to Walmart.</li>
                    <li><b>Update Inventory: </b>By clicking on 'Update Inventory' your product(s) inventory will
                        updated
                        on Walmart.com</li>
                    <li><b>Get Product Status: </b>By clicking on this your product status is 'synced' with
                        walmart.com</li>
                    <li><b>Sync With Shopify: </b> By clicking "Sync With Shopify" your product's will synchronize
                        with
                        Shopify Store.</li>
                    <li><b>Get Promo Price Status: </b>By clicking this button your product's promotional price
                        status
                        will be sync from Walmart.</li>
                    <li><b>Validate Product(s): </b> This will validate your products as per Walmart requirements.</li>
                    </ol></br></br>
                    <style type="text/css">
                    	.order-list-ced li {
						  list-style: unset !important;
						}
                    </style>
                    <li style="font-family:verdana">
                        <strong>
                            NOTE:
                            <p>1) If there is any product without SKU, it will not be listed on App.</p>
                            <p>2) Each variant must have unique SKU for every product.</p>
                            <p>3) If merchant changed the SKU of uploaded product and again uploading same product
                                ID but different SKU,
                                then it
                                might be conflicting SKU at the time of product upload. In this case you can set YES
                                to
                                SKU
                                OVERRIDE and then upload that product).
                            </p>
                            <p>4) If you want to set the promotional price or the offer price to the product you can
                                do
                                it be
                                adding promotional price to the product. This will display the actual price and as
                                well
                                as the
                                offer price of the product on Walmart.</p>
                            <!--<p>3) Each product must have positive inventory quantity at time of product upload. If the product quantity is negative or zero then products will not be uploaded on walmart.com</p>-->
                            <p>5) Product price must be assigned to each product. Product price cannot be 0. You can
                                update
                                product price by admin panel of Shopify.</p>
                            <p>6) You can also add Shipping Exception to products individually by clicking on Add
                                Shipping
                                Exception on Product Edit</p>

                        </strong>
                    </li>
                    <li>
                        <p>Now to update any information of products,<br><i>Click <b>EDIT</b> icon of the
                                product</i>.
                            You can see many editable and non editable fields.</p>
                    </li>
                    <h3 id="sec4-1"><u>Configure Simple Products (Non Variant)</u></h3>
                    <hr/>

                    <li>
                        <p>
                            <img class="image-edit"
                                 src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/simpleproduct.png"
                                 alt="attribute-nonvariant"/>
                            As visible in the image above, the product has NO variations, the product can be
                            uploaded
                            directly.<br>
                        </p>
                    </li>

                    <li>
                        <h3 id="sec4-2"><u>Configure Variant Products</u></h3>
                        <hr/>
                        <p> Those products having different variations are called Variant Products.
                            <img class="image-edit"
                                 src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/variantproduct.png"
                                 alt="attribute-variant-new"/>
                            The attributes of the products are updated, again, as per walmart.com’s category. As
                            Variant
                            Product is selected, it shows variable attribute listings. There are 3 products listing
                            in
                            the above image, first is the parent product and the other 2 are the children products.
                            <br/>
                        </p>
                    </li>
                    <h3 id="sec4-3"><u> Promotional Pricing</u></h3>
                    <hr/>
                    <li>
                        <p>
                            If you want to add the offer price or the sale price then you can set it by adding the
                            promotional price of that particular product.
                            You can do it by clicking the <b>Edit</b> button of that particular product. <br>
                            Then click on <b>Add Promotional Price</b> a popup box will appear. <br>
                            Set the offer price and date for that offer. Then click <b>"SAVE"</b>.
                            <img class="image-edit"
                                 src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/promotional_price.png"
                                 alt="attribute-nonvariant"/>
                            <br>
                        </p>
                    </li>
                    <h3 id="sec4-4"><u> Shipping Exception</u></h3>
                    <hr/>
                    <li>
                        <p> You can now add Shipping Exception of each of the product individually also. <br> Go to
                            <b>Edit</b>
                            button of that particular product. <br>
                            Then Click on <b>Add Shipping Exception</b> fields will appear to add the shipping
                            related
                            information.<br>


                            <img class="image-edit"
                                 src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/shipping_exception.png"
                                 alt="attribute-nonvariant"/>
                            <br>
                        </p>
                    </li>
                    </li>
                    </ul>

                    <hr>


                    <h2 id="sec5"><u>Manage Products</u></h2>
                    <p>After all the products are properly edited. Means products have their Barcode (UPC,
                        ISBN-10,ISBN-13,GTIN-14), Quantity and Attribute values, Price information, products can be
                        uploaded at once.<br>
                        Go to top menu, Click <b>Manage Products -><a
                                    href="<?= Yii::getAlias('@walmartbasepath') ?>/assets/walmart/walmartproduct/index"
                                    target="_self"> Upload Products</a></b><br>All the products that are imported to
                        the
                        app are listed here (uploaded and not uploaded both).<br>
                        Filter the product status to <b>Not uploaded</b> products. Tick all the check boxes on the
                        leftmost column, thereafter Select <b>Upload </b> and Click <b>SUBMIT</b>.</p>
                    <p><b>Upload:</b> It <b>uploads</b> the new products to walmart.com or <b>update</b> the
                        existing
                        products information.</p>

                    </p>

                    <h3 id="sec5-1"><u>Upload Products</u></h3>
                    <p>
                        <b>Select the product, choose upload and Click Submit.</b>
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/bulk_upload.png"
                             alt="product-mass-upload-new"/>
                        Upon selecting and uploading the products, the next page appears with PRODUCT UPLOAD.
                        Product
                        Upload will show the Number of products successfully uploaded and Number of Products having
                        ERRORS. Furthermore, Errors can occur due to <b>DUPLICATE</b> SKU(s) or Barcode,
                        <b>MISSING</b>
                        Image, <b>INVALID</b> Quantity and <b>IMPROPER</b> mapping of Shopify variant options with
                        walmart.com’s attributes. For <b>batch</b> upload, errors are displayed for each product if
                        any
                        product has incomplete information. To describe the error for each products in brief, see
                        the
                        image below.
                    </p>
                    <img class="image-edit"
                         src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/ProductUpload.png"
                         alt="status_bar"/>
                    <p>
                        If product has all the information valid, it gets uploaded on walmart.com and STATUS changes
                        from <b>"Not uploaded"</b> to <b>"Item Processing"</b> or <b>ERROR ICON</b> gets displayed
                        in
                        action column.
                    </p>
                    <!-- <img class="image-edit" src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/guide/after_upload.png" alt="after_upload" /> -->

                    <hr>
                    <h3 id="sec5-2"><u>Walmart Product Feed</u></h3>
                    <p> After Uploading the products, second task is to <b>Update Feed Status</b>. The data is sent
                        to
                        Walmart.com in the form of feed, so we need to update the feed status.<br>
                        Select the feed by ticking the check boxes,<br>
                        Then select the "Update Feed Status".<br>
                        Click on <b>SUBMIT</b> button.
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/WalmartFeed.png"
                             alt="status_bar"/>
                        Thereafter, click on Get Product Status
                    </p>
                    <hr>

                    <h3 id="sec5-3"><u>Walmart Taxcode</u></h3>
                    <p> If you don't know about the tax code of your product you can seacrh it from here. Just put your product category and you will get walmart tax code for it. You can use the same tax code for same category of product.
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/WalmartTaxcode.png"
                             alt="status_bar"/>
                    </p>
                    <hr>
                    <h3 id="sec5-4"><u>Repricing</u></h3>
                    <p>
                        The repricing feature on Walmart will allow you to update the price according to the best market price of other seller on Walmart.com. Repricing will help you to Win the BuyBox by making slight variations in original price of your products

                        Steps to Enable the Product for Repricing:
                        <ol>
                        <li>You can set “Enable Repricing” by selecting that product then click on “Go for Repricing”.</li>

                        <li>Here you can see the product information, buy box price, buy box ship price, 2nd best offer price, 3rd best offer price and 4th offer price. You can see a question over there asking “Want to Perform Repricing For this Product?” Then set that to “Yes”, and set the min price and max price of that product.</li>

                        <li>Thereafter, click on “Save” button. That product will be set enable for repricing.</li>
                        </ol>

                       <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/Repricing.png"
                             alt="repricing_feature"/>
                    </p>
                     
                    <hr>

                    <h2 id="sec6"><u>Order Management</u></h2>
                    <p>
                        Under order management section, all the details of Walmart orders, whether it's
                        Acknowledged,
                        shipped, returned or refunded are stored.
                    </p>

                    <b>Note: Only the READY orders will be imported onto Walmart Shopify Integration app.</b>
                    <p>If any new orders are created on walmart.com, they are instantly fetched by our app. Same
                        order
                        is generated for the Shopify store if you have set "YES" to <b>Sync Order on Shopify</b>,
                        for
                        merchants to view details and fulfill them easily in their native order processing
                        system.<br>To
                        view all the imported orders from our app.<br>

                    <h3 id="sec6-2"><u>Sales Orders</u></h3>
                    <p>
                        Walmart orders can be viewed under sales order area in the app. To check order details,
                        <i>Go to top menu <b>Orders -><a
                                        href="<?= Yii::getAlias('@walmartbasepath') ?>/assets/walmart/walmartorderdetail/index"
                                        target="_self">Sales Order</a>.</b></i>
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/SalesOrder.png"
                             alt="order-listing"/>
                    </p>

                    <h3 id="sec6-3"><u>Failed Orders</u></h3>
                    <p>
                        In the case quantity is not available in Shopify store or product SKU doesn’t exist) app
                        does
                        not fetch order from walmart.com. For checking details of failed orders<br> Open <b>Orders
                            -> <a
                                    href="<?= Yii::getAlias('@walmartbasepath') ?>/assets/walmart/walmartorderimporterror/index"
                                    target="_self">Failed Order</a></b> section.
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/FailedOrder.png"
                             alt="failed-orders-list"/>

                    </p>

                    <!-- by shivam -->
                    <h2 id="sec7"><u>Import / Export</u></h2>
                    <p>
                        Under Import/Export section, can make changes in bulk by Ecporting the CSV file and then
                        importing it.
                    </p>


                    <h3 id="sec7-1"><u>Product Update</u></h3>
                    <p>
                        From here you can Export the CSV file and make changes in product information. <b>Note:</b>
                        Do
                        not change the product ID.<br>
                        <ul>
                        <li>1) Choose the category of products you want to update and Click on <b>Export</b> button to Export the CSV file.</li>
                        <li>2) Update the csv and save it</li>
                        <li>3) Click on <b>Browse</b> button to select the edited file.</li>
                        <li>4) Click on <b>Import</b> button to upload the edited file.</li>

                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/ImportExport1.png"
                             alt="order-listing"/>
                    </p>

                    <h3 id="sec7-2"><u>Price, Inventory and Barcode</u></h3>
                    <p>
                        From here you can Export the CSV file and update the Price, Inventory and Barcode of the
                        products in bulk. You just need to proceed following steps- <br>
                        <ul>
                        <li>1) First choose which option you want to update, PRICE/ INVENTORY/ BARCODE.</li>
                        <li>2) Then choose for in which category of products you want to update the selected option</li>
                        <li>3) Click on <b>Export</b> button</li>
                        </ul>
                        After this you need import the updated file. For this just perform following steps-
                        <ul>
                        <li>1) Choose the option which you have updated previously</li>
                        <li>2) Browse the updated file</li>
                        <li>3) Click on <b>Import</b> button</li>
                        </ul>
                        

                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/ImportExport2.png"
                             alt="order-listing"/>
                    </p>

                    <h3 id="sec7-3"><u>Retire Product</u></h3>
                    <p>
                        From here you can Export the CSV file and retire the products in bulk. <br>
                        <ul>
                        <li>1) Choose the category of products you want to update and Click on <b>Export</b> button to Export the CSV file.</li>
                        <li>2) Update the file and save it.</li>
                        <li>3) Click on <b>Browse</b> button to select the updated file.</li>
                        <li>4) Click on <b>Import</b> button to upload the edited file.</li>
                        </ul>
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/ImportExport3.png"
                             alt="order-listing"/>
                    </p>

                    <h2 id="sec8"><u>Report Section</u></h2>
                    <p>
                        Here you can see the 'Low Stock Report' and 'Sales by SKU'.
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/report.png"
                             alt="order-listing"/>
                    </p>
                    <h3 id="sec8-1">Low Stock Report</h3>
                    <p>You can see those products here in which stock is low, so that you would be able to manage your inventory
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/LowStockReport.png"
                             alt="order-listing"/>
                    </p>
                    <h3 id="sec8-2">Sales By SKU</h3>
                    <p>
                        Here you can see the sales of your product within a year , month, week and today.
                        <img class="image-edit"
                             src="<?= Yii::getAlias('@walmartbasepath') ?>/assets/images/walmart-guide/SalesBySku.png"
                             alt="order-listing"/>
                    </p>
                    <!-- end by shivam -->

                </div><!--/right-->
            </div><!--/row-->
        </div><!--/container-->
    </div>

</div>
<div class="configuration_model">
    <div id="myModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <iframe width="560" height="400" frameborder="0" allowfullscreen=""></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->registerCssFile(Yii::$app->request->baseUrl . "/css/setup-styles.css"); ?>
<?php $this->registerJsFile(Yii::$app->request->baseUrl . '/js/setup-scripts.js'); ?>
<script>
    var csrfToken = $('meta[name="csrf-token"]').attr("content");

    function showVideo(id) {
        var src = "";
        /*if(id=='1'){
         src = "https://www.youtube.com/embed/OEBX7GCLh30";
         }
         else{
         src = "https://www.youtube.com/embed/BhHMCTBWvjY";
         }*/
        if (id) {
            src = id;
        }
        $('.configuration_model #myModal').modal('show');
        $('.configuration_model #myModal iframe').attr('src', src);
        //$('.model').attr("style", "display: block !important");
    }
    $('.configuration_model #myModal .close').click(function () {
        $('.configuration_model #myModal iframe').removeAttr('src');
        $('.configuration_model #myModal').modal('hide');
    });
</script>
