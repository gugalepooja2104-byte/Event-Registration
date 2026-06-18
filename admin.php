<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include(__DIR__ . '/db.php');

// Get all unique events
$events = $conn->query("SELECT DISTINCT event_name FROM registrations");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <nav class="navbar">
        <div class="logo">CampusEvents</div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="page-container">

<?php while($eventRow = $events->fetch_assoc()) { 
    $eventName = $eventRow['event_name'];

    // Get registrations for this event
    $result = $conn->query("SELECT * FROM registrations WHERE event_name='$eventName' ORDER BY id DESC");
?>

    <div class="card" style="margin-bottom:30px;">
       <?php 
        $count = $conn->query("SELECT COUNT(*) as total FROM registrations WHERE event_name='$eventName'")
              ->fetch_assoc()['total'];
        ?>
        <h2><?= $eventName ?> (<?= $count ?> Students)</h2>

        <div class="table-container">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Student ID</th>
                    <th>Email</th>
                    <th>Fee</th>
                    <th>Txn ID</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php while($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['student_id'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td>₹<?= $row['fee'] ?></td>
                    <td><?= $row['transaction_id'] ?: 'Not Submitted' ?></td>

                    <td>
                        <?php if($row['payment_status'] == 'Paid') { ?>
                            <span class="status-paid">Paid</span>
                        <?php } else { ?>
                            <span class="status-pending">Pending</span>
                        <?php } ?>
                    </td>

                    <td>
                        <?php if($row['payment_status'] != 'Paid') { ?>
                            <a href="approve.php?id=<?= $row['id'] ?>">
                                <button class="btn btn-success">Approve</button>
                            </a>
                        <?php } ?>

                        <a href="delete.php?id=<?= $row['id'] ?>" 
                           onclick="return confirm('Delete this record?')">
                            <button class="btn btn-danger">Delete</button>
                        </a>
                    </td>
                </tr>
                <?php } ?>

            </table>
        </div>
    </div>

<?php } ?>

</div>

<footer>
    <p>&copy; 2026 CampusEvents</p>
</footer>

</body>
</html>