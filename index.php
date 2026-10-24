<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!empty($_SESSION['user'])) {
    $destination = $_SESSION['user']['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php';
    header('Location: ' . $destination);
} else {
    header('Location: login.php');
}
exit;
