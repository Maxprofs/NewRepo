<?php
namespace frontend\modules\walmart\controllers;

use Yii;
use yii\web\Controller;
use frontend\modules\walmart\components\XmlValidator;
use frontend\modules\walmart\components\WalmartProduct;

class FeedUploadController extends Controller
{
    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actions()
    {
        $this->layout = 'main';
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionUpload()
    {
        $feed_type = Yii::$app->request->post('feed-type');
        $feed_file = isset($_FILES['feed-file']['name'])?$_FILES['feed-file']:'';
        
        $consumer_id = trim(Yii::$app->request->post('consumer_id'));
        $secret_key = trim(Yii::$app->request->post('secret_key'));

        if($feed_type == '' || $feed_file == '' || $consumer_id == '' || $secret_key == '')
        {
            if($feed_type == '') {
                Yii::$app->session->setFlash('error', "Please Select Feed Type.");
            } else {
                Yii::$app->session->setFlash('error', "consumer_id, secret_key, Feed File are required.");
            }

            return $this->redirect('index');
        }
        else
        {
            $mimes = array('text/xml');
            if (!in_array($feed_file['type'], $mimes)) {
                Yii::$app->session->setFlash('error', "Please select only xml file");
                return $this->redirect(['index']);
            }

            $filePath = self::uploadFile($feed_file, $feed_type);

            if(self::validateItemXml($filePath, $feed_type))
            {
                $WalmartProduct = new WalmartProduct($consumer_id, $secret_key);
                if($feed_type == 'item')
                {
                    $WalmartProduct->uploadFeedOnWalmart($filePath, 'item');
                }
                elseif($feed_type == 'price')
                {
                    $WalmartProduct->uploadFeedOnWalmart($filePath, 'price');
                }
                elseif($feed_type == 'inventory')
                {
                    $WalmartProduct->uploadFeedOnWalmart($filePath, 'inventory');
                }
            }
            else
            {
                echo 'Invalid Feed';
                die;
            }
        }
    }

    public function validateItemXml($filePath, $feed_type)
    {
        switch ($feed_type) {
            case 'item':
                //$xsdPath = Yii::getAlias('@webroot') . '/frontend/modules/walmart/components/Xml/walmart_xsd/MPItemFeed.xsd';
                $xsdPath = Yii::getAlias('@webroot') . '/frontend/modules/walmart/components/Xml/v3item_xsd/MPItemFeed.xsd';
                break;

            case 'price':
                $xsdPath = Yii::getAlias('@webroot') . '/frontend/modules/walmart/components/Xml/price_xsd/BulkPriceFeed.xsd';
                break;

            case 'inventory':
                $xsdPath = Yii::getAlias('@webroot') . '/frontend/modules/walmart/components/Xml/inventory_xsd/InventoryFeed.xsd';
                break;
            
            default:
                return false;
        }

        $xmlValidator = new XmlValidator();

        $xmlValidator->setXMLFile($filePath);

        $xmlValidator->setXSDFile($xsdPath);
        try {
            if ($xmlValidator->validate()) {
                return true;
            }
        } catch (\Exception $e) {
            echo "Feed Validation Failed : <div style='background-color: #f2dede; color: #a94442;'>".$e->getMessage()."</div>";die('xml validation exception.');
            return false;
        }
        return false;
    }

    public function uploadFile($file, $feed_type, $merchant_id="other-merchants")
    {
        try {
            $file_name = $file['name'];
            $file_upload_path = Yii::getAlias('@webroot') . '/var/feed-upload/' . $merchant_id . '/' . $feed_type;
            
            if (!file_exists($file_upload_path)) {
                mkdir($file_upload_path, 0775, true);
            }

            $target = $file_upload_path.'/'.$file_name;
            if (file_exists($target)) {
                unlink($target);
            }
            move_uploaded_file($file['tmp_name'], $target);

            return $target;
        } catch(Exception $e) {
            echo $e->getMessage();die('file not uploaded.');
        }
    }

    
}
