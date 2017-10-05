<?php
use frontend\modules\jet\components\Data;

$urlcallschedule = \yii\helpers\Url::toRoute(['site/requestcall']);
$merchant_id = MERCHANT_ID;

$data = Data::sqlRecords("SELECT * FROM `call_schedule` WHERE `status`='".Data::CALL_SCHEDULE_STATUS."' AND `marketplace`='".Data::MARKETPLACE."' AND `merchant_id` ='".$merchant_id."' ", 'all');

?>
<style>
    #view_call .modal-body h3{
        text-align: center;
    }
    #view_call .modal-body div{
        margin: 20px;
    }
    .modal-content .modal-body {
      padding-top: 0;
    }
    #view_call .modal-body > div span:first-child{
        display: block;
    }
    #view_call .modal-body input, #view_call .modal-body select {
      border: 1px solid #aaa;
      border-radius: 4px;
      box-sizing: border-box;
      padding: 7px 10px;      
  }
  #view_call .modal-body select {
    width: 32%;
  }
  #view_call .modal-body input {
      width: 100%;
  }
  #view_call .modal-body .btn.btn-primary {
      display: table;
      margin: 0 auto;
  }
  .ist {
  float: right;
}
  #view_call .modal-body h4{
    text-align: center;
}
</style>
<div class="container">
    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="text-align: center;font-family: " Comic Sans MS";">Schedule a Call Back
                     !!!</h4>
                 </div>
                <?php if(empty($data))
                { 
                    ?>

                    <div class="modal-body">
                        <h4 style="text-align: center;">Let us contact you at your best availability!
                        </h4>
                        <h4>Tell us the time and date</h4>
                        <div>
                            <span> Enter your phone number: </span>
                            <input class="callschedule-input" type="text"
                                   id="walmart_call_schedule" name="walmart_call_schedule" autocomplete="off"
                                   placeholder="+1 201-555-5555"
                                   value="">                           
                        </div>
                        <div>
                            <span>Choose Date</span>
                            <input class="callschedule-input" size="16" type="text" value="" readonly id="form_datetime" placeholder="dd-mm-YYYY">
                        </div>

                        <div>
                            <span>Choose Time Slot</span>
                            <select name="time1" id="time1">
                                <option value="">Choose Time</option>
                                <?php
                                for($i = 1 ; $i < 12 ; $i++)
                                { ?>
                                    <option value="<?= $i. ' to ' . ($i+1) ?>"><?= $i. ' to ' . ($i+1) ?></option>
                                <?php }
                                ?>
                            </select>
                            <select name="time2" id="time2">
                                <option value="">Select Format </option>
                                <option value="AM">AM</option>
                                <option value="PM">PM</option>
                            </select>
                            <select name="time3" id="time3" class="ist">
                                <option value="">Select Time Zone</option>
                                <option value="IST">IST</option>
                                <option value="EST">EST</option>
                                <option value="GMT">GMT</option>
                                <option value="UTC">UTC</option>
                            </select>
                        </div>

                        <div>
                            <button class="btn btn-primary" type="button"
                                    onClick="callschedule(this);" title="Number for call schedule">Request a call-back
                            </button>
                        </div>                    
                    </div>

                <?php }else{ ?>

                    <div class="modal-body">
                        <p>Your request is being processed, you would get a call back from one of our executives soon.</p>
                    </div>
                <?php } ?>

                <div class="modal-footer Attrubute_html">
                    <div class="v_error_msg" style="display:none;"></div>
                    <div class="v_success_msg alert-success alert" style="display:none;"></div>
                </div>

            </div>
        </div>
    </div>
</div>
<script>
    function callschedule() 
    {
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        var phonenumber = $('#walmart_call_schedule').val();
        var date = $('#form_datetime').val();
        var time = $('#time1').val();
        var format = $('#time2').val();
        var time_zone = $('#time3').val();
        if (phonenumber.trim()=="") 
        {
            alert("Please enter contact number");
            return false;
        }else if(date.trim()=="") 
        {
            alert("Please choose preferred date for call back");
            return false;
        }
        else if( format.trim()=="" || time_zone.trim()=="" )
        {
            alert("Please choose your prefered time frame");
            return false;
        }
        var url = '<?= $urlcallschedule; ?>';
        $.ajax({
            method: "post",
            url: url,
            dataType: 'json',
            data: {
                number: phonenumber,
                date: date,
                time:time,
                format:format,
                time_zone:time_zone,
                _csrf: csrfToken
            }
        })
        .done(function (msg) 
        {
            if (msg.success) 
            {
                $('.v_success_msg').html('');
                $('.v_success_msg').append("Your request for call is accepted.You will get call back from our team soon.");
                $('.v_error_msg').hide();
                $('.v_success_msg').show();
                setTimeout(function () {
                    window.location.reload();
                }, 3000);
            }
            else if (msg.error) 
            {
                $('.v_error_msg').html('');
                $('.v_error_msg').append(msg.message);
                $('.v_success_msg').hide();
                $('.v_error_msg').show();
            }
            else 
            {
                $('.v_error_msg').html('');
                $('.v_error_msg').append("something went wrong.");
                $('.v_success_msg').hide();
                $('.v_error_msg').show();
            }
        });
    }
    $("#form_datetime").datepicker({ minDate: +1, maxDate: "+1M +10D",dateFormat:"dd-mm-yy" });
</script>