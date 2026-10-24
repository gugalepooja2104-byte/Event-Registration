<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$error = '';
$title = '';
$description = '';
$date = '';
$time = '';
$venue = '';
$seats = '';
$rules = '';
$fee = '0.00';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $date = trim((string) ($_POST['date'] ?? ''));
    $time = trim((string) ($_POST['time'] ?? ''));
    $venue = trim((string) ($_POST['venue'] ?? ''));
    $seats = trim((string) ($_POST['seats_available'] ?? ''));
    $rules = trim((string) ($_POST['rules_regulation'] ?? ''));
    $fee = trim((string) ($_POST['registration_fee'] ?? ''));

    $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    $dateIsValid = $parsedDate && $parsedDate->format('Y-m-d') === $date && $date >= date('Y-m-d');
    $timeIsValid = preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time) === 1;
    $seatsIsValid = filter_var($seats, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]);
    $feeIsValid = preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $fee) === 1;
    $image = $_FILES['event_image'] ?? null;

    if ($title === '' || strlen($title) > 180 || strlen($description) > 5000 || strlen($rules) > 20000 || $venue === '' || strlen($venue) > 180) {
        $error = 'Enter a title and venue. Description must be 5,000 characters or fewer and rules 20,000 characters or fewer.';
    } elseif (!$dateIsValid || !$timeIsValid) {
        $error = 'Choose a valid future date and event time.';
    } elseif ($seatsIsValid === false) {
        $error = 'Seat capacity must be a whole number between 1 and 100,000.';
    } elseif (!$feeIsValid) {
        $error = 'Registration fee must be a non-negative amount with no more than two decimal places.';
    } elseif ($image && $image['error'] !== UPLOAD_ERR_OK && $image['error'] !== UPLOAD_ERR_NO_FILE) {
        $error = 'The image upload failed. Please choose another image and try again.';
    } else {
        $imagePath = null;
        if ($image && $image['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
            ];
            $extension = strtolower(pathinfo((string) $image['name'], PATHINFO_EXTENSION));
            $temporaryPath = (string) $image['tmp_name'];

            if ((int) $image['size'] > 5 * 1024 * 1024) {
                $error = 'Event images must be 5 MB or smaller.';
            } elseif (!isset($allowedTypes[$extension]) || !is_uploaded_file($temporaryPath)) {
                $error = 'Upload a valid JPG, JPEG, or PNG image.';
            } else {
                $imageInfo = getimagesize($temporaryPath);
                $fileInfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $fileInfo->file($temporaryPath);

                if ($imageInfo === false || $imageInfo['mime'] !== $allowedTypes[$extension] || $mimeType !== $allowedTypes[$extension]) {
                    $error = 'Upload a valid JPG, JPEG, or PNG image.';
                } else {
                    $uploadDirectory = __DIR__ . '/../uploads/events';
                    if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
                        error_log('Event image upload directory is missing or not writable.');
                        $error = 'Image uploads are temporarily unavailable. Contact an administrator.';
                    } else {
                        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
                        if (!move_uploaded_file($temporaryPath, $uploadDirectory . '/' . $fileName)) {
                            error_log('Failed to move a validated event image into the upload directory.');
                            $error = 'The event image could not be saved. Please try again.';
                        } else {
                            $imagePath = 'uploads/events/' . $fileName;
                        }
                    }
                }
            }
        }

        if ($error === '') {
            $seatCount = (int) $seats;
            try {
                $statement = $mysqli->prepare(
                    'INSERT INTO events (title, description, `date`, `time`, venue, seats_available, image_path, rules_regulation, registration_fee)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $statement->bind_param('ssssissss', $title, $description, $date, $time, $venue, $seatCount, $imagePath, $rules, $fee);
                $statement->execute();
            } catch (mysqli_sql_exception $exception) {
                if ($imagePath !== null && is_file(__DIR__ . '/../' . $imagePath)) {
                    if (!unlink(__DIR__ . '/../' . $imagePath)) {
                        error_log('Unable to clean up an event image after the database insert failed.');
                    }
                }
                throw $exception;
            }
            set_flash('Event created. Students can now register.');
            header('Location: dashboard.php');
            exit;
        }
    }
}

$pageTitle = 'Add event';
$assetBase = '../';
require __DIR__ . '/../includes/header.php';
?>
<section class="form-page-heading">
  <a class="back-link" href="dashboard.php">← All events</a>
  <p class="eyebrow">EVENT MANAGEMENT</p>
  <h1>Add an event</h1>
  <p class="muted">Publish the details students need to plan ahead.</p>
</section>
<section class="form-panel">
  <?php if ($error !== ''): ?><div class="alert alert-error" role="alert"><?= escape($error) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data" data-validate class="form-stack">
    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
    <label>Event title<input name="title" maxlength="180" required value="<?= escape($title) ?>"></label>
    <label>Description <span class="label-optional">Optional</span><textarea name="description" rows="4" maxlength="5000"><?= escape($description) ?></textarea></label>
    <label>Rules &amp; regulations <span class="label-optional">Optional</span><textarea name="rules_regulation" rows="5" maxlength="20000"><?= escape($rules) ?></textarea></label>
    <div class="form-row">
      <label>Date<input name="date" type="date" min="<?= escape(date('Y-m-d')) ?>" required value="<?= escape($date) ?>"></label>
      <label>Time<input name="time" type="time" required value="<?= escape($time) ?>"></label>
    </div>
    <label>Venue<input name="venue" maxlength="180" required value="<?= escape($venue) ?>"></label>
    <label>Total seats<input name="seats_available" type="number" min="1" max="100000" step="1" required value="<?= escape($seats) ?>"></label>
    <label>Registration fee (INR)<input name="registration_fee" type="number" min="0" max="99999999.99" step="0.01" required value="<?= escape($fee) ?>"></label>
    <label>Event image <span class="label-optional">JPG, JPEG, or PNG; maximum 5 MB</span><input name="event_image" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png"></label>
    <div class="form-actions"><a class="button button-quiet" href="dashboard.php">Cancel</a><button class="button" type="submit">Publish event <span aria-hidden="true">→</span></button></div>
  </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
