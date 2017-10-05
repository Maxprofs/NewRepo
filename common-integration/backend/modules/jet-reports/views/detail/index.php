
<button id="today-button" class="pull-left btn btn-primary ">Today Installation</button>
<button id="yesterday-button" class="pull-left btn btn-primary ">Yesterday Installation</button>
<button id="week-button" class="pull-left btn btn-primary ">Week Installation</button>
<button id="month-button" class="pull-left btn btn-primary ">Month Installation</button>

<div id=today>
<table class="table table-striped table-bordered">
<tr>
	<th>M-ID</th>
	<th>Shop url</th>
	<th>Email</th>
	<th>Install Date</th>
	<th>Expire Date</th>
	<th>Status</th>
	<th>App Status</th>
</tr>
<tr>
	<?php 
	foreach ($today as $key => $value) {
	?>
	<td><?= $value['merchant_id'];?></td>
	<td><?= $value['shopurl'];?></td>
	<td><?= $value['email'];?></td>
	<td><?= $value['install_date'];?></td>
	<td><?= $value['expire_date'];?></td>
	<td><?= $value['status'];?></td>
	<td><?= $value['app_status'];?></td>
</tr>
<?php }?>
</table>
</div>

<div id=month>
<table class="table table-striped table-bordered">
<tr>
	<th>M-ID</th>
	<th>Shop url</th>
	<th>Email</th>
	<th>Install Date</th>
	<th>Expire Date</th>
	<th>Status</th>
	<th>App Status</th>
</tr>
<tr>
	<?php 
	foreach ($month as $key => $value) {
	?>
	<td><?= $value['merchant_id'];?></td>
	<td><?= $value['shopurl'];?></td>
	<td><?= $value['email'];?></td>
	<td><?= $value['install_date'];?></td>
	<td><?= $value['expire_date'];?></td>
	<td><?= $value['status'];?></td>
	<td><?= $value['app_status'];?></td>
</tr>
<?php }?>
</table>
</div>

<div id=yesterday>
<table class="table table-striped table-bordered">
<tr>
	<th>M-ID</th>
	<th>Shop url</th>
	<th>Email</th>
	<th>Install Date</th>
	<th>Expire Date</th>
	<th>Status</th>
	<th>App Status</th>
</tr>
<tr>
	<?php 
	foreach ($yesterday as $key => $value) {
	?>
	<td><?= $value['merchant_id'];?></td>
	<td><?= $value['shopurl'];?></td>
	<td><?= $value['email'];?></td>
	<td><?= $value['install_date'];?></td>
	<td><?= $value['expire_date'];?></td>
	<td><?= $value['status'];?></td>
	<td><?= $value['app_status'];?></td>
</tr>
<?php }?>
</table>
</div>

<div id=week>
<table class="table table-striped table-bordered">
<tr>
	<th>M-ID</th>
	<th>Shop url</th>
	<th>Email</th>
	<th>Install Date</th>
	<th>Expire Date</th>
	<th>Status</th>
	<th>App Status</th>
</tr>
<tr>
	<?php 
	foreach ($week as $key => $value) {
	?>
	<td><?= $value['merchant_id'];?></td>
	<td><?= $value['shopurl'];?></td>
	<td><?= $value['email'];?></td>
	<td><?= $value['install_date'];?></td>
	<td><?= $value['expire_date'];?></td>
	<td><?= $value['status'];?></td>
	<td><?= $value['app_status'];?></td>
</tr>
<?php }?>
</table>
</div>
