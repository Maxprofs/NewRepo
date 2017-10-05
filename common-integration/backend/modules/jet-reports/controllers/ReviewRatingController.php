<?php

namespace backend\modules\reports\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\modules\reports\components\Data;



/**
 * ReviewRatingController
 */
class ReviewRatingController extends BaseController
{


    /**
     * Lists all JetExtensionDetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        
        $url = 'https://apps.shopify.com/jet-integration';

        $handle=curl_init($url);
        curl_setopt($handle, CURLOPT_VERBOSE, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($handle);
        $data = [];
        preg_match_all('/<meta itemprop="ratingValue" content="(.*?)">/s',$content,$estimates);
        $data['ratingValue'] = $estimates[1][0];
        preg_match_all('/<meta itemprop="bestRating" content="(.*?)">/s',$content,$estimates);
        $data['bestRating'] = $estimates[1][0];
        preg_match_all('/<meta itemprop="reviewCount" content="(.*?)">/s',$content,$estimates);
        $data['reviewCount'] = $estimates[1][0];
        preg_match_all('/<div class="resourcesreviews-reviews">(.*?)<div class="pagination">/s',$content,$estimates);
        $data['reviews'] = $estimates[1][0];
        return $this->render('index', [
            'data' => $data,
        ]);
    }

    

    
}
