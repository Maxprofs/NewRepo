<?php
use yii\helpers\Html;

$this->title = 'Update Product Information';
$this->params['breadcrumbs'][] = $this->title;

$importUrl = \yii\helpers\Url::toRoute(['neweggcustomwork/readcsv']);
?>
<div class="product_csv content-section">

    <div class="form new-section">
        <div class="jet-pages-heading">
            <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
            <a class="help_jet" id="instant-help" title="Need Help"></a>
            <div class="clear"></div>
        </div>

        <div class="csv_export col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="csv_import1">
                <form action="<?php echo $importUrl; ?>" method="post" enctype="multipart/form-data">
                    <h4>Import Updated Product Csv File</h4>
                    <p>After export csv file,all the product information and can be
                        entered in same csv file and information will be updated after upload the product csv file.</p>
                    <div class="input-wrap clearfix">
                        <input type="file" name="csvfile" id="browse"/>
                        <input type="submit" class="btn btn-primary" value="Import" id="import"/>
                    </div>
                </form>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>

