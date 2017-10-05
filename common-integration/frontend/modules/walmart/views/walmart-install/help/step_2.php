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
                        <h3 id="sec1">Walmart Api Details</h3>
                        <br>

                        <p>To successfully integrate your Shopify Store with Walmart and start selling on it, few
                            settings are required to be configured. </p>
                        <p>

                        <h1>
                            How to get Walmart Api Credentials ?
                        </h1>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
