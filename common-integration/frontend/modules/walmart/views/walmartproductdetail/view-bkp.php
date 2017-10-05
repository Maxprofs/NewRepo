<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 17/7/17
 * Time: 12:34 PM
 */
use yii\helpers\Html;


$html = '<table class="table table-striped table-bordered product_detail" cellspacing="0" width="100%"><tbody>';
foreach ($data as $key => $value) {

    if($key == 'ITEM PAGE URL')
    {
        if (!empty($value)) {
            $html .= '
        <tr>
            <td class="value_label" width="33%">
                <strong>' . strtoupper($key) . '</strong>
            </td>
            <td class="value form-group " width="100%">
                <a href="'.$value.'" target="_blank">' . $value . '</a>
                
            </td>
        </tr>';
        }
    }elseif ($key == 'PRIMARY IMAGE URL'){
        if (!empty($value)) {
            $html .= '
        <tr>
            <td class="value_label" width="33%">
                <strong>' . strtoupper($key) . '</strong>
            </td>
            <td class="value form-group " width="100%">
                <a href="'.$value.'" target="_blank"><img src="'.$value.'" class="image_hover" width="100" height="100"></a>
            </td>
        </tr>';
        }

    }else{
        if (!empty($value)) {
            $html .= '
        <tr>
            <td class="value_label" width="33%">
                <strong>' . strtoupper($key) . '</strong>
            </td>
            <td class="value form-group " width="100%">
                <span>' . $value . '</span>
                
            </td>
        </tr>';
        }
    }

}
$html .= '</tbody></table>';
?>
<div class="container">
    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="text-align: center;font-family: " Comic Sans MS";">Product
                    Information on Walmart</h4>
                </div>
                <div class="modal-body">
                    <?= $html ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .form-group {
        margin: 0 0 0;
    }
</style>
