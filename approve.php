<?php
include(__DIR__ . '/db.php');

$id = $_GET['id'];

$conn->query("UPDATE registrations SET payment_status='Paid' WHERE id=$id");

header("Location: admin.php");
?>