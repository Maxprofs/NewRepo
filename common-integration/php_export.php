<?php 
$db_name = 'cedcom5_sPy11F';
$db_user = 'cedcom5_sPy11F';
$db_pass = '-ZD,uo(M+N01';
$host_name = 'cedcommerce.com';
$backup_file = "shopify_db(25April2016).sql";

//export db from php
 $command = "mysqldump --database " . $db_name  . " -u ". $db_user  . " -p'". $db_pass . "' > " . $backup_file;
$output = shell_exec($command);
var_dump($output);
echo 'done';

//code to import db by ftp
/* $command = "mysqldump --database " . $db_name  . " -u ". $db_user  . " -p'". $db_pass . "' < " . $backup_file;
$output = shell_exec($command);
var_dump($output);
echo 'done';
 */
?>