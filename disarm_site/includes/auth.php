<?php
if (empty($_SESSION['disarm_auth'])) {
    header('Location: login.php');
    exit;
}
