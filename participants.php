<?php
$page_title = 'Pjesëmarrësit';
$active_page = 'participants';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$error_message = '';
$participants = array();
$groups = array();
$editing_participant = null;
$edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));

if (!$db_error && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';
    $full_name = isset($_POST['full_name']) && is_string($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';

    if (!verify_csrf_token()) {
        $error_message = 'Kërkesa skadoi. Rifresko faqen dhe provo përsëri.';
    } elseif ($action === 'save') {
        $posted_id = filter_input(INPUT_POST, 'participant_id', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
        if ($full_name === '') {
            $error_message = 'Shkruaj emrin e plotë të pjesëmarrësit.';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Shkruaj një adresë email-i të vlefshme ose lëre fushën bosh.';
        } elseif ($email !== '' && strlen($email) > 255) {
            $error_message = 'Email-i nuk mund të jetë më i gjatë se 255 karaktere.';
        } elseif (strlen($full_name) > 150) {
            $error_message = 'Emri nuk mund të jetë më i gjatë se 150 karaktere.';
        } elseif (!isset($_POST['participant_id']) || $_POST['participant_id'] === '') {
            if ($email === '') {
                $email = null;
            }
            $statement = mysqli_prepare($conn, 'INSERT INTO participants (full_name, email) VALUES (?, ?)');
            if ($statement) {
                mysqli_stmt_bind_param($statement, 'ss', $full_name, $email);
                if (mysqli_stmt_execute($statement)) {
                    mysqli_stmt_close($statement);
                    header('Location: participants.php?status=created');
                    exit;
                }
                error_log('Club Hub participant insert failed: ' . mysqli_stmt_error($statement));
                mysqli_stmt_close($statement);
            } else {
                error_log('Club Hub participant insert prepare failed: ' . mysqli_error($conn));
            }
            $error_message = 'Pjesëmarrësi nuk u ruajt. Provo përsëri.';
        } elseif ($posted_id === false || $posted_id === null) {
            $error_message = 'ID-ja e pjesëmarrësit për editim nuk është e vlefshme.';
        } else {
            if ($email === '') {
                $email = null;
            }
            $statement = mysqli_prepare($conn, 'UPDATE participants SET full_name = ?, email = ? WHERE id = ?');
            if ($statement) {
                mysqli_stmt_bind_param($statement, 'ssi', $full_name, $email, $posted_id);
                if (mysqli_stmt_execute($statement) && mysqli_stmt_affected_rows($statement) >= 0) {
                    mysqli_stmt_close($statement);
                    header('Location: participant.php?id=' . $posted_id . '&status=updated');
                    exit;
                }
                error_log('Club Hub participant update failed: ' . mysqli_stmt_error($statement));
                mysqli_stmt_close($statement);
            } else {
                error_log('Club Hub participant update prepare failed: ' . mysqli_error($conn));
            }
            $error_message = 'Të dhënat e pjesëmarrësit nuk u përditësuan.';
        }
    } elseif ($action === 'delete') {
        $posted_id = filter_input(INPUT_POST, 'participant_id', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
        if ($posted_id === false || $posted_id === null) {
            $error_message = 'Pjesëmarrësi i zgjedhur nuk është i vlefshëm.';
        } elseif (mysqli_begin_transaction($conn)) {
            $membership_statement = mysqli_prepare($conn, 'DELETE FROM participant_groups WHERE participant_id = ?');
            $participant_statement = mysqli_prepare($conn, 'DELETE FROM participants WHERE id = ?');
            $delete_ok = false;
            if (!$membership_statement) {
                error_log('Club Hub participant memberships delete prepare failed: ' . mysqli_error($conn));
            }
            if (!$participant_statement) {
                error_log('Club Hub participant delete prepare failed: ' . mysqli_error($conn));
            }
            if ($membership_statement && $participant_statement) {
                mysqli_stmt_bind_param($membership_statement, 'i', $posted_id);
                mysqli_stmt_bind_param($participant_statement, 'i', $posted_id);
                if (mysqli_stmt_execute($membership_statement)) {
                    if (mysqli_stmt_execute($participant_statement)) {
                        $delete_ok = mysqli_stmt_affected_rows($participant_statement) === 1;
                    } else {
                        error_log('Club Hub participant delete failed: ' . mysqli_stmt_error($participant_statement));
                    }
                } else {
                    error_log('Club Hub participant memberships delete failed: ' . mysqli_stmt_error($membership_statement));
                }
            }
            if ($membership_statement) {
                mysqli_stmt_close($membership_statement);
            }
            if ($participant_statement) {
                mysqli_stmt_close($participant_statement);
            }
            if ($delete_ok && mysqli_commit($conn)) {
                header('Location: participants.php?status=deleted');
                exit;
            }
            mysqli_rollback($conn);
            $error_message = 'Pjesëmarrësi nuk u fshi. Kontrollo nëse ai ekziston dhe provo përsëri.';
        } else {
            error_log('Club Hub participant deletion transaction failed: ' . mysqli_error($conn));
            $error_message = 'Pjesëmarrësi nuk u fshi. Provo përsëri.';
        }
    } else {
        $error_message = 'Veprimi i kërkuar nuk njihet.';
    }
}

if (!$db_error) {
    $groups_result = mysqli_query($conn, 'SELECT id, name FROM `groups` ORDER BY name');
    if ($groups_result) {
        while ($group = mysqli_fetch_assoc($groups_result)) {
            $groups[] = $group;
        }
        mysqli_free_result($groups_result);
    } else {
        error_log('Club Hub participant group filter list failed: ' . mysqli_error($conn));
        $error_message = 'Lista e grupeve për filtrim nuk mund të ngarkohet.';
    }

    if ($edit_id !== false && $edit_id !== null) {
        $edit_statement = mysqli_prepare($conn, 'SELECT id, full_name, email FROM participants WHERE id = ?');
        if ($edit_statement) {
            mysqli_stmt_bind_param($edit_statement, 'i', $edit_id);
            if (mysqli_stmt_execute($edit_statement)) {
                mysqli_stmt_bind_result($edit_statement, $edit_participant_id, $edit_name, $edit_email);
                if (mysqli_stmt_fetch($edit_statement)) {
                    $editing_participant = array('id' => $edit_participant_id, 'full_name' => $edit_name, 'email' => $edit_email);
                }
            } else {
                error_log('Club Hub participant edit lookup failed: ' . mysqli_stmt_error($edit_statement));
                $error_message = 'Të dhënat për editim nuk mund të ngarkohen.';
            }
            mysqli_stmt_close($edit_statement);
        } else {
            error_log('Club Hub participant edit lookup prepare failed: ' . mysqli_error($conn));
            $error_message = 'Të dhënat për editim nuk mund të ngarkohen.';
        }
        if ($editing_participant === null && $error_message === '') {
            $error_message = 'Pjesëmarrësi për editim nuk u gjet.';
        }
    }

    $search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
    $group_filter = isset($_GET['group_filter']) && is_string($_GET['group_filter']) ? $_GET['group_filter'] : '';
    $sort = isset($_GET['sort']) && is_string($_GET['sort']) ? $_GET['sort'] : 'date';
    if (!in_array($sort, array('date', 'name', 'group'), true)) {
        $sort = 'date';
    }
    $order_by = $sort === 'name'
        ? 'p.full_name ASC, p.id DESC'
        : ($sort === 'group' ? 'MIN(g.name) ASC, p.full_name ASC' : 'p.created_at DESC, p.id DESC');

    $query = 'SELECT p.id, p.full_name, p.email, p.created_at,
                     GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ", ") AS group_names
              FROM participants p
              LEFT JOIN participant_groups pg ON pg.participant_id = p.id
              LEFT JOIN `groups` g ON g.id = pg.group_id';
    $where = '';
    if ($search !== '') {
        $where .= ' WHERE (p.full_name LIKE ? OR p.email LIKE ?)';
    }
    if ($group_filter === 'ungrouped') {
        $where .= ($where === '' ? ' WHERE ' : ' AND ')
            . 'NOT EXISTS (SELECT 1 FROM participant_groups filter_pg WHERE filter_pg.participant_id = p.id)';
    } elseif (ctype_digit((string) $group_filter) && (int) $group_filter > 0) {
        $where .= ($where === '' ? ' WHERE ' : ' AND ')
            . 'EXISTS (SELECT 1 FROM participant_groups filter_pg WHERE filter_pg.participant_id = p.id AND filter_pg.group_id = ?)';
    } else {
        $group_filter = '';
    }
    $query .= $where . ' GROUP BY p.id, p.full_name, p.email, p.created_at ORDER BY ' . $order_by;

    $list_statement = mysqli_prepare($conn, $query);
    if ($list_statement) {
        $like_search = '%' . $search . '%';
        $has_group_id = ctype_digit((string) $group_filter) && (int) $group_filter > 0;
        if ($search !== '' && $has_group_id) {
            $group_id = (int) $group_filter;
            mysqli_stmt_bind_param($list_statement, 'ssi', $like_search, $like_search, $group_id);
        } elseif ($search !== '') {
            mysqli_stmt_bind_param($list_statement, 'ss', $like_search, $like_search);
        } elseif ($has_group_id) {
            $group_id = (int) $group_filter;
            mysqli_stmt_bind_param($list_statement, 'i', $group_id);
        }
        if (mysqli_stmt_execute($list_statement)) {
            mysqli_stmt_bind_result($list_statement, $participant_id, $participant_name, $participant_email, $created_at, $group_names);
            while (mysqli_stmt_fetch($list_statement)) {
                $participants[] = array(
                    'id' => $participant_id,
                    'full_name' => $participant_name,
                    'email' => $participant_email,
                    'created_at' => $created_at,
                    'group_names' => $group_names
                );
            }
        } else {
            error_log('Club Hub participant list query failed: ' . mysqli_stmt_error($list_statement));
            $error_message = 'Lista e pjesëmarrësve nuk mund të ngarkohet.';
        }
        mysqli_stmt_close($list_statement);
    } else {
        error_log('Club Hub participant list query prepare failed: ' . mysqli_error($conn));
        $error_message = 'Lista e pjesëmarrësve nuk mund të ngarkohet.';
    }
}

$page_query = http_build_query(array_filter(array(
    'q' => $_GET['q'] ?? '',
    'group_filter' => $_GET['group_filter'] ?? '',
    'sort' => $_GET['sort'] ?? 'date'
), function ($value) {
    return $value !== '';
}));

require __DIR__ . '/includes/header.php';
?>
<section class="container app-section">
  <div class="app-heading">
    <p class="eyebrow">Menaxhimi / Pjesëmarrësit</p>
    <h1 class="app-title">Pjesëmarrësit</h1>
    <p class="app-lead">Regjistro, kërko dhe organizo personat që marrin pjesë në aktivitetet e Club Hub.</p>
  </div>

  <?php if ($db_error): ?>
    <div class="alert alert-danger" role="alert">Nuk mund të lidhemi me databazën Club Hub. Kontrollo konfigurimin te <code>includes/db.php</code>.</div>
  <?php elseif (($_GET['status'] ?? '') === 'created'): ?>
    <div class="alert alert-success" role="status">Pjesëmarrësi u regjistrua me sukses.</div>
  <?php elseif (($_GET['status'] ?? '') === 'deleted'): ?>
    <div class="alert alert-success" role="status">Pjesëmarrësi dhe anëtarësimet e tij u fshinë.</div>
  <?php endif; ?>
  <?php if ($error_message !== ''): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div class="row g-4 align-items-start">
    <div class="col-12 col-lg-4">
      <section class="card form-card" id="participant-form">
        <div class="card-body">
          <h2 class="card-title"><?= $editing_participant ? 'Edito pjesëmarrësin' : 'Regjistro pjesëmarrës' ?></h2>
          <form method="post" action="participants.php<?= $editing_participant ? '?edit=' . (int) $editing_participant['id'] : '' ?>">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="save">
            <?php if ($editing_participant): ?>
              <input type="hidden" name="participant_id" value="<?= (int) $editing_participant['id'] ?>">
            <?php endif; ?>
            <div class="mb-3">
              <label class="form-label" for="full_name">Emri i plotë</label>
              <input class="form-control" type="text" id="full_name" name="full_name" maxlength="150" required value="<?= htmlspecialchars($full_name ?? ($editing_participant['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label" for="email">Email <span class="optional-label">(opsional)</span></label>
              <input class="form-control" type="email" id="email" name="email" maxlength="255" value="<?= htmlspecialchars($email ?? ($editing_participant['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <button class="btn button" type="submit"><?= $editing_participant ? 'Ruaj ndryshimet' : 'Regjistro pjesëmarrësin' ?></button>
            <?php if ($editing_participant): ?>
              <a class="btn btn-outline-secondary ms-2" href="participants.php">Anulo</a>
            <?php endif; ?>
          </form>
        </div>
      </section>
    </div>

    <div class="col-12 col-lg-8">
      <section class="card list-card">
        <div class="card-body">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 list-heading">
            <h2 class="card-title mb-0">Lista e pjesëmarrësve</h2>
            <span class="badge count-badge"><?= count($participants) ?> rezultate</span>
          </div>
          <form class="participant-filters row g-2 align-items-end" method="get" action="participants.php">
            <div class="col-12 col-md-6">
              <label class="form-label" for="q">Kërko me emër ose email</label>
              <input class="form-control" type="search" id="q" name="q" value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="p.sh. Arta ose email@example.com">
            </div>
            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label" for="group_filter">Filtro sipas grupit</label>
              <select class="form-select" id="group_filter" name="group_filter">
                <option value="">Të gjithë grupet</option>
                <option value="ungrouped"<?= ($group_filter ?? '') === 'ungrouped' ? ' selected' : '' ?>>Pa grup</option>
                <?php foreach ($groups as $group): ?>
                  <option value="<?= (int) $group['id'] ?>"<?= (string) ($group_filter ?? '') === (string) $group['id'] ? ' selected' : '' ?>>
                    <?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label" for="sort">Rendit sipas</label>
              <select class="form-select" id="sort" name="sort">
                <option value="date"<?= ($sort ?? 'date') === 'date' ? ' selected' : '' ?>>Regjistrimit (më të rejat)</option>
                <option value="name"<?= ($sort ?? '') === 'name' ? ' selected' : '' ?>>Emrit (A–Z)</option>
                <option value="group"<?= ($sort ?? '') === 'group' ? ' selected' : '' ?>>Grupit (A–Z)</option>
              </select>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
              <button class="btn button button-small" type="submit">Apliko filtrat</button>
              <a class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center" href="participants.php">Pastro filtrat</a>
              <a class="btn btn-outline-success btn-sm d-inline-flex align-items-center" href="export_participants.php<?= $page_query !== '' ? '?' . htmlspecialchars($page_query, ENT_QUOTES, 'UTF-8') : '' ?>">Shkarko CSV</a>
            </div>
          </form>
          <?php if (count($participants) === 0): ?>
            <p class="empty-state mb-0"><?= ($search ?? '') !== '' || ($group_filter ?? '') !== '' ? 'Nuk u gjetën pjesëmarrës për këta filtra.' : 'Nuk ka ende pjesëmarrës të regjistruar.' ?></p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table align-middle participant-table mb-0">
                <thead><tr><th scope="col">Emri</th><th scope="col">Email</th><th scope="col">Grupet</th><th scope="col">Regjistruar</th><th scope="col">Veprimet</th></tr></thead>
                <tbody>
                  <?php foreach ($participants as $participant): ?>
                    <tr>
                      <td><a href="participant.php?id=<?= (int) $participant['id'] ?>"><?= htmlspecialchars($participant['full_name'], ENT_QUOTES, 'UTF-8') ?></a></td>
                      <td><?= htmlspecialchars($participant['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars($participant['group_names'] ?? 'Pa grup', ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars(date('d.m.Y', strtotime($participant['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                      <td>
                        <div class="d-flex flex-wrap gap-1">
                          <a class="btn btn-sm btn-outline-success" href="participant.php?id=<?= (int) $participant['id'] ?>">Detajet / Grupet</a>
                          <a class="btn btn-sm btn-outline-secondary" href="participants.php?edit=<?= (int) $participant['id'] ?>#participant-form">Edit</a>
                          <form method="post" action="participants.php?<?= htmlspecialchars($page_query, ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('A je i sigurt që dëshiron ta fshish këtë pjesëmarrës dhe anëtarësimet e tij?');">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="participant_id" value="<?= (int) $participant['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Fshi pjesëmarrësin</button>
                          </form>
                        </div>
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
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
