<?php
use yii\helpers\Html;

$this->title = 'Convert Csv According to Cedcommerce Format';
$this->params['breadcrumbs'][] = $this->title;
$convent_url = \yii\helpers\Url::toRoute(['convertcsv/convert']);
$product_update = json_encode([
    'id' => 'ID',
    'sku' => 'Sku',
    'type' => 'Product-type',
    'title' => 'Title',
    'fullfilment_lag_time' => 'Fullfilment-Lag-Time',
    'sku_override' => 'Sku-Override',
    'product_id_override' => 'Product-Id-Override',
    'short_description' => 'Short-Description',
    'self_description' => 'Self-Description',
    'product_type' => 'Product Type',
    'description' => 'Description',
    'product_taxcode' =>'Product-Taxcode'
]);
?>
    <div class="product_csv content-section">

        <div class="form new-section">
            <div class="jet-pages-heading">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
                <a class="help_jet" id="instant-help" title="Need Help"></a>
                <div class="clear"></div>
            </div>
            <?= Html::beginForm(['convertcsv/ajax-bulk-upload'], 'post', ['id' => 'walmart_bulk_product']); ?>
            <div class="csv_export col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="csv_import1">
                        <h4>Import Product Upload Csv File</h4>
                        <p>After export csv file,all the product information such as price and inventory can be
                            entered in same csv file and information will be updated after upload the product csv
                            file.</p>
                        <div class="input-wrap clearfix">
                            <select id= "csv_convert_type">
                              <option value="">
                                Select Convert Type
                              </option>
                              <option value="productupdate">
                                product update
                              </option>
                            </select>
                            <input type="file" name="csvfile" id="csvfile"/>
                            <button type="button" class="btn btn-primary noconfirmbox" id="import" onclick="readcsv()">Convert Csv As
                           </button>
                        </div>
                    </form>
                </div>
            </div>
            <?= Html::endForm() ?>
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
            min-height: 245px;
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

        .form-control {
            margin-left: 10px;
        }

        .csv-dropdown{
            float: left;
            margin-left: 10px;
            width: 25%;
        }
        #action-dropdown2{
            margin-left: 10px;
            width: 25%;
        }
    </style>
    <div id="convert" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">


            <div class="modal-body">
                <form id="convert-fields-form" method="post" action="">
                    <h4>Map Fields</h4>
                    <div class="convert-fields">
                        <!--  -->
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="convert-yes">ok</button>
                <button type="button" class="btn" id="convert-cancel" data-dismiss="modal">Cancel</button>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    function readcsv(){
       if ($("#csvfile").val().toLowerCase()) {  
           //Checks whether the browser supports HTML5  
           if (typeof (FileReader) != "undefined") { 
             
             var dropdown = $('#csv_convert_type').val();
             var array = [];
             if(dropdown=='productupdate'){
              array = '<?= $product_update ?>';
             }
             array = JSON.parse(array);
             var select = "<select class='select_value'>";
             select+="<option>Select Value</option>";
             $.each(array,function(key,value){
                  select+='<option value='+key+'>'+value+'</option>';
             });
             select = select+"</select>"; 
               var reader = new FileReader();  
               reader.onload = function (e) {  
                   var table = $("<table></table>");
                   var csvrows = e.target.result.split("\n");  
                   for (var i = 0; i < 1; i++) {  
                       if (csvrows[i] != "") { 
                           var csvcols = csvrows[i].split(",");  
                           for (var j = 0; j < csvcols.length; j++) {  
                               var cols = "<tr><td>" + csvcols[j] + "</td><td>"+select+"</td></tr>";  
                               table += cols;

                           }  
                       }  
                   }
                   $('.convert-fields').append(table);
                   $('#show-map-csvmodel').append(table)
                    $('body').attr('data-sync', 'show');
                    $('#edit-modal-close').click();
                    if ($('body').attr('data-sync') == 'show') {
                        $('#convert').modal('show');
                        $('body').removeAttr('data-sync');
                    }

                    $("#convert").on('shown.bs.modal', function () {
                        $('#convert-yes').unbind('click');
                        $('#convert-yes').on('click', function () {
                           alert("inn");
                        });
                    });
                   
               }  
               reader.readAsText($("#csvfile")[0].files[0]);  
           }  
           else {  
               alert("Sorry! Your browser does not support HTML5!");  
           }  
       }  
       else {  
           alert("Please upload a valid CSV file!");  
       }

    }
    var array = [];
    $('#convert').on('change','.select_value',function(){
      if($(this).val() != ""){
        if($.inArray($(this).val(), array) != '-1'){
          $(this).val('');
        }else{
          array.push($(this).val());
        }
      }
      
      
    });
</script>