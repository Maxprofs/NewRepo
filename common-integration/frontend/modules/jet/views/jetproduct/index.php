<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use frontend\modules\jet\models\JetProduct;
use frontend\modules\jet\components\Data;

$this->title = 'Manage Products';
$this->params['breadcrumbs'][] = $this->title;

$merchant_id = MERCHANT_ID;
$urlJet= \yii\helpers\Url::toRoute(['jetproduct/getjetdata']);
$urlJetEdit= \yii\helpers\Url::toRoute(['jetproduct/editdata']);
$urlJetPrice = \yii\helpers\Url::toRoute(['jetrepricing/dynamicprice']);
$urlJetError= \yii\helpers\Url::toRoute(['jetproduct/errorjet']);
$saveVariantImageUrl= \yii\helpers\Url::toRoute(['jetproduct/changevariantimage']);
$urlJetShip= \yii\helpers\Url::toRoute(['jetproduct/viewshipexception']);
$batchupdateurl = \yii\helpers\Url::toRoute(['jetproduct/batchproductupdate']);
$syncshopifyproducturl = \yii\helpers\Url::toRoute(['jetproduct/syncshopifyproduct']);
$categorymapurl = \yii\helpers\Url::toRoute(['categorymap/index']);
?>
<?php if(JetProduct::find()->select('id')->where(['merchant_id'=>$merchant_id])->count()==0):?>
    <div class="yii_notice no_product_error">
        Either there is no product(s) in your shopify store or all product(s) having no sku.Please fill sku for all product(s) from Shopify Store.
    </div>
<?php endif;?>

