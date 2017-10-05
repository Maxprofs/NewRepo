<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\modules\neweggcanada\components\Data;

$url = Data::getUrl('newegg-api/save');

?>
<div class="api_enable jet_config content-section test-api-step">
    <!-- <div class="">
           <p class="note"><b class="note-text">Note:</b> In order to obtain the LIVE API credentials fisrt all the TEST APIs MUST be running because Jet doesn’t provides LIVE API details until all TEST APIs have been set and running.</p>
    </div> -->
    <div>
        <h5>HOW TO GET AUTHORIZATION KEYS FROM NEWEGG MARKETPLACE
            <a href="https://cedcommerce.com/blog/how-to-get-authorization-keys-from-newegg-marketplace/"
               class="pull-right" target="_blank">Click Here</a>
        </h5>
    </div>
    <div class="api_field fieldset enable_api">
        <div class="help-block help-block-error top_error alert-danger"
             style="display: none;border-radius: 4px;margin-bottom: 10px;padding: 10px;"></div>
        <?php

        $form = ActiveForm::begin([
            'id' => 'newegg_api',
            'action' => $url,
            'method' => 'post',
            'options' => ['name' => 'Newegg_api'],
        ]) ?>
        <ul class="table table-sliiped" cellspacing="0">
            <li>
                <div>
                    <div class="value_label">
                        <span class="control-label">Newegg Seller Id</span>
                    </div>
                    <div class="form-group required">
                        <input placeholder="Please enter Seller Id" autofocus="autofocus" id="api-seller_id"
                               class="form-control" type="text" value="" name="seller_id" maxlength="255">
                        <div class="has-error">
                            <p class="help-block block-error" style="display: none;">'Newegg Seller Id' is Required</p>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </li>
            <li>
                <div>
                    <div class="value_label">
                        <span class="control-label">Newegg Authorization Key</span>
                    </div>
                    <div class="form-group required">
                        <input placeholder="Please enter Authorization Key" autofocus="autofocus" id="api-authorization"
                               class="form-control" type="text" value="" name="authorization">
                        <div class="has-error">
                            <p class="help-block block-error" style="display: none;">'Newegg Authorization Key' is
                                Required</p>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </li>
            <li>
                <div>
                    <div class="value_label">
                        <span class="control-label">Newegg Secret Key</span>
                    </div>
                    <div class="form-group required">
                        <input placeholder="Please enter Secret Key" autofocus="autofocus" id="api-secret_key"
                               class="form-control" type="text" value="" name="secret_key">
                        <div class="has-error">
                            <p class="help-block block-error" style="display: none;">'Newegg Secret Key' is Required</p>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </li>

            <li>
                <div>
                    <div class="clearfix">
                        <input type="button" class="btn btn-primary next" value="Next" id="test_button">
                    </div>
                </div>
            </li>

        </ul>
        <?php
        ActiveForm::end();
        ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var url = '<?php echo $url;?>';
        UnbindNextClick()
        $('.next').on('click', function (event) {
            //check validation
            event.preventDefault();
            var flag = false;
            $('form .required .form-control').each(function () {
                var value = $(this).val().trim();
                if (value == "") {
                    flag = true;
                    $(this).addClass("select_error");
                    $(this).next('div').children('.block-error').show();
                }
                else {
                    $(this).removeClass("select_error");
                    $(this).next('div').children('.block-error').hide();
                }
            });
            if (!flag) {
                $('#LoadingMSG').show();
                $.ajax({
                    method: "POST",
                    url: url,
                    dataType: "json",
                    data: $("form").serialize(),
                })
                    .done(function (response) {
                        $('#LoadingMSG').hide();
                        if (response.success) {
                            $('.top_error').hide();
                            nextStep();
                        } else {
                            $('.top_error').html(response.message);
                            $('.top_error').show();
                        }
                    });
            }
        });
    });
</script>
