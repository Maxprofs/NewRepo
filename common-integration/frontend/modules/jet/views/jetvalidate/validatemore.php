<?php
use frontend\modules\jet\components\Data;

$exportUrl = \yii\helpers\Url::toRoute(['jetvalidate/export-validation']);
$uploadUrl = \yii\helpers\Url::toRoute(['jetproduct-fileupload/startupload']);

$merchant_id = MERCHANT_ID;


?>

<div class="container">
    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content validate_more">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="text-align: center;font-family: " Comic Sans MS";">Bulk upload validated skus or export invalid skus</h4>
                </div>
                <div class="validate_upload">
                    <a href="<?= $uploadUrl; ?>"><i class="fa fa-cloud-upload fa-5x" aria-hidden="true"> </i> <span>Upload</span><a>
                    <a href="<?= $exportUrl; ?>"><i class="fa fa-cloud-download fa-5x" aria-hidden="true"> </i> <span>Export</span></a>
                </div>
                <!-- <div class="modal-footer Attrubute_html">
                    <div class="v_error_msg" style="display:none;"></div>
                    <div class="v_success_msg alert-success alert" style="display:none;"></div>
                </div> -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>


