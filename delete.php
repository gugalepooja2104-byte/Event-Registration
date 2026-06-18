<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include(__DIR__ . '/db.php');

$id = $_GET['id'];

$conn->query("DELETE FROM registrations WHERE id=$id");

header("Location: admin.php");
?>