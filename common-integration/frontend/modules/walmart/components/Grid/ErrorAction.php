<?php
namespace frontend\modules\walmart\components\Grid\OrderDetails;

use Yii;
use Closure;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ErrorAction as CoreActionColumn;

class ErrorAction extends CoreActionColumn
{

    public $template = '{view}{truck}{refund}{cancel}';/* {view} {update} {delete}';*/

    /**
     * Initializes the default button rendering callbacks.
     */
    protected function initDefaultButtons()
    {
        $this->buttons['view'] = function ($url, $model, $key) {
                 $options = array_merge([
                        'title' => Yii::t('yii', 'view'),
                        'aria-label' => Yii::t('yii', 'view'),
                        'onclick' => "checkorderstatus('{$model->purchase_order_id}');",
                        'data-pjax' => '1',
                    ], $this->buttonOptions);
                    return Html::a('<span class="fa fa-eye"></span>', 'javascript:void(0);', $options);
            };  
       

           

            $this->buttons['cancel'] = function ($url, $model, $key) {
                    $cancelUrl = \yii\helpers\Url::toRoute(['walmartorderdetail/cancel-order','pid'=>$model->purchase_order_id]);
                        $options = array_merge([
                            'title' => Yii::t('yii', 'Cancel'),
                            'aria-label' => Yii::t('yii', 'Cancel'),
                            'onclick' => "confirm('Are you sure,Want to cancel order?')?window.location='{$cancelUrl}':''",
                            'data-pjax' => '1',
                        ], $this->buttonOptions);
                        $options['data-step']='3';
                    $options['data-intro']="Cancel order from walmart.";
                    $options['data-position']='left';
                        return Html::a('<span class="glyphicon glyphicon-remove-circle"></span>', 'javascript:void(0);', $options);
                   
                };
           
        parent::initDefaultButtons();
    }

}
