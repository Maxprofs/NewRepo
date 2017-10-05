<?php
use yii\helpers\Html;
use frontend\modules\walmart\models\WalmartCategory;
use frontend\modules\walmart\models\WalmartCategoryMap;
use frontend\modules\walmart\components\Jetcategorytree;
use frontend\modules\walmart\components\Data;
use yii\helpers\ArrayHelper;

$this->title = 'Shopify-Walmart Category Mapping';
$this->params['breadcrumbs'][] = $this->title;

$merchant_id = Yii::$app->user->identity->id;
$model = WalmartCategoryMap::find()->where(['merchant_id'=>$merchant_id])->andWhere(['!=','product_type',''])->all();
//$data = WalmartCategory::find()->select('id,category_id,title,parent_id,level')->all();
$category_tree = array();
$category_detail = array();
$rootCategory = \frontend\modules\walmart\components\Walmartcategory::getrootcategory();
//list($category_tree,$category_detail) = Jetcategorytree::createCategoryTreeArray($data);
list($category_tree, $category_detail) = \frontend\modules\walmart\components\Walmartcategory::getcategorytree();

$this->registerCssFile(Yii::$app->request->baseUrl . "/frontend/modules/walmart/assets/css/tokenize2.css");
$this->registerJsFile(Yii::$app->request->baseUrl . "/frontend/modules/walmart/assets/js/tokenize2.js", ['depends' => [\yii\web\JqueryAsset::className()]]);

$categoryMappings = ArrayHelper::toArray($model);

$options = [];
//$index = 0;
$allOptions = [];
$selectedOptions = [];
$savedMappings = [];
$productTypeCount = count($categoryMappings);
$categoryOptions = '';
foreach ($categoryMappings as $key => $categoryMapping) {
    $options[] = ['text'=>$categoryMapping['product_type'], 'value'=>$categoryMapping['product_type']];
    $allOptions[] = $categoryMapping['product_type'];

    $categoryId = trim($categoryMapping['category_id']);
    if(!empty($categoryId)) {
        $savedMappings[$categoryId]['category_path'] = $categoryMapping['category_path'];
        $savedMappings[$categoryId]['taxcode'] = $categoryMapping['tax_code'];
        $savedMappings[$categoryId]['product_type'][] = $categoryMapping['product_type'];
    }
}

?>

<div id="category_map" class="category-map-index content-section">
    <form method="post" action="<?= Data::getUrl('categorymap/save-new') ?>" class="category_map form new-section">

        <input type="hidden" name="productTypeCount" value="<?= $productTypeCount ?>" />
