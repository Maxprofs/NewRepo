<?php
$feebBackSaveUrl = \yii\helpers\Url::toRoute(['site/client-feedback']);
?>
<div id="feedbackform">
    <div style="display: none" id="feedbacksuccess" class="alert-success alert fade in"></div>
    <div style="display: none" id="feedbackerror" class="alert alert-danger"></div>
    <div class="feedbackform-wrapper">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <h1>did you faced any issue?</h1>
                    <p>feedBack Type</p>
                    <ul>
                        <li><input class="type" type="radio" name="type" value="comment"> Comment</li>
                        <li><input class="type" type="radio" name="type" value="bugreport"> Bug Report</li>
                        <li><input class="type" type="radio" name="type" value="question"> Question</li>
                    </ul>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <h1>Write Feedback</h1>
                        
                    <br>
                    <textarea id="description" name="description" rows="3" cols="25"
                              placeholder="write your feedback here"></textarea>
                    <button id='submit'>Submit</button>
            </div>
        </div>


    </div>
</div>

<script type="text/javascript">
    $('#submit').on('click', function () {
        var existurl = '<?= $feebBackSaveUrl; ?>';
        var type = $('.type:checked').val();
        if (!type) {
            $('#feedbacksuccess').css('display', 'none');
            $('#feedbackerror').css('display', 'block');
            $('#feedbackerror').html('Please select feedback type');
            setTimeout(function () {
                $('#feedbackerror').css('display', 'none');
            }, 3000);
            return false;
        }
        var description = $.trim($('#description').val());
        if (description.length == 0) {
            $('#feedbacksuccess').css('display', 'none');
            $('#feedbackerror').css('display', 'block');
            $('#feedbackerror').html('feedback description is required');
            setTimeout(function () {
                $('#feedbackerror').css('display', 'none');
            }, 3000);
            return false;
        }
        $.ajax({
            url: existurl,
            method: "post",
            dataType: 'json',
            data: {type: type, description: description},
            success: function (data) {
                if (data.success) {
                    $('#feedbackerror').css('display', 'none');
                    $('#feedbacksuccess').css('display', 'block');
                    $('#feedbacksuccess').html(data.message);
                    setTimeout(function () {
                        $('#feedbacksuccess').css('display', 'none');
                    }, 3000);
                } else {
                    $('#feedbacksuccess').css('display', 'none');
                    $('#feedbackerror').css('display', 'block');
                    $('#feedbackerror').html(data.message);

                }
            }
        })
    });

</script>

