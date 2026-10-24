<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!empty($_SESSION['user'])) {
    header('Location: ' . ($_SESSION['user']['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php'));
    exit;
}

$error = '';
$name = '';
$email = '';
$rollNo = '';
$role = 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $rollNo = trim((string) ($_POST['roll_no'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $role = (string) ($_POST['role'] ?? '');
    $adminCode = (string) ($_POST['admin_code'] ?? '');

    if ($name === '' || strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || $rollNo === '' || strlen($rollNo) > 40 || !in_array($role, ['student', 'admin'], true)) {
        $error = 'Enter a valid name, email address, and roll number.';
    } elseif ($role === 'admin' && (!is_string(getenv('ADMIN_REGISTRATION_CODE')) || getenv('ADMIN_REGISTRATION_CODE') === '' || !hash_equals((string) getenv('ADMIN_REGISTRATION_CODE'), $adminCode))) {
        $error = 'Admin registration requires a valid invitation code.';
    } elseif (strlen($password) < 8) {
        $error = 'Your password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'The passwords do not match.';
    } else {
        try {
            $statement = $mysqli->prepare('INSERT INTO users (name, email, roll_no, password, role) VALUES (?, ?, ?, ?, ?)');
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $statement->bind_param('sssss', $name, $email, $rollNo, $passwordHash, $role);
            $statement->execute();
            session_regenerate_id(true);
            unset($_SESSION['csrf_token']);
            $_SESSION['user'] = [
                'id' => $mysqli->insert_id,
                'name' => $name,
                'email' => $email,
                'role' => $role,
            ];
            header('Location: ' . ($role === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php'));
            exit;
        } catch (mysqli_sql_exception $exception) {
            if ((int) $exception->getCode() === 1062) {
                $error = 'That email address or roll number is already registered.';
            } else {
                throw $exception;
            }
        }
    }
}

$pageTitle = 'Create account';
$assetBase = '';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
  <div class="auth-aside">
    <p class="eyebrow">YOUR CAMPUS, IN MOTION</p>
    <h1>Make room for what happens next.</h1>
    <p>Discover talks, competitions, workshops, and the people you will meet there.</p>
    <div class="aside-rule"></div>
    <span class="aside-note">One account. Every campus event.</span>
  </div>
  <div class="auth-panel">
    <p class="eyebrow">CAMPUS ACCESS</p>
    <h2>Create your account</h2>
    <p class="muted">Use your college details to get started.</p>
    <?php if ($error !== ''): ?><div class="alert alert-error" role="alert"><?= escape($error) ?></div><?php endif; ?>
    <form method="post" data-validate class="form-stack">
      <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
      <label>Account type
        <select name="role" required>
          <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
          <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin (invitation code required)</option>
        </select>
      </label>
      <label>Admin invitation code <span class="label-optional">Required for admin accounts only</span><input name="admin_code" type="password" autocomplete="off"></label>
      <label>Full name<input name="name" autocomplete="name" maxlength="120" required value="<?= escape($name) ?>"></label>
      <label>College email<input name="email" type="email" autocomplete="email" maxlength="190" required value="<?= escape($email) ?>"></label>
      <label>Roll number<input name="roll_no" maxlength="40" required value="<?= escape($rollNo) ?>"></label>
      <label>Password<input name="password" type="password" autocomplete="new-password" minlength="8" required></label>
      <label>Confirm password<input name="confirm_password" type="password" autocomplete="new-password" minlength="8" required data-match="password"></label>
      <button class="button button-wide" type="submit">Create account <span aria-hidden="true">→</span></button>
    </form>
    <p class="auth-switch">Already registered? <a href="login.php">Sign in</a></p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
