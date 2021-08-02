<?php
require_once('config.php');
require_once('./functions/db_helper.php');

$dbh = getDbConnect();
$data = getNewImage($dbh);

$pickups = getPickupImage($dbh);
shuffle($pickups);
$pickup = $pickups[0];

$categories = getCategory($dbh);

include_once('./views/top.html');
?>