<?php
session_start();
session_destroy();
header('Location: /Restaurant-Management-System/personnel/login.php');
exit;
?>