<script src="//cdn.ckeditor.com/4.6.2/standard/ckeditor.js"></script>
<div class="jet-product-index content-section">
    <div class="form new-section">
        <?=Html::beginForm(['jetproduct/bulk'],'post',['id'=>'jet_bulk_product'/*,'onsubmit'=>'return validateBulkAction()'*/]);?>
        <div class="jet-pages-heading ">
            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
            </div>
            <div class="product-upload-menu m-menu confirmbox">
            	<?= Html::a('Sync status', yii\helpers\Url::toRoute('jet-report/index'), ['data-step'=>'8', 'data-position'=>'left', 'data-intro'=>'Sync status(s)' ,'class' => 'btn btn-primary']) ?>
                <button type="button" class="btn btn-primary" id="sync_with_btn" data-step='9' data-position='left' data-intro='Click to select and Sync product details from shopify store to app' onclick="cnfrmSync()">Select & Sync Product(s)</button>
                <?=Html::a('Bulk Upload Products', yii\helpers\Url::toRoute('jetproduct-fileupload/startupload'),['class'=>'btn btn-primary','data-step'=>'11', 'data-position'=>'left']);?>  
                <?= Html::a('Validate Product(s)', ['jetvalidate/index'], [ 'id' => 'product_validate', 'data-step'=>'13', 'data-position'=>'left', 'data-intro'=>'Validate Product(s) as per Jet Requirements.' ,'class' => 'btn btn-primary']) ?>                                                                     
                <?php if($avail_for_sale)
                {
                    ?>                        
                        <?=Html::a('Fetch Marketplace Pricing', yii\helpers\Url::toRoute('jetrepricing/getrepricing'),['class'=>'btn btn-primary','data-step'=>'14', 'data-position'=>'left','title'=>'Get and compare your product price with competitor price','data-intro'=>'Get and compare your product price with competitor price']);?>
                    <?php                        
                }
               ?> 
               <?= Html::a('Reset Filter', ['jetproduct/index'], [ 'data-toggle'=>'tooltip','title'=>'Click to reset all filters','data-position'=>'top','class' => 'btn btn-primary', 'data-step'=>'10', 'data-position'=>'bottom', 'data-intro'=>'Click to reset all filters']) ?>
            </div>
            <div class="product-upload-menu confirmbox">
                <div class="jet-upload-submit"></div>
					<?= Html::a('Sync status', yii\helpers\Url::toRoute('jet-report/index'), ['data-step'=>'8', 'data-position'=>'left', 'data-intro'=>'Sync status(s)' ,'class' => 'btn btn-primary']) ?>
                    <button type="button" class="btn btn-primary" id="sync_with_btn" data-step='9' data-position='left' data-intro='Click to select and Sync product details from shopify store to app' onclick="cnfrmSync()">Select & Sync Product(s)</button>
                    
                    <?=Html::a('Bulk Upload Products', yii\helpers\Url::toRoute('jetproduct-fileupload/index'),['class'=>'btn btn-primary','data-step'=>'10', 'data-position'=>'left','data-intro'=>'Upload all valid products to jet in one click']);?>                    
                    <span class="pop_up" id="view_more_options" data-step="11" data-position="bottom" data-intro="Click to see more features" data-toggle = 'tooltip' title="Click to see more features"><i class="fa fa-bars" aria-hidden="true"></i></span>

                    <div class="popup-box confirmbox" style="display: none;">
                        <?= Html::a('Reset Filter', ['jetproduct/index'], ['data-position'=>'top','class' => 'btn btn-primary', 'data-step'=>'12', 'data-position'=>'bottom', 'data-intro'=>'Click to reset all filters']) ?> 
                        <?/*<?=Html::a('Manual Sync Products(s)', yii\helpers\Url::toRoute('jetproduct/batchproductupdate'),['class'=>'btn btn-primary','data-step'=>'13','data-intro'=>'Click to sycn product information', 'data-position'=>'left']);*/?>
                        <?php /*<a class="btn btn-primary" href="<?= yii\helpers\Url::toRoute('jetproduct/batchproductupdate') ?>" data-step="13" data-position="left" data-intro="Synchronize shopify products with Jet">Manual Sync Products(s)<?php if($countUpdate>0){?><span class="sync_notication"><?php echo $countUpdate;?></span><?php }?></a>
                        */?>
                        <?= Html::a('Validate Product(s)', ['jetvalidate/index'], [ 'id' => 'product_validate','data-step'=>'13', 'data-position'=>'left', 'data-intro'=>'Validate Product(s) as per Jet Requirements.' ,'class' => 'btn btn-primary']) ?>  
                        <?php if($avail_for_sale)
                        {?>                        
                            <?=Html::a('Fetch Marketplace Pricing', yii\helpers\Url::toRoute('jetrepricing/getrepricing'),['class'=>'btn btn-primary','data-step'=>'14', 'data-position'=>'left','title'=>'Get and compare your product price with competitor price','data-intro'=>'Get and compare your product price with competitor price']);?>
                            <?php                        
                        }
                       ?>                                           
                    </div>
                </div>
            <div class="clear"></div>
        </div>
        <div class="jet_notice" style="background-color: #FCF8E3;">
            <div class="col-lg-7 col-md-7 col-sm-12 col-xs-12">
                <span class="font_bell">
                    <i class="fa fa-list" aria-hidden="true"></i>
                </span>
                Don't see all of your products? Just click <a href="<?= $categorymapurl ?>">here</a> to map all shopify product type(s) with jet category.
            </div>
            <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                <div class="product-upload-menu">
                    Show per page
                    <select onchange="selectPage(this)" class="form-control" style="display: inline-block; width: auto; margin-top: 0px; margin-left: 5px; margin-right: 5px;" name="per-page">
                        <option value="25" <?php if(isset($_GET['per-page']) && $_GET['per-page']==25){echo "selected=selected";}?>>25</option>
                        <option <?php if(!isset($_GET['per-page'])){echo "selected=selected";}?> value="50">50</option>
                        <option value="100" <?php if(isset($_GET['per-page']) && $_GET['per-page']==100){echo "selected=selected";}?> >100</option>
                        <option value="200" <?php if(isset($_GET['per-page']) && $_GET['per-page']==200){echo "selected=selected";}?> >200</option>
                    </select>
                    Items
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
        <?php
        $errorActionFlag = $editActionFlag = $imageActionFlag = $viewActionFlag = $shipActionFlag = false;

        $bulkActionSelect = Html::dropDownList('action', null, [''=>'-- select bulk action --','batch-upload'=>'Upload Products', 'batch-inventory' => 'Upload Inventory','batch-resend' => 'Resend Variants', 'batch-price' => 'Upload Price', 'archieved-batch'=>'Archive Products', 'unarchieved-batch' => 'Unarchive Products'], ['id'=>'jet_product_select','class'=>'form-control','data-step'=>'2','data-intro'=>"Select the BULK ACTION you want to operate.",'data-position'=>'bottom']);

        $bulkActionSubmit = Html::Button('submit', ['class'=>'btn btn-primary', 'onclick'=>'validateBulkAction()', 'data-step'=>'3','data-intro'=>"Submit the operated bulk action.",'data-position'=>'bottom']);
        ?>
        <?php Pjax::begin(['timeout' => 5000, 'clientOptions' => ['container' => 'pjax-container']]); ?>
        <div class="responsive-table-wrap">
            <?= GridView::widget([
                'id'=>"product_grid",
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'layout' => "{summary}\n<div class='table-responsive'>{items}</div>\n{pager}",
                'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",
                'pager' => [
                    'class' => \liyunfang\pager\LinkPager::className(),
                    'pageSizeList' => [25,50,100,200],
                    'pageSizeOptions' => ['id'=>'per-page-id','class' => 'form-control','style' => 'display: none;width:auto;margin-top:0px;'],
                    'maxButtonCount'=>5,
                ],
                'summary'=>'<div class="summary clearfix"><div class="col-lg-5 col-md-5 col-sm-5 col-xs-12"><span class="show-items">Showing <b>{begin}-{end}</b> of <b>{totalCount}</b> items.</span></div><div class="col-lg-2 col-md-2 col-sm-2 col-xs-12 jet-product-submit"><div class="bulk-action-wrapper">'.$bulkActionSubmit.'<span title="Need Help" class="help_jet white-bg" style="cursor:pointer;" id="instant-help"></span></div></div><div class="col-lg-5 col-md-5 col-sm-5 col-xs-12 jet-product-bulk"><div class="bulk-action-wrapper">'.$bulkActionSelect.'</div></div></div>',
                'rowOptions'=>function ($model) {
                    if (($model->error !="") && ($model->error !="NULL")){
                        return ['class'=>'danger'];
                    }elseif ($model->status =="Available for Purchase"){
                        return ['class'=>'success'];
                    }
                },
                'columns' => [
                    ['class' => 'yii\grid\CheckboxColumn','checkboxOptions'=>['class'=>'bulk_checkbox'],'headerOptions'=>['id'=>'checkbox_header','data-step'=>'1','data-intro'=>"Select Products to Upload",'data-position'=>'right']],
                    [
                        'attribute' => 'image',
                        'format' => 'html',
                        'contentOptions'=>['class'=>'prod-img-class '],
                        'label' => 'Image',
                        //'headerOptions' => ['data-toggle'=>'tooltip1','title'=>'Product Image'],
                        'value' => function ($data)
                        {
                            if(trim($data['image'])!="")
                            {
                                $imageSrc=explode(',',$data['image']);
                                if(count($imageSrc)>0)
                                {
                                   return Html::img($imageSrc[0],['width' => '80px','height'=>'80px','class'=>'image_hover']);
                                    
                                } else {
                                    return Html::img($data['image'], ['width' => '80px','height'=>'80px','class'=>'image_hover']);
                                }
                            }
                            else {
                                return Html::img(Yii::$app->getUrlManager()->getBaseUrl().'/images/noimage.png',
                                    ['width' => '80px','height'=>'80px','class'=>'image_hover']);
                            }
                        },
                    ],
                    [
                        'attribute'=>'title',
                        'contentOptions'=>['style'=>'width: 400px;'],

                        'format'=>'raw',
                        'value' => function($data){
                            //print_r($data);die();
                            if (trim($data->update_title)!="") {
                                return $data->update_title;
                            }
                            return $data['title'];
                        },
                    ],
                    [
                        'attribute'=> 'sku',
                        'label'=>'SKU',
                        'headerOptions' => ['width' => '250'],
                        'format' => 'raw',
                        'value'=> function($data){
                            if ($data['type']=='simple') 
                                $url = "https://" . SHOP . "/admin/products/".$data['id'];
                            elseif ($data['type']=='variants') 
                                $url = "https://" . SHOP . "/admin/products/".$data['id'].'/variants/'.$data['variant_id'];                                                    
                            return Html::a($data['sku'], $url, ['target' => '_blank','title' => 'Product SKU']);
                        }
                    ],
                    [
                        'attribute'=>'status',
                        'label'=>'Status',
                        'headerOptions' => ['width' => '400'],
                        'filter' => [Data::AVAILABLE_FOR_PURCHASE=>Data::AVAILABLE_FOR_PURCHASE,Data::EXCLUDED=>Data::EXCLUDED,Data::MISSING_LISTING_DATA=>Data::MISSING_LISTING_DATA,Data::UNDER_JET_REVIEW=>Data::UNDER_JET_REVIEW,Data::ARCHIVED=>Data::ARCHIVED,Data::NOT_UPLOADED=>Data::NOT_UPLOADED,Data::UNAUTHORIZED=>Data::UNAUTHORIZED],
                        'filterInputOptions'=> ['class' => 'form-control','onchange'=>"selectFilter();"],                        
                        'format'=>'html',
                        'value' => function ($data) {
                            if($data->option_status != null)
                            {
                                $status = explode(',',$data['option_status']);
                                $value =array_count_values($status);

                                if(!empty($value)){
                                    //return Html::renderTagAttributes($value);
                                    $status = [Data::AVAILABLE_FOR_PURCHASE,Data::EXCLUDED,Data::MISSING_LISTING_DATA,Data::UNDER_JET_REVIEW,Data::ARCHIVED,Data::NOT_UPLOADED,Data::UNAUTHORIZED];
                                    $html1='';
                                    $html1.='<ul>';
                                    foreach($value as $key=>$val){
                                        if(empty($key) || !in_array($key,$status)){
                                            $key='Others';
                                        }

                                        $html1.='<li class="'.$key.'">'.$key.' : '.$val.'</li>';
                                    }
                                    $html1.='</ul>';
                                    return $html1;
                                }
                            }else{
                                $html1='';
                                $html1.='<ul>';

                                $html1.='<li class="'.$data['status'].'">'.$data['status'].'</li>';
                                $html1.='</ul>';
                                return $html1;
                            }
                        }
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header'=>'<a herf="javascript:void(0)">Action</a>','headerOptions' => ['width' => '80'],
                        'headerOptions' => ['width' => '250','data-toggle'=>'tooltip1','title'=>'Choose action to view/update/error details'],
                        'template' => '{view}{update}{errorJet}{shipexception}{dynamicprice}{link}',
                        'buttons' => [
                            'view' => function ($url,$model) use (&$viewActionFlag)
                            {
                                if($model->status!="Not Uploaded")
                                {
                                    $options = ['data-pjax'=>0, 'class'=>'view' ,'onclick'=>'clickView(this.id)','title'=>'View Product Detail','id'=>$model->sku];
                                    if(!$viewActionFlag) {
                                        $viewActionFlag = true;
                                        $options['data-step']='6';
                                        $options['data-intro']="View Product Information.";
                                        $options['data-position']='left';
                                    }
                                    return Html::a(
                                        '<span class="glyphicon glyphicon-eye-open jet-data"> </span>',
                                        'javascript:void(0)',$options
                                    );
                                }
                            },
                            'update' => function ($url,$model) use (&$editActionFlag)
                            {
                                $options = ['data-pjax'=>0,'class'=>'update' ,'onclick'=>'clickEdit(this.id)','title'=>'Edit product','id'=>$model->id];
                                if(!$editActionFlag) {
                                    $editActionFlag = true;
                                    $options['data-step']='4';
                                    $options['data-intro']="Edit Product Information.";
                                    $options['data-position']='left';
                                }
                                return Html::a(
                                    '<span class="glyphicon glyphicon-pencil"> </span>',
                                    'javascript:void(0)',$options
                                );
                            },
                            'errorJet'=> function ($url,$model) use (&$errorActionFlag)
                            {
                                if($model->error !="" && $model->error !="NULL") {
                                    $options = ['data-pjax'=>0,'class'=>'errorJet' ,'onclick'=>'checkError(this.id)','title'=>'Upload Error','id'=>$model->id];
                                    if(!$errorActionFlag) {
                                        $errorActionFlag = true;
                                        $options['data-step']='5';
                                        $options['data-intro']="Click Here to get Errors during the Uploading of this product.";
                                        $options['data-position']='left';
                                    }
                                    return Html::a(
                                        '<span class=" glyphicon fa fa-exclamation-circle upload-error"> </span>',
                                        'javascript:void(0)',$options
                                    );
                                }
                            },
                            'shipexception'=>function($url,$model) use (&$shipActionFlag)
                            {
                                if( ($model->status!="Not Uploaded") && (($model->merchant_id=="44") || ($model->merchant_id=="641") || ($model->merchant_id=="3")|| ($model->merchant_id=="14")) ) {
                                    $options = ['data-pjax'=>0,'class'=>'shipexception' ,'onclick'=>'clickException(this.id,"'.$model->sku.'")','title'=>'Shipping Exception','id'=>$model->id];
                                    if(!$shipActionFlag) {
                                        $shipActionFlag = true;
                                        $options['data-step']='7';
                                        $options['data-intro']="Click Here to add Shipping Exceptions for this product";
                                        $options['data-position']='left';
                                    }
                                    return Html::a(
                                        '<span class="fa fa-truck" style="font-size: 17px;"> </span>',
                                        'javascript:void(0)',$options
                                    );
                                }
                            },                            
                        ],
                    ],
                    [
                        'attribute'=> 'type',
                        'label'=>'Type',
                        'headerOptions' => ['width' => 'auto'],
                        'filter' => ["simple"=>"simple","variants"=>"variants"],
                        'filterInputOptions'=> ['class' => 'form-control','onchange'=>"selectFilter();"]
                    ], 
                    [
                        'attribute'=>'qty',
                        'headerOptions' => ['width' => '250']
                    ],
                    [
                        'attribute'=>'price',                       
                        'format'=>'raw',
                        'filter'=>'<span id="price_span" class="price_from_span">From :</span> <input id="price_from" class="form-control" type="text" name="'.$searchModel->getClassName().'[price_from]" value="'.$searchModel->price_from.'" /><br/>'.'<span class="price_to_span">To :</span> <input class="form-control" type="text" name="'.$searchModel->getClassName().'[price_to]" value="'.$searchModel->price_to.'"/>',
                        'value' => function($data){
                            if (trim($data->update_price)!="") {
                                return $data->update_price;
                            }
                            return $data['price'];
                        },
                    ],
                    [
                        'attribute'=>'upc',
                        'headerOptions' => ['data-toggle'=>'tooltip1','title'=>'Must be one of the following values in numeric: GTIN-14 (14 digits), EAN (13 digits), ISBN-10 (10 digits), ISBN-13 (13 digits), UPC (1 2 digits)',]
                    ],
                    [
                        'attribute'=>'ASIN',
                    ], 
                    [
                   		'attribute'=>'vendor',
                    ],
                    [
                        'attribute'=> 'option_buybox_status',
                        'label'=>'Marketplace Price',
                        'format'=>'raw',
                        'visible'=> $avail_for_sale?true:false,
                        'headerOptions' => ['width' => 'auto'],
                        'filter' => ["Need Repricing","Best Price"],
                        'filterInputOptions'=> ['class' => 'form-control','onchange'=>"selectFilter();"],
                        'value' => function ($data) {
                            if($data->option_buybox_status==""){
                                $html1='';
                                $html1.='<ul>';
                                $html1.='<li class="repricing_no">N/A</li>';
                                $html1.='</ul>';
                                return $html1;
                            }else{
                                //if($data->type!="simple"){
                                    $enabled = array_diff(explode(',',$data->option_buybox_status), ['']);
                                    $value = array_count_values($enabled);
                                    $html1='';
                                    $html1.='<ul>';
                                    if(count($value)==0){
                                        $html1.='<li class="repricing_no">N/A</li>';
                                    }else{
                                        foreach($value as $key=>$val){
                                            if($key == 0)
                                            {
                                                $class = 'Better' ;
                                                $key='<a onclick="clickPricing(\''.$data->id.'\',\''.$data->type.'\',\''.$data->title.'\',0,1);" data-pjax=0>Need Repricing</a>';
                                            }else{
                                                $class = 'Best' ;
                                                $key='Best Price - <a onclick="clickPricing(\''.$data->id.'\',\''.$data->type.'\',\''.$data->title.'\',1,0);" data-pjax=0>View Pricing</a>';
                                            }
                                            $html1.='<li class="'.$class.'">'.$key.'</li>';
                                        }
                                    }
                                    $html1.='</ul>';
                                    return $html1;
                                /*}else{

                                    $enabled = array_diff(explode(',',$data->option_buybox_status), ['']);
                                    $value = array_count_values($enabled);
                                    $html1='';
                                    $html1.='<ul>';
                                    if(count($value)==0){
                                        $html1.='<li class="repricing_no">N/A</li>';
                                    }else{
                                        foreach($value as $key=>$val){
                                                if($key == 0){
                                                    $class = 'Better' ;
                                                    $key='<a onclick="clickPricing(\''.$data->id.'\',\''.$data->type.'\',\''.$data->title.'\',0,1);" data-pjax=0>Need Repricing</a>';
                                                }else{
                                                    $class = 'Best' ;
                                                    $key='Best Price - <a onclick="clickPricing(\''.$data->id.'\',\''.$data->type.'\',\''.$data->title.'\',1,0);" data-pjax=0>View Pricing</a>';
                                                }
                                                $html1.='<li class="'.$class.'">'.$key.'</li>';
                                        }
                                    }
                                    $html1.='</ul>';
                                    return $html1;
                                }*/
                                
                            }
                        }
                    ],
                    [
                        'attribute'=> 'product_type',
                        'label'=>'Product Type',
                        'headerOptions' => ['width' => '250'],
                    ],
                ],
            ]); ?> </div>
        <?php Pjax::end(); ?>
        <?= Html::endForm() ?>
    </div>
