<?php
session_start();
session_destroy();
header('Location: /Restaurant-Management-System/admin/login.php');
exit;
?>
