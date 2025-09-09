<?php
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<title><?=htmlspecialchars($title ?? '2xtreme', ENT_QUOTES)?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?=BASE_URL?>/app/public/css/style.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="<?=BASE_URL?>/index.php">2xtreme</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?=BASE_URL?>/app/x/x_list.php">X</a></li>
        <li class="nav-item"><a class="nav-link" href="<?=BASE_URL?>/app/admin/users.php">Users</a></li>
        <li class="nav-item"><a class="nav-link" href="<?=BASE_URL?>/app/auth/logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container my-5">
  <div class="card shadow-sm rounded-4 mx-auto" style="max-width: 800px;">
    <div class="card-body">
