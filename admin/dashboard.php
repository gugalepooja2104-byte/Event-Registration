<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$result = $mysqli->query(
    'SELECT e.id, e.title, e.description, e.`date`, e.`time`, e.venue, e.seats_available,
            e.image_path, e.registration_fee,
            (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS registration_count
     FROM events e
     ORDER BY e.`date` DESC, e.`time` DESC'
);
$events = $result->fetch_all(MYSQLI_ASSOC);
$totalRegistrations = array_sum(array_map(static fn (array $event): int => (int) $event['registration_count'], $events));

$pageTitle = 'Admin dashboard';
$assetBase = '../';
require __DIR__ . '/../includes/header.php';
?>
<section class="dashboard-heading">
  <div>
    <p class="eyebrow">ADMINISTRATION</p>
    <h1>Campus events, at a glance.</h1>
    <p class="muted">Create events and keep track of student registrations.</p>
  </div>
  <a class="button" href="create_event.php"><span aria-hidden="true">+</span> Add event</a>
</section>
<section class="stats-row" aria-label="Event statistics">
  <div class="stat-block"><span class="stat-number"><?= count($events) ?></span><span class="stat-label">Total events</span></div>
  <div class="stat-block"><span class="stat-number"><?= $totalRegistrations ?></span><span class="stat-label">Registrations</span></div>
</section>
<section class="content-section">
  <div class="section-heading"><div><p class="eyebrow">EVENT MANAGEMENT</p><h2>All events</h2></div></div>
  <?php if (!$events): ?>
    <div class="empty-state"><span class="empty-mark">+</span><h3>No events created</h3><p>Add the first event to open registration to students.</p><a href="create_event.php" class="text-link">Create an event →</a></div>
  <?php else: ?>
    <div class="table-wrap"><table>
      <thead><tr><th>Event</th><th>Date &amp; time</th><th>Venue</th><th>Fee</th><th>Seats left</th><th>Registered</th><th></th></tr></thead>
      <tbody><?php foreach ($events as $event): ?>
        <tr>
          <td class="table-primary">
            <span class="admin-event-cell">
              <?php if (!empty($event['image_path'])): ?>
                <img class="admin-event-thumb" src="../<?= escape($event['image_path']) ?>" alt="" loading="lazy">
              <?php else: ?>
                <span class="admin-event-thumb admin-event-thumb-empty" aria-hidden="true">CE</span>
              <?php endif; ?>
              <span><?= escape($event['title']) ?></span>
            </span>
          </td>
          <td><?= escape(date('M j, Y', strtotime($event['date']))) ?><br><span class="table-secondary"><?= escape(date('g:i A', strtotime($event['time']))) ?></span></td>
          <td><?= escape($event['venue']) ?></td>
          <td><?= (float) $event['registration_fee'] > 0 ? '₹' . escape(number_format((float) $event['registration_fee'], 2)) : 'Free' ?></td>
          <td><span class="metric-pill <?= (int) $event['seats_available'] > 0 ? 'metric-available' : 'metric-full' ?>"><?= (int) $event['seats_available'] ?> left</span></td>
          <td><span class="metric-pill metric-registered"><?= (int) $event['registration_count'] ?></span></td>
          <td><a class="text-link" href="attendees.php?event_id=<?= (int) $event['id'] ?>">Attendees →</a></td>
        </tr>
      <?php endforeach; ?></tbody>
    </table></div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
