<?php
use yii\helpers\Html;
use yii\captcha\Captcha;
use yii\widgets\ActiveForm;

use frontend\modules\jet\models\JetCategory;
use frontend\modules\jet\models\JetAttributes;

if($model->fulfillment_node)
{
    $id=$model->fulfillment_node;
    ?> 
    <div class="form-group field-jetproduct-jet_attributes enable_api">
        <div class="category_value" style="margin-bottom:5px">
            <span>
                <?= $merchantCategory['root_title'].'&nbsp
                <i class="fa fa-angle-right" aria-hidden="true"></i>
                &nbsp'.$merchantCategory['parent_title'].'&nbsp
                <i class="fa fa-angle-right" aria-hidden="true"></i>
                &nbsp'.$merchantCategory['title'] ?>
            </span>
            <input id="jetproduct-fulfillment_node" class="form-control" type="hidden" readonly="" value="<?= $id ?>" name="JetProduct[fulfillment_node]">
        </div>
        <div id="jet_Attrubute_html" class="Attrubute_html">
            <?php            
                unset($merchantCategory);
                if($model->type=='variants')
                {
                    echo $this->render('varients1', [
                        'model' => $model,
                        'attributes'=>$attributes['attributes'],
                    ]);
                }
                else
                {
                    echo $this->render('simple1',[
                        'model'=>$model,
                        'attributes'=>$attributes['attributes'],
                    ]);
                }                
            ?>
        </div>    
    </div>
<?php 
}?> 