</div>

<div id="view_jet_product" style="display:none"></div>
<div id="edit_jet_product" style="display:none"></div>
<div id="jet_dynamic_pricing" style="display:none"></div>
<div id="products_error" style="display:none"></div>
<div id="products_shipexception" style="display:none"></div>
<div id="edit_walmart_product" style="display:none"></div>
<?php
$sync_fields = [
    'sku' => 'SKU',
    'title' => 'Title',
    'image' => 'Image',
    'product_type'=>'Product Type',
    'inventory' => 'Inventory',
    'weight' => 'Weight',
    'price' => 'Price',
    'upc' => 'Barcode(ISBN, UPC, GTIN, etc.)',
    'vendor' => 'Vendor(Brand)',
    'description' => 'Description',
    'variant_options' => 'Variant Options',
];
?>
<!-- Modal Sync Form html  -->
<div id="sync" class="modal fade sync-shopify">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <form id="sync-fields-form" action="<?= \yii\helpers\Url::toRoute(['jetscript/shopifyproductsync'])?>" method="post">
                    <h4 class="sync-store-prod-popup"> Select Fields to Sync with Shopify store</h4>
                    <div class="sync-fields">                        
                            <div class="checkbox_options">
                                <div>
                                    <input type="checkbox" name="selectAll" onclick="selectAllC(this.checked);"/><label>Select All</label>
                                </div>
                                <?php foreach ($sync_fields as $sync_index => $sync_value) : ?>
                                    <div>
                                         <input type="checkbox" class="sync-fields-checkbox"
                                           name="sync-fields[<?= $sync_index ?>]" value="1"/>
                                         <label><?= $sync_value ?></label>
                                    </div>   
                                 <?php endforeach; ?>
                            </div>                        
                    </div>
                    <div class="modal-footer">
                        <input type="submit" value="Sync details" class="btn btn-primary" >
                        <button style="float: right;margin-right: 1%;" type="button" class="btn btn-primary" id="sync-cancel" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>             
        </div>
    </div>
