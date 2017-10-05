<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 6/6/17
 * Time: 6:46 PM
 */
namespace frontend\modules\walmart\controllers;

use frontend\modules\walmart\components\Data;
use frontend\modules\walmart\components\WalmartRepricing;
use Yii;
use yii\base\Exception;

class HelpController extends WalmartmainController
{

    public function actionIndex()
    {
        $resultdata = array();
        $query = "SELECT * FROM `walmart_faq` ";
        $resultdata = Data::sqlRecords($query, "all", "select");

        return $this->render('index', [
            'data' => $resultdata
        ]);
    }

    public function actionWalmartBlog()
    {
        if (!isset($_GET['page'])) {
            $offset = 0;
            $limit = 5;
            $page = 0;
        } else {
            $page= $_GET['page'];
            $limit = 5;
            $offset = $page * $limit;
        }

        $walamrt_cat_id = 165;
        $blogData = '';
        $query = "SELECT
                    * FROM ced_posts p
                    JOIN ced_term_relationships tr ON (p.ID = tr.object_id)
                    JOIN ced_term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    JOIN ced_terms t ON (tt.term_id = t.term_id)
                    WHERE p.post_type='post'
                    AND p.post_status = 'publish'
                    AND tt.taxonomy = 'category'
                    AND t.term_id = $walamrt_cat_id
                    AND p.post_status = 'publish' 
                    ORDER BY p.post_modified DESC LIMIT $offset,$limit";
        $blogData = Yii::$app->db2->createCommand($query)->queryAll();

        $query2 = "SELECT
                    * FROM ced_posts p
                    JOIN ced_term_relationships tr ON (p.ID = tr.object_id)
                    JOIN ced_term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    JOIN ced_terms t ON (tt.term_id = t.term_id)
                    WHERE p.post_type='post'
                    AND p.post_status = 'publish'
                    AND tt.taxonomy = 'category'
                    AND t.term_id = $walamrt_cat_id
                    AND p.post_status = 'publish' 
                    ORDER BY p.post_modified DESC LIMIT 0,5";
        $recentPost = Yii::$app->db2->createCommand($query2)->queryAll();

        return $this->render('walmartblog', [
            'data' => $blogData,'recentpost'=>$recentPost ,'page' => $page
        ]);
    }

    /*public function actionViewBlog()
    {
        $blogData = '';

        return $this->render('viewblog', [
            'data' => $blogData
        ]);
    }*/

}