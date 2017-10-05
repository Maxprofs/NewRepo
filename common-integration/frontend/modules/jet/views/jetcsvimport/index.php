<?php
use yii\helpers\Html;

$this->title = 'Import New Products';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="product_csv content-section">    
    <div class="row form new-section">  
          <div class="jet-pages-heading">
            <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
            <div class="clear"></div>
        </div>
        <p class="jet-partner-panel-para">Download sample csv file <a href="<?php echo \yii\helpers\Url::toRoute(['jetcsvimport/samplecsv']);?>"
            method="post" enctype="multipart/form-data">[from here]</a> to import new product in app </p>
        <div class="csv_import col-lg-12 col-md-6 col-sm-6 col-xs-12">
            <div class="csv_import1">
                <form action="<?php echo \yii\helpers\Url::toRoute(['jetcsvimport/import']); ?>" method="post"
                      enctype="multipart/form-data">
                    <h4>Import CSV to insert Products</h4>
                    <div class="input-wrap">
                        <input type="file" name="csvfile"/>
                        <input type="submit" class="btn btn-primary" value="Submit"/>
                    </div>
                </form>
            </div>
        </div>        
    </div>
</div>
<style>
    .jet-partner-panel-para{
        margin: 10px 0 10px 15px;
    }
    .row.form.new-section{
        width: 95%;
    }
    input {
        display: inline-block;
    }

    .csv_export {
        padding-left: 10px;
        padding-right: 0;
        align-self: center;
    }

    .csv_import1 {
        border: 1px solid #d4d4d4 !important;
        border-radius: 3px 3px 0 0;
        margin: 25px 0;
        min-height: 150px;
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
    }
</style>