<?php
require_once 'config.php';

// Already logged in
if (!empty($_SESSION['disarm_auth'])) {
    header('Location: index.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw = $_POST['password'] ?? '';
    if (SITE_PASSWORD !== '' && hash_equals(SITE_PASSWORD, $pw)) {
        $_SESSION['disarm_auth'] = true;
        header('Location: index.php'); exit;
    }
    $error = 'Invalid password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — <?= SITE_TITLE ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <div class="label" style="margin-bottom:48px">DISARM Framework · v<?= SITE_VERSION ?></div>
    <h1>Access<br>Required</h1>
    <p class="login-sub">This environment is password protected.</p>
    <?php if ($error): ?>
    <div class="flash flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="form-group">
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" autofocus autocomplete="current-password">
      </div>
      <div style="margin-top:36px">
        <button type="submit" class="btn btn-dark" style="width:100%;justify-content:center">Enter →</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
