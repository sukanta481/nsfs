<?php
/**
 * OpsDesk page shell. Same <head>, sidebar and top header as the rest of the CMS
 * (mirrors top_header.php without re-including includes/top.php).
 * Set $ops_title before including.
 */
$ops_title = isset($ops_title) ? $ops_title : 'OpsDesk';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($ops_title) ?> | OpsDesk | NSFS Admin</title>
  <link rel="icon" type="image/png" href="images/logo.png">
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="fonts/css/font-awesome.min.css" rel="stylesheet">
  <link href="css/custom.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="opsdesk/assets/opsdesk.css?v=<?= filemtime(__DIR__ . '/assets/opsdesk.css') ?>" rel="stylesheet">
  <script src="js/jquery.min.js"></script>
  <script>window.OPS_CSRF = <?= json_encode(ops_csrf_token()) ?>;</script>
</head>
<body class="nav-md">
<div class="container body">
<div class="main_container">
<?php require __DIR__ . '/../left_panel.php'; ?>
<?php require __DIR__ . '/../header_banner.php'; ?>
<div class="right_col" role="main">
<div class="ops">
<?php if ($f = ops_take_flash()): ?>
  <div class="note <?= $f['type'] === 'ok' ? 'ok' : 'err' ?> flash"><i class="fa <?= $f['type'] === 'ok' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i><div><?= h($f['msg']) ?></div></div>
<?php endif; ?>
