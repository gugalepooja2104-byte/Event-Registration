<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$eventId = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$eventId) {
    http_response_code(400);
    exit('A valid event is required.');
}

$eventStatement = $mysqli->prepare('SELECT id, title, `date`, `time`, venue FROM events WHERE id = ?');
$eventStatement->bind_param('i', $eventId);
$eventStatement->execute();
$event = $eventStatement->get_result()->fetch_assoc();
if (!$event) {
    http_response_code(404);
    exit('Event not found.');
}

$attendeeStatement = $mysqli->prepare(
    'SELECT u.name, u.email, u.roll_no, r.registration_date
     FROM registrations r
     INNER JOIN users u ON u.id = r.user_id
     WHERE r.event_id = ?
     ORDER BY r.registration_date ASC'
);
$attendeeStatement->bind_param('i', $eventId);
$attendeeStatement->execute();
$attendees = $attendeeStatement->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Event attendees';
$assetBase = '../';
require __DIR__ . '/../includes/header.php';
?>
<section class="dashboard-heading">
  <div>
    <a class="back-link" href="dashboard.php">← All events</a>
    <p class="eyebrow">ATTENDEE LIST · <?= count($attendees) ?> REGISTERED</p>
    <h1><?= escape($event['title']) ?></h1>
    <p class="muted"><?= escape(date('M j, Y', strtotime($event['date']))) ?> · <?= escape(date('g:i A', strtotime($event['time']))) ?> · <?= escape($event['venue']) ?></p>
  </div>
</section>
<section class="content-section">
  <?php if (!$attendees): ?>
    <div class="empty-state"><h3>No registrations yet</h3><p>Student registrations will appear here.</p></div>
  <?php else: ?>
    <div class="table-wrap"><table>
      <thead><tr><th>Student</th><th>Roll number</th><th>Email</th><th>Registered on</th></tr></thead>
      <tbody><?php foreach ($attendees as $attendee): ?>
        <tr>
          <td class="table-primary"><?= escape($attendee['name']) ?></td>
          <td><?= escape($attendee['roll_no']) ?></td>
          <td><a class="table-email" href="mailto:<?= escape($attendee['email']) ?>"><?= escape($attendee['email']) ?></a></td>
          <td><?= escape(date('M j, Y g:i A', strtotime($attendee['registration_date']))) ?></td>
        </tr>
      <?php endforeach; ?></tbody>
    </table></div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
