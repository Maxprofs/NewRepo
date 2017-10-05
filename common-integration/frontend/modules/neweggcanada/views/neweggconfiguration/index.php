<?php
use frontend\modules\neweggcanada\models\NeweggConfiguration;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\modules\neweggcanada\components\Data;

$this->title = 'Newegg Configurations';
$this->params['breadcrumbs'][] = $this->title;
$isPrice = false;
$priceType = "";
$priceValue = "";
$query = "SELECT * FROM `newegg_can_email_template`";
$email = Data::sqlRecords($query, "all");

?>
<script>
</script>
<div class="jet-configuration-index content-section">
    <div class="jet_configuration form new-section">
        <div class="jet-pages-heading">
            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
                <a class="help_jet" target="_blank"
                   href="https://shopify.cedcommerce.com/integration/newegg-marketplace/sell-on-newegg"
                   title="Need Help"></a>
            </div>
            <div class="product-upload-menu">
                <button type="button" id="instant-help" class="btn btn-primary">Help</button>
                <input type="submit" name="submit" value="save" class="btn btn-primary" onclick="$('#newegg_config').submit();">
            </div>
            <div class="clear"></div>
        </div>
        <?php $form = ActiveForm::begin([
            'id' => 'newegg_config',
            'action' => \yii\helpers\Url::toRoute(['neweggconfiguration/index']),
            'method' => 'post',
            'options' => ['name' => 'newegg_configupdate'],
        ]) ?>
<!--        <input type="hidden" name="<?/*= Yii::$app->request->csrfParam; */?>"
               value="<?/*= Yii::$app->request->csrfToken; */?>"/>-->

        <div class="ced-entry-heading-wrapper">
            <div class="entry-edit-head">
                <h4 class="fieldset-legend">Newegg Setting</h4>
            </div>
            <div class="fieldset enable_api" id="api-section">

                <table class="table table-striped table-bordered" cellspacing="0">
                    <tbody>
                    <tr>
                        <td class="value_label">
                            <span>Seller Id</span>
                        </td>
                        <td>
                            <span><input id="seller_id" type="text" name="seller_id"
                                         value="<?php if (isset($data['seller_id'])) {
                                             echo trim($data['seller_id']); ?>

                                <?php } ?>" class="form-control"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="value_label">
                            <span>Authorization</span>
                        </td>
                        <td>
                            <!-- <span><textarea rows="4" cols="50" id="authorization" name="authorization"></textarea></span>-->
                            <span><input type="text" id="authorization" name="authorization"
                                         value="<?php if (isset($data['authorization'])) {
                                             echo trim($data['authorization']); ?>

                                <?php } ?>" class="form-control"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="value_label">
                            <span>Newegg Secret Key</span>
                        </td>
                        <td>
                            <span><input type="text" id="secret_key" name="secret_key"
                                         value="<?php if (isset($data['secret_key'])) {
                                             echo trim($data['secret_key']); ?>

                                <?php } ?>" class="form-control"></span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

              <!-- Manufacturer setting start-->
