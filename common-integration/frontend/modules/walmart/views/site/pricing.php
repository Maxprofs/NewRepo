<div class="row clearfix">
    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
        <div class="jet-plan-wrapper active monthly-plan">
            <h3 class="plan-heading">Basic Service</h3>
            <div class="plan-wrapper">

                <span class="price"><strong>Free</strong><span class="month"></span><br> (First 7 Days)</span>

                <div class="clear"></div>

            </div>
            <?php
            $url = Yii::$app->request->getUrl();
            if (!Yii::$app->user->isGuest) { ?>

                <a title="Applicable Only For Once">
                    <div class="addtocart yearly-plan">
                        You Get
                    </div>
                </a>
            <?php }
            ?>
            <div class="what-can-do">
                <ul>
                    <li>Import max 10000 SKU *</li>
                    <li>Map category to product-type</li>
                    <li>Attribute Mapping</li>
                    <li>Upload 10% of catalog or 1000 SKUs **</li>
                    <li>Inventory Synchronization</li>
                    <li>Order fetching</li>
                    <li>Order Sync to store</li>
                    <li>Order Cancellation</li>
                    <li>Return Management</li>
                    <li>Zopim Support</li>
                    <li>Skype Support</li>
                    <li>Email Support</li>
                    <li>Call Support</li>
                    <li>Marketplace Related update</li>
                    <li>Regular Updates of new app launch</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
        <div class="jet-plan-wrapper yearly-plan">
            <h3 class="plan-heading">Yearly Plan</h3>
            <div class="plan-wrapper">
                <span class="old-price"></span>
                <span class="price"><strong>$30</strong><span class="month">/mo</span><br> (USD billed annually)</span>

                <!--<h3 class="free"><span>FREE 7 Days</span></h3>-->
                <!-- <a href="http://cedcommerce.com/shopify-extensions/jet-shopify-integration"><div class="addtocart">Add to cart</div></a> -->
                <!--<p class="push-sign">Save 25%</p>-->

            </div>
            <?php
            $url = Yii::$app->request->getUrl();
            if (!Yii::$app->user->isGuest) { ?>

                <a href="<?= Yii::$app->request->getBaseUrl() . '/walmart/site/paymentplan?plan=3' ?>">
                    <div class="addtocart yearly-plan">
                        Choose this Plan
                    </div>
                </a>
            <?php }
            ?>
            <div class="what-can-do">
                <ul>
                    <li>Import max 10000 SKU *</li>
                    <li>Map category to product-type</li>
                    <li>Attribute Mapping</li>
                    <li>Upload All Product(s)</li>
                    <li>Inventory Synchronization</li>
                    <li>Order fetching</li>
                    <li>Order Sync to store</li>
                    <li>Order Cancellation</li>
                    <li>Return Management</li>
                    <li>Zopim Support</li>
                    <li>Skype Support</li>
                    <li>Email Support</li>
                    <li>Call Support</li>
                    <li>Personal Account Manager</li>
                    <li>Weekly Newsletter</li>
                    <li>Webinars</li>
                    <li>Marketplace Related update</li>
                    <li>Regular Updates of new app launch</li>
                    <li>Future Offer Subscription Applicable</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
        <div class="jet-plan-wrapper active monthly-plan">
            <h3 class="plan-heading">Half Yearly plan</h3>
            <div class="plan-wrapper">
                <span style="padding: 0px;margin-top:3%;" class="price"><strong> $40</strong><span
                            class="month">/mo</span><br>(USD billed half-yearly)</span>
                <!--<h3 class="free"><span>FREE 7 Days</span></h3>-->

                <div class="clear"></div>
                <!-- <a href="http://cedcommerce.com/shopify-extensions/jet-shopify-integration"><div class="addtocart">Add to cart</div></a> -->

            </div>
            <?php
            $url = Yii::$app->request->getUrl();

            if (!Yii::$app->user->isGuest) { ?>

                <a href="<?= Yii::$app->request->getBaseUrl() . '/walmart/site/paymentplan?plan=2' ?>">
                    <div class="addtocart yearly-plan">
                        Choose this Plan
                    </div>
                </a>
            <?php }
            ?>
            <div class="what-can-do">
                <ul>
                    <li>Import max 10000 SKU *</li>
                    <li>Map category to product-type</li>
                    <li>Attribute Mapping</li>
                    <li>Upload 50% of catalog or 5000 SKUs**</li>
                    <li>Inventory Synchronization</li>
                    <li>Order fetching</li>
                    <li>Order Sync to store</li>
                    <li>Order Cancellation</li>
                    <li>Return Management</li>
                    <li>Zopim Support</li>
                    <li>Skype Support</li>
                    <li>Email Support</li>
                    <li>Call Support</li>
                    <li>Personal Account Manager</li>
                    <li>Weekly Newsletter</li>
                    <li>Marketplace Related update</li>
                    <li>Regular Updates of new app launch</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="pricing_terms">
    <h6>Terms & Conditions</h6>
    <ul>
        <li>*If you want to import more than 10000 products on the app then the Setup charges will be applicable.</li>
        <li>**This condition is applicable only when you are requesting us to upload your products on Walmart, if you are doing it yourself this condition does not apply.</li>
    </ul>
</div>
