<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/db.php';

$error_message = '';
$setup_allowed = in_array($_SERVER['REMOTE_ADDR'] ?? '', array('127.0.0.1', '::1'), true);
$admin_exists = false;

if (!$db_error) {
    $admin_count_result = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM administrators');
    if ($admin_count_result) {
        $admin_count = mysqli_fetch_assoc($admin_count_result);
        $admin_exists = (int) $admin_count['total'] > 0;
        mysqli_free_result($admin_count_result);
    } else {
        error_log('Club Hub administrator setup check failed: ' . mysqli_error($conn));
        $error_message = 'Gjendja e konfigurimit nuk mund të kontrollohet.';
    }
}

if (!$db_error && !$admin_exists && $setup_allowed && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    $password_confirmation = isset($_POST['password_confirmation']) && is_string($_POST['password_confirmation'])
        ? $_POST['password_confirmation']
        : '';

    if (!verify_csrf_token()) {
        $error_message = 'Kërkesa skadoi. Rifresko faqen dhe provo përsëri.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Shkruaj një email të vlefshëm.';
    } elseif (strlen($password) < 12) {
        $error_message = 'Fjalëkalimi duhet të ketë të paktën 12 karaktere.';
    } elseif ($password !== $password_confirmation) {
        $error_message = 'Fjalëkalimet nuk përputhen.';
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $statement = mysqli_prepare($conn, 'INSERT INTO administrators (email, password_hash) VALUES (?, ?)');
        if ($statement) {
            mysqli_stmt_bind_param($statement, 'ss', $email, $password_hash);
            if (mysqli_stmt_execute($statement)) {
                mysqli_stmt_close($statement);
                header('Location: login.php?status=admin_created');
                exit;
            }
            error_log('Club Hub administrator setup insert failed: ' . mysqli_stmt_error($statement));
            mysqli_stmt_close($statement);
            $error_message = 'Llogaria nuk u krijua. Rifresko faqen dhe provo përsëri.';
        } else {
            error_log('Club Hub administrator setup prepare failed: ' . mysqli_error($conn));
            $error_message = 'Llogaria nuk u krijua. Provo përsëri.';
        }
    }
}

$page_title = 'Konfigurimi fillestar';
require __DIR__ . '/includes/header.php';
?>
<section class="container app-section auth-section">
  <div class="app-heading">
    <p class="eyebrow">Club Hub / Konfigurimi fillestar</p>
    <h1 class="app-title">Krijo administratorin e parë</h1>
    <p class="app-lead">Ky hap mund të kryhet vetëm një herë dhe vetëm nga kompjuteri ku po ekzekutohet aplikacioni.</p>
  </div>
  <?php if ($db_error): ?>
    <div class="alert alert-danger" role="alert">Nuk mund të lidhemi me databazën Club Hub. Kontrollo konfigurimin te <code>includes/db.php</code>.</div>
  <?php elseif (!$setup_allowed): ?>
    <div class="alert alert-warning" role="alert">Konfigurimi fillestar lejohet vetëm nga kompjuteri i serverit (localhost).</div>
  <?php elseif ($admin_exists): ?>
    <div class="alert alert-info" role="status">Administratori tashmë është krijuar. Përdor faqen e hyrjes.</div>
    <a class="btn button" href="login.php">Shko te hyrja</a>
  <?php else: ?>
    <?php if ($error_message !== ''): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <section class="card form-card auth-card">
      <div class="card-body">
        <h2 class="card-title">Të dhënat e llogarisë</h2>
        <form method="post" action="setup_admin.php">
          <?php csrf_field(); ?>
          <div class="mb-3">
            <label class="form-label" for="email">Email i administratorit</label>
            <input class="form-control" type="email" id="email" name="email" maxlength="255" autocomplete="username" required value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label" for="password">Fjalëkalimi (minimumi 12 karaktere)</label>
            <input class="form-control" type="password" id="password" name="password" minlength="12" autocomplete="new-password" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="password_confirmation">Përsërit fjalëkalimin</label>
            <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" minlength="12" autocomplete="new-password" required>
          </div>
          <button class="btn button" type="submit">Krijo administratorin</button>
        </form>
      </div>
    </section>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
