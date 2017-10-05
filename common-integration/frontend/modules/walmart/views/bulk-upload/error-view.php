<?php

$errorsArray = require $errorsArrayFile;

if(count($errorsArray))
{
?>
	<div class="content-section">
		<div class="form new-section">
			<ul class="error-list">
			<li class="header"> <span class="error"> SKU </span> <span> Error</span></li>
	<?php
			foreach ($errorsArray as $sku => $error) {
	?>
				<li> <span class="error"> <?= $sku ?> : </span> <?= $error ?></li>
	<?php
			}
	?>
			</ul>
		</div>
	</div>
<?php
}
else
{
?>
	<div>
		<p>No Error Occured during Product Upload.</p>
	</div>
<?php
}
?>
