<?php
use yii\helpers\Html;
use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\ProductFeed;

$this->title = 'View Feed Details';
$this->params['breadcrumbs'][] = $this->title;
$ipmort_url=\yii\helpers\Url::toRoute(['walmartproductfeed/index']);

?>
<div class="content-section">
    <div class="form new-section">
<?php   if (isset($feed_data['error'])) { ?>
        <div class="walmart-product-feed-index">
            <div class="jet-pages-heading">
                <div class=" title-need-help">
                <h1 class="Jet_Products_style">Feed Information :</h1>

                </div>
                <div class="product-upload-menu">
                <!--<button class="btn btn-primary " type="submit" title="Back"><span>Back</span>
                </button>-->
                    <a href="<?= $ipmort_url;?>">
                        <button class="btn btn-primary uptransform" type="button" title="Back"><span>Back</span></button>
                    </a>
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

<?php   } elseif (isset($feed_data['feedId'])) { ?>

        <div class="walmart-product-feed-index">
            <div class="jet-pages-heading">
                <div class=" title-need-help">
                <h1 class="Jet_Products_style">Feed Information :</h1>

                </div>
                <div class="product-upload-menu">
                    <a href="<?= $ipmort_url;?>">
                        <button class="btn btn-primary uptransform" type="button" title="Back"><span>Back</span></button>
                    </a>
                </div>
                <div class="clear"></div>
            </div>
            <div class="ced-entry-heading-wrapper">
                <div class="fieldset walmart-configuration-index">
                    <div class="ced-form-grid-walmart">
                        <ul>
                            <div class="ced-form-item odd">
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <label><span>Feed Id :</span></label>
                                </li>
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <?= $feed_data['feedId'] ?>
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
                            <div class="ced-form-item odd">
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <label><span>Feed Status :</span></label>
                                </li>
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <?= $feed_data['feedStatus'] ?>
                                </li>
                                <div class="clear"></div>
                            </div>
                            <div class="ced-form-item even">
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <label><span>Item Received</span></label>
                                </li>
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <?= $feed_data['itemsReceived'] ?>
                                </li>
                                <div class="clear"></div>
                            </div>
                            <div class="ced-form-item odd">
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <label><span>Item Succeeded:</span></label>
                                </li>
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <?= $feed_data['itemsSucceeded'] ?>
                                </li>
                                <div class="clear"></div>
                            </div>
                            <div class="ced-form-item even">
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <label><span>Item Failed</span></label>
                                </li>
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <?= $feed_data['itemsFailed'] ?>
                                </li>
                                <div class="clear"></div>
                            </div>
                            <div class="ced-form-item odd">
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <label><span>Item Processing:</span></label>
                                </li>
                                <li class="ced-lg-hlaf ced-xs-full">
                                    <?= $feed_data['itemsProcessing'] ?>
                                </li>
                                <div class="clear"></div>
                            </div>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="ced-entry-heading-wrapper">
                <form action="<?= Data::getUrl('walmartproductfeed/reuploadproducts') ?>" method="post" name="frm">
                    <div class="entry-edit-head">
                        <h4 class="fieldset-legend">Feed Item Details:</h4>
                    </div>
                    <div style="display: none;" id="reupload-products">
                        <button type="submit">Re-Upload Products</button>
                    </div>
                    <div class="Attrubute_html" id="jet_Attrubute_html">
                        <table class="table feed_table table-striped table-bordered" border="2">
                            <thead>
                            <tr>
                                <th>
                                    <input id="select_all" type="checkbox" onclick="selectAll(this)" />
                                </th>
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
                            <tbody>
                            <?php if(isset($feed_data['itemDetails']['itemIngestionStatus']))
                            {
                                foreach ($feed_data['itemDetails']['itemIngestionStatus'] as $data)
                                {?>
                                    <?php if(!empty($data['ingestionErrors']['ingestionError'])) { ?>
                                    <tr class="feed_error">
                                        <td>
                                    <?php $code = ProductFeed::isListedError($data['ingestionErrors']['ingestionError']);
                                          if($code !== false) : ?>
                                            <input type="checkbox" value="<?= $code ?>" name="error_type[]" class="check" onclick="selectOne(this)" title="Click here to re-upload this product" />
                                            <input type="hidden" name="product_sku[]" value="<?= $data['sku']?>" />
                                    <?php endif; ?>
                                        </td>
                                    <?php } else { ?>
                                    <tr class="feed_success">
                                        <td></td>
                                    <?php } ?>
                                        <td><?= $data['sku']?></td>
                                        <td><?= $data['ingestionStatus']?></td>
                                        <?php if(!empty($data['ingestionErrors']['ingestionError'])){ ?>
                                                <td>
                                                    <ol>
                                                        <?php foreach ($data['ingestionErrors']['ingestionError'] as $key => $val){?>

                                                            <li id="feed_error_des"><?= $val['description']; ?></li>
                                                        <?php } ?>
                                                    </ol>
                                                </td>
                                        <?php }else{ ?>
                                            <td><?= '-' ?></td>
                                        <?php }?>
                                    </tr>
                                <?php }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
<?php   } ?>
    </div>
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

<script type="text/javascript">
    function selectAll(element)
    {
        if($(element).is(':checked')) {
            $('.check').prop('checked', true);
        } else {
            $('.check').prop('checked', false);
        }

        ToggleButton();
    }

    function selectOne(element)
    {
        if($("#select_all").is(':checked'))
        {
            $("#select_all").prop("checked", false);
        }
        else
        {
            var allCheckflag = true;
            $.each($(".check"), function(){
                if($(this).is(':checked') === false) {
                    allCheckflag = false;
                    return false;
                }
            });

            if(allCheckflag) {
                $("#select_all").prop("checked", true);
            }
        }

        ToggleButton();
    }

    function ToggleButton()
    {
        var flag = false;
        $.each($(".check"), function(){
            if($(this).is(':checked') === true) {
                flag = true;
                return false;
            }
        });

        if(flag === true) {
            $("#reupload-products").show();
        } else {
            $("#reupload-products").hide();
        }
    }
</script>