<!-- 
        <div class="ced-entry-heading-wrapper">
            <div class="entry-edit-head">
                <h4 class="fieldset-legend">Newegg Manufacturer</h4>
            </div>

            <div class="fieldset enable_api" id="manufacturer">
                <table class="table table-striped table-bordered" cellspacing="0">
                    <tbody>
                    <tr>
                        <td class="value_label">
                            <span>Manufacturer Name</span>

                            <span class="text-validator">Alteast one manufacturer are required for uploading product.</span>
                        </td>
                        <td class="value" id="manufacturer">
                            <span>
                                <span>
                               <input id="manufacturer" type="text" name="manufacturer"
                                         value="<?php if (isset($data['manufacturer'])) {
                                             echo trim($data['manufacturer']); ?>

                                <?php } ?>" class="form-control" required oninvalid="this.setCustomValidity('Manufacturer  name cannot be empty.')" 
                                    onchange="this.setCustomValidity('')">
                            </span>
                            </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div> -->

        <!-- Manufacturer setting End  -->

        <div class="ced-entry-heading-wrapper">
            <div class="entry-edit-head">
                <h4 class="fieldset-legend">Orders Setting</h4>
            </div>

            <div class="fieldset enable_api" id="cancel-order-section">
                <table class="table table-striped table-bordered" cellspacing="0">
                    <tbody>
                    <tr>
                        <td class="value_label">
                            <span>Auto Cancel Orders</span>

                            <span class="text-validator">Want to automatically cancel orders when product inventory is out of stock or sku is not available.</span>
                        </td>
                        <td class="value" id="cancel_order">
                            <span>
                                 <select name="cancel_order" class="form-control cancel_order">
                                    <option
                                        value="No" <?php if (isset($clientData['cancel_order']) && $clientData['cancel_order'] == "No") {
                                        echo "selected=selected";
                                    } ?>>No</option>
                                    <option
                                        value="Yes" <?php if (isset($clientData['cancel_order']) && $clientData['cancel_order'] == "Yes") {
                                        echo "selected=selected";
                                    } ?>>Yes</option>
                                </select>
                            </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="fieldset enable_api" id="cancel-order-section">
                <table class="table table-striped table-bordered" cellspacing="0">
                    <tbody>
                    <tr>
                        <td class="value_label">
                            <span> Shopify Order Sync </span>

                            <span class="text-validator">Set 'No' if you don't want to sync order(s) in shopify store.</span>
                        </td>
                        <td class="value" id="shopify_order_sync">
                            <span>
                                 <select name="shopify_order_sync" class="form-control shopify_order_sync">
                                    <option
                                            value="No" <?php if (isset($clientData['shopify_order_sync']) && $clientData['shopify_order_sync'] == "No") {
                                        echo "selected=selected";
                                    } ?>>No</option>
                                    <option
                                            value="Yes" <?php if (isset($clientData['shopify_order_sync']) && $clientData['shopify_order_sync'] == "Yes") {
                                        echo "selected=selected";
                                    } ?>>Yes</option>
                                </select>
                            </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

  

        <?php if (isset($email) && !empty($email)) { ?>
            <div class="ced-entry-heading-wrapper">
                <div class="entry-edit-head">
                    <h4 class="fieldset-legend">Email Subscription Setting</h4>
                </div>
                <div class="fieldset enable_api" id="email-subscription-section">
                    <table class="table table-striped table-bordered" cellspacing="0">
                        <tbody>

                        <?php foreach ($email as $key => $value): ?>
                            <?php if ($value['show_on_admin_setting'] == 1): ?>
                                <tr>
                                <td class="value_label">
                                    <label><span><?php echo $value['custom_title']; ?></span></label>
                                </td>
                                <?php if (isset($clientData['email/' . $value['template_title']]) && !empty($clientData['email/' . $value['template_title']])): ?>
                                    <td>
                                        <input type="checkbox" id="email/<?php echo $value['template_title']; ?>"
                                               name="email/<?php echo $value['template_title']; ?>"
                                               value="<?= $clientData['email/' . $value['template_title']]; ?>" checked>

                                    </td>
                                <? else: ?>
                                    <td class="value_label">
                                        <input type="checkbox" id="email/<?php echo $value['template_title']; ?>"
                                               name="email/<?php echo $value['template_title']; ?>" value="1">
                                    </td>
                                    </tr>
                                <?php endif; ?>

                            <?php endif; ?>
                        <?php endforeach; ?>

                        </tbody>
                    </table>

                </div>
            </div>
        <?php } ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<style>
    .jet-configuration-index .value_label {
        width: 50%;
    }

    .jet-configuration-index .table-striped select, .jet-configuration-index .form-control {
        width: 100%;
        display: inline-block;
        padding-left: 10px;
    }

    .jet-configuration-index .value {
        border: medium none !important;
        display: inline-block;
        width: 100%;
    }

    #custom_price_csv span, #custom_title_csv span {
        width: 85%;
        display: inline-block;
    }

    .jet-configuration-index .help_jet {
        display: inline-block;
    }

    .help_jet {
        width: 50px !important;
    }

    .jet-configuration-index .value_label {
        width: 50%;
    }

</style>

<script type="text/javascript">
    $('#instant-help').click(function () {
        var configQuicktour = introJs().setOptions({
            doneLabel: 'Finish',
            showStepNumbers: false,
            exitOnOverlayClick: false,
            steps: [
                {
                    element: '#api-section',
                    intro: 'Edit the Newegg API details.',
                    position: 'bottom'
                },

             /*    {
                 element: '#manufacturer',
                 intro: 'Global manufacturer name for all product .',
                 position: 'bottom'
                 },*/
                /*{
                    element: '#return-location-section',
                    intro: 'Update Walmart Return Location Address.',
                    position: 'bottom'
                },
                {
                    element: '#product-setting',
                    intro: 'Globally apply product tax code,set custom product price and remove free shipping from all product',
                    position: 'bottom'
                },*/

                {
                 element: '#cancel-order-section',
                 intro: 'Manage Cancel Order Setting.',
                 position: 'bottom'
                 },

                /*     {
                 element: '#custom-pricing-section',
                 intro: 'Manage Product custom/dynamic pricing.',
                 position: 'bottom'
                 },*/
                /*     {
                 element: '#custom_price_csv_field',
                 intro: 'Select "Yes" to update price of each product.',
                 position: 'left'
                 },
                 {
                 element: '#custom_price_csv_label',
                 intro: "Get CSV file by clicking 'CLICK HERE'.",
                 position: 'bottom'
                 },
                 {
                 element: '#custom-title-section',
                 intro: 'Manage Product custom/dynamic Title Setting.',
                 position: 'bottom'
                 },
                 {
                 element: '#custom_title_csv_field',
                 intro: 'Select "Yes" to update title.',
                 position: 'left'
                 },
                 {
                 element: '#custom_title_csv_label',
                 intro: "Get CSV file by clicking 'CLICK HERE'.",
                 position: 'bottom'
                 },*/
                {
                    element: '#email-subscription-section',
                    intro: 'Update Email Subscription Setting from here. Check the corresponding Checkbox to receive Mails and Uncheck to Not Receive Mails.',
                    position: 'bottom'
                }

            ]
        });

        configQuicktour.start().oncomplete(function () {
            window.location.href = '<?= Data::getUrl("site/index") ?>';
        });
    });
</script>

<?php $get = Yii::$app->request->get();
if (isset($get['tour'])) :
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            var configQuicktour = introJs().setOptions({
                doneLabel: 'Finish',
                showStepNumbers: false,
                exitOnOverlayClick: false,
                steps: [
                    {
                        element: '#api-section',
                        intro: 'Edit the Newegg API details.',
                        position: 'bottom'
                    },
                    {
                     element: '#cancel-order-section',
                     intro: 'Manage Cancel Order Setting.',
                     position: 'bottom'
                     },
                ]
            });

            setTimeout(function () {

                configQuicktour.start().oncomplete(function () {
                    window.location.href = '<?= Data::getUrl("site/index") ?>';
                });
            },1000);
        });
    </script>
<?php endif; ?>

