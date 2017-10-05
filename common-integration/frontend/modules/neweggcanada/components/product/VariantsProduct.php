<?php
namespace frontend\modules\neweggcanada\components\product;
use frontend\modules\neweggcanada\components\Data;
use Yii;
use yii\base\Component;
use frontend\modules\neweggcanada\components\Helper;
use frontend\modules\neweggcanada\components\categories\Categoryhelper;

class VariantsProduct extends Component
{
  /*
    product basic info prepare

    */
    public static function getProductBasicInfo($productArray)
    {

        $data = [];
         if(isset($productArray['spn']) && !empty($productArray['spn'])){
            $data['SellerPartNumber'] =trim($productArray['spn']);
        }
        else{

            //$data['SellerPartNumber'] =trim('SPN'.$productArray['option_id']);
            $data['SellerPartNumber'] =trim($productArray['option_sku']);
        }
      /*  if(empty($productArray['manufacturer'])){
           $manufacturerData =Helper::categoryManufacturerDetail($productArray['shopify_product_type']);
           if(empty($manufacturerData["manufacturer"])){
                $manufacturerConfigData =Helper::configurationDetail(MERCHANT_ID);
                $data['Manufacturer'] = trim($manufacturerConfigData['manufacturer']);
            }
            else{
                $data['Manufacturer'] = trim($manufacturerData['manufacturer']);
            }
            
        }
        else{
            $data['Manufacturer'] = trim($productArray['manufacturer']);
        }*/
        if(!empty($productArray['manufacturer'])){
            $data['Manufacturer'] = trim($productArray['manufacturer']);
        }
        if(!empty($productArray['short_description'])){
            $data['BulletDescription'] = trim($productArray['short_description']);
        }
        $type=self::checkUpcType($productArray['option_unique_id']);
        if($type=='UPC' || $type=="EAN"){
            $data['UPC'] = $productArray['option_unique_id'];
        }
        else{
            $data['ManufacturerPartNumberOrISBN'] = $productArray['option_mpn'];
        }
        $data['WebsiteShortTitle'] = trim($productArray['option_sku']);
        $data['ProductDescription'] = trim($productArray['description']);
        $data['ItemWeight']= trim(sprintf("%.2f",trim($productArray['option_weight'])));
        $data['PacksOrSets']='1';
        $data['ShippingRestriction']=trim('no');
        $data['SellingPrice']=trim($productArray['option_price']);
        $data['Shipping']=trim('Free');
        $data['Inventory']=(int)trim($productArray['option_qty']);
        $data['ActivationMark']='true';
        
        if(empty($productArray['option_image'])){
            if(isset($productArray['mainImage'])){
                $image = explode(',',$productArray['mainImage']);
                $data['ItemImages']=[['Image'=>['ImageUrl'=>$image[0]]]];
            }
        }
        else{
            $image = explode(',',$productArray['option_image']);
            $data['ItemImages']=[['Image'=>['ImageUrl'=>$image[0]]]];
        }
       //$data['ItemImages']=[['Image'=>['ImageUrl'=>'http://www.hdwallpapers.in/walls/minimal_play_vector-wide.jpg']]];

        return $data;
       

    }
    /*
    Subcategory basic info prepare

    */

