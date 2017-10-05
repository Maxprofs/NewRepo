<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 29/8/17
 * Time: 12:56 PM
 */
?>
<?php
use yii\helpers\Html;
use frontend\modules\walmart\components\Data;

$this->title = 'Blog Section';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="jet-product-index content-section">
    <div class="form new-section">
        <div class="jet-pages-heading ">
            <div class="title-need-help">
                <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
            </div>
            <div class="product-upload-menu">
                <div class="jet-upload-submit"></div>
            </div>
            <div class="clear"></div>
        </div>
        <section class="blog-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <?php
                        if (empty($data)) {
                            return $this->redirect(['walmart-blog']);
                        } else {

                            foreach ($data as $blog) {

                                $query = "SELECT *
                                     FROM ced_postmeta WHERE post_id = '" . $blog['ID'] . "' AND meta_key='_thumbnail_id'";
                                $post_meta_id = Yii::$app->db2->createCommand($query)->queryOne();

                                $query2 = "SELECT guid
                                     FROM ced_posts WHERE ID = '" . $post_meta_id['meta_value'] . "' AND post_type='attachment'";
                                $post_image = Yii::$app->db2->createCommand($query2)->queryOne();
                                ?>

                                <div class="post">
                                    <div class="post-title-top">
                                        <div class="post-date">
                                            <span class="time">
                                                <i class="fa fa-calendar" aria-hidden="true">
                                                </i>
                                                <?php $date=date_create($blog['post_date']); ?>
                                                <div class="year"><?= date_format($date,"M");?></div>
                                                <div class="date-year"><?php echo date_format($date,"d"); echo ' , ' ; echo date_format($date,"Y")?></div>
                                            </span>
                                            <!-- <span class="time">
                                                <time>
                                                    <?php $date=date_create($blog['post_date']);
                                                    echo date_format($date,"m");  ?>
                                                </time>
                                            </span> -->
                                            <div class="post-details">
                                                <h3 class="post-title">
                                                    <a href="<?= $blog['guid'] ?>" target="_blank"><?php echo $blog['post_title'] ?></a>
                                                </h3>
                                                <div class="update-created">
                                                    <span class="lastupdate"> 
                                                        <strong>
                                                            Last updated on :
                                                        </strong> 
                                                        <time>
                                                            <?php $date=date_create($blog['post_modified']); echo date_format($date,"d M Y ");  ?>
                                                            
                                                        </time>
                                                    </span>
                                                    <!--<span class="created-by">
                                                        <strong>
                                                            Created by : 
                                                        </strong>
                                                         <span class="author">Chris Hemsworth</span>
                                                    </span>-->
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="clearfix">
                                        <div class="post-content col-lg-5">
                                            <div class="post-message">
                                                <?php echo substr(strip_tags($blog['post_content']), 0, 250);
                                                echo '[...]'; ?>
                                            </div>
                                            <a href="<?= $blog['guid'] ?>" target="_blank">Read More <i class="fa fa-angle-double-right" aria-hidden="true"></i></a>
                                        </div>
                                        <div class="post-image col-lg-7">
                                            <div class="post-img-wrap">
                                                <a href="<?= $blog['guid'] ?>" target="_blank">
                                                    <img src="<?php echo $post_image['guid'] ?>" alt="post-img">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                        }
                        ?>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                        <div class="recent-post-wrapper">
                            <div class="recent-post-title">
                                <h3>Recent Post</h3>
                            </div>
                            <?php
                            if (!empty($recentpost)) {

                                foreach ($recentpost as $blog) {
                                    $query = "SELECT *
                                     FROM ced_postmeta WHERE post_id = '" . $blog['ID'] . "' AND meta_key='_thumbnail_id'";
                                    $post_meta_id = Yii::$app->db2->createCommand($query)->queryOne();

                                    $query2 = "SELECT guid
                                     FROM ced_posts WHERE ID = '" . $post_meta_id['meta_value'] . "' AND post_type='attachment'";
                                    $post_image = Yii::$app->db2->createCommand($query2)->queryOne();
                                    ?>
                                    <div class="recent-posts">
                                        <div class="recent-post-content  clearfix">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <!--<div class="recent-post-img">
                                                    <img src="<?php /*echo $post_image['guid'] */?>"
                                                         alt="recent-post-img">
                                                </div>-->
                                                <div class="recent-post-message">
                                                    <a href="<?= $blog['guid'] ?>"
                                                       target="_blank"><strong><?php echo substr(strip_tags($blog['post_title']), 0, 40) ?></strong></a>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="recent-post-message">
                                                    <?php echo substr(strip_tags($blog['post_content']), 0, 75);
                                                        ?>
                                                    <a href="<?= $blog['guid'] ?>" target="_blank" > <i class="fa fa-angle-double-right" aria-hidden="true"></i> </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php }
                            }
                            ?>

                        </div>
                    </div>
                </div>
            </div>
            <div class="pagination">
                <?php
                if ($page == 0) {
                    $previous = $page + 1;
                    ?>
                    <a href="<?= Data::getUrl('help/walmart-blog?page=' . $previous) ?>">Older Post</a>
                <?php } else {
                    $previous = $page + 1;
                    $next = $page - 1;
                    ?>
                    <a href="<?= Data::getUrl("help/walmart-blog?page=" . $previous) ?>">Older Post</a>

                    <a href="<?= Data::getUrl("help/walmart-blog?page=" . $next) ?>">Newer Post</a>

                <?php }
                ?>

            </div>
        </section>

    </div>
</div>
