<?php
// Try changing "localhost" to "127.0.0.1"
$conn = new mysqli("127.0.0.1", "root", "", "your_database_name", 3307); 



if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>