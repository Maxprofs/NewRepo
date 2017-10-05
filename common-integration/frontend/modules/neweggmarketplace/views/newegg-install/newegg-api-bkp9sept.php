<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\modules\neweggmarketplace\components\Data;

$url = Data::getUrl('newegg-api/save');

?>
<div class="api_enable newegg_config content-section test-api-step" xmlns="http://www.w3.org/1999/html">

    <div>
        <h5>HOW TO GET AUTHORIZATION KEYS FROM NEWEGG MARKETPLACE
            <a href="https://cedcommerce.com/blog/how-to-get-authorization-keys-from-newegg-marketplace/"
               class="pull-right" target="_blank">Click Here</a>
        </h5>
    </div>
    <div class="api_field fieldset enable_api">
        <div class="help-block help-block-error top_error alert-danger"
             style="display: none;border-radius: 4px;margin-bottom: 10px;padding: 10px;"></div>
        <div class="help-block help-block-success top_success alert-success"
             style="display: none;border-radius: 4px;margin-bottom: 10px;padding: 10px;"></div>

        <div class="cards-wrapper wraps">
            <ul class="nav nav-tabs">
                <li id="newegg-us" class="tabs active"><a data-toggle="tab" href="#open_us">US</a>
                </li>
                <li id="newegg-can" class="tabs"><a data-toggle="tab"
                                                        href="#open_can">CANADA</a></li>
            </ul>

            <div class="tab-content">
                <div id="open_us" class="tab-pane fade in active">
                    <div>
                        <?php

                        $form = ActiveForm::begin([
                            'id' => 'newegg_api_us',
                            'action' => $url,
                            'method' => 'post',
                            'options' => ['name' => 'Newegg_api_us'],
                        ]) ?>
                        <ul class="table table-sliiped" cellspacing="0">
                            <li>

                                <input class="form-control " type="hidden" value="1" name="newegg_us_detail"
                                       maxlength="255">

                            </li>
                            <li>
                                <div>
                                    <h4>Enter Newegg US Api</h4>

                                    <div class="value_label">
                                        <span class="control-label">Newegg Seller Id</span>
                                    </div>
                                    <div class="form-group required">
                                        <input placeholder="Please enter Seller Id" autofocus="autofocus"
                                               id="api-seller_id"
                                               class="form-control newegg-us" type="text" value="" name="seller_id"
                                               maxlength="255">
                                        <div class="has-error">
                                            <p class="help-block block-error" style="display: none;">'Newegg Seller Id'
                                                is Required</p>
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
                                        <input placeholder="Please enter Authorization Key" autofocus="autofocus"
                                               id="api-authorization"
                                               class="form-control newegg-us" type="text" value=""
                                               name="authorization">
                                        <div class="has-error">
                                            <p class="help-block block-error" style="display: none;">'Newegg
                                                Authorization Key' is
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
                                        <input placeholder="Please enter Secret Key" autofocus="autofocus"
                                               id="api-secret_key"
                                               class="form-control newegg-us" type="text" value="" name="secret_key">
                                        <div class="has-error">
                                            <p class="help-block block-error" style="display: none;">'Newegg Secret Key'
                                                is Required</p>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </li>
                            <li>
                                <div class="newegg_us_default">

                                    <input id="input" type="checkbox" value="1" name="newegg_us_default">
                                        </input>
                                        <label>
                                    Default Store
                                    </label>
                                </div>
                            </li>
                            <li>
                                <div>
                                    <div class="clearfix">
                                        <input type="button" class="btn btn-primary next-us" value="Submit"
                                               id="test_button">
                                    </div>
                                </div>
                            </li>

                        </ul>
                        <?php
                        ActiveForm::end();
                        ?>
                    </div>


                </div>
                <div id="open_can" class="tab-pane fade">

                    <div>
                        <?php

                        $form = ActiveForm::begin([
                            'id' => 'newegg_api_can',
                            'action' => $url,
                            'method' => 'post',
                            'options' => ['name' => 'Newegg_api_can'],
                        ]) ?>
                        <ul class="table table-sliiped" cellspacing="0">
                            <li>

                                <input class="form-control .newegg-can" type="hidden" value="1" name="newegg_can_detail"
                                       maxlength="255">

                            </li>
                            <li>
                                <div>
                                    <div>
                                        <h4>Enter Newegg CANADA Api</h4>
                                    </div>
                                    <div class="value_label">
                                        <span class="control-label">Newegg Seller Id</span>
                                    </div>
                                    <div class="form-group required">
                                        <input placeholder="Please enter Seller Id" autofocus="autofocus"
                                               id="api-seller_id"
                                               class="form-control newegg-can" type="text" value="" name="seller_id"
                                               maxlength="255">
                                        <div class="has-error">
                                            <p class="help-block block-error" style="display: none;">'Newegg Seller Id'
                                                is Required</p>
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
                                        <input placeholder="Please enter Authorization Key" autofocus="autofocus"
                                               id="api-authorization"
                                               class="form-control newegg-can" type="text" value=""
                                               name="authorization">
                                        <div class="has-error">
                                            <p class="help-block block-error" style="display: none;">'Newegg
                                                Authorization Key' is
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
                                        <input placeholder="Please enter Secret Key" autofocus="autofocus"
                                               id="api-secret_key"
                                               class="form-control newegg-can" type="text" value="" name="secret_key">
                                        <div class="has-error">
                                            <p class="help-block block-error" style="display: none;">'Newegg Secret Key'
                                                is Required</p>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </li>
                            <li>
                                <div class="newegg_can_default">
                                        <input id="input_can" type="checkbox" value="1" name="newegg_can_default" /><!-- <input id="newegg-can" type="checkbox" value="1" name="newegg_can_default" /> -->
                                    <label>
                                        Default Store
                                    </label>
                                </div>
                            </li>

                            <li>
                                <div>
                                    <div class="clearfix">
                                        <input type="button" class="btn btn-primary next-can" value="Submit"
                                               id="test_button">
                                    </div>
                                </div>
                            </li>

                        </ul>
                        <?php
                        ActiveForm::end();
                        ?>
                    </div>

                </div>
            </div>
        </div>
        <div>
            <div class="clearfix">
                <input type="button" class="btn btn-primary next-step" value="Next" id="test_button">
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('.next-step').css('display','none');
        var url = '<?php echo $url;?>';
        UnbindNextClick()
        $('.next-us').on('click', function (event) {
            //check validation
            event.preventDefault();
            var flag = false;
            $('form .required .newegg-us').each(function () {
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
                    data: $("#newegg_api_us").serialize(),
                })
                    .done(function (response) {
                        $('#LoadingMSG').hide();
                        if (response.success) {
                            $('.top_error').hide();
                            /*$('.next').bind('click');*/
                            $('.top_success').html(response.message);
                            $('.top_success').show();
                            $('.next-step').css('display','block');
                            /*$('.newegg_can_default').css('display','none');*/
                        } else {
                            $('.top_error').html(response.message);
                            $('.top_error').show();
                        }
                    });
            }
        });


        $('.next-can').on('click', function (event) {
            //check validation

            event.preventDefault();
            var flag = false;
            $('form .required .newegg-can').each(function () {
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
                    data: $("#newegg_api_can").serialize(),
                })
                    .done(function (response) {
                        $('#LoadingMSG').hide();
                        if (response.success) {
                            $('.top_error').hide();
                            /*$('.next').unbind('click');*/
                            $('.top_success').html(response.message);
                            $('.top_success').show();
                            $('.next-step').css('display','block');
                            $('.newegg_us_default').css('display','none');


                        } else {
                            $('.top_error').html(response.message);
                            $('.top_error').show();
                        }
                    });
            }
        });

        $('.next-step').on('click', function (event) {
            //check validation
            event.preventDefault();
            nextStep();
        });

    });
</script>
<style type="text/css">

.newegg_config .tabs.active a {
    color: #fff !important;
}
.newegg_config .tabs.active {
    background: #d97704 none repeat scroll 0 0;
    border-bottom: 3px solid #c96700;
}
.newegg_config .nav.nav-tabs {
    background: #c4c4c4 none repeat scroll 0 0;
}
#newegg-us, #newegg-can {
    text-align: center;
    width: 50%;
}
#newegg-us > a:hover, #newegg-can > a:hover {
    color: #993700;
}
.newegg_us_default > label,.newegg_can_default > label {
    margin: 13px 0 0 5px;
}
.newegg_us_default > input,.newegg_can_default > input {
    float: left;
}
#newegg-us, #newegg_can {
    text-align: center;
    width: 50%;
}
#newegg-us > a:focus, #newegg-can > a:focus {
    background-color: transparent;
    border: 0 solid #fff;
    color: #fff;
    outline: medium none;
}
#newegg-can > a, #newegg-us a {
    color: #000;
}
</style>