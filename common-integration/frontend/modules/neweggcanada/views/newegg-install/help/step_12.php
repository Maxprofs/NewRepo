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

                        <img class="image-edit"
                             src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggcanada/assets/images/newegg/newegg_configuration.png"
                             alt="configuration-settings"/>
                        <p>To successfully integrate your Shopify Store with Newegg and start selling on it, few
                            settings are required to be configured. </p>

                        <img class="image-edit"
                             src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggcanada/assets/images/newegg/seller_id.png"
                             alt="configuration-settings"/>
                        <div>
                            <h3>HOW TO GET AUTHORIZATION KEYS FROM NEWEGG MARKETPLACE
                                <a href="https://cedcommerce.com/blog/how-to-get-authorization-keys-from-newegg-marketplace/"
                                   class="pull-right"
                                   target="_blank">Click Here</a>
                            </h3>
                        </div>
                        <br>
                        <br>
                        <div>
                            <h2>How to add Manufacturer to Newegg System</h2>

                            <p><b>If a manufacturer (MFR) is not found in our system you can’t create a new item. You will need to
                                    first add any unknown MFR in our system, if you don’t, this will hinder the creating of new item(s)
                                    in our system because the system will not allow you to proceed further.
                                    Therefore, you need to request and wait that the requested MFR has been approved.
                                    If approved or denied you will be automatically notified via email through your account admin email address.
                                </b></p>

                            <p>How-To:</p>
                            <ol>
                                <li>Navigation > Manage Items > Manufacturer</li>
                                <li>Click "Add Manufacturer" button, add the required field "Manufacturer name". We recommend entering all information.</li>
                                <li>When information has been entered:</li>
                            </ol>


                            <p>New - will reset all fields
                            Submit & New - submit current MFR request and clear fields
                            Submit & Close - submit current MFR request and closes popup window
                            Cancel - cancel request closes popup window Note: Go to Manufacturer List tab, to search for your MFR in our system, if not found, go back to Manufacturer Request tab to request.</p>
                            <span>Note: Go to Manufacturer List tab, to search for your MFR in our system, if not found, go back to Manufacturer Request tab to request.</span>

                            <img class="image-edit"
                                 src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggcanada/assets/images/newegg/manufacturer.png"
                                 alt="configuration-settings"/>

                        </div>
                        <br>
                        <br>
                        <div>
                            <h2>Newegg Manufacturer List</h2>

                            <img class="image-edit"
                                 src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/neweggcanada/assets/images/newegg/manufactures_name.png"
                                 alt="configuration-settings"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
