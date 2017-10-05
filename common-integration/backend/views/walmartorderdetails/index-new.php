<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

?>
<div class="content-section">
    <div class="form new-section">
        <div class="jet-pages-heading">
            <div class="title-need-help">
                <h3 class="Jet_Products_style">Walmart Order Report</h3>
            </div>
        </div>
        <div class="walmart-order-detail-form">
            <form action="<?php echo \yii\helpers\Url::toRoute(['walmartorderdetails/index']); ?>" method="get">
                <select name="export" class="form-control csv-dropdown" id="action-dropdown1">
                    <option value="">All Order</option>
                    <option value="3" <?php if(isset($days) && !empty($days) && $days==3){ echo 'selected';}?>>3 Days</option>
                    <option value="5" <?php if(isset($days) && !empty($days) && $days==5){ echo 'selected';}?>>5 Days</option>
                    <option value="7" <?php if(isset($days) && !empty($days) && $days==7){ echo 'selected';}?>>7 Days</option>
                    <option value="30" <?php if(isset($days) && !empty($days) && $days==30){ echo 'selected';}?>>1 Mnth</option>
                    <option value="90" <?php if(isset($days) && !empty($days) && $days==90){ echo 'selected';}?>>3 Mnth</option>
                    <option value="180" <?php if(isset($days) && !empty($days) && $days==180){ echo 'selected';}?>>6 Mnth</option>
                    <option value="365" <?php if(isset($days) && !empty($days) && $days==365){ echo 'selected';}?>>1 Year</option>
                </select>
                <div class="input-wrap">
                    <input type="submit" class="btn btn-primary" id='export' value="Search"/>
                </div>
            </form>
        </div>

        <div class="cd-faq-content">
            <?= Html::beginForm(['report/index'], 'post', ['id' => 'jet_bulk_product']);//Html::beginForm(['walmartproduct/bulk'],'post');         ?>

            <?php Pjax::begin(['timeout' => 30000, 'clientOptions' => ['container' => 'pjax-container']]); ?>

            <?= GridView::widget([
                'dataProvider' => new \yii\data\ArrayDataProvider([
                    'allModels' => $order,
                    'sort' => [
                        'attributes' => $searchOrderAttributes,
                    ],
                ]),
                'filterModel' => $searchOrderModel,
                'summary' => '<div class="summary clearfix"><div class="col-lg-5 col-md-5 col-sm-5 col-xs-12"><span class="show-items">Showing <b>{begin}-{end}</b> of <b>{totalCount}</b> items.</span></div></div>',
                'columns' => $searchOrderColumns,
            ]); ?>
            <?php Pjax::end(); ?>
            <?= Html::endForm() ?>

        </div>

        <a href="#0" class="cd-close-panel">Close</a>
    </div>
</div>
