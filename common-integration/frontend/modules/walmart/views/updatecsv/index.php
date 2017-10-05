<?php
use yii\helpers\Html;

$this->title = 'Update Product Information';
$this->params['breadcrumbs'][] = $this->title;

$importUrl = \yii\helpers\Url::toRoute(['updatecsv/readcsv']);
?>
<div class="product_csv content-section">

    <div class="form new-section">
        <div class="jet-pages-heading">
            <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
            <a class="help_jet" id="instant-help" title="Need Help"></a>
            <div class="clear"></div>
        </div>
        <div class="csv_import col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="csv_import1">
                <form action="<?php echo \yii\helpers\Url::toRoute(['updatecsv/exportproduct']); ?>" method="post">
                    <h4>Export Product Information</h4>
                    <p>Get csv of all main products (excluding variants) to update the information. Just select products status and click on Export.</p>
                    <p class="command">Hold down the Ctrl (windows) / Command (Mac) button to select multiple options.</p>
                    <!-- <select name="export[]" multiple="multiple" class="form-control csv-dropdown" id="action-dropdown1">
                        <option value="all">ALL PRODUCT</option>
                        <option value="PUBLISHED">PUBLISHED</option>
                        <option value="UNPUBLISHED">UNPUBLISHED</option>
                        <option value="STAGE">STAGE</option>
                        <option value="Not Uploaded">NOT UPLOADED</option>
                        <option value="Item Processing">Item Processing</option>
                    </select> -->
                    <div class="product_information">
                        <ul id="action-dropdown1" class="form-control csv-dropdown">
                            <li><span>published</span> <a class="dismiss"></a></li>
                            <li><span>unpublished</span> <a class="dismiss"></a></li>
                            <li><span>stage</span> <a class="dismiss"></a></li>
                        </ul>
                    </div>
                    <div class="input-wrap">
                        <input type="submit" class="btn btn-primary" id='export' value="Export"/>
                    </div>
                </form>
            </div>
        </div>
        <div class="csv_export col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <div class="csv_import1">
                <form action="<?php echo $importUrl; ?>" method="post" enctype="multipart/form-data">
                    <h4>Import Updated Product Csv File</h4>
                    <p>After updation in csv you can import it here. Just browse your file and click on Import. All the changes will be shown on app and on Walmart too.</p>
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
<style>

    .csv_import {
        padding-left: 0;
        padding-right: 10px;
    }

    .csv_export {
        padding-left: 10px;
        padding-right: 0;
    }

    .csv_import1 {
        border: 1px solid #d4d4d4;
        border-radius: 3px 3px 0 0;
        margin: 25px 0;
        min-height: 275px;
    }

    .csv_import1 h4 {
        background: #dfdfdf none repeat scroll 0 0;
        font-size: 18px;
        margin: 0 0 15px;
        padding: 13px 10px;
    }

    .csv_import1 p {
        line-height: 25px;
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
    #action-dropdown1{
        float: left;
        margin-left: 10px;
        width: 25%;
    }
</style>
<script type="text/javascript">
    var intro = "";
    $(function () {

        var intro = introJs().setOptions({
            showStepNumbers: false,
            exitOnOverlayClick: false,
            doneLabel: 'Done',
            steps: [
                {
                    element: '#action-dropdown1',
                    intro: 'Select product status to export csv',
                    position: 'right'
                },
                {
                    element: '#export',
                    intro: ' Click here to export the CSV of all the products that are availabe on the app.',
                    position: 'left'
                },
                {
                    element: '#browse',
                    intro: "Click here to browse the CSV file that you have updated or edited.",
                    position: 'left'
                },
                {
                    element: '#import',
                    intro: "Click here to import the CSV file that you have browsed.",
                    position: 'left'
                }
            ]
        });
        $('#instant-help').click(function () {
            intro.start();
        });
    });
</script>
<?php $get = Yii::$app->request->get();
if (isset($get['tour'])) :
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            var productQuicktour = introJs().setOptions({
                doneLabel: 'Next page',
                showStepNumbers: false,
                exitOnOverlayClick: false,
                steps: [
                    {
                        element: '#action-dropdown1',
                        intro: 'Select product status to export csv',
                        position: 'right'
                    },
                    {
                        element: '#export',
                        intro: ' Click here to export the CSV of all the products that are available on the app.',
                        position: 'left'
                    },
                    {
                        element: '#browse',
                        intro: "Click here to browse the CSV file that you have updated or edited.",
                        position: 'left'
                    },
                    {
                        element: '#import',
                        intro: "Click here to import the CSV file that you have browsed.",
                        position: 'left'
                    }
                ]
            });

            setTimeout(function () {
                productQuicktour.start().oncomplete(function () {
                    window.location.href = '<?= \frontend\modules\walmart\components\Data::getUrl("productcsv/index?tour") ?>';
                }, 1000);
            });
        });
    </script>
<?php endif; ?>
