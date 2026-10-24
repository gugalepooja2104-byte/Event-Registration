<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('student');

$userId = (int) $_SESSION['user']['id'];
$eventStatement = $mysqli->prepare(
    'SELECT e.id, e.title, e.description, e.`date`, e.`time`, e.venue, e.seats_available,
            e.image_path, e.rules_regulation, e.registration_fee, r.id AS registration_id
     FROM events e
     LEFT JOIN registrations r ON r.event_id = e.id AND r.user_id = ?
     WHERE e.`date` >= CURDATE()
     ORDER BY e.`date` ASC, e.`time` ASC'
);
$eventStatement->bind_param('i', $userId);
$eventStatement->execute();
$events = $eventStatement->get_result()->fetch_all(MYSQLI_ASSOC);

$registeredStatement = $mysqli->prepare(
    'SELECT e.title, e.`date`, e.`time`, e.venue, r.registration_date
     FROM registrations r
     INNER JOIN events e ON e.id = r.event_id
     WHERE r.user_id = ?
     ORDER BY e.`date` DESC, e.`time` DESC'
);
$registeredStatement->bind_param('i', $userId);
$registeredStatement->execute();
$registeredEvents = $registeredStatement->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Student dashboard';
$assetBase = '../';
require __DIR__ . '/../includes/header.php';
?>
<section class="dashboard-heading">
  <div>
    <p class="eyebrow">STUDENT DASHBOARD</p>
    <h1>Good to see you, <?= escape($_SESSION['user']['name']) ?>.</h1>
    <p class="muted">Find something happening on campus and save your seat.</p>
  </div>
  <div class="heading-index"><span><?= count($events) ?></span><small>upcoming<br>events</small></div>
</section>
<section class="content-section">
  <div class="section-heading">
    <div><p class="eyebrow">WHAT'S ON</p><h2>Upcoming events</h2></div>
  </div>
  <?php if (!$events): ?>
    <div class="empty-state"><span class="empty-mark">+</span><h3>Nothing on the calendar yet</h3><p>Check back soon for new campus events.</p></div>
  <?php else: ?>
    <div class="event-grid">
      <?php foreach ($events as $event): ?>
        <article class="event-card">
          <?php if (!empty($event['image_path'])): ?>
            <!-- Fixed the directory path by pointing directly from root server context -->
            <img class="event-banner" src="<?= $assetBase . escape($event['image_path']) ?>" alt="<?= escape($event['title']) ?>" loading="lazy">
          <?php else: ?>
            <div class="event-banner event-banner-empty" aria-hidden="true"><span>Campus Events</span></div>
          <?php endif; ?>
          <div class="event-info">
            <div class="event-date"><strong><?= escape(date('d', strtotime($event['date']))) ?></strong><span><?= escape(date('M', strtotime($event['date']))) ?></span></div>
            <div class="event-copy">
              <p class="event-meta"><?= escape(date('D, M j', strtotime($event['date']))) ?> <span aria-hidden="true">·</span> <?= escape(date('g:i A', strtotime($event['time']))) ?></p>
              <h3><?= escape($event['title']) ?></h3>
              <p class="event-description"><?= escape($event['description']) ?></p>
              <p class="venue-line"><span aria-hidden="true">📍</span> <?= escape($event['venue']) ?></p>
            </div>
          </div>
          <div class="event-action">
            <div class="event-badges">
              <span class="seat-count"><?= (int) $event['seats_available'] ?> seats left</span>
              <span class="fee-badge"><?= (float) $event['registration_fee'] > 0 ? '₹' . escape(number_format((float) $event['registration_fee'], 2)) : 'Free' ?></span>
            </div>
            <button class="button button-quiet button-small" type="button" data-dialog-open="event-details-<?= (int) $event['id'] ?>" aria-controls="event-details-<?= (int) $event['id'] ?>" aria-haspopup="dialog">Rules &amp; details</button>
            <?php if ($event['registration_id']): ?>
              <button class="button button-registered button-small" type="button" disabled>Registered</button>
            <?php elseif ((int) $event['seats_available'] === 0): ?>
              <button class="button button-disabled button-small" type="button" disabled>Full</button>
            <?php else: ?>
              <form method="post" action="register_event.php">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>">
                <button class="button button-small" type="submit">Register <span aria-hidden="true">→</span></button>
              </form>
            <?php endif; ?>
          </div>
          <dialog class="event-dialog" id="event-details-<?= (int) $event['id'] ?>">
            <div class="event-dialog-inner">
              <p class="eyebrow">EVENT DETAILS</p>
              <h2><?= escape($event['title']) ?></h2>
              <p class="event-dialog-meta"><?= escape(date('D, M j, Y', strtotime($event['date']))) ?> · <?= escape(date('g:i A', strtotime($event['time']))) ?> · <?= escape($event['venue']) ?></p>
              <h3>Rules &amp; regulations</h3>
              <?php if (trim((string) $event['rules_regulation']) !== ''): ?>
                <div class="event-rules"><?= nl2br(escape($event['rules_regulation'])) ?></div>
              <?php else: ?>
                <p class="muted">No additional rules have been provided for this event.</p>
              <?php endif; ?>
              <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                <form method="dialog"><button class="button" type="submit">Close</button></form>
              </div>
            </div>
          </dialog>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<section class="content-section registered-section">
  <div class="section-heading"><div><p class="eyebrow">YOUR PLANS</p><h2>My registrations</h2></div></div>
  <?php if (!$registeredEvents): ?>
    <p class="muted">Events you register for will show up here.</p>
  <?php else: ?>
    <div class="table-wrap"><table>
      <thead><tr><th>Event</th><th>Date &amp; time</th><th>Venue</th><th>Registered</th></tr></thead>
      <tbody><?php foreach ($registeredEvents as $event): ?>
        <tr>
          <td class="table-primary"><?= escape($event['title']) ?></td>
          <td><?= escape(date('M j, Y', strtotime($event['date']))) ?>, <?= escape(date('g:i A', strtotime($event['time']))) ?></td>
          <td><?= escape($event['venue']) ?></td>
          <td><?= escape(date('M j, Y', strtotime($event['registration_date']))) ?></td>
        </tr>
      <?php endforeach; ?></tbody>
    </table></div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
