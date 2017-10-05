<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Tabs;

use common\models\User;
/* @var $this yii\web\View */
/* @var $model app\models\JetProduct */
/* @var $form yii\widgets\ActiveForm */
?>
<style>
	#savenuploadbutton{
		display: none;
	}
</style>

<div class="jet-product-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="form-group">
    
    		<div class="jet-pages-heading">
			    <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
			    <a class="help_jet" href="<?= Yii::$app->request->baseUrl?>/how-to-sell-on-jet-com#sec4" target="_blank" title="Need Help"></a>
			    <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
			    <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary','id'=>'saveedit']) ?>
		        <?= Html::button($model->isNewRecord ? '' : 'Save & Upload', ['id'=>'savenuploadbutton','class' => $model->isNewRecord ? 'btn btn-success savenuploadbutton' : 'btn btn-primary savenuploadbutton','onclick'=>'savenupload();']) ?>
		        <?= Html::hiddenInput('savenuploadinput','1',['id'=>'savenuploadinput','disabled'=>'disabled']);?>
		        <?= Html::hiddenInput('selection[]',trim($_GET['id']),['id'=>'savenuploadselection','disabled'=>'disabled']);?>
			    
			    <div class="clear"></div>	
		    </div>
        <script type='text/javascript'>
                function savenupload(){
                        <?php if($model->type!="simple"){?>
                            if(typeof checkselectedBeforeSubmit !== 'undefined' && $.isFunction(checkselectedBeforeSubmit)){
                                if(!checkselectedBeforeSubmit()){
                                        return false;
                                }
                            }
                            
                        <?php }?>
                        $('#savenuploadinput').prop('disabled', false);
                        $('#savenuploadselection').prop('disabled', false);
                        $( "#saveedit").trigger("click");
                }
        </script>
    <?php 
        if(Yii::$app->user->identity->id==14){
            $items= [
             [
                'label' => 'General Information',
                'content' => $this->render('general_tab', ['model' => $model, 'form' => $form]),
                'active' => true
            ],
            [
                'label' => 'Category & Attributes',
                'content' => $this->render('category_tab1', ['model' => $model, 'form' => $form,'connection'=>$connection]),
            ],
            /*[
                'label' => 'Attributes',

                'content' => $this->render('attribute_tab', ['model' => $model, 'form' => $form]),
                'options' => ['id' => 'attribute_tab_id'],
            ],
              */  
         ];
        }else{
            $items= [
             [
                'label' => 'General Information',
                'content' => $this->render('general_tab', ['model' => $model, 'form' => $form]),
                'active' => true
            ],
            [
                'label' => 'Category & Attributes',
                'content' => $this->render('category_tab', ['model' => $model, 'form' => $form,'connection'=>$connection]),
            ],
            /*  [
                'label' => 'Attributes',

                'content' => $this->render('attribute_tab', ['model' => $model, 'form' => $form]),
                'options' => ['id' => 'attribute_tab_id'],
            ],
                */
         ];
        }
    	?>
	</div>
	<?php 
    	/*if($data){
			$items[]=
    			[
   					'label' => 'Shipping Exception',
   					'content' => $this->render('shipexception_tab',['data'=>$data, 'model'=> $model]),
    				
    			];
    		$items[]=
    			[
    				'label' => 'Return Exception',
       				'content' => $this->render('returnexception_tab',['data'=>$data, 'model'=> $model]),
        		
    			];
    	}	*/
    ?>
    <?= Tabs::widget([
    		'items' => $items,
    		//'items' =>$menuitems,
	]);
    ?>
</div>
<script type="text/javascript">
            $('form').on('keyup keypress', function(e) {
              var keyCode = e.keyCode || e.which;
              if (keyCode === 13) { 
                e.preventDefault();
                return false;
              }
            });
</script>

