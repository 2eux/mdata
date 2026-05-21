<?php
session_start();
session_destroy();
header("Location: /atri/Pages/index.php");
exit();
?>