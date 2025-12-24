<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title><?= $title ?? 'Admin Panel' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

<?php
  $cssPath = '/Restaurant-Management-System/assets/css/style.css';
  $cssFile = $_SERVER['DOCUMENT_ROOT'] . $cssPath;
  $cssVersion = file_exists($cssFile) ? filemtime($cssFile) : time();
?>
  <link rel="stylesheet" href="<?= htmlspecialchars($cssPath . '?v=' . $cssVersion) ?>">
</head>
<body class="<?= $bodyClass ?? '' ?>">
