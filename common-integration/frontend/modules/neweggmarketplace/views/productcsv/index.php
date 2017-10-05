<?php
use yii\helpers\Html;

$this->title = 'Update Product Information';
$this->params['breadcrumbs'][] = $this->title;

$importUrl = \yii\helpers\Url::toRoute(['productcsv/readcsv']);
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
                <form action="<?php echo \yii\helpers\Url::toRoute(['productcsv/export']); ?>" method="post">
                    <h4>Export Product Information</h4>
                    <p>Export csv file to update the product information.</p>
                    <select name="export[]" multiple="multiple" class="form-control csv-dropdown" id="action-dropdown1">
                        <!--<option value="all">All Product</option>-->
                        <option value="ACTIVATED">ACTIVATED</option>
                        <option value="DEACTIVATED">DEACTIVATED</option>
                        <option value="SUBMITTED">SUBMITTED</option>
                        <option value="Not Uploaded">NOT UPLOADED</option>
                        <option value="Partial Uploaded">Partial Uploaded</option>
                    </select>
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
