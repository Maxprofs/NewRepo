<?php
use yii\helpers\Html;
use frontend\modules\walmart\components\Data;

$this->title = 'Upload Feed to Walmart';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="form new-section">
    <div class="jet-pages-heading">
        <h1 class="Jet_Products_style"><?= Html::encode($this->title) ?></h1>
        <div class="clear"></div>
    </div>
    <div class="csv_export col-lg-8 col-md-8 col-sm-8 col-xs-12">
        <div class="csv_import1">
            <form enctype="multipart/form-data" method="post" id="feed_upload_form" action="<?= Data::getUrl('feed-upload/upload') ?>">
                <h4><?= Html::encode($this->title) ?></h4>
                <p>Select the type of Feed you want to upload from the dropdown and then browse the file to upload on walmart.</p>

                <div>
                    <label for="consumer_id">Consumer Id</label>
                    <input type="text" value="" name="consumer_id" id="consumer_id">
                    <!-- OpenCart Client Details : 
                    e43a8d8c-c385-413f-9beb-9ad524358e37 -->
                </div>

                <div>
                    <label for="secret_key">Secret Key</label>
                    <input type="text" value="" name="secret_key" id="secret_key">
                    <!-- OpenCart Client Details :
                    MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAIGQPSN2UUQ7mNrgm95/0SKk1jafzOIRCbkcqw+uPV2sxHBdNdgeOnw9X4DUnG0vGdZLLirbW+rJik+B326vGEYZ6IJYB4cCT4fIJczWFMe/CaL8QMn63p0Ldif6HigvvX6Hf95pNMfeGzKDY/ay8pnREfGXna+mgLgYDX+wvWQjAgMBAAECgYAXIuG4Dm3OqHHQTCGpZtu6uwLBuGGG0RISa0skvrGYo7xkcFCoKiW5f+ApCk5wtOOBP+Wbo5ZTY+/96Kw0gLHtuNII1GCpG9iZBgPO+gANLUv/Usd0mh2SJmfCuQwb5ipueIUiDYk/I/x4tmw77YRUa+omcrZDMWDKyYw5Ap9CyQJBANZ91n+4MnYunnLiJPqcBgyJz+ggjm4xQVEA9aOWpms6Mw8HHE2T7Qah/10masD6TgQuO2aykkfC91Xyx+yIDfUCQQCaovZtxOsOa79t9utYVQJqvxKblD9z3hFKBgxeH5/+FHanAggSEAHnpbiyajUK1fnaLUS62E32M8N2l7KmsYK3AkB6b8abjWewspfb+F8ndxy0144IcV0cZZ1lQej2Aq7okoZG08ZCOmiY2qxMXfueQMN7+3AJBXIe6JoLQ9T/ngJpAkEAlBnFqIXzlgEt+zSEXDo2sWPEf4rxZphluJ6oU7E4O1D//p2ceygnmuOGTCIaIQGkZQf8DnNB363d44QQT0QtawJAVrf8OI8BY+o1nQ0wKDgt34NTRwQ0EVlq2if3RLcAQ1p081HPGPBJUFF7avYM4b1tM5+dlV4tfygUb91jdoSXLw==  -->
                </div>

                <label for="feed_type">Feed Type</label>
                <select class="form-control" name="feed-type" id="feed_type">
                    <option value="">Select Options</option>
                    <option value="item">Item Feed</option>
                    <option value="price">Price Feed</option>
                    <option value="inventory">Inventory Feed</option>
                </select>

                <label for="feed_file">Feed File</label>
				<input type="file" name="feed-file" id="feed_file">

                <div class="input-wrap clearfix">                   
                    <input type="button" onclick="validateBulkAction()" value="Submit Feed" class="btn btn-primary" />
                </div>
            </form>
        </div>
    </div>
    <div class="clear"></div>
</div>
<style type="text/css">
	.validation_error { 
		background-color: #f2dede;
	    border-color: #ebccd1;
	    border-radius: 4px;
	    color: #a94442;
	    margin-bottom: 20px;
	    padding: 15px;
	}
	.validation_success {
	    background-color: #dff0d8;
	    border-color: #d6e9c6;
	    color: #3c763d;
	    margin-bottom: 20px;
	    padding: 15px;
	}
</style>
<script type="text/javascript">
	function validateBulkAction() 
	{
        var type = $('#feed_type').val();
        if (type == '' || $('#feed_file').get(0).files.length === 0) 
        {
        	if(type == '')
            	alert('Please Select Feed Type.');
            else
            	alert('Please Browse Feed File.');
        } 
        else 
        {
			$("#feed_upload_form").submit();
		}
    }
</script>
