<?php
$serverName = "(localdb)\\Local"; 
$connectionOptions = array("Database" => "SuperMarket");

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}
?>
