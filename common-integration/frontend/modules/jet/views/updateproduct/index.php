<?php
use yii\helpers\Html;

$this->title = "Update Product's Barcode";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product_csv">
    <div class="jet-pages-heading">
        <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
        <div class="clear"></div>

    </div>
    <p class="jet-partner-panel-para">Download a sample csv file <a
            href="<?php echo \yii\helpers\Url::toRoute(['updateproduct/samplecsv']); ?>"
            method="post" enctype="multipart/form-data">Click here </a> to update products barcode.</p>
    <div class="row">
        <div class="csv_import col-lg-6 col-md-6 col-sm-6 col-xs-12">

            <div class="csv_import1">
                <form action="<?php echo \yii\helpers\Url::toRoute(['updateproduct/updatebarcode']); ?>" method="post"
                      enctype="multipart/form-data">
                    <h4>Import CSV to Update Products Barcode</h4>

                    <div class="input-wrap">

                        <input type="file" name="csvfile"/>
                        <input type="submit" class="btn btn-info" value="Submit"/>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
<style>
    /*.csv_import {
      padding-left: 0;
      padding-right: 10px;
    }*/
    .jet-partner-panel-para{
        margin: 30px 0;
    }
    input {
        display: inline-block;
        margin-top: 0px !important;
    }

    .csv_export {
        padding-left: 10px;
        padding-right: 0;
        align-self: center;
    }

    .csv_import1 {
        border: 1px solid #d4d4d4 !important;
        border-radius: 3px 3px 0 0;
        margin-top: 20px;
        min-height: 120px;
    }

    .csv_import1 h4 {
        background: #d4d4d4 none repeat scroll 0 0 !important;
        color: #000 !important;
        font-size: 15px;
        text-align: center;
        margin: 0 0 15px;
        padding: 13px 10px;
    }

    .csv_import1 p {
        line-height: 25px;
        min-height: 40px !important;
        padding-left: 10px;
        padding-right: 10px;
    }

    .input-wrap {
        padding-left: 10px;
        padding-right: 10px;
    }

    .input-wrap input {
        display: inline-block;
        margin-top: 10px;
    }
</style>