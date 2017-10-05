<?php
use yii\helpers\Html;
use frontend\modules\neweggmarketplace\components\Helper;
$this->title = 'View Feed Details';
$this->params['breadcrumbs'][] = $this->title;
//$backurl=\yii\helpers\Url::toRoute(['walmartproductfeed/index']);
$feed_array=[];
?>
<div class="content-section">

<?= Html::beginForm(['neweggproductfeed/index'], 'post',['class'=>'form new-section']);
if (isset($feed_data['error'])) {
    ?>
    <div class="walmart-product-feed-index">
        <div class="jet-pages-heading">
            <div class=" title-need-help">
            <h1 class="Jet_Products_style">Feed Information :</h1>
                
            </div>
            <div class="product-upload-menu">
            <button class="btn btn-primary " type="submit" title="Back"><span>Back</span>
            </button>
            </div>
            <div class="clear"></div>
        </div>
        <div class="ced-entry-heading-wrapper">
            <div class="fieldset walmart-configuration-index">
                <div class="ced-form-grid-walmart">
                    <ul>
                        <?php foreach ($feed_data['error'] as $key => $value) { ?>
                            <div class="ced-form-item odd">
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <label><span>Error :</span></label>
                                </li>
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <?= $value['info'] ?>
                                </li>
                                <div class="clear"></div>
                            </div>
                            <div class="ced-form-item even">
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <label><span>Feed Created Date</span></label>
                                </li>
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <?= $feed_created_date ?>
                                </li>
                                <div class="clear"></div>
                            </div>
                        <?php } ?>

                    </ul>
                </div>
            </div>
        </div>

    </div>
    <?php
} elseif (isset($feed_data['NeweggEnvelope']['Message']['ProcessingReport']['ProcessingSummary'])) {
    ?>

    <div class="walmart-product-feed-index">
        <div class="jet-pages-heading">
            <div class=" title-need-help">
            <h1 class="Jet_Products_style">Feed Information :</h1>
                
            </div>
            <div class="product-upload-menu">
            <button class="btn btn-primary " type="submit" title="Back"><span>Back</span>
            </button>
            </div>
            <div class="clear"></div>
        </div>
        <div class="ced-entry-heading-wrapper">
            <div class="fieldset walmart-configuration-index">
                <div class="ced-form-grid-walmart">
                    <ul>
                      <div class="ced-form-item even">
                            <li class="ced-lg-hlaf ced-xs-full">
                                <label><span>Item Received</span></label>
                            </li>
                            <li class="ced-lg-hlaf ced-xs-full">
                                <?= $feed_data['NeweggEnvelope']['Message']['ProcessingReport']['ProcessingSummary']['ProcessedCount'] ?>
                            </li>
                            <div class="clear"></div>
                        </div>
                        <div class="ced-form-item even">
                            <li class="ced-lg-hlaf ced-xs-full">
                                <label><span>Feed Created Date</span></label>
                            </li>
                            <li class="ced-lg-hlaf ced-xs-full">
                                <?= $feed_detail['created_at'] ?>
                            </li>
                            <div class="clear"></div>
                        </div>
                        <div class="ced-form-item odd">
                            <li class="ced-lg-hlaf ced-xs-full">
                                <label><span>Item Succeeded:</span></label>
                            </li>
                            <li class="ced-lg-hlaf ced-xs-full">
                                <?= $feed_data['NeweggEnvelope']['Message']['ProcessingReport']['ProcessingSummary']['SuccessCount'] ?>
                            </li>
                            <div class="clear"></div>
                        </div>
                        <div class="ced-form-item even">
                            <li class="ced-lg-hlaf ced-xs-full">
                                <label><span>Item Failed</span></label>
                            </li>
                            <li class="ced-lg-hlaf ced-xs-full">
                                <?= $feed_data['NeweggEnvelope']['Message']['ProcessingReport']['ProcessingSummary']['WithErrorCount'] ?>
                            </li>
                            <div class="clear"></div>
                        </div>
                        </ul>
                </div>
            </div>
        </div>
        <div class="ced-entry-heading-wrapper">
            <div class="entry-edit-head">
                <h4 class="fieldset-legend">Feed Items Details:</h4>
            </div>
            <div class="Attrubute_html" id="jet_Attrubute_html">
                <table class="table feed_table table-striped table-bordered" border="2">
                    <thead>
                    <tr>
                        <th>
                            <center>SKU</center>
                        </th>
                        <th>
                            <center>Status</center>
                        </th>
                        <th>
                            <center>Description</center>
                        </th>
                    </tr>
                    </thead>
                    <?php if(isset($feed_data['NeweggEnvelope']['Message']['ProcessingReport']['Result']) && is_array($feed_data['NeweggEnvelope']['Message']['ProcessingReport']['Result']))
                    {?>
                    <tbody>
                       <?php 
                       if(isset($feed_data['NeweggEnvelope']['Message']['ProcessingReport']['Result'][0])){
                        foreach ($feed_data['NeweggEnvelope']['Message']['ProcessingReport']['Result'] as $key=>$data)
                        {
                            ?>
                            <?php if(!empty($data['ErrorList']['ErrorDescription'])){?>
                            <tr class="feed_error">
                                <?php }
                                else{ ?>
                            <tr class="feed_success">
                                <?php } ?>
                                <td><?php echo $data['AdditionalInfo']['SellerPartNumber'];
                                $id = Helper::getProdcutId($data['AdditionalInfo']['SellerPartNumber']);
                                if($id){
                                    $feed_array[]= $id;

                                }
                                ?></td>

                                <td><?= "ERROR"?></td>
                                <?php if(!empty($data['ErrorList']['ErrorDescription'])){ ?>
                                        <td>
                                            <ol>
                                                <?php foreach ($data['ErrorList']['ErrorDescription'] as $key => $val){?>
                                                    <li id="feed_error_des"><?= $val; ?></li>
                                                <?php } ?>
                                            </ol>
                                        </td>
                                <?php }else{ ?>
                                    <td><?= '-' ?></td>
                                <?php }?>
                            </tr>
                        <?php }}
                        else{ 
                         if(!empty($feed_data['NeweggEnvelope']['Message']['ProcessingReport']['Result']['ErrorList']['ErrorDescription'])){?>
                            <tr class="feed_error">
                                <?php }
                                else{ ?>
                            <tr class="feed_success">
                                <?php } ?>
                         <td><?php if(isset($feed_data['NeweggEnvelope']['Message']['ProcessingReport']['Result']['AdditionalInfo']['SellerPartNumber'])){
                            echo $feed_data['NeweggEnvelope']['Message']['ProcessingReport']['Result']['AdditionalInfo']['SellerPartNumber'];
                            $id = Helper::getProdcutId($feed_data['NeweggEnvelope']['Message']['ProcessingReport']['Result']['AdditionalInfo']['SellerPartNumber']);
                              if($id){
                                    $feed_array[]= $id;

                                }
                            } ?></td>
                                <td><?= "ERROR"?></td>
                                <?php if(!empty($feed_data['NeweggEnvelope']['Message']['ProcessingReport']['Result']['ErrorList']['ErrorDescription'])){ ?>
                                        <td>
                                            <ol>
                                                <?php foreach ($feed_data['NeweggEnvelope']['Message']['ProcessingReport']['Result']['ErrorList']['ErrorDescription'] as $key => $val){?>

                                                    <li id="feed_error_des"><?= $val; ?></li>
                                                <?php } ?>
                                            </ol>
                                        </td>
                                <?php }else{ ?>
                                    <td><?= '-' ?></td>
                                <?php }?>
                            </tr>

                       <?php }  
                       }
                       $idData= explode(',',$feed_detail['product_ids']);
                       foreach ($idData as $idDatakey => $idDatavalue) {
                          if(!in_array($idDatavalue, $feed_array)){?>
                            <tr class="feed_success">
                            <td><?php echo Helper::getProdcutSku($idDatavalue); ?></td>
                            <td><?php echo 'SUCCESS'; ?></td>
                            <td><?= '-' ?></td>
                          <?php }
                   }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
<?php } ?>
</div>
<style>
    td{
        text-align: center;
    }

    .feed_error  {
        background-color: #ffb2b2 !important;
    }
    .feed_success  {
        background-color: #b2FBb2 !important;
    }
    .feed_table{
        margin-bottom: 0px;
    }
    #feed_error_des{
        color:#333;
        text-align: left;
    }
</style>