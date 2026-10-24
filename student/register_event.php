<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
verify_csrf();

$eventId = filter_var($_POST['event_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($eventId === false || $eventId === null) {
    set_flash('Choose a valid event.', 'error');
    header('Location: dashboard.php');
    exit;
}

$userId = (int) $_SESSION['user']['id'];
try {
    $mysqli->begin_transaction();
    $update = $mysqli->prepare('UPDATE events SET seats_available = seats_available - 1 WHERE id = ? AND `date` >= CURDATE() AND seats_available > 0');
    $update->bind_param('i', $eventId);
    $update->execute();

    if ($update->affected_rows !== 1) {
        $mysqli->rollback();
        set_flash('This event is no longer available for registration.', 'error');
    } else {
        $insert = $mysqli->prepare('INSERT INTO registrations (user_id, event_id) VALUES (?, ?)');
        $insert->bind_param('ii', $userId, $eventId);
        $insert->execute();
        $mysqli->commit();
        set_flash('Your seat is confirmed. See you there!');
    }
} catch (mysqli_sql_exception $exception) {
    $mysqli->rollback();
    if ((int) $exception->getCode() === 1062) {
        set_flash('You are already registered for this event.', 'error');
    } elseif ((int) $exception->getCode() === 1452) {
        set_flash('This event or account is no longer available.', 'error');
    } else {
        throw $exception;
    }
}

header('Location: dashboard.php');
exit;
