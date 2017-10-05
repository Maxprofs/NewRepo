<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\modules\walmart\components\Data;
use dosamigos\datepicker\DatePicker;
?>
<script type="text/javascript" src="/integration/js/bootstrap-datepicker.js"></script>
<link rel="stylesheet" href="/integration/css/bootstrap-datepicker3.css"></link>
<div class="container">
	  <!-- Modal -->
	  <div class="modal fade" tabindex="-1" id="price-edit-modal" role="dialog" aria-labelledby="myLargeModalLabel">
	    <div class="modal-dialog modal-lg">
	      <!-- Modal content-->
	      <div class="modal-content" id='edit-content'>
	        <div class="modal-header">
	          <h4 class="modal-title" style="text-align: center;font-family: "Comic Sans MS";"></h4>
	        </div>
	        <div class="modal-body">
				<form id="promotions-form" action="<?= \yii\helpers\Url::toRoute(['walmartproduct/promotion-save']) ?>">
                
                    <input type="hidden" name="merchant_id" value="<?= $post['merchant_id'] ?>" />
                    <input type="hidden" name="option_id" value="<?= $post['option_id'] ?>" />
                    <input type="hidden" name="sku" value="<?= $post['sku'] ?>" />
                    <input type="hidden" name="product_id" value="<?= $post['product_id'] ?>" />
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>
                                    <center>Product Id</center>
                                </th>
                                <th>
                                    <center>Option Id</center>
                                </th>
                                <th>
                                    <center>Price</center>
                                </th>
                                <th>
                                    <center>Special Price</center>
                                </th>
                                <th>
                                    <center>Start Date</center>
                                </th>
                                <th>
                                    <center>End Date</center>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="promotion-tbody">
                            <?php foreach($promotions as $promotion){ ?>
                            <tr>
                                <td><input type="hidden" name="promotion[id][]" value="<?= $promotion['id'] ?>"?><?= $post['product_id'] ?></td>
                                <td><?= $post['option_id'] ?></td>
                                <td><input type="text" name="promotion[orignal_price][]" value="<?= $promotion['original_price'] ?>" /></td>
                                <td><input type="text" name="promotion[special_price][]" value="<?= $promotion['special_price'] ?>" /></td>
                                <td></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><button type="button" style="margin-left: 90%;" class="btn btn-primary" id="addPromotion"  >Add Promotion </button></td>
                            </tr>
                        </tfoot>
                        
                    </table>
                </form>
	        </div>
	        <div class="modal-footer Attrubute_html">
	          <div class="promotion_error_msg" style="display:none;"></div>
	          <div class="promotion_success_msg alert-success alert" style="display:none;"></div>
	          
	          <?= Html::submitButton('Save', ['class' => 'btn btn-primary','id'=>'save-promotion']) ?>
	          <button type="button" class="btn btn-default" id="close_promotions" data-dismiss="modal">Close</button>
	        </div>
	      </div>
	      
	      <div class="modal-content"  style="padding:20px;display:none">
				
		  </div>
		  		      
		</div>
	</div>	 
</div>
<script type="text/javascript">
    
    j$('#addPromotion').on('click',function(){
        $rowData = '<tr>\
                            <td><?= $post["product_id"] ?></td>\
                            <td><?= $post["option_id"] ?></td>\
                            <td><center><input type="text" name="promotion[orignal_price][]" value="<?= $post["price"] ?>"></center></td>\
                            <td><center><input type="text" name="promotion[special_price][]"></center></td>'+'<td><?php echo DatePicker::widget([
                    "name" => "promotion[effective_date][]",
                    "clientOptions"=>[
                    "autoclose"=>true,
                    "format"=>"yyyy-mm-dd",
                    ]
                    ]);?></td>'+'</tr>';
        j$('#promotion-tbody').append($rowData);
    });

    j$('#save-promotion').on('click',function(){
        var postData = j$("#promotions-form").serializeArray();
        //console.log(postData);
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        var formURL = j$("#promotions-form").attr("action");
        console.log(formURL);
        j$('#LoadingMSG').show();
            j$.ajax(
            {
                url : formURL,
                type: "POST",
                dataType: 'json',
                data : postData,
                _csrf : csrfToken,
                success:function(data, textStatus, jqXHR) 
                {
                    j$('#LoadingMSG').hide();
                    if(data.success)
                    {   
                        j$('.promotion_success_msg').html('');
                        j$('.promotion_success_msg').append(data.success);
                        j$('.promotion_error_msg').hide();
                        j$('.promotion_success_msg').show();
                        j$('#close_promotions').click();
                        
                    }
                    else
                    {

                        j$('.promotion_error_msg').html('');
                        j$('.promotion_error_msg').append(data.error);
                        j$('.promotion_success_msg').hide();
                        j$('.promotion_error_msg').show();
                        
                        
                    }

                    //data: return data from server
                },
                error: function(jqXHR, textStatus, errorThrown) 
                {
                    j$('.promotion_error_msg').html('');
                    j$('#LoadingMSG').hide();
                    j$('.promotion_error_msg').append("something went wrong..");
                    j$('.promotion_error_msg').show();
                }
            });
    });
    
</script>