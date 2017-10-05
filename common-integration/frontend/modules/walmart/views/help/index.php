<?php
use yii\helpers\Html;

$this->title = 'Help Section';
$this->params['breadcrumbs'][] = $this->title;
$urlWalmartEdit = \yii\helpers\Url::toRoute(['help/viewvideo']);

?>

<div class="jet-product-index content-section">
    <div class="form new-section">
        <div class="jet-pages-heading ">
            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
            </div>
            <div class="product-upload-menu">
                <div class="jet-upload-submit"></div>
            </div>
            <div class="clear"></div>
        </div>

        <!--Desktop View starts-->

        <div class="cards-wrapper wraps">
            <div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <ul class="nav nav-tabs">
                        <li id="product" class="tabs active"><a data-toggle="tab" href="#home">Video(s) Information</a>
                        </li>
                        <li id="order" class="tabs"><a data-toggle="tab" href="#menu1">Document(s) Information</a></li>
                        <li id="faq" class="tabs"><a data-toggle="tab" href="#menu2">FAQs Information</a></li>
                    </ul>

                    <div class="tab-content">
                        <div id="home" class="tab-pane fade">
                            <div class="row">
                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="video-wrapper">
                                        <a href="javascript:void(0)" onclick='showVideo(this.id)'
                                           id="https://www.youtube.com/embed/OEBX7GCLh30">
                                            <div class="help-video">
                                                <img src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/video_overlay.png">
                                                <!--<video>
                                                    <source src="<? /*= Yii::$app->request->baseUrl; */ ?>/frontend/modules/walmart/assets/videos/APIActivation.mp4"
                                                            type="video/mp4">
                                                </video>-->
                                            </div>
                                        </a>
                                        <span class="video-heading">Shopify-Walmart Installation/Registration</span>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="video-wrapper">
                                        <a href="javascript:void(0)" onclick='showVideo(this.id)'
                                           id="https://www.youtube.com/embed/iRehY23WuRw">
                                            <div class="help-video">
                                                <img src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/video_overlay.png">
                                                <!--<video>
                                                    <source src="<? /*= Yii::$app->request->baseUrl; */ ?>/frontend/modules/walmart/assets/videos/APIActivation.mp4"
                                                            type="video/mp4">
                                                </video>-->
                                            </div>
                                        </a>
                                        <span class="video-heading">Product Upload on Walmart.com</span>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="video-wrapper">
                                        <a href="javascript:void(0)" onclick='showVideo(this.id)'
                                           id="https://www.youtube.com/embed/IbZ4DReVm8g">
                                            <div class="help-video">
                                                <img src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/video_overlay.png">
                                                <!--<video>
                                                    <source src="<? /*= Yii::$app->request->baseUrl; */ ?>/frontend/modules/walmart/assets/videos/APIActivation.mp4"
                                                            type="video/mp4">
                                                </video>-->
                                            </div>
                                        </a>
                                        <span class="video-heading"> Walmart Order Details. </span>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="video-wrapper">
                                        <a href="javascript:void(0)" onclick='showVideo(this.id)'
                                           id="https://www.youtube.com/embed/E4QVK1LbNZ4">
                                            <div class="help-video">
                                                <img src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/video_overlay.png">
                                                <!--<video>
                                                    <source src="<? /*= Yii::$app->request->baseUrl; */ ?>/frontend/modules/walmart/assets/videos/APIActivation.mp4"
                                                            type="video/mp4">
                                                </video>-->
                                            </div>
                                        </a>
                                        <span class="video-heading">Add Promotional Pricing On Walmart Product.</span>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="video-wrapper">
                                        <a href="javascript:void(0)" onclick='showVideo(this.id)'
                                           id="https://www.youtube.com/embed/yDo8fk6XGFY">
                                            <div class="help-video">
                                                <img src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/video_overlay.png">
                                                <!--<video>
                                                    <source src="<? /*= Yii::$app->request->baseUrl; */ ?>/frontend/modules/walmart/assets/videos/APIActivation.mp4"
                                                            type="video/mp4">
                                                </video>-->
                                            </div>
                                        </a>
                                        <span class="video-heading">Configuration Setting.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="menu1" class="tab-pane fade">

                            <div class="row">
                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="help-wrapper">
                                        <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/CategoryMapping.pdf' ?>"
                                           target="_blank">
                                            <div class="help-icon"><img height="auto" width="100"
                                                                        src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                        class="sub-feature-images1"></div>
                                            <div class="help-content">
                                                <span class="help-heading">Category Mapping</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="help-wrapper">
                                        <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/AttributeMapping.pdf' ?>"
                                           target="_blank">
                                            <div class="help-icon"><img height="auto" width="100"
                                                                        src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                        class="sub-feature-images1"></div>
                                            <div class="help-content">
                                                <span class="help-heading">Attribute Mapping</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="help-wrapper">
                                        <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/ProductUploadProcessonWalmart.pdf' ?>"
                                           target="_blank">

                                            <div class="help-icon"><img height="auto" width="100"
                                                                        src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                        class="sub-feature-images1"></div>
                                            <div class="help-content">
                                                <span class="help-heading">Product Upload process on Walmart</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="help-wrapper">
                                        <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/FiveWaysWalmartMarketplaceAPIFacilitateSellingonWalmart.pdf' ?>"
                                           target="_blank">

                                            <div class="help-icon"><img height="auto" width="100"
                                                                        src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                        class="sub-feature-images1"></div>
                                            <div class="help-content">
                                                <span class="help-heading">Five Ways WalmartMarketplace API Facilitate Selling on Walmart</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="help-wrapper">
                                        <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/PromotionalPricingFeatureonWalmart.pdf' ?>"
                                           target="_blank">

                                            <div class="help-icon"><img height="auto" width="100"
                                                                        src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                        class="sub-feature-images1"></div>
                                            <div class="help-content">
                                                <span class="help-heading">Promotional Pricing</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="help-wrapper">
                                        <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/RepricingonWalmartIntegrationApp.pdf' ?>"
                                           target="_blank">

                                            <div class="help-icon"><img height="auto" width="100"
                                                                        src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                        class="sub-feature-images1"></div>
                                            <div class="help-content">
                                                <span class="help-heading">Repricing</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="help-wrapper">
                                        <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/ShippingExceptiononwalmart.pdf' ?>"
                                           target="_blank">

                                            <div class="help-icon"><img height="auto" width="100"
                                                                        src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                        class="sub-feature-images1"></div>
                                            <div class="help-content">
                                                <span class="help-heading">Shipping Exception</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="help-wrapper">
                                        <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/OrderNotFetchedFromWalmart.pdf' ?>"
                                           target="_blank">

                                            <div class="help-icon"><img height="auto" width="100"
                                                                        src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                        class="sub-feature-images1"></div>
                                            <div class="help-content">
                                                <span class="help-heading">Order Not Fetched From Walmart</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="help-wrapper">
                                        <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/ProductsAreNotImportingOnAPP.pdf' ?>"
                                           target="_blank">

                                            <div class="help-icon"><img height="auto" width="100"
                                                                        src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                        class="sub-feature-images1"></div>
                                            <div class="help-content">
                                                <span class="help-heading">Products Are Not Importing On APP</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                    <div class="help-wrapper">
                                        <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/ProductsAreNotUploadingOnWalmart.pdf' ?>"
                                           target="_blank">

                                            <div class="help-icon"><img height="auto" width="100"
                                                                        src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                        class="sub-feature-images1"></div>
                                            <div class="help-content">
                                                <span class="help-heading">Products Are Not Uploading On  Walmart</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div id="menu2" class="tab-pane fade">
                            <div class="row">
                                <div class="content-section">
                                    <div class="form new-section">
                                        <div>
                                            <section class="cd-faq">

                                                <div class="cd-faq-items">
                                                    <ul id="basics" class="cd-faq-group">

                                                        <?php foreach ($data as $row) { ?>
                                                            <li id="<?= $row['id'] ?>">
                                                                <a class="cd-faq-trigger"
                                                                   href="#0"><?= $row['query'] ?></a>
                                                                <div class="cd-faq-content" id="div_<?= $row['id'] ?>">
                                                                    <p><?= $row['description'] ?></p>
                                                                </div> <!-- cd-faq-content -->
                                                            </li>
                                                        <?php } ?>

                                                    </ul> <!-- cd-faq-group -->

                                                </div> <!-- cd-faq-items -->
                                                <a href="#0" class="cd-close-panel">Close</a>
                                            </section> <!-- cd-faq -->
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>

        <!--Desktop View end-->

        <!--Mobile View starts-->

        <div class="mobile-wrapper">
            <div class="row">
                <div class="content-section">
                    <div class="form new-section">

                        <div>
                            <section class="cd-faq">

                                <div class="cd-faq-items">
                                    <ul id="basics" class="cd-faq-group">

                                        <li>
                                            <a class="cd-faq-trigger"
                                               href="#0">Video(s) Information</a>
                                            <div class="cd-faq-content">
                                                <div class="row">
                                                    <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                                        <div class="video-wrapper">
                                                            <a href="javascript:void(0)" onclick='showVideo(this.id)'
                                                               id="https://www.youtube.com/embed/OEBX7GCLh30">
                                                                <div class="help-video">
                                                                    <img src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/video_overlay.png">
                                                                    <!--<video>
                                                    <source src="<? /*= Yii::$app->request->baseUrl; */ ?>/frontend/modules/walmart/assets/videos/APIActivation.mp4"
                                                            type="video/mp4">
                                                </video>-->
                                                                </div>
                                                            </a>
                                                            <span class="video-heading">Shopify-Walmart Installation/Registration</span>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                                        <div class="video-wrapper">
                                                            <a href="javascript:void(0)" onclick='showVideo(this.id)'
                                                               id="https://www.youtube.com/embed/iRehY23WuRw">
                                                                <div class="help-video">
                                                                    <img src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/video_overlay.png">
                                                                    <!--<video>
                                                    <source src="<? /*= Yii::$app->request->baseUrl; */ ?>/frontend/modules/walmart/assets/videos/APIActivation.mp4"
                                                            type="video/mp4">
                                                </video>-->
                                                                </div>
                                                            </a>
                                                            <span class="video-heading">Product Upload on Walmart.com</span>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                                        <div class="video-wrapper">
                                                            <a href="javascript:void(0)" onclick='showVideo(this.id)'
                                                               id="https://www.youtube.com/embed/IbZ4DReVm8g">
                                                                <div class="help-video">
                                                                    <img src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/video_overlay.png">
                                                                    <!--<video>
                                                    <source src="<? /*= Yii::$app->request->baseUrl; */ ?>/frontend/modules/walmart/assets/videos/APIActivation.mp4"
                                                            type="video/mp4">
                                                </video>-->
                                                                </div>
                                                            </a>
                                                            <span class="video-heading"> Walmart Order Details. </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                                        <div class="video-wrapper">
                                                            <a href="javascript:void(0)" onclick='showVideo(this.id)'
                                                               id="https://www.youtube.com/embed/E4QVK1LbNZ4">
                                                                <div class="help-video">
                                                                    <img src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/video_overlay.png">
                                                                    <!--<video>
                                                    <source src="<? /*= Yii::$app->request->baseUrl; */ ?>/frontend/modules/walmart/assets/videos/APIActivation.mp4"
                                                            type="video/mp4">
                                                </video>-->
                                                                </div>
                                                            </a>
                                                            <span class="video-heading">Add Promotional Pricing On Walmart Product.</span>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                                        <div class="video-wrapper">
                                                            <a href="javascript:void(0)" onclick='showVideo(this.id)'
                                                               id="https://www.youtube.com/embed/yDo8fk6XGFY">
                                                                <div class="help-video">
                                                                    <img src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/video_overlay.png">
                                                                    <!--<video>
                                                    <source src="<? /*= Yii::$app->request->baseUrl; */ ?>/frontend/modules/walmart/assets/videos/APIActivation.mp4"
                                                            type="video/mp4">
                                                </video>-->
                                                                </div>
                                                            </a>
                                                            <span class="video-heading">Configuration Setting.</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> <!-- cd-faq-content -->
                                        </li>
                                        <li>
                                            <a class="cd-faq-trigger"
                                               href="#0">Document(s) Information</a>
                                            <div class="cd-faq-content">
                                                <div class="row">
                                                    <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                                        <div class="help-wrapper">
                                                            <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/CategoryMapping.pdf' ?>"
                                                               target="_blank">
                                                                <div class="help-icon"><img height="auto" width="100"
                                                                                            src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                                            class="sub-feature-images1"></div>
                                                                <div class="help-content">
                                                                    <span class="help-heading">Category Mapping</span>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                                        <div class="help-wrapper">
                                                            <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/AttributeMapping.pdf' ?>"
                                                               target="_blank">
                                                                <div class="help-icon"><img height="auto" width="100"
                                                                                            src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                                            class="sub-feature-images1"></div>
                                                                <div class="help-content">
                                                                    <span class="help-heading">Attribute Mapping</span>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                                        <div class="help-wrapper">
                                                            <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/ProductUploadProcessonWalmart.pdf' ?>"
                                                               target="_blank">

                                                                <div class="help-icon"><img height="auto" width="100"
                                                                                            src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                                            class="sub-feature-images1"></div>
                                                                <div class="help-content">
                                                                    <span class="help-heading">Product Upload process on Walmart</span>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                                        <div class="help-wrapper">
                                                            <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/FiveWaysWalmartMarketplaceAPIFacilitateSellingonWalmart.pdf' ?>"
                                                               target="_blank">

                                                                <div class="help-icon"><img height="auto" width="100"
                                                                                            src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                                            class="sub-feature-images1"></div>
                                                                <div class="help-content">
                                                                    <span class="help-heading">Five Ways WalmartMarketplace API Facilitate Selling on Walmart</span>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                                        <div class="help-wrapper">
                                                            <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/PromotionalPricingFeatureonWalmart.pdf' ?>"
                                                               target="_blank">

                                                                <div class="help-icon"><img height="auto" width="100"
                                                                                            src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                                            class="sub-feature-images1"></div>
                                                                <div class="help-content">
                                                                    <span class="help-heading">Promotional Pricing</span>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                                        <div class="help-wrapper">
                                                            <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/RepricingonWalmartIntegrationApp.pdf' ?>"
                                                               target="_blank">

                                                                <div class="help-icon"><img height="auto" width="100"
                                                                                            src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                                            class="sub-feature-images1"></div>
                                                                <div class="help-content">
                                                                    <span class="help-heading">Repricing</span>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                                                        <div class="help-wrapper">
                                                            <a href="<?php echo Yii::$app->request->baseUrl . '/walmart-policy/pdf/ShippingExceptiononwalmart.pdf' ?>"
                                                               target="_blank">

                                                                <div class="help-icon"><img height="auto" width="100"
                                                                                            src="<?= Yii::$app->request->baseUrl; ?>/frontend/modules/walmart/assets/images/pdf.png"
                                                                                            class="sub-feature-images1"></div>
                                                                <div class="help-content">
                                                                    <span class="help-heading">Shipping Exception</span>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div> <!-- cd-faq-content -->
                                        </li>
                                        <li>
                                            <a class="cd-faq-trigger"
                                               href="#0">FAQs Information</a>
                                            <div class="cd-faq-content">
                                                <div class="row">
                                                    <div class="content-section">
                                                        <div class="form new-section">
                                                            <div>
                                                                <section class="cd-faq">

                                                                    <div class="cd-faq-items">
                                                                        <ul id="basics" class="cd-faq-group">

                                                                            <?php foreach ($data as $row) { ?>
                                                                                <li id="<?= $row['id'] ?>">
                                                                                    <a class="cd-faq-trigger"
                                                                                       href="#0"><?= $row['query'] ?></a>
                                                                                    <div class="cd-faq-content" id="div_<?= $row['id'] ?>">
                                                                                        <p><?= $row['description'] ?></p>
                                                                                    </div> <!-- cd-faq-content -->
                                                                                </li>
                                                                            <?php } ?>

                                                                        </ul> <!-- cd-faq-group -->

                                                                    </div> <!-- cd-faq-items -->
                                                                    <a href="#0" class="cd-close-panel">Close</a>
                                                                </section> <!-- cd-faq -->
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div> <!-- cd-faq-content -->
                                        </li>

                                    </ul> <!-- cd-faq-group -->

                                </div> <!-- cd-faq-items -->
                                <a href="#0" class="cd-close-panel">Close</a>
                            </section> <!-- cd-faq -->
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!--Mobile View End-->


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
<style type="text/css">

    .cards .pro-info i {
        color: #D22525;
        font-size: 16px;
    }

    .pro-info.variant-label .label1 p {
        background: #292836 none repeat scroll 0 0 !important;
        border-radius: 1px;
        color: #fff;
        font-size: 15px;
        margin: 15px 0;
        padding: 5px;
    }

    .pro-info.variant-label .label p {
        background: #292836 none repeat scroll 0 0 !important;
        border-radius: 1px;
        color: #fff;
        font-size: 15px;
        margin: 15px 0;
        padding: 5px;
    }

    .pro-info .label1 > a {
        float: left;
    }

    .pro-info .label1 p {
        background: #e5eaed none repeat scroll 0 0;
        border-radius: 25px;
        padding: 4px 0;
    }

    .modal-content {
        background: rgba(0, 0, 0, 0) none repeat scroll 0 0;
        border: medium none;
        box-shadow: none;
        margin: 0 auto;
        top: 125px;
        width: 100%;
    }
</style>

<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet"
      href="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/frontend/modules/walmart/assets/css/faqstyle.css"> <!-- Resource style -->
<script type="text/javascript" src="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/js/modernizr.js"></script>
<script type="text/javascript" src="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/js/jquery-2.1.1.js"></script>
<script type="text/javascript"
        src="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/js/jquery.mobile.custom.min.js"></script>
<script type="text/javascript" src="<?= Yii::$app->getUrlManager()->getBaseUrl(); ?>/js/main.js"></script>
<script>
    $(document).ready(function () {

        var activeTab = "<?= Yii::$app->request->get('tab', '') ?>";
        if(activeTab == 'faq')
        {
            $('#faq a').trigger('click');
            $('#menu2').addClass('active in');

            /*var id = window.location.hash.substr(1);
            if(id)
            {
                $('#'+id).addClass('content-visible');
                $('#div_'+id).show();
            }*/
        }
        else
        {
            $('#home').addClass('active in');
        }

    });

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