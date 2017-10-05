<?php 
use yii\helpers\Html;
use frontend\modules\referral\components\Dashboard;

$this->title = 'Referrals';

$link = Dashboard::getReferrerLink();
$amount = Dashboard::getActiveAmount();
?>
<div class="jet-configuration-index content-section">
    <div class="jet_configuration">
        <div class="affiliate-dashboard">
        <div class="form new-section no-pad">
            <div class="update-heading grey-heading">
                <span class="heading"><?= Html::encode($this->title) ?></span>
            </div>
            <div class="code-section">
                <div class="row">
                <div class="col-lg-6 col-sm-6 col-md-6 col-xs-8"><div class="affiliate-link"><a href="#refferal">What is Referral System?</a></div></div>
                <div class="col-lg-6 col-sm-6 col-md-6 col-xs-4 text-right"><p class="balance"><img src="<?= Yii::$app->request->baseUrl.'/frontend/modules/referral/assets/images/wallet.png' ?>"><span class="price">$<?= $amount ?></span></p></div>
                <div class="col-lg-offset-1 col-md-offset-1 col-lg-10 col-md-10 col-sm-12 col-xs-12 text-center">
                    <div class="refferal-form">
                        
                        <p class="copy-text">Click to copy the referral link and share it with your friends</p>
                        <div class="form">
                            <div> 
                                <p id="jet_ref_link" class="referral-link"><?= isset($link['jet'])?$link['jet']:'' ?></p>                               

                                <div class="copy-wrap">
                                    <span class="jet icon">JET.COM<!-- <img src="<?= Yii::$app->request->baseUrl.'/frontend/modules/referral/assets/images/jet_logo.png' ?>"> --></span>
                                    <a type="button" class="btn btn-primary copy-btn" href="JavaScript:void(0);" onclick="copyToClipboard(this, 'jet_ref_link')">copy</a>
                                </div>
                            </div>
                            <div>
                                
                                <p id="walmart_ref_link" class="referral-link"><?= isset($link['jet'])?$link['walmart']:'' ?></p>
                                <div class="copy-wrap">
                                    <span class="walmart icon">WALMART<!-- <img src="<?= Yii::$app->request->baseUrl.'/frontend/modules/referral/assets/images/walmart_logo.png' ?>"> --></span>
                                    <a type="button" class="btn btn-primary copy-btn" href="JavaScript:void(0);" onclick="copyToClipboard(this, 'walmart_ref_link')">copy</a>
                                </div>
                            </div>
                            <div>
                                
                                <p id="newegg_ref_link" class="referral-link"><?= isset($link['jet'])?$link['newegg']:'' ?></p>
                                <div class="copy-wrap">
                                    <span class="newegg icon">NEWEGG<!-- <img src="<?= Yii::$app->request->baseUrl.'/frontend/modules/referral/assets/images/newegg_logo.png' ?>"> --></span>
                                    <a class="btn btn-primary copy-btn" href="JavaScript:void(0);" onclick="copyToClipboard(this, 'newegg_ref_link')">copy</a>
                                </div>
                            </div>
                        </div>
                        <!-- <p>Initiate your sharing by passing this referral link to more and more friends and Earn on every share.</p> -->
                        <p>Earn $20 Loyalty Bonus for each referral of CedCommerce Shopify Multi-Channel Seller apps.</p>
                        <p>Copy the above links and share it with your friends. You can share this link on Facebook, Tweet it and also WhatsApp it. Spread the word and reap the rewards </p>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <div class="form new-section no-pad"> 
            <div class="update-heading grey-heading">
                <span class="heading">How does Referral System Work?</span> 
            </div>    
            <div id="refferal" class="program row">
                <div class="col-lg-offset-2 col-md-offset-2 col-sm-offset-1 col-lg-8 col-md-8 col-sm-10 col-xs-12">
                    
                    <div class="steps">
                        <div class="step one">
                            <div class="row">
                                <div class="col-lg-4 col-sm-5 col-md-4 col-xs-12">
                                    <img src="<?= Yii::$app->request->baseUrl.'/frontend/modules/referral/assets/images/refer-1.png' ?>">
                                </div>
                                <div class="col-lg-8 col-sm-7 col-md-8 col-xs-12">
                                    <span class="step-heading">Step1</span>
                                    <!-- <p>Get the Referral Link. Share it with your friends.</p> -->
                                    <p>Being a CedCommerce integration app user, you are already a Referral Partner. Just copy the Referral Link, and share it with your friends. </p>
                                    <p>Each Referral is unique and helps us identify <b>‘who-has-referred-whom’</b></p>
                                </div>
                               
                            </div>
                        </div>
                        <div class="step two">
                            <div class="row">
                               
                                <div class="col-lg-4 col-sm-5 col-md-4 col-xs-12">
                                    <img src="<?= Yii::$app->request->baseUrl.'/frontend/modules/referral/assets/images/refer-2.png' ?>">
                                </div>
                                <div class="col-lg-8 col-sm-7 col-md-8 col-xs-12">
                                    <span class="step-heading">Step2</span>
                                    <!-- <p>They come through your link, use our app which comes with FREE trial for limited period.</p> -->
                                    <p>Following your Referral Link, your you friends install the referred app, install the referred app and use its limited period FREE trial.</p>
                                </div>

                            </div>
                        </div>
                        <div class="step three">
                            <div class="row">
                               
                               <div class="col-lg-4 col-sm-5 col-md-4 col-xs-12">
                                    <img src="<?= Yii::$app->request->baseUrl.'/frontend/modules/referral/assets/images/refer-3.png' ?>">
                                </div>
                                <div class="col-lg-8 col-sm-7 col-md-8 col-xs-12">
                                    <span class="step-heading">Step3</span>
                                    <p>After the expiry of trial, your friends purchase the app. </p>
                                </div>

                            </div>
                        </div>
                        <div class="step four">
                            <div class="row">
                                <div class="col-lg-4 col-sm-5 col-md-4 col-xs-12">
                                    <img src="<?= Yii::$app->request->baseUrl.'/frontend/modules/referral/assets/images/refer-4.png' ?>">
                                </div>
                                <div class="col-lg-8 col-sm-7 col-md-8 col-xs-12">
                                    <span class="step-heading">Step4</span>
                                    <!-- <p>And you receive your reward. Redeem your reward by either choosing 1-month free subscription or cash back*.</p> -->
                                    <p>And you receive your reward, which is either 1-month free subscription for any of our integration app or a CashBack*, you decide what works best for you.</p>
                                </div>
                            </div>
                        </div>
                        <div class="step five">
                            <div class="row">

                                <div class="col-lg-4 col-sm-5 col-md-4 col-xs-12">
                                    <img src="<?= Yii::$app->request->baseUrl.'/frontend/modules/referral/assets/images/refer-5.png' ?>">
                                </div>
                                <div class="col-lg-8 col-sm-7 col-md-8 col-xs-12">
                                    <span class="step-heading">Step5</span>
                                    <p>Get the amount reflected in your account after 30 days of payment made by your friends.</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form new-section no-pad" id="terms-conditions">
            <div class="update-heading grey-heading">
                <span class="heading">*Terms and Conditions:</span>
            </div> 
            <div class="terms-n-conditions">
                <div class="row">
                    <div class="col-lg-offset-1 col-md-offset-1 col-lg-10 col-md-10 col-sm-12 col-xs-12">
                         
                        <div class="list">
                            <!-- <p class="listing">If you are an existing customer, then you can directly become our affiliate by clicking 
                            on the Become an Affiliate link in the header of the app.</p>
                            <p class="listing">Outside customers are required to fill the Affiliate form and Submit it for approval. After submission the customer’s details will be verified and his account will be activated post admin approval if all the details are found to be genuine and correct.</p> -->

                            <!-- <p class="listing">As a part of Reward, customer gets 1-month FREE subscription or he can also opt for cash back. The cash back will include (Original App Amount – 20%)/Purchased subscription tenure.</p> -->
                            <p class="listing">As a part of Reward, you (referrer) gets 1-month FREE subscription or he can also opt for cash. The cash reward will include (Yearly Plan Amount – 20%) / Subscription tenure (i.e. 12 Months).</p>
                            <!-- <p><strong>Note:</strong> Shopify deducts 20% on every purchase. Thus, 20% is deducted from the original amount as this 20% does not comes to us. It directly goes to Shopify. After deducting 20%, the remaining amount is divided by Purchased Subscription Tenure to obtain the monthly value. That monthly amount is provided to you as cash back. So, if Half Yearly subscription is purchased, the amount will be divided by 6. For Yearly subscription, it will be divided by 12.</p> -->
                            <p><strong>Note:</strong> Cash reward will be calculated according to yearly plan. Shopify deducts 20% on every purchase. Thus, 20% is deducted from the original amount as this 20% does not comes to us. It directly goes to Shopify. After deducting 20%, the remaining amount is divided by 1 Year Subscription Tenure to obtain the monthly value, which you get as Loyalty Bonus.</p>
                            <!-- <p><strong>For e.g.</strong> If App being purchased costs $300 for 6 months then, 300-20% = 300 – (20% of 300) = 300 – 60 = 240. Now, 240/6 = $40. So, you will earn $40 as cash back.</p>
                            <p>Likewise, if subscription period is of 12 months then, 240/12 = $20. In this case, you will earn $20 as cash back.</p> -->
                            <p><strong>For e.g.</strong> If Yearly Plan of purchased app costs $300 then, 300 – (20% of 300) = 300 – 60 = 240. Now, 240/12 = $20. So, you will earn $20 as Loyalty Bonus.</p>
                            <!-- <p class="listing">The cash reward amount will reflect in your account after 30 days of the payment made by the referred customer because shopify takes some time in releasing amount.</p> -->
                            <p class="listing">You will be able to use the Loyalty Bonus exactly after 30 days of the payment made by the person whom you referred because shopify takes some time in releasing amount. </p>

                            <!-- <p class="listing">In order to Redeem the reward, you should have an earning of minimum $200 from you referring or 10 Paid Subscriptions. This amount will be credited in your PayPal account.</p> -->
                            <p class="listing">The reward can be easily redeemed as soon as you’ve $200 as Loyalty Bonus or 10 persons referred by you’ve installed and purchased the concerning apps. </p>

                            <!-- <p class="listing">If refund has been provided to the referred customer due to some reason, then the required amount will be automatically deducted from your account.</p> -->

                            <!-- <p class="listing">At any point, CedCommerce reserves the right to terminate the partnership any time if it founds any violation of conditions or some kind of false or illegal information about the company. Moreover, CedCommerce can also change the Affiliate Policy which can be checked by regularly visiting the site.</p> -->
                            <p class="listing">At any point, CedCommerce reserves the right to terminate the partnership any time if it founds any violation of conditions or some kind of false or illegal information about the company. Moreover, CedCommerce also reserves the right to make changes in its Referral Policy from time-to-time.</p>

                            <p><strong>Loyalty Bonus Return Condition:</strong></p>
                            <p class="listing">If a person referred by you is Selling with us for more than a month and then decides to uninstall the app and claim the Refund, then in this case Loyalty Bonus earned due to THAT PERSON will be taken back from you.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function copyToClipboard(element, elementId) 
    {
        $('.copy-btn').html('copy');
        $('.copy-btn').removeClass('copied');
        
        $(element).html('copied');
        $(element).addClass('copied');

        /*setTimeout(function() { 
            $(element).html('copy'); 
            $(element).removeClass('copied');
        }, 3000);*/

        var aux = document.createElement("input");
        aux.setAttribute("value", document.getElementById(elementId).innerHTML);
        document.body.appendChild(aux);
        aux.select();
        document.execCommand("copy");
        document.body.removeChild(aux);
    }
</script>