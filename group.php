<?php
$page_title = 'Detajet e grupit';
$active_page = 'group';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$group_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
$group = null;
$members = array();
$participants = array();
$registered_participant_count = 0;
$error_message = '';
$status_message = '';
$selected_participant_id = isset($_POST['participant_id']) && is_scalar($_POST['participant_id'])
    ? (string) $_POST['participant_id']
    : '';

if (!$db_error && $group_id !== false && $group_id !== null) {
    $group_statement = mysqli_prepare($conn, 'SELECT id, name, description FROM `groups` WHERE id = ?');
    if ($group_statement) {
        mysqli_stmt_bind_param($group_statement, 'i', $group_id);
        if (mysqli_stmt_execute($group_statement)) {
            mysqli_stmt_bind_result($group_statement, $found_group_id, $found_group_name, $found_group_description);
            if (mysqli_stmt_fetch($group_statement)) {
                $group = array(
                    'id' => $found_group_id,
                    'name' => $found_group_name,
                    'description' => $found_group_description
                );
            }
        } else {
            error_log('Club Hub group lookup failed: ' . mysqli_stmt_error($group_statement));
            $error_message = 'Grupi nuk mund të ngarkohet.';
        }
        mysqli_stmt_close($group_statement);
    } else {
        error_log('Club Hub group lookup prepare failed: ' . mysqli_error($conn));
        $error_message = 'Grupi nuk mund të ngarkohet.';
    }
}

if (!$db_error && $group !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $posted_group_id = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
    $participant_id = filter_input(INPUT_POST, 'participant_id', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));

    if (!verify_csrf_token()) {
        $error_message = 'Kërkesa skadoi. Rifresko faqen dhe provo përsëri.';
    } elseif ($posted_group_id !== $group_id || $participant_id === false || $participant_id === null) {
        $error_message = 'Të dhënat e kërkesës nuk janë të vlefshme.';
    } elseif ($action === 'add') {
        $participant_statement = mysqli_prepare($conn, 'SELECT id FROM participants WHERE id = ?');
        $participant_exists = false;

        if ($participant_statement) {
            mysqli_stmt_bind_param($participant_statement, 'i', $participant_id);
            if (mysqli_stmt_execute($participant_statement)) {
                mysqli_stmt_store_result($participant_statement);
                $participant_exists = mysqli_stmt_num_rows($participant_statement) === 1;
            } else {
                error_log('Club Hub participant lookup failed: ' . mysqli_stmt_error($participant_statement));
                $error_message = 'Pjesëmarrësi nuk mund të verifikohet.';
            }
            mysqli_stmt_close($participant_statement);
        } else {
            error_log('Club Hub participant lookup prepare failed: ' . mysqli_error($conn));
            $error_message = 'Pjesëmarrësi nuk mund të verifikohet.';
        }

        if ($error_message === '' && !$participant_exists) {
            $error_message = 'Pjesëmarrësi i zgjedhur nuk ekziston.';
        } elseif ($error_message === '') {
            $add_statement = mysqli_prepare($conn, 'INSERT INTO participant_groups (participant_id, group_id) VALUES (?, ?)');
            if ($add_statement) {
                mysqli_stmt_bind_param($add_statement, 'ii', $participant_id, $group_id);
                if (mysqli_stmt_execute($add_statement)) {
                    mysqli_stmt_close($add_statement);
                    header('Location: group.php?id=' . $group_id . '&status=added');
                    exit;
                }

                if (mysqli_stmt_errno($add_statement) === 1062) {
                    mysqli_stmt_close($add_statement);
                    header('Location: group.php?id=' . $group_id . '&status=already_added');
                    exit;
                }

                error_log('Club Hub membership insert failed: ' . mysqli_stmt_error($add_statement));
                mysqli_stmt_close($add_statement);
                $error_message = 'Pjesëmarrësi nuk mund të shtohet në grup.';
            } else {
                error_log('Club Hub membership insert prepare failed: ' . mysqli_error($conn));
                $error_message = 'Pjesëmarrësi nuk mund të shtohet në grup.';
            }
        }
    } elseif ($action === 'remove') {
        $remove_statement = mysqli_prepare($conn, 'DELETE FROM participant_groups WHERE participant_id = ? AND group_id = ?');
        if ($remove_statement) {
            mysqli_stmt_bind_param($remove_statement, 'ii', $participant_id, $group_id);
            if (mysqli_stmt_execute($remove_statement)) {
                $membership_removed = mysqli_stmt_affected_rows($remove_statement) === 1;
                mysqli_stmt_close($remove_statement);
                $status = $membership_removed ? 'removed' : 'not_member';
                header('Location: group.php?id=' . $group_id . '&status=' . $status);
                exit;
            }
            error_log('Club Hub membership removal failed: ' . mysqli_stmt_error($remove_statement));
            mysqli_stmt_close($remove_statement);
            $error_message = 'Anëtarësimi nuk mund të largohet.';
        } else {
            error_log('Club Hub membership removal prepare failed: ' . mysqli_error($conn));
            $error_message = 'Anëtarësimi nuk mund të largohet.';
        }
    } else {
        $error_message = 'Veprimi i kërkuar nuk njihet.';
    }
}

