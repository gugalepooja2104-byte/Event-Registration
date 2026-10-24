<?php
$pageTitle = $pageTitle ?? 'College Events';
$currentUser = $_SESSION['user'] ?? null;
$flashMessage = take_flash();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= escape($pageTitle) ?> | Campus Events</title>
  <link rel="stylesheet" href="<?= escape($assetBase ?? '') ?>assets/css/style.css">
  <script src="<?= escape($assetBase ?? '') ?>assets/js/validation.js" defer></script>
</head>
<body>
<header class="site-header">
  <a class="brand" href="<?= escape($assetBase ?? '') ?><?= $currentUser ? ($currentUser['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php') : 'index.php' ?>">
    <span class="brand-mark" aria-hidden="true">CE</span>
    <span>Campus Events</span>
  </a>
  <nav class="top-nav" aria-label="Main navigation">
    <?php if ($currentUser): ?>
      <span class="nav-user">
        <span class="nav-user-name"><?= escape($currentUser['name']) ?></span>
        <span class="nav-user-role"><?= escape(ucfirst($currentUser['role'])) ?></span>
      </span>
      <form action="<?= escape($assetBase ?? '') ?>logout.php" method="post" class="inline-form">
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
        <button class="button button-quiet button-small" type="submit">Sign out</button>
      </form>
    <?php else: ?>
      <a href="<?= escape($assetBase ?? '') ?>login.php">Sign in</a>
      <a class="button button-small" href="<?= escape($assetBase ?? '') ?>register.php">Create account</a>
    <?php endif; ?>
  </nav>
</header>
<main class="page-shell">
  <?php if ($flashMessage): ?>
    <div class="alert alert-<?= escape($flashMessage['type']) ?>" role="status">
      <?= escape($flashMessage['message']) ?>
    </div>
  <?php endif; ?>