</div>

<script type="text/javascript">
function selectAllC(status)
{
    $('.sync-fields-checkbox').each(function() {
        this.checked = status;                        
    });         
}
    $('#reset_bulk').click(function()
    {
        var option = confirm("Do you want to reset the Bulk Action");
        if (option == true)
            location.reload();
        else
            return false;
    });

    var submit_form = false;
    $('body').on('keyup','.filters > td > input', function(event)
    {
        if (event.keyCode == 13) {
            if(submit_form === false) {
                submit_form = true;
                $("#product_grid-filters").yiiGridView("applyFilter");
            }
        }
    });

    $("body").on('beforeFilter', "#product_grid-filters" , function(event) {
        return submit_form;
    });
    $("body").on('afterFilter', "#product_grid-filters" , function(event) {
        submit_form = false;
    });
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    function clickView(id)
    {
        var url='<?= $urlJet ?>';
        var merchant_id='<?= $merchant_id;?>';
        $('#LoadingMSG').show();
        $.ajax({
            method: "post",
            url: url,
            data: {id:id,merchant_id : merchant_id,_csrf : csrfToken }
        })
            .done(function(msg) {
                $('#LoadingMSG').hide();
                $('#view_jet_product').html(msg);
                $('#view_jet_product').css("display","block");
                $('#view_jet_product #myModal').modal('show');
            });
    }
    function clickEdit(id)
    {
        var url='<?= $urlJetEdit; ?>';
        var merchant_id='<?= $merchant_id;?>';
        $('#LoadingMSG').show();
        $.ajax({
            method: "post",
            url: url,
            data: {id:id,merchant_id : merchant_id,_csrf : csrfToken}
        })
            .done(function(msg){
                $('#LoadingMSG').hide();
                $('#edit_jet_product').html(msg);
                $('#edit_jet_product').css("display","block");
                $('#edit_jet_product #myModal').modal({
                    keyboard: false,
                    backdrop: 'static'
                });
                renderCategoryTab(id);//Code By Himanshu
            });
    }
    function clickPricing(id,type,title,best,better)
    {
        var url='<?= $urlJetPrice; ?>';
        $('#LoadingMSG').show();
        $.ajax({
            method: "post",
            url: url,
            data: {id:id,type:type,title:title,best:best,better:better,_csrf : csrfToken}
        })
            .done(function(msg)
            {
                $('#LoadingMSG').hide();
                $('#jet_dynamic_pricing').html(msg);
                $('#jet_dynamic_pricing').css("display","block");
                $('#jet_dynamic_pricing #myModal').modal({
                    keyboard: false,
                    backdrop: 'static'
                });
            });
    }
    //add shipping exception
    function clickException(id,sku)
    {
        var url='<?= $urlJetShip ?>';
        var merchant_id='<?= $merchant_id;?>';
        $('#LoadingMSG').show();
        $.ajax({
            method: "post",
            url: url,
            data: {id:id,sku:sku,merchant_id : merchant_id,_csrf : csrfToken }
        })
            .done(function(msg) {
                $('#LoadingMSG').hide();
                $('#products_shipexception').html(msg);
                $('#products_shipexception').show();
                $('#products_shipexception #myModal').modal('show');
            });
    }

    <?php $categoryTabUrl = Yii::$app->getUrlManager()->getBaseUrl().'/jet/jetproduct/render-category-tab'; ?>
    function renderCategoryTab(productId)
    {
        var csrf_token = $('meta[name="csrf-token"]').attr("content");
        $.ajax({
            showLoader: true,
            url : '<?= $categoryTabUrl ?>',
            type: "POST",
            dataType: 'json',
            data : { id : productId, _csrf : csrf_token }
        }).done(function(data) {
            $('#edit_jet_product').find("#category_tab").html(data.html);
        });
    }

    function checkError(id)
    {
        var url='<?= $urlJetError ?>';
        var merchant_id='<?= $merchant_id;?>';
        $('#LoadingMSG').show();
        $.ajax({
            method: "post",
            url: url,
            data: {id:id,merchant_id : merchant_id,_csrf : csrfToken }
        })
            .done(function(msg) {
                //console.log(msg);
                $('#LoadingMSG').hide();
                $('#products_error').html(msg);
                $('#products_error').css("display","block");
                $('#products_error #myModal').modal('show');
            });
    }
    function selectPage(node){
        var value=$(node).val();
        $('#product_grid').children('#per-page-id').val(value).trigger('change');
        $('form.gridview-filter-form').trigger('submit');
    }

    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
        $('[data-toggle="tooltip1"]').tooltip({container: "tr"});
        $('#price_span').closest('td').addClass('price_grid');
        // $('[data-toggle="tooltip1"]').tooltip({container: ".desc"});
    });

    function validateBulkAction()
    {
        var action = $('#jet_product_select').val();
        if(action == '') {
            alert('Please Select Bulk Action');
            //return false;
        }else
        {
            if($("input:checkbox:checked.bulk_checkbox").length == 0)
            {
                alert('Please Select Products Before Submit.');
                //return false;
            }
            else
            {
                $("#jet_bulk_product").submit();
                //return true;
            }
        }
    }

    $(function(){
        var intro = introJs().setOptions({
            showStepNumbers: false,
            exitOnOverlayClick: false,
        });
        $('#instant-help').click(function(){
            intro.start().onchange(function(targetElement){
                if(targetElement.id=="view_more_options"){
                    showPopupBox(1);
                }
            }).oncomplete(function(){
                showPopupBox(0);
            });
        });
    });
    function showDropDownOption(){
        var introReprise = introJs();
        introReprise.setOptions({
        steps: [
          
          {
            element: 'select#jet_product_select>option[value="batch-price"]',//'select#jet_product_select>option:eq(3)',
            intro: "Choose Product(s) & Select 'Upload Price' Option to Upload on Jet.",
            position: 'bottom'
          },

        ],
        showStepNumbers:false,
        exitOnOverlayClick: false,
      });

      introReprise.onbeforechange(function(element) {
        if (this._currentStep === 0) {
          setTimeout(function() {
            //$("#jet_product_select").show().focus();//trigger("mousedown");
            var len = $("#jet_product_select").find("option").length;
            $("#jet_product_select").attr("size", len);
            $('.introjs-helperLayer').addClass('introjs-reprice-helperLayer');
            $('#jet_product_select').parent().parent().addClass('introjs-reprice-fixParent');
            
          });
        }
      });
      introReprise.oncomplete(function() {
                    $('.introjs-helperLayer').removeClass('introjs-reprice-helperLayer');
                    $('#jet_product_select').parent().parent().removeClass('introjs-reprice-fixParent');
                    $("#jet_product_select").removeAttr("size");
                });
      introReprise.start();
    }