if (!$db_error && $group !== null && $error_message === '') {
    $status = $_GET['status'] ?? '';
    if ($status === 'added') {
        $status_message = 'Pjesëmarrësi u shtua në grup.';
    } elseif ($status === 'removed') {
        $status_message = 'Pjesëmarrësi u largua nga ky grup. Regjistrimi i tij mbetet i ruajtur.';
    } elseif ($status === 'already_added') {
        $status_message = 'Ky pjesëmarrës është tashmë anëtar i grupit.';
    } elseif ($status === 'not_member') {
        $status_message = 'Pjesëmarrësi nuk ishte anëtar i këtij grupi.';
    }
}

if (!$db_error && $group !== null && $error_message === '') {
    $members_statement = mysqli_prepare(
        $conn,
        'SELECT participants.id, participants.full_name, participants.email
         FROM participant_groups
         INNER JOIN participants ON participants.id = participant_groups.participant_id
         WHERE participant_groups.group_id = ?
         ORDER BY participants.full_name'
    );
    if ($members_statement) {
        mysqli_stmt_bind_param($members_statement, 'i', $group_id);
        if (mysqli_stmt_execute($members_statement)) {
            mysqli_stmt_bind_result($members_statement, $member_id, $member_name, $member_email);
            while (mysqli_stmt_fetch($members_statement)) {
                $members[] = array(
                    'id' => $member_id,
                    'full_name' => $member_name,
                    'email' => $member_email
                );
            }
        } else {
            error_log('Club Hub group members query failed: ' . mysqli_stmt_error($members_statement));
            $error_message = 'Anëtarët e grupit nuk mund të ngarkohen.';
        }
        mysqli_stmt_close($members_statement);
    } else {
        error_log('Club Hub group members query prepare failed: ' . mysqli_error($conn));
        $error_message = 'Anëtarët e grupit nuk mund të ngarkohen.';
    }

    $participants_count_result = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM participants');
    if ($participants_count_result) {
        $participant_count_row = mysqli_fetch_assoc($participants_count_result);
        $registered_participant_count = (int) $participant_count_row['total'];
        mysqli_free_result($participants_count_result);
    } else {
        error_log('Club Hub participant count query failed: ' . mysqli_error($conn));
        $error_message = 'Numri i pjesëmarrësve nuk mund të lexohet.';
    }

    $participants_statement = mysqli_prepare(
        $conn,
        'SELECT participants.id, participants.full_name
         FROM participants
         WHERE NOT EXISTS (
           SELECT 1 FROM participant_groups
           WHERE participant_groups.participant_id = participants.id
             AND participant_groups.group_id = ?
         )
         ORDER BY participants.full_name'
    );
    if ($participants_statement) {
        mysqli_stmt_bind_param($participants_statement, 'i', $group_id);
        if (mysqli_stmt_execute($participants_statement)) {
            mysqli_stmt_bind_result($participants_statement, $available_participant_id, $available_participant_name);
            while (mysqli_stmt_fetch($participants_statement)) {
                $participants[] = array(
                    'id' => $available_participant_id,
                    'full_name' => $available_participant_name
                );
            }
        } else {
            error_log('Club Hub participant choices query failed: ' . mysqli_stmt_error($participants_statement));
            $error_message = 'Lista e pjesëmarrësve nuk mund të ngarkohet.';
        }
        mysqli_stmt_close($participants_statement);
    } else {
        error_log('Club Hub participant choices prepare failed: ' . mysqli_error($conn));
        $error_message = 'Lista e pjesëmarrësve nuk mund të ngarkohet.';
    }
}

