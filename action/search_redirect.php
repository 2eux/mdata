<?php
$keyword = $_GET['keyword'] ?? '';
$type    = $_GET['type'] ?? 'material';

if(empty($keyword)){
    header("Location: home.php");
    exit();
}

if($type == 'material'){
    header("Location: material.php?keyword=" . urlencode($keyword));
}
elseif($type == 'service'){
    header("Location: service.php?keyword=" . urlencode($keyword));
}
elseif($type == 'vendor'){
    header("Location: vendor.php?keyword=" . urlencode($keyword));
}
exit();
?>