<?php

namespace backend\modules\walmart\controllers;

use Yii;


/**
 * DetailsController implements the CRUD actions for JetExtensionDetail model.
 */
class ReviewRatingController extends BaseController
{
    public static $counter = 0;
    

    /**
     * Lists all JetExtensionDetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        $htmlData = $this->curlRequest();
        if(isset($htmlData['reviews']) && !empty($htmlData['reviews'])){
             return $this->render('index', [
            'data' => $htmlData,
        ]);
        }
        return $this->render('index', [
            'data' => $htmlData,
        ]);
    }

    public function curlRequest(){
        $url = 'https://apps.shopify.com/5eabff626f2420c086bbba243b69d22a/reviews.json';

        $handle=curl_init($url);
        curl_setopt($handle, CURLOPT_VERBOSE, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($handle);
        $rating = json_decode($content,true);
        if(isset($rating['reviews']) && !empty($rating['reviews'])){
            return $rating;
        }
        else{
            self::$counter++;
            if(self::$counter == 4){
                $data['no_content']='Review Rating Api Not Working';
                return $data;
            }
            else{
                $this->curlRequest();
            }
        }


    }

    

    
}
