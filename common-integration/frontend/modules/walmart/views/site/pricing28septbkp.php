<div class="row clearfix">
    <div class="col-lg-offset-2 col-md-offset-2 col-lg-4 col-md-4 col-sm-6 col-xs-12">
        <div class="jet-plan-wrapper yearly-plan">
            <h3 class="plan-heading">Yearly Plan</h3>
            <div class="plan-wrapper">
                <span class="old-price"></span>
                <span class="price"><strong>$30</strong><span class="month">/mo</span><br> (USD billed annually)</span>

                <h3 class="free"><span>FREE 7 Days</span></h3>
                <!-- <a href="http://cedcommerce.com/shopify-extensions/jet-shopify-integration"><div class="addtocart">Add to cart</div></a> -->
                <p class="push-sign">Save 25%</p>

            </div>
            <?php
            $url = Yii::$app->request->getUrl();
            if($url != '/integration/walmart'){ ?>

                <a href="<?= Yii::$app->request->getBaseUrl().'/walmart/site/paymentplan?plan=3' ?>">
                    <div class="addtocart yearly-plan">
                        Choose this Plan
                    </div>
                </a>
            <?php }
            ?>
            <div class="what-can-do">

                <ul>
                    <li>Upto 10000 Product(s) including variants.</li>
                    <li>Mapping Walmart Category with Product Type</li>
                    <li>Inventory Synchronization</li>
                    <li>Order Management</li>
                    <li>Return Management</li>
                    <li>Settlement</li>
                    <li>Flat 25% Saving</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
        <div class="jet-plan-wrapper active monthly-plan">
            <h3 class="plan-heading">Half Yearly plan</h3>
            <div class="plan-wrapper">
                <span style="padding: 0px;margin-top:3%;" class="price"><strong> $40</strong><span class="month">/mo</span><br>(USD billed half-yearly)</span>
                <h3 class="free"><span>FREE 7 Days</span></h3>

                <div class="clear"></div>
                <!-- <a href="http://cedcommerce.com/shopify-extensions/jet-shopify-integration"><div class="addtocart">Add to cart</div></a> -->

            </div>
            <?php
            $url = Yii::$app->request->getUrl();

            if($url != '/integration/walmart'){ ?>

                <a href="<?= Yii::$app->request->getBaseUrl().'/walmart/site/paymentplan?plan=2' ?>">
                    <div class="addtocart yearly-plan">
                        Choose this Plan
                    </div>
                </a>
            <?php }
            ?>
            <div class="what-can-do">

                <ul>
                    <li>Upto 10000 Product(s) including variants.</li>
                    <li>Mapping Walmart Category with Product Type</li>
                    <li>Inventory Synchronization</li>
                    <li>Order Management</li>
                    <li>Return Management</li>
                    <li>Settlement</li>
                </ul>
            </div>
        </div>
    </div>
</div>