</script>
<style>
.introjs-reprice-helperLayer{
    border: 4px solid #474747;
    height: 20px !important;
    width:130px !important;
    background-color: transparent;
}
.introjs-reprice-fixParent{
      z-index: 9999991 !important;
}
</style>
<?php $get = Yii::$app->request->get();

if(isset($get['tour'])) :
    ?>
    <script type="text/javascript">
        $(document).ready(function(){
            var productQuicktour = introJs().setOptions({
                doneLabel: 'Next page',
                showStepNumbers: false,
                exitOnOverlayClick: false,

            });
            productQuicktour.onchange(function(targetElement){
                if(targetElement.id=="view_more_options"){
                    showPopupBox(1);
                }
            });
            productQuicktour.onbeforechange(function(targetElement) {
              //alert(targetElement.className);
            });
            setTimeout(function () {
                productQuicktour.start().oncomplete(function() {
                    showPopupBox(0);
                    window.location.href = '<?= Data::getUrl("jetproduct-fileupload/index?tour") ?>';
                },1000);
            });
        });
    </script>
<?php endif; ?>
<?php
if(isset($get['_edt'])) :
    ?>
    <script type="text/javascript">
        $(document).ready(function(){
            $("#<?=trim($get['_edt'])?>").trigger('click');
        });
    </script>
