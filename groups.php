<?php
$page_title = 'Grupet';
$active_page = 'groups';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$error_message = '';
$groups = array();

if (!$db_error && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = isset($_POST['name']) && is_string($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) && is_string($_POST['description']) ? trim($_POST['description']) : '';

    if (!verify_csrf_token()) {
        $error_message = 'Kërkesa skadoi. Rifresko faqen dhe provo përsëri.';
    } elseif ($group_name === '') {
        $error_message = 'Shkruaj emrin e grupit.';
    } else {
        if ($description === '') {
            $description = null;
        }

        $insert_statement = mysqli_prepare($conn, 'INSERT INTO `groups` (name, description) VALUES (?, ?)');
        if ($insert_statement) {
            mysqli_stmt_bind_param($insert_statement, 'ss', $group_name, $description);
            if (mysqli_stmt_execute($insert_statement)) {
                mysqli_stmt_close($insert_statement);
                header('Location: groups.php?status=created');
                exit;
            }
            error_log('Club Hub group insert failed: ' . mysqli_stmt_error($insert_statement));
            mysqli_stmt_close($insert_statement);
        } else {
            error_log('Club Hub group insert prepare failed: ' . mysqli_error($conn));
        }
        $error_message = 'Grupi nuk u krijua. Provo përsëri.';
    }
}

if (!$db_error) {
    $groups_result = mysqli_query(
        $conn,
        'SELECT `groups`.id, `groups`.name, `groups`.description, `groups`.created_at,
                COUNT(participant_groups.participant_id) AS member_count
         FROM `groups`
         LEFT JOIN participant_groups ON participant_groups.group_id = `groups`.id
         GROUP BY `groups`.id, `groups`.name, `groups`.description, `groups`.created_at
         ORDER BY `groups`.id DESC'
    );
    if ($groups_result) {
        while ($group = mysqli_fetch_assoc($groups_result)) {
            $groups[] = $group;
        }
        mysqli_free_result($groups_result);
    } else {
        error_log('Club Hub group list failed: ' . mysqli_error($conn));
        $error_message = 'Lista e grupeve nuk mund të ngarkohet.';
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="container app-section">
  <div class="app-heading">
    <p class="eyebrow">Menaxhimi / Grupet</p>
    <h1 class="app-title">Grupet</h1>
    <p class="app-lead">Shiko grupet e ruajtura dhe numrin e anëtarëve; hape cilindo grup për të shtuar ose larguar pjesëmarrës.</p>
  </div>

  <?php if ($db_error): ?>
    <div class="alert alert-danger" role="alert">Nuk mund të lidhemi me databazën Club Hub. Kontrollo konfigurimin te <code>includes/db.php</code>.</div>
  <?php elseif (($_GET['status'] ?? '') === 'created'): ?>
    <div class="alert alert-success" role="status">Grupi u krijua me sukses.</div>
  <?php endif; ?>

  <?php if ($error_message !== ''): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div class="row g-4 align-items-start">
    <div class="col-12 col-lg-4">
      <section class="card form-card">
        <div class="card-body">
          <h2 class="card-title">Krijo grup</h2>
          <form method="post" action="groups.php">
            <?php csrf_field(); ?>
            <div class="mb-3">
              <label class="form-label" for="name">Emri i grupit</label>
              <input class="form-control" type="text" id="name" name="name" maxlength="150" required value="<?= htmlspecialchars($group_name ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label" for="description">Përshkrimi <span class="optional-label">(opsional)</span></label>
              <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($description ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <button class="btn button" type="submit">Krijo grupin</button>
          </form>
        </div>
      </section>
    </div>

    <div class="col-12 col-lg-8">
      <section class="card list-card">
        <div class="card-body">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 list-heading">
            <h2 class="card-title mb-0">Lista e grupeve</h2>
            <span class="badge count-badge"><?= count($groups) ?> gjithsej</span>
          </div>
          <?php if (count($groups) === 0): ?>
            <p class="empty-state mb-0">Nuk ka ende grupe. Krijo grupin e parë nga formulari.</p>
          <?php else: ?>
            <div class="group-list">
              <?php foreach ($groups as $group): ?>
                <a class="group-list-item" href="group.php?id=<?= (int) $group['id'] ?>">
                  <span>
                    <strong><?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <small><?= htmlspecialchars($group['description'] ?? 'Pa përshkrim', ENT_QUOTES, 'UTF-8') ?></small>
                  </span>
                  <span class="group-list-meta">
                    <span class="group-member-count"><?= (int) $group['member_count'] ?><small><?= (int) $group['member_count'] === 1 ? 'anëtar' : 'anëtarë' ?></small></span>
                    <span class="group-arrow" aria-hidden="true">↗</span>
                  </span>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
