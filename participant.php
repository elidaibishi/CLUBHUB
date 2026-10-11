<?php
$page_title = 'Detajet e pjesëmarrësit';
$active_page = 'participants';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$participant_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
$participant = null;
$groups = array();
$available_groups = array();
$error_message = '';
$status_message = '';

if (!$db_error && $participant_id !== false && $participant_id !== null) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';
        if (!verify_csrf_token()) {
            $error_message = 'Kërkesa skadoi. Rifresko faqen dhe provo përsëri.';
        } elseif ($action === 'add_groups') {
            $posted_groups = $_POST['group_ids'] ?? array();
            if (!is_array($posted_groups) || count($posted_groups) === 0) {
                $error_message = 'Zgjidh të paktën një grup për t’u shtuar.';
            } else {
                $group_ids = array();
                foreach ($posted_groups as $posted_group_id) {
                    $valid_group_id = is_scalar($posted_group_id)
                        ? filter_var($posted_group_id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))
                        : false;
                    if ($valid_group_id === false) {
                        $error_message = 'Një nga grupet e zgjedhura nuk është i vlefshëm.';
                        break;
                    }
                    $group_ids[] = $valid_group_id;
                }
                if ($error_message === '' && mysqli_begin_transaction($conn)) {
                    $add_statement = mysqli_prepare(
                        $conn,
                        'INSERT INTO participant_groups (participant_id, group_id) VALUES (?, ?)
                         ON DUPLICATE KEY UPDATE participant_id = VALUES(participant_id)'
                    );
                    $add_ok = $add_statement !== false;
                    if ($add_ok) {
                        foreach (array_unique($group_ids) as $group_id) {
                            mysqli_stmt_bind_param($add_statement, 'ii', $participant_id, $group_id);
                            if (!mysqli_stmt_execute($add_statement)) {
                                error_log('Club Hub participant group add failed: ' . mysqli_stmt_error($add_statement));
                                $add_ok = false;
                                break;
                            }
                        }
                        mysqli_stmt_close($add_statement);
                    } else {
                        error_log('Club Hub participant group add prepare failed: ' . mysqli_error($conn));
                    }
                    if ($add_ok && mysqli_commit($conn)) {
                        header('Location: participant.php?id=' . $participant_id . '&status=groups_added');
                        exit;
                    }
                    mysqli_rollback($conn);
                    $error_message = 'Pjesëmarrësi nuk mund të shtohet në grupet e zgjedhura.';
                } elseif ($error_message === '') {
                    error_log('Club Hub participant group transaction failed: ' . mysqli_error($conn));
                    $error_message = 'Ndryshimet nuk mund të ruhen. Provo përsëri.';
                }
            }
        } elseif ($action === 'remove_group') {
            $group_id = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
            if ($group_id === false || $group_id === null) {
                $error_message = 'Grupi i zgjedhur nuk është i vlefshëm.';
            } else {
                $remove_statement = mysqli_prepare(
                    $conn,
                    'DELETE FROM participant_groups WHERE participant_id = ? AND group_id = ?'
                );
                if ($remove_statement) {
                    mysqli_stmt_bind_param($remove_statement, 'ii', $participant_id, $group_id);
                    if (mysqli_stmt_execute($remove_statement)) {
                        $removed = mysqli_stmt_affected_rows($remove_statement) === 1;
                        mysqli_stmt_close($remove_statement);
                        header('Location: participant.php?id=' . $participant_id . '&status=' . ($removed ? 'group_removed' : 'not_in_group'));
                        exit;
                    }
                    error_log('Club Hub participant group removal failed: ' . mysqli_stmt_error($remove_statement));
                    mysqli_stmt_close($remove_statement);
                } else {
                    error_log('Club Hub participant group removal prepare failed: ' . mysqli_error($conn));
                }
                $error_message = 'Lidhja me grupin nuk mund të largohet.';
            }
        } else {
            $error_message = 'Veprimi i kërkuar nuk njihet.';
        }
    }

    $participant_statement = mysqli_prepare($conn, 'SELECT id, full_name, email, created_at FROM participants WHERE id = ?');
    if ($participant_statement) {
        mysqli_stmt_bind_param($participant_statement, 'i', $participant_id);
        if (mysqli_stmt_execute($participant_statement)) {
            mysqli_stmt_bind_result($participant_statement, $found_id, $found_name, $found_email, $found_created_at);
            if (mysqli_stmt_fetch($participant_statement)) {
                $participant = array(
                    'id' => $found_id,
                    'full_name' => $found_name,
                    'email' => $found_email,
                    'created_at' => $found_created_at
                );
            }
        } else {
            error_log('Club Hub participant details lookup failed: ' . mysqli_stmt_error($participant_statement));
            $error_message = 'Të dhënat e pjesëmarrësit nuk mund të ngarkohen.';
        }
        mysqli_stmt_close($participant_statement);
    } else {
        error_log('Club Hub participant details prepare failed: ' . mysqli_error($conn));
        $error_message = 'Të dhënat e pjesëmarrësit nuk mund të ngarkohen.';
    }

    if ($participant !== null) {
        $groups_statement = mysqli_prepare(
            $conn,
            'SELECT `groups`.id, `groups`.name, `groups`.description
             FROM participant_groups
             INNER JOIN `groups` ON `groups`.id = participant_groups.group_id
             WHERE participant_groups.participant_id = ?
             ORDER BY `groups`.name'
        );
        if ($groups_statement) {
            mysqli_stmt_bind_param($groups_statement, 'i', $participant_id);
            if (mysqli_stmt_execute($groups_statement)) {
                mysqli_stmt_bind_result($groups_statement, $group_id, $group_name, $group_description);
                while (mysqli_stmt_fetch($groups_statement)) {
                    $groups[] = array('id' => $group_id, 'name' => $group_name, 'description' => $group_description);
                }
            } else {
                error_log('Club Hub participant groups query failed: ' . mysqli_stmt_error($groups_statement));
                $error_message = 'Grupet e pjesëmarrësit nuk mund të ngarkohen.';
            }
            mysqli_stmt_close($groups_statement);
        } else {
            error_log('Club Hub participant groups prepare failed: ' . mysqli_error($conn));
            $error_message = 'Grupet e pjesëmarrësit nuk mund të ngarkohen.';
        }

        $available_statement = mysqli_prepare(
            $conn,
            'SELECT `groups`.id, `groups`.name
             FROM `groups`
             WHERE NOT EXISTS (
               SELECT 1 FROM participant_groups
               WHERE participant_groups.group_id = `groups`.id
                 AND participant_groups.participant_id = ?
             )
             ORDER BY `groups`.name'
        );
        if ($available_statement) {
            mysqli_stmt_bind_param($available_statement, 'i', $participant_id);
            if (mysqli_stmt_execute($available_statement)) {
                mysqli_stmt_bind_result($available_statement, $available_id, $available_name);
                while (mysqli_stmt_fetch($available_statement)) {
                    $available_groups[] = array('id' => $available_id, 'name' => $available_name);
                }
            } else {
                error_log('Club Hub participant available groups query failed: ' . mysqli_stmt_error($available_statement));
                $error_message = 'Lista e grupeve të disponueshme nuk mund të ngarkohet.';
            }
            mysqli_stmt_close($available_statement);
        } else {
            error_log('Club Hub participant available groups prepare failed: ' . mysqli_error($conn));
            $error_message = 'Lista e grupeve të disponueshme nuk mund të ngarkohet.';
        }
    }
}