if (!$db_error && ($group_id === false || $group_id === null || ($group === null && $error_message === ''))) {
    http_response_code(404);
}

require __DIR__ . '/includes/header.php';
?>
<section class="container app-section">
  <?php if ($db_error): ?>
    <div class="alert alert-danger" role="alert">Nuk mund të lidhemi me databazën Club Hub. Kontrollo konfigurimin te <code>includes/db.php</code>.</div>
  <?php elseif ($group === null): ?>
    <div class="app-heading">
      <p class="eyebrow">Menaxhimi / Grupet</p>
      <h1 class="app-title">Grupi nuk u gjet</h1>
      <p class="app-lead">Kontrollo lidhjen ose kthehu te lista e grupeve.</p>
    </div>
    <?php if ($error_message !== ''): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <a class="btn button" href="groups.php">Kthehu te grupet</a>
  <?php else: ?>
    <div class="app-heading">
      <p class="eyebrow"><a href="groups.php">Grupet</a> / Detajet</p>
      <h1 class="app-title"><?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="app-lead"><?= htmlspecialchars($group['description'] ?? 'Pa përshkrim.', ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <?php if ($status_message !== ''): ?>
      <div class="alert alert-success" role="status"><?= htmlspecialchars($status_message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($error_message !== ''): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="row g-4 align-items-start">
      <div class="col-12 col-lg-4">
        <section class="card form-card">
          <div class="card-body">
            <h2 class="card-title">Shto pjesëmarrës</h2>
            <?php if (count($participants) === 0): ?>
              <p class="empty-state mb-0">
                <?php if ($registered_participant_count === 0): ?>
                  Nuk ka ende pjesëmarrës të regjistruar. <a href="participants.php">Regjistro pjesëmarrësin e parë</a>, pastaj ai do të shfaqet këtu.
                <?php else: ?>
                  Të gjithë pjesëmarrësit e regjistruar janë tashmë në këtë grup. Anëtarët e rinj do të shfaqen këtu pasi t’i regjistrosh.
                  <a href="participants.php">Regjistro pjesëmarrës të ri</a>.
                <?php endif; ?>
              </p>
            <?php else: ?>
              <form method="post" action="group.php?id=<?= (int) $group_id ?>">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="group_id" value="<?= (int) $group_id ?>">
                <div class="mb-3">
                  <label class="form-label" for="participant_id">Pjesëmarrësi</label>
                  <select class="form-select" id="participant_id" name="participant_id" required>
                    <option value="">Zgjidh pjesëmarrësin</option>
                    <?php foreach ($participants as $participant): ?>
                      <option value="<?= (int) $participant['id'] ?>"<?= $selected_participant_id === (string) $participant['id'] ? ' selected' : '' ?>>
                        <?= htmlspecialchars($participant['full_name'], ENT_QUOTES, 'UTF-8') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <button class="btn button" type="submit"><span aria-hidden="true">+</span> Shto në grup</button>
              </form>
            <?php endif; ?>
          </div>
        </section>
      </div>

      <div class="col-12 col-lg-8">
        <section class="card list-card">
          <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 list-heading">
              <h2 class="card-title mb-0">Anëtarët e grupit</h2>
              <span class="badge count-badge"><?= count($members) ?> gjithsej</span>
            </div>
            <?php if (count($members) === 0): ?>
              <p class="empty-state mb-0">Ky grup nuk ka ende anëtarë.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table align-middle participant-table mb-0">
                  <thead>
                    <tr>
                      <th scope="col">Emri</th>
                      <th scope="col">Email</th>
                      <th scope="col"><span class="visually-hidden">Veprimi</span></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($members as $member): ?>
                      <tr>
                        <td><?= htmlspecialchars($member['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($member['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end">
                          <form method="post" action="group.php?id=<?= (int) $group_id ?>" onsubmit="return confirm('A dëshiron ta largosh këtë pjesëmarrës nga ky grup? Profili dhe grupet e tjera mbeten të paprekura.');">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="group_id" value="<?= (int) $group_id ?>">
                            <input type="hidden" name="participant_id" value="<?= (int) $member['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit"><span aria-hidden="true">−</span> Largo nga grupi</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </section>
      </div>
    </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
