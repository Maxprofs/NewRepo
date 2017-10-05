<?php
use yii;
use yii\helpers\Html;
use yii\widgets\DetailView;
use frontend\modules\jet\models\JetProduct;
/* @var $this yii\web\View */
/* @var $model app\models\JetErrorfileInfo */
$merchant_id = \Yii::$app->user->identity->id;
$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Jet Errorfile Infos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$productSkus=array();
$productSkus=explode(',',$model->product_skus);
?>
<div class="jet-errorfile-info-view">
    <h1><?= "View Rejetced Products" ?></h1>
    <p>
     	<?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger pull-right',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Resubmit File', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>
	<div class="clear"></div>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            //'jet_file_id',
           // 'file_name',
            'file_type',
            'status',
            'error:ntext',
            'date',
           // 'jetinfofile_id',
        ],
    ]); 
    if($productSkus>0){?>
		<table class="table table-striped table-bordered detail-view">
			<tr>
				<th>Id</th>
				<th>Product Title</th>
				<th>Sku</th>
				<th>Action</th>
			</tr>
			
				<?php 
				foreach ($productSkus as $key=>$value){
					$result="";					
					$result=JetProduct::find()->where(['merchant_id'=>$merchant_id,'sku'=>$value])->one();
					if($result){?>
					<tr>
						<td><?php echo $key+1;?></td>
						<td><?php echo $result->title;?></td>
						<td><?php echo $result->sku;?></td>
						<td><a target="_blank" href="<?=Yii::$app->request->baseUrl;?>/jetproduct/update?id=<?php echo $result->id;?>" title="update"><span class="glyphicon glyphicon-pencil"></span></a></td>
					</tr>
			  <?php }
				}
				?>
				
		</table>
<?php }?>
</div>
