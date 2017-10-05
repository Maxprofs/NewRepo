<?php

namespace frontend\modules\jet\models;

use Yii;

/**
 * This is the model class for table "jet_selected_category".
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $merchant_id
 * @property string $title
 * @property integer $parent_id
 * @property string $parent_title
 * @property integer $root_id
 * @property string $root_title
 * @property integer $level
 * @property string $jet_attributes
 * @property string $status
 */
class JetSelectedCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
	public $flag=true;
	public $stack="";
	
    public static function tableName()
    {
        return 'jet_selected_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'merchant_id', 'title', 'parent_id', 'parent_title', 'root_id', 'root_title', 'level', 'jet_attributes', 'status'], 'required'],
            [['category_id', 'merchant_id', 'parent_id', 'root_id', 'level'], 'integer'],
            [['title', 'parent_title', 'root_title', 'jet_attributes'], 'string'],
            [['status'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category ID',
            'merchant_id' => 'Merchant ID',
            'title' => 'Title',
            'parent_id' => 'Parent ID',
            'parent_title' => 'Parent Title',
            'root_id' => 'Root ID',
            'root_title' => 'Root Title',
            'level' => 'Level',
            'jet_attributes' => 'Jet Attributes',
            'status' => 'Status',
        ];
    }
    public function get_categories()
    {
    	$arrayCategories=array();
    	$merchant_id = \Yii::$app->user->identity->id;
    	$data=$this->find()->where(['merchant_id'=>$merchant_id])->all();
    	foreach ($data as $value) {
    		$arrayCategories[$value->category_id] =
    		array("parent_id" => $value->parent_id,
    				"name" => $value->title,
    				"cat_id" => $value->category_id,
    				"cat_level" => $value->level,
    		);
    	}
    	return $arrayCategories;
    }
    
    public function createTree($array, $currentParent, $currLevel = 0, $prevLevel = -1)
    {
    	//var_dump($array);die;
    	foreach ($array as $categoryId => $category)
    	{
    		if ($currentParent == $category['parent_id'])
    		{
    			if ($currLevel > $prevLevel){
    				echo "<ul id='ul_".$categoryId."'>";
    				if($this->flag==true)
    				{
    					$this->stack=$categoryId;
    					$this->flag=false;
    				}
    			}
    			if ($currLevel == $prevLevel)
    				echo " </li> ";
    
    			$cat_id_hidden = array('1','2');//set category id hidden
    			if(in_array($categoryId,$cat_id_hidden)){
    				// $display = 'none';
    			}
    			else{
    				// $display = 'block';
    			}
    
    			//class="level_'.$category['cat_level'].' "
    			echo '<li id="li_'.$categoryId.'">';
    			echo '<span id="span_'.$categoryId.'">';
    			echo '<input type="radio" class="tree_checkbox" onclick="selectCat(this)" name="select_Cat" value="'.$categoryId.'"/>';
    			echo $category['name'].'</span>';
    			//echo '<span id="span_'.$categoryId.'">'.$category['name'].'</span>';
    			//echo '</a>';
    			//echo '<input type="text" id="m_cat_id_'.$category['cat_id'].'" name="m_cat_id_'.$category['cat_id'].'" value="id = '.$category['cat_id'].' : level = '.$category['cat_level'].' "/>';
    			if ($currLevel > $prevLevel) {
    			$prevLevel = $currLevel;
    			}
    				$currLevel++;
    				$this->createTree($array, $categoryId, $currLevel, $prevLevel);
    				$currLevel--;
    		}
    		}
    		if ($currLevel == $prevLevel)
    			echo " </li></ul> ";
    		//echo $this->stack;die("hello");
    		return $this->stack;
    }
}
