<?php
session_start();
include(__DIR__ . '/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM admin WHERE username='$username' AND password='$password'");

    if ($result->num_rows > 0) {
        $_SESSION['admin'] = $username;
        header("Location: admin.php");
    } else {
        echo "<script>alert('Invalid Login');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <nav class="navbar">
        <div class="logo">CampusEvents</div>
    </nav>
</header>

<div class="page-container">
    <div class="card login-box">
        <h2>Admin Login</h2>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>

            <button class="btn btn-primary" type="submit">Login</button>
        </form>
    </div>
</div>

<footer>
    <p>&copy; 2026 CampusEvents</p>
</footer>

</body>
</html>