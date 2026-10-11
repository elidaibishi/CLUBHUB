<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/db.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error_message = '';
$email = '';

if (!$db_error && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';

    if (!verify_csrf_token()) {
        $error_message = 'Kërkesa skadoi. Rifresko faqen dhe provo përsëri.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error_message = 'Shkruaj email-in dhe fjalëkalimin.';
    } else {
        $statement = mysqli_prepare($conn, 'SELECT id, password_hash FROM administrators WHERE email = ?');
        if ($statement) {
            mysqli_stmt_bind_param($statement, 's', $email);
            if (mysqli_stmt_execute($statement)) {
                mysqli_stmt_bind_result($statement, $admin_id, $password_hash);
                if (mysqli_stmt_fetch($statement) && password_verify($password, $password_hash)) {
                    mysqli_stmt_close($statement);
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $admin_id;
                    $_SESSION['admin_email'] = $email;
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    header('Location: index.php');
                    exit;
                }
                $error_message = 'Email-i ose fjalëkalimi nuk është i saktë.';
            } else {
                error_log('Club Hub administrator login failed: ' . mysqli_stmt_error($statement));
                $error_message = 'Hyrja nuk mund të kryhet tani.';
            }
            mysqli_stmt_close($statement);
        } else {
            error_log('Club Hub administrator login prepare failed: ' . mysqli_error($conn));
            $error_message = 'Hyrja nuk mund të kryhet tani.';
        }
    }
}

$page_title = 'Hyrja e administratorit';
require __DIR__ . '/includes/header.php';
?>
<section class="container app-section auth-section">
  <div class="app-heading">
    <p class="eyebrow">Club Hub / Administrimi</p>
    <h1 class="app-title">Hyr në panelin e Club Hub</h1>
    <p class="app-lead">Vetëm administratori mund të menaxhojë pjesëmarrësit, grupet dhe regjistrimet.</p>
  </div>
  <?php if ($db_error): ?>
    <div class="alert alert-danger" role="alert">Nuk mund të lidhemi me databazën Club Hub. Kontrollo konfigurimin te <code>includes/db.php</code>.</div>
  <?php elseif (($_GET['status'] ?? '') === 'admin_created'): ?>
    <div class="alert alert-success" role="status">Administratori u krijua. Hyr me email-in dhe fjalëkalimin e sapokrijuar.</div>
  <?php elseif ($error_message !== ''): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <section class="card form-card auth-card">
    <div class="card-body">
      <h2 class="card-title">Identifikohu</h2>
      <form method="post" action="login.php">
        <?php csrf_field(); ?>
        <div class="mb-3">
          <label class="form-label" for="email">Email i administratorit</label>
          <input class="form-control" type="email" id="email" name="email" maxlength="255" autocomplete="username" required value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label" for="password">Fjalëkalimi</label>
          <input class="form-control" type="password" id="password" name="password" autocomplete="current-password" required>
        </div>
        <button class="btn button" type="submit">Hyr në panel</button>
      </form>
      <p class="auth-help mb-0">Llogaria e parë krijohet nga <a href="setup_admin.php">konfigurimi fillestar</a> në kompjuterin ku është instaluar aplikacioni.</p>
    </div>
  </section>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