    public static function getVariantsSubCategoryProperty($productArray)
    {
        $mapper = [];
        if($productArray['newegg_data'])
        {
           /*Check attribute Mapping is Global or Product Wise*/
           if(isset($productArray['newegg_option_attributes']) && $productArray['newegg_option_attributes'])
           {
                /*Product wise attribute Mapping Exist*/

                /*Check attribute Value Mapping is Product Wise*/
                if(isset($productArray['mapped_value_data']) && !empty($productArray['mapped_value_data']))
                {
                    /*Product wise attribute Value Mapping Exist*/
                    $array = $productArray['mapped_value_data'];
                    $array1 = json_decode($array,true);
                    $data = json_decode($productArray['newegg_data'],true);
                    $arrayValue= $array1[$productArray['shopify_product_type']][$productArray['category_id']];
                    $neweggAttribute = json_decode($productArray['newegg_option_attributes'],true);
                    foreach ($arrayValue as $key1 => $value1) {
                        foreach ($neweggAttribute as $key => $value) {
                            if($key==$key1){
                                if(isset($value1[$productArray['option_sku']])){
                                    $mapper[$key]=$value1[$productArray['option_sku']];
                                }
                            
                            }
                        }
                    }
                    /*check required attribute exist or not*/
                    $comm_attr = [];
                    if(isset($productArray['newegg_attributes']) && !empty($productArray['newegg_attributes']))
                    {
                        $comm_attr = json_decode($productArray['newegg_attributes'],true);
                    }
                    if(isset($comm_attr) && !empty($comm_attr)){
                        $category[trim(str_replace(" ","",$data['subcategory']['name']))]=array_merge($comm_attr,$mapper);
                    }
                    else{
                        $category[trim(str_replace(" ","",$data['subcategory']['name']))]=$mapper;

                    }
                    return $category;

                }

           }
           else
           {
                /*Global attribute Mapping Exist*/
                $map = [];
                $sql = "SELECT `mapped_value_data` FROM `newegg_can_value_mapping` WHERE merchant_id='".MERCHANT_ID."' AND shopify_product_type='".$productArray['shopify_product_type']."' AND category_id='".$productArray['category_id']."'";
                $array = Data::sqlRecords($sql,"one","select");
                $query = 'SELECT `attribute_map`,`attribute_map_data` from newegg_can_attribute_map Where `shopify_product_type`="'.$productArray['shopify_product_type'].'" and `newegg_category_id`="'.$productArray['category_id'].'"';
                $attribute = Data::sqlRecords($query,"one","select");
                $jAttribute = json_decode($attribute['attribute_map'],true);
                $subCategoryID = explode(',',$productArray['category_path']);
                foreach ($jAttribute as $jk => $jv) {
                    $jData=explode('->',$jv);
                    $map[$jData[0]]=$jData[1];
                }
                if(!empty($array))
                {
                    $array1 = json_decode($array['mapped_value_data'],true);
                    $data = json_decode($productArray['newegg_data'],true);
                    $arrayValue= $array1[$productArray['shopify_product_type']][$productArray['category_id']];
                    $required_Data=json_decode($attribute['attribute_map_data'],true);
                    $arrayRequiredData = $required_Data[$productArray['shopify_product_type']][$productArray['category_id']];
                    $category = [];
                    $subcategoryRequiredData = Categoryhelper::getSubcategoryRequiredAttribute($subCategoryID[0],$subCategoryID[1]);
                    $required_attribute = array_intersect_key($arrayRequiredData,$subcategoryRequiredData);
                    foreach ($map as $mapkey => $mapvalue) {
                        if(isset($arrayValue[$mapkey][$mapvalue])){
                            foreach ($arrayValue[$mapkey][$mapvalue] as $keys => $values) {
                                if(isset($productArray['variant_option1']) && !empty($productArray['variant_option1'])){
                                    if(isset($values[$productArray['variant_option1']])){
                                        $attr_val_data=$values[$productArray['variant_option1']];
                                        $mapper[$mapkey]=$attr_val_data;
                                    }

                                }
                                if(isset($productArray['variant_option2']) && !empty($productArray['variant_option2'])){
                                    if(isset($values[$productArray['variant_option2']])){
                                        $attr_val_data=$values[$productArray['variant_option2']];
                                        $mapper[$mapkey]=$attr_val_data;  
                                    }
                                }
                                if(isset($productArray['variant_option3']) && !empty($productArray['variant_option3'])){
                                    if(isset($values[$productArray['variant_option3']])){
                                        $attr_val_data=$values[$productArray['variant_option3']];
                                        $mapper[$mapkey]=$attr_val_data;
                                    }
                                    
                                }
                                
                            }
                        }
                    }
                    if(count($required_attribute)>0){
                        /*check attribute having groupBy and Required both property*/
                        $comm_attr = array_intersect_key($required_attribute,$mapper);
                        if(count($comm_attr)>0){
                            foreach ($comm_attr as $comm_attr_key => $comm_attr_value) {
                                unset($required_attribute[$comm_attr_key]);
                            }
                            if(count($required_attribute)>0){
                                foreach ($required_attribute as $skey => $svalue) {
                                    $mapper[$skey]=$svalue['value'];
                                }
                            }
                            $category[trim(str_replace(" ","",$data['subcategory']['name']))]=$mapper;
                        }
                        else{
                            foreach ($required_attribute as $skey => $svalue) {
                                $mapper[$skey]=$svalue['value'];
                            }
                            $category[trim(str_replace(" ","",$data['subcategory']['name']))]=$mapper;
                        }

                    }
                    else{
                        $category[trim(str_replace(" ","",$data['subcategory']['name']))]=$mapper;
                    }
                    return $category;
                }
           }
        }

    }
    /*check Upc type*/
    public static function checkUpcType($product_upc){
        if(is_numeric($product_upc))
        {
            if(strlen($product_upc)==12)
                return "UPC";
            elseif(strlen($product_upc)==10)
                return "ISBN";
            elseif(strlen($product_upc)==13){
                $string =substr($product_upc,0, 3);
                if(($string==978) || ($string==979)){
                    return "ISBN";
                }
                return "EAN";
            }
        }
        return "";
    }
}