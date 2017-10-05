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
                        <h3 id="sec1">Newegg Api Details</h3>
                        <br>
                        <p>To successfully integrate your Shopify Store with Newegg and start selling on it, few
                            settings are required to be configured. </p>
                        <br>
                        <img class="image-edit"
                             src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/newegg_configuration.png"
                             alt="configuration-settings"/>


                        <img class="image-edit"
                             src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggmarketplace/assets/images/newegg/seller_id.png"
                             alt="configuration-settings"/>
                        <br>
                        <br>
                        <br>
                        <div>
                            <h4>HOW TO GET AUTHORIZATION KEYS FROM NEWEGG MARKETPLACE
                                <a href="https://cedcommerce.com/blog/how-to-get-authorization-keys-from-newegg-marketplace/"
                                   class="pull-right"
                                   target="_blank">Click Here</a>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