if (!$db_error && ($participant_id === false || $participant_id === null || ($participant === null && $error_message === ''))) {
    http_response_code(404);
}

if (($_GET['status'] ?? '') === 'updated') {
    $status_message = 'Të dhënat e pjesëmarrësit u përditësuan.';
} elseif (($_GET['status'] ?? '') === 'groups_added') {
    $status_message = 'Pjesëmarrësi u lidh me grupet e zgjedhura.';
} elseif (($_GET['status'] ?? '') === 'group_removed') {
    $status_message = 'Pjesëmarrësi u largua nga grupi. Të dhënat e tij mbeten të ruajtura.';
} elseif (($_GET['status'] ?? '') === 'not_in_group') {
    $status_message = 'Pjesëmarrësi nuk ishte i regjistruar në atë grup.';
}

require __DIR__ . '/includes/header.php';
?>
<section class="container app-section">
  <?php if ($db_error): ?>
    <div class="alert alert-danger" role="alert">Nuk mund të lidhemi me databazën Club Hub. Kontrollo konfigurimin te <code>includes/db.php</code>.</div>
  <?php elseif ($participant === null): ?>
    <div class="app-heading">
      <p class="eyebrow"><a href="participants.php">Pjesëmarrësit</a> / Detajet</p>
      <h1 class="app-title">Pjesëmarrësi nuk u gjet</h1>
      <p class="app-lead">Kontrollo adresën ose kthehu te lista e pjesëmarrësve.</p>
    </div>
    <a class="btn button" href="participants.php">Kthehu te pjesëmarrësit</a>
  <?php else: ?>
    <div class="app-heading">
      <p class="eyebrow"><a href="participants.php">Pjesëmarrësit</a> / Detajet</p>
      <h1 class="app-title"><?= htmlspecialchars($participant['full_name'], ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="app-lead">Të dhënat e regjistrimit dhe grupet ku merr pjesë.</p>
      <a class="btn btn-outline-secondary btn-sm mt-3" href="participants.php?edit=<?= (int) $participant['id'] ?>#participant-form">Edito pjesëmarrësin</a>
    </div>
    <?php if ($status_message !== ''): ?>
      <div class="alert alert-success" role="status"><?= htmlspecialchars($status_message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($error_message !== ''): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="row g-4 align-items-start">
      <div class="col-12 col-lg-5">
        <section class="card list-card participant-detail-card">
          <div class="card-body">
            <h2 class="card-title">Të dhënat e regjistrimit</h2>
            <dl class="participant-details">
              <dt>Emri i plotë</dt><dd><?= htmlspecialchars($participant['full_name'], ENT_QUOTES, 'UTF-8') ?></dd>
              <dt>Email</dt><dd><?= htmlspecialchars($participant['email'] ?? 'Nuk është dhënë', ENT_QUOTES, 'UTF-8') ?></dd>
              <dt>Regjistruar më</dt><dd><?= htmlspecialchars(date('d.m.Y H:i', strtotime($participant['created_at'])), ENT_QUOTES, 'UTF-8') ?></dd>
            </dl>
          </div>
        </section>
        <section class="card form-card mt-4">
          <div class="card-body">
            <h2 class="card-title">Shto në disa grupe</h2>
            <?php if (count($available_groups) === 0): ?>
              <p class="empty-state mb-0"><?= count($groups) > 0 ? 'Pjesëmarrësi është tashmë në të gjitha grupet.' : 'Krijo një grup përpara se të caktosh pjesëmarrës.' ?></p>
            <?php else: ?>
              <form method="post" action="participant.php?id=<?= (int) $participant['id'] ?>">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="add_groups">
                <fieldset class="mb-3">
                  <legend class="form-label">Zgjidh një ose më shumë grupe</legend>
                  <div class="group-checkbox-list">
                  <?php foreach ($available_groups as $available_group): ?>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="group-<?= (int) $available_group['id'] ?>" name="group_ids[]" value="<?= (int) $available_group['id'] ?>">
                      <label class="form-check-label" for="group-<?= (int) $available_group['id'] ?>"><?= htmlspecialchars($available_group['name'], ENT_QUOTES, 'UTF-8') ?></label>
                    </div>
                  <?php endforeach; ?>
                  </div>
                </fieldset>
                <button class="btn button" type="submit"><span aria-hidden="true">+</span> Shto në grupet e zgjedhura</button>
              </form>
            <?php endif; ?>
          </div>
        </section>
      </div>

      <div class="col-12 col-lg-7">
        <section class="card list-card">
          <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 list-heading">
              <h2 class="card-title mb-0">Grupet e pjesëmarrësit</h2>
              <span class="badge count-badge"><?= count($groups) ?> gjithsej</span>
            </div>
            <?php if (count($groups) === 0): ?>
              <p class="empty-state mb-0">Pjesëmarrësi ende nuk është lidhur me ndonjë grup.</p>
            <?php else: ?>
              <div class="group-list">
                <?php foreach ($groups as $group): ?>
                  <div class="participant-group-item">
                    <a class="group-list-item" href="group.php?id=<?= (int) $group['id'] ?>">
                      <span><strong><?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($group['description'] ?? 'Pa përshkrim', ENT_QUOTES, 'UTF-8') ?></small></span>
                      <span class="group-arrow" aria-hidden="true">↗</span>
                    </a>
                    <form method="post" action="participant.php?id=<?= (int) $participant['id'] ?>" onsubmit="return confirm('A dëshiron ta largosh pjesëmarrësin nga ky grup? Të dhënat e pjesëmarrësit nuk do të fshihen.');">
                      <?php csrf_field(); ?>
                      <input type="hidden" name="action" value="remove_group">
                      <input type="hidden" name="group_id" value="<?= (int) $group['id'] ?>">
                      <button class="btn btn-sm btn-outline-danger" type="submit"><span aria-hidden="true">−</span> Largo nga grupi</button>
                    </form>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </section>
      </div>
    </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