<!--        <div style="display: none;border-radius: 4px;margin-bottom: 10px;padding: 10px;" class="help-block help-block-error top_error alert-danger">Please map atleast one product type with Walmart category to list products</div>-->
        <div style="display: none;border-radius: 4px;margin-bottom: 10px;padding: 10px;" class="help-block help-block-error top_error alert-danger"></div>

        <table class="table table-striped table-bordered category_mapping">
            <thead>
            <tr>
                <th class="center" colspan="2">Walmart Category Name</th>
                <th>Walmart Tax Code (<a href="<?= Data::getUrl('walmarttaxcodes/index') ?>">click here to get taxcode</a>)</th>
                <th>Product Type(shopify)</th>
            </tr>
            </thead>
            <tbody id="map-container">
            <?php
            $index = 0;

            foreach ($savedMappings as $key => $savedMapping)
            {
                ?>
                <tr>
                    <td class="cat_root" <?= $index==0?"data-step='4' data-intro='Select Walmart Category From Drop-down for your Product Types.' data-position='right'":'' ?>>
                        <select class="form-control root" name="Parent[<?= $index ?>]" onchange="changeRootCategory(this)">
                            <option value="">Please Select Category</option>
                            <?php
                            $childCategories = [];
                            foreach ($category_tree as $category => $child)
                            {
                                $categoryOptions .= '<option value="'.$category.'">'.$category.'</option>';

                                $mappedCategories = explode(',', $savedMapping['category_path']);
                                $mappedParentCategory = $mappedCategories[0];
                                $mappedChildCategory = $mappedCategories[1];

                                if($mappedParentCategory == $category)
                                {
                                    $childCategories = $child;
                                    ?>
                                    <option value="<?= $category ?>" selected=""><?= $category ?></option>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <option value="<?= $category ?>"><?= $category ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </td>

                    <td <?= $index==0?"data-step='7' data-intro='Select Walmart Child Category From Drop-down for your Product Types.' data-position='right'":'' ?>>
                        <select class="form-control child-cat" name="Child[<?= $index ?>]">
                            <option value="">Please Select Category</option>
                            <?php
                            foreach ($childCategories as $childCategory => $v)
                            {
                                if($mappedChildCategory == $childCategory)
                                {
                                    ?>
                                    <option value="<?= $childCategory ?>" selected=""><?= $childCategory ?></option>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <option value="<?= $childCategory ?>"><?= $childCategory ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </td>

                    <td <?= $index==0?"data-step='10' data-intro='Enter Tax Code for the Corresponding Shopify Product Type.' data-position='bottom'":'' ?>>
                        <input type="text" value="<?= $savedMapping['taxcode'] ?>" name="taxcode[<?= $index ?>]" class="form-control" />
                    </td>

                    <td <?= $index==0?"data-step='13' data-intro='Choose Shopify Product Types from here.' data-position='left'":'' ?>>
                        <select class="multiSel" multiple name="ProductType[<?= $index ?>][]">
                            <?php
                            foreach ($savedMapping['product_type'] as $productType)
                            {
                                $selectedOptions[] = $productType;
                                ?>
                                <option value="<?= $productType ?>" selected=""><?= $productType ?></option>
                                <?php
                            }
                            ?>
                        </select>
                        <span class="delete_button" title="delete this row" onclick="deleteRow(this)" <?= $index==0?"data-step='16' data-intro='Delete row from here.' data-position='left'":'' ?>></span>
                    </td>
                </tr>
                <?php
                $index++;
            }
            ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="4">
                    <button type="button" class="btn btn-primary add-row" onclick="addNewRow()"  data-step='1' data-intro="Add new mapping row." data-position='right'>
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </button>
                </td>
            </tr>
            </tfoot>
        </table>
        <!--<div class="page-bottom-actions">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>-->
        <div class="clear"></div>
    </form>
</div>

<script>
    var options = <?= json_encode($options) ?>;
    var allOptions = <?= json_encode($allOptions) ?>;
    var selectedOptions = <?= json_encode($selectedOptions) ?>;
    var category_tree = <?= json_encode($category_tree) ?>;
    var categoryOptions = '<?= $categoryOptions ?>';
    var indexCount = <?= $index ?>;

    $(document).ready(function(){
        $('.multiSel').each(function(){
            tokenize($(this));
            tokenAddEvent($(this));
            tokenRemoveEvent($(this));
        });
    });

    function tokenize(element)
    {
        element.tokenize2({
            dataSource: function(search, object) {

                var items = searchItems(search, object);

                object.trigger('tokenize:dropdown:fill', [items]);
            },
            sortable: true,
            placeholder: 'Search your product type...',
            displayNoResultsMessage: true,
            dropdownMaxItems: 100000
        });
    }

    function tokenAddEvent(element)
    {
        element.on('tokenize:tokens:add', function(e, value, text, force) {
            var index = selectedOptions.indexOf(value);
            if (index == -1 && allOptions.indexOf(value) > -1) {
                selectedOptions.push(value);
            }
        });
    }

    function tokenRemoveEvent(element)
    {
        element.on('tokenize:tokens:remove', function(e, value) {
            var index = selectedOptions.indexOf(value);
            if (index > -1) {
                selectedOptions.splice(index, 1);
            }
        });
    }

    function searchItems(search, object)
    {
        var $searchString = object.transliteration(search);
        var $items = [];
        var $pattern = (object.options.searchFromStart ? '^' : '') + object.escapeRegex($searchString);
        var $regexp = new RegExp($pattern, 'i');
        var $this = object;

        $.each(options, function(k, v){
            var text = v.text;
            var value = v.value;
            if((selectedOptions.indexOf(value)==-1) && $regexp.test($this.transliteration(text))) {
                $items.push({ value: value, text: text });
            }
        });

        return $items;
    }

    function changeRootCategory(element)
    {
        $('#LoadingMSG').show();

        var childCatElement = $(element).parents('td').next().find('select');

        var rootCategoryVal = $(element).val();

        if(category_tree.hasOwnProperty(rootCategoryVal))
        {
            childCatElement.html('').show();
            var childCategories = category_tree[rootCategoryVal];
            if(Object.keys(childCategories).length)
            {
                $.each(childCategories, function(key, value){
                    childCatElement.append('<option value="'+key+'">'+key+'</option>');
                });
            }
            else
            {
                childCatElement.html('<option>No categories found.</option>');
            }
        }
        else
        {
            childCatElement.hide();
        }

        $('#LoadingMSG').hide();
    }

    function addNewRow()
    {
        var parentCatIntro = '';
        var childCatIntro = '';
        var taxcodeIntro = '';
        var productTypeIntro = '';
        var deleteIntro = '';
        if($('td[data-step="4"]').length == 0)
            parentCatIntro = "data-step='4' data-intro='Select Walmart Category From Drop-down for your Product Types.' data-position='right'";

        if($('td[data-step="7"]').length == 0)
            childCatIntro = "data-step='7' data-intro='Select Walmart Child Category From Drop-down for your Product Types.' data-position='right'";

        if($('td[data-step="10"]').length == 0)
            taxcodeIntro = "data-step='10' data-intro='Enter Tax Code for the Corresponding Shopify Product Type.' data-position='bottom'";

        if($('td[data-step="13"]').length == 0)
            productTypeIntro = "data-step='13' data-intro='Choose Shopify Product Types from here.' data-position='left'";

        if($('span[data-step="16"]').length == 0)
            deleteIntro = "data-step='16' data-intro='Delete row from here.' data-position='left'";

        var rootCatSelect = $('<select name="Parent['+indexCount+']" class="form-control root-cat" onchange="changeRootCategory(this)">');
        rootCatSelect.append('<option value="">Please Select Category</option>').append(getParentCategoryOptions());

        var childCatSelect = $('<select name="Child['+indexCount+']" class="form-control child-cat" style="display:none;">');

        var productTypeSelect = $('<select class="multiSel" name="ProductType['+indexCount+'][]">').attr('multiple',true);

        $('#map-container').append(
            $('<tr>').append(
                $('<td '+parentCatIntro+'>').append(rootCatSelect)
            ).append(
                $('<td '+childCatIntro+'>').append(childCatSelect)
            ).append(
                $('<td '+taxcodeIntro+'>').append('<input type="text" name="taxcode['+indexCount+']" value="" class="form-control" />')
            ).append(
                $('<td '+productTypeIntro+'>').append(productTypeSelect).append('<span onclick="deleteRow(this)" class="delete_button" title="delete this row" '+deleteIntro+'></span>')
            )
        );

        tokenize(productTypeSelect);
        tokenAddEvent(productTypeSelect);
        tokenRemoveEvent(productTypeSelect);
        indexCount++;
    }

    function getParentCategoryOptions()
    {
        if(categoryOptions == '')
        {
            var caterories = <?= json_encode(array_keys($category_tree)) ?>;

            if(Object.keys(caterories).length)
            {
                $.each(caterories, function(key, value){
                    categoryOptions += '<option value="'+value+'">'+value+'</option>';
                });
            }
        }

        return categoryOptions;
    }

    function deleteRow(element)
    {
        var container = $(element).parent().parent();
        container.find('.multiSel option:selected').each(function(){
            var value = $(this).val();
            var index = selectedOptions.indexOf(value);
            if (index > -1) {
                selectedOptions.splice(index, 1);
            }
        });

        container.remove();
    }
</script>

<script type="text/javascript">
var url = '<?= Data::getUrl("walmart-install/save-category-map") ?>';
var csrfToken = $('meta[name="csrf-token"]').attr("content");

UnbindNextClick();

	$('.next').on('click', function(event){
		var flag = false;
		/*console.log(flag);*/

		$('#map-container').each(function () {
            if($(this).val() == "") {
                $(this).addClass("select_error");
                $('.top_error').html("Please map atleast one Walmart Category");
                $('.top_error').show();
            } else {
                flag = true;
                $(this).removeClass("select_error");
                $('.top_error').hide();
                return false;
            }
        });
		$('#map-container .root-cat').each(function(){
			if($(this).val() == "") {
				$(this).addClass("select_error");
				$('.top_error').html("Please map atleast one Walmart Category with shopify product type to list products");
				$('.top_error').show();
			} else {
				flag = true;
				$(this).removeClass("select_error");
				$('.top_error').hide();
				return false;
			}
	  	});

	  	if(flag)
	  	{
	  		$('#LoadingMSG').show(); 
		    $.ajax({
	            method: "POST",
	            url: url,
	            data: $("form").serialize(),
	            dataType : "json"
		    })
		    .done(function(response)
		    {
		        $('#LoadingMSG').hide();
		        if(response.success) {
					$('.top_error').hide();
					nextStep();
		        } else {
					$('.top_error').html(response.message);
					$('.top_error').show();
		        }
			});
	  	}
	});

/*$('#category_map').submit(function( event ) {
	  var flag=false;
	  $('.cat_root .root').each(function(){
		 if($(this).val()==""){
		  	flag=true;
		  	$(this).addClass("select_error");
		  	//$('.error_category_map').css('display','block');
		 }
		 else{
			 flag=false;
			 $(this).removeClass("select_error");
			 //$('.error_category_map').css('display','none');
			 return false;
		 }
	  });
	  if(flag){
		  return false;
	  }
});*/
</script>
<style>
	.center,.cat_root{
		text-align: center;
	}
	.cat_root .form-control{
		display: inline-block;
	}
	
</style>