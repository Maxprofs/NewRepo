<?php
namespace frontend\modules\walmart\controllers;

use Yii;
use \DOMDocument;
use yii\web\Controller;
use frontend\modules\walmart\components\Upload;


/**
 * Upload Controller (For Uploading Products via csv file)
 */
class UploadController extends Controller
{
    const WALMART_CONSUMER_ID = '';

    const  WALMART_SECRET_KEY = '';

    public $_csvFilePath = 'var/walmart/csv/Product.csv';

    public $_sizeOfRequest = 10;

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionProductupload()
    {
        $csvFilePath = $this->_csvFilePath;
        if(file_exists($csvFilePath)) 
        {
            $itemCount = Upload::getRowsInCsv($csvFilePath);
            if($itemCount)
            {
                $size_of_request = $this->_sizeOfRequest;

                $pages = (int)(ceil($itemCount / $size_of_request));
                
                return $this->render('product_upload', [
                    'totalcount' => $itemCount,
                    'pages' => $pages,
                    'csvFilePath' => $csvFilePath
                ]);
            }
            else
            {
                die('No data found in csv.');
            }
        }
        else {
            die('Csv file not found.');
        }
    }

    public function actionStartproductupload()
    {
        $session = Yii::$app->session;

        $index = Yii::$app->request->post('index',false);
        if($index !== false)
        {
            $csvFilePath = Yii::$app->request->post('csvFilePath',false);

            if($csvFilePath)
            {
                $size_of_request = $this->_sizeOfRequest;

                $csvData = Upload::readItemCsv($csvFilePath, $size_of_request, $index);

                if(count($csvData))
                {
                    $xml = new DOMDocument( "1.0");
                    $xml->formatOutput = true;

                    $str = time();
                    $MPItemFeed = $xml->createElement("MPItemFeed");
                    $MPItemFeed->setAttribute("xmlns", "http://walmart.com/");

                        $MPItemFeedHeader = $xml->createElement("MPItemFeedHeader");
                            $version = $xml->createElement("version", 3.1);
                            $MPItemFeedHeader->appendChild($version);

                            $requestId = $xml->createElement("requestId", $str);
                            $MPItemFeedHeader->appendChild($requestId);

                            $requestBatchId = $xml->createElement("requestBatchId", $str);
                            $MPItemFeedHeader->appendChild($requestBatchId);

                        $MPItemFeed->appendChild($MPItemFeedHeader);

                    $uploadProduct = new Upload(self::WALMART_CONSUMER_ID, self::WALMART_SECRET_KEY);
                    foreach ($csvData as $product) 
                    {
                        $uploadProduct->prepareProduct($product, $xml, $MPItemFeed);
                    }
                    $xml->appendChild($MPItemFeed);
                    
                    $xml->save('/opt/lampp/htdocs/integration/frontend/modules/walmart/test/test'.$index.'.xml');
                }
                else
                {
                    return json_encode(['error'=>sprintf('No data found at index : %s', $index)]);
                }
            }
            else
            {
                return json_encode(['error'=>'Csv file not found.']);
            }
        }
        else
        {
            return json_encode(['error'=>'Undefined Index']);
        }
    }

    public function actionInventoryupload()
    {
        $csvFilePath = $this->_csvFilePath;
        if(file_exists($csvFilePath)) 
        {
            $itemCount = Upload::getRowsInCsv($csvFilePath);
            if($itemCount)
            {
                $size_of_request = $this->_sizeOfRequest;

                $pages = (int)(ceil($itemCount / $size_of_request));
                
                return $this->render('inventory', [
                    'totalcount' => $itemCount,
                    'pages' => $pages,
                    'csvFilePath' => $csvFilePath
                ]);
            }
            else
            {
                die('No data found in csv.');
            }
        }
        else {
            die('Csv file not found.');
        }
    }

    public function actionStartinventoryupload()
    {
        $session = Yii::$app->session;

        $index = Yii::$app->request->post('index',false);
        if($index !== false)
        {
            $csvFilePath = Yii::$app->request->post('csvFilePath',false);

            if($csvFilePath)
            {
                $size_of_request = $this->_sizeOfRequest;

                $csvData = Upload::readItemCsv($csvFilePath, $size_of_request, $index);

                if(count($csvData))
                {
                    $xml = new DOMDocument( "1.0");
                    $xml->formatOutput = true;

                    $InventoryFeed = $xml->createElement("InventoryFeed");

                    $InventoryFeed->setAttribute("xmlns", "http://walmart.com/");

                        $InventoryHeader = $xml->createElement("InventoryHeader");

                            $version = $xml->createElement("version", 1.4);
                            $InventoryHeader->appendChild($version);

                        $InventoryFeed->appendChild($InventoryHeader);

                    $upload = new Upload(self::WALMART_CONSUMER_ID, self::WALMART_SECRET_KEY);
                    foreach ($csvData as $product) 
                    {
                        $upload->prepareData('inventory', $product, $xml, $InventoryFeed);
                    }
                    $xml->appendChild($InventoryFeed);
                    
                    $xml->save('/opt/lampp/htdocs/integration/frontend/modules/walmart/test/inventory'.$index.'.xml');
                }
                else
                {
                    return json_encode(['error'=>sprintf('No data found at index : %s', $index)]);
                }
            }
            else
            {
                return json_encode(['error'=>'Csv file not found.']);
            }
        }
        else
        {
            return json_encode(['error'=>'Undefined Index']);
        }
    }
}