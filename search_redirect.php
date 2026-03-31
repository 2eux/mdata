<?php
$type = $_GET['type'];
$keyword = $_GET['keyword'];

if($type == "material"){
    header("Location: material.php?search=" . urlencode($keyword));
}
elseif($type == "service"){
    header("Location: service.php?search=" . urlencode($keyword));
}
elseif($type == "vendor"){
    header("Location: vendor.php?search=" . urlencode($keyword));
}
?>