<?php endif; ?>

<?php
if(isset($get['_upd'])) :
    ?>
<?php endif; ?>
<script>
    $(".pop_up").click(function () {
        $(".popup-box").toggle("slow");
    });
    function showPopupBox(toggle){
        if(toggle==1)
            $(".popup-box").toggle("slow");
        else
            $(".popup-box").toggle("hide");
    }
    var heightTbody = 0;
    var containerWidth = 0;
    var contentWidth = 0;
    var scrollRight = 0;
    var scrollbarWidth = 0;//SCROLLBAR width actual
    var scrollbarHeight =0;
    var swipeHtml = '<div class="follow"><div class="swipe-left" id="swipeLeft" style="display:none;" ><span><i class="fa fa-chevron-left" aria-hidden="true"></i>'+
        '</span></div><div class="swipe-right" id="swipeRight" style="display:none;" ><span><i class="fa fa-chevron-right" aria-hidden="true"></i>'+
        '</span></div></div>';   
    $(document).on('pjax:success', function() {
        gridContentChanged();
    });
    $(function(){
        $('body').prepend(swipeHtml);
        heightTbody = $('table.table tbody').outerHeight();
        appendScrollTabFunction();
    });

    $(window).on('resize',function() {
        $('#swipeLeft').css('display', 'none');
        $('#swipeRight').css('display', 'none');
        containerWidth = 0;
        contentWidth = 0;
        scrollbarWidth = 0;
        scrollbarHeight = 0;
        heightTbody = $('table.table tbody').outerHeight();
        appendScrollTabFunction();
    });

    function gridContentChanged(){
        $('#swipeLeft').css('display', 'none');
        $('#swipeRight').css('display', 'none');
        containerWidth = 0;
        contentWidth = 0;
        scrollbarWidth = 0;
        scrollbarHeight = 0;
        heightTbody = $('table.table tbody').outerHeight();
        appendScrollTabFunction();
    }
    function appendScrollTabFunction(){
        containerWidth = $('div.table-responsive').width();
        containerWidth = parseFloat(containerWidth);
        contentWidth = $('table.table').width();
        contentWidth = parseFloat(contentWidth);
        scrollbarHeight = getScrollbarHeight();
        //maxScroll = parseFloat(((containerWidth/contentWidth)*100).toFixed(2));//SCROLLBAR width
        if(contentWidth > containerWidth){
            scrollbarWidth = getScrollbarWidth();
            mainContentScroll();
        }
    }

    $(document).on('click', '#swipeRight', function(){
        $('div.table-responsive').animate( { scrollLeft: contentWidth }, 800);
    });

    $(document).on('click', '#swipeLeft', function(){
        $('div.table-responsive').animate( { scrollLeft: 0 }, 800);
    });
    $('div.table-responsive').scroll(function(){
        if(contentWidth > containerWidth){
            mainContentScroll();
        }
    });
    $(document).scroll(function(){
        if(contentWidth > containerWidth){
            mainContentScroll();
        }
    });
    function mainContentScroll()
    {
        var currentScrollValue = $('div.table-responsive').scrollLeft();
        currentScrollValue = parseFloat(currentScrollValue);
        var top = $('table.table tbody').offset().top;
        //var scr = parseFloat($('table.table thead').height())/*+parseFloat($('table.table tbody tr:first').height())*/+/**/parseFloat($('table.table thead').offset().top);//125;
        $('.follow').css('display', 'none');
        if((parseFloat($(window).scrollTop())+scrollbarHeight)>=top+100 && (parseFloat($(window).scrollTop())+scrollbarHeight)<=top + heightTbody+100){// - followHeight
            //scr += parseFloat($(window).scrollTop());//(parseFloat($(window).scrollTop())+scrollbarHeight)-top;
            $('.follow').css('display', 'block');
        }
        /* var offset = parseInt(scr);
         if (offset > 0) {
         //$('.follow').css('transform', 'translateY('+ offset +'px)');
         }*/

        //if(elementInViewportNew(document.querySelectorAll('table.table tbody tr'))){//if(elementStart < scroll && elementEnd > scroll){
        if(currentScrollValue == 0){// && currentScrollValue < maxScroll
            //scrollbar at left part
            $('#swipeLeft').css('display', 'none');
            $('#swipeRight').css('display', 'block');
        }else if(currentScrollValue > 0  && currentScrollValue < contentWidth-scrollbarWidth){
            //scrollbar at middle part
            $('#swipeLeft').css('display', 'block');
            $('#swipeRight').css('display', 'block');
        }else{
            //scrollbar at right part
            $('#swipeLeft').css('display', 'block');
            $('#swipeRight').css('display', 'none');
        }
    }

    function getScrollbarWidth() {
        var original = $('div.table-responsive').scrollLeft();
        $('div.table-responsive').scrollLeft(contentWidth);
        scrollRight = $('div.table-responsive').scrollLeft();
        scrollRight = parseFloat(scrollRight);
        $('div.table-responsive').scrollLeft(original);
        return contentWidth - scrollRight
    }
    function getScrollbarHeight() {
        var original = $(document).scrollTop();
        $(window).scrollTop($(document).height());
        var scrollBottom = $(window).scrollTop();
        scrollBottom = parseFloat(scrollBottom);
        $(document).scrollTop(original);
        return parseFloat($(document).height()) - scrollBottom;
    }
    /*$('#price_from').change(function(event){
     alert("jji");
     event.preventDefault();
     event.stopPropagation();
     });*/

    function cnfrmSync()
    {
        $('#sync').modal('show');

        $("#sync").on('shown.bs.modal', function () {
            $('#sync-yes').unbind('click');
            $('#sync-yes').on('click', function () {
                syncWithShopify();
            });
        });
    }

    function syncWithShopify()
    {
        var selectCount = 0;
        $.each($(".sync-fields-checkbox"), function () {
            if ($(this).is(':checked') === true) {
                selectCount++;
            }
        });

        if (selectCount)
        {
            $('#sync-cancel').click();
            var url = "<?= \yii\helpers\Url::toRoute(['jetscript/shopifyproductsync'])?>";

            var fields = $("#sync-fields-form").serialize();
            console.log(fields);

            $.ajax({
                method: "post",
                url: url,
                dataType: "json",
                data: {_csrf: csrfToken, sync_fields: fields}
            })
            .done(function (response) {
                $('#LoadingMSG').hide();
                if (response.success) {
                    alert(response.message);
                    window.location.reload();
                }
                else if (response.error) {
                    alert(response.message);
                }
                else {
                    alert("something went wrong.");
                }
            });
        }
        else {
            alert("Please select fields to sync.");
        }
        $("#sync-fields-form")[0].reset();
    }   
</script>
<style>
    .Excluded {
        color: #1A75CF;
    }
    .Not.Uploaded, .Missing.Listing.Data,.Unauthorized{
        color: red;
    }
    .Archived{
        color: #d08c00;
    }
    .Available.for.Purchase,.Under.Jet.Review{
        color: green;
    }
</style>
