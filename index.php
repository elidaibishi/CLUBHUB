<?php
$page_title = 'Përmbledhja';
$active_page = 'dashboard';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$participant_count = 0;
$group_count = 0;
$unassigned_count = 0;
$recent_participants = array();
$dashboard_groups = array();
$largest_group = null;
$dashboard_error = '';

if (!$db_error) {
    $counts_result = mysqli_query(
        $conn,
        'SELECT
           (SELECT COUNT(*) FROM participants) AS participant_count,
           (SELECT COUNT(*) FROM `groups`) AS group_count,
           (SELECT COUNT(*) FROM participants p
            WHERE NOT EXISTS (
              SELECT 1 FROM participant_groups pg WHERE pg.participant_id = p.id
            )) AS unassigned_count'
    );
    if ($counts_result) {
        $counts = mysqli_fetch_assoc($counts_result);
        $participant_count = (int) $counts['participant_count'];
        $group_count = (int) $counts['group_count'];
        $unassigned_count = (int) $counts['unassigned_count'];
        mysqli_free_result($counts_result);
    } else {
        error_log('Club Hub dashboard counts query failed: ' . mysqli_error($conn));
        $dashboard_error = 'Përmbledhja e statistikave nuk mund të ngarkohet.';
    }

    $recent_result = mysqli_query(
        $conn,
        'SELECT id, full_name, email, created_at
         FROM participants ORDER BY created_at DESC, id DESC LIMIT 5'
    );
    if ($recent_result) {
        while ($participant = mysqli_fetch_assoc($recent_result)) {
            $recent_participants[] = $participant;
        }
        mysqli_free_result($recent_result);
    } else {
        error_log('Club Hub recent participants query failed: ' . mysqli_error($conn));
        $dashboard_error = 'Pjesëmarrësit e fundit nuk mund të ngarkohen.';
    }

    $largest_result = mysqli_query(
        $conn,
        'SELECT `groups`.id, `groups`.name, COUNT(participant_groups.participant_id) AS member_count
         FROM `groups`
         LEFT JOIN participant_groups ON participant_groups.group_id = `groups`.id
         GROUP BY `groups`.id, `groups`.name
         ORDER BY member_count DESC, `groups`.name ASC
         LIMIT 1'
    );
    if ($largest_result) {
        $largest_group = mysqli_fetch_assoc($largest_result);
        mysqli_free_result($largest_result);
    } else {
        error_log('Club Hub largest group query failed: ' . mysqli_error($conn));
        $dashboard_error = 'Statistikat e grupeve nuk mund të ngarkohen.';
    }

    $dashboard_groups_result = mysqli_query(
        $conn,
        'SELECT `groups`.id, `groups`.name, `groups`.description,
                COUNT(participant_groups.participant_id) AS member_count
         FROM `groups`
         LEFT JOIN participant_groups ON participant_groups.group_id = `groups`.id
         GROUP BY `groups`.id, `groups`.name, `groups`.description
         ORDER BY `groups`.name ASC'
    );
    if ($dashboard_groups_result) {
        while ($group = mysqli_fetch_assoc($dashboard_groups_result)) {
            $dashboard_groups[] = $group;
        }
        mysqli_free_result($dashboard_groups_result);
    } else {
        error_log('Club Hub dashboard groups query failed: ' . mysqli_error($conn));
        $dashboard_error = 'Lista e grupeve nuk mund të ngarkohet.';
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="container app-section">
  <div class="app-heading">
    <p class="eyebrow">Club Hub / Menaxhimi</p>
    <h1 class="app-title">Përmbledhja</h1>
    <p class="app-lead">Shiko grupet e krijuara dhe anëtarët e tyre, regjistro pjesëmarrës të rinj dhe menaxho anëtarësimet.</p>
  </div>

  <?php if ($db_error): ?>
    <div class="alert alert-danger" role="alert">
      Nuk mund të lidhemi me databazën Club Hub. Kontrollo konfigurimin te <code>includes/db.php</code>.
    </div>
  <?php elseif ($dashboard_error !== ''): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($dashboard_error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div class="row g-3 dashboard-stats mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
      <article class="card stat-card h-100"><div class="card-body">
        <p class="stat-label">Pjesëmarrës gjithsej</p><p class="stat-value"><?= $participant_count ?></p>
        <a href="participants.php">Hap listën <span aria-hidden="true">↗</span></a>
      </div></article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <article class="card stat-card h-100"><div class="card-body">
        <p class="stat-label">Grupe gjithsej</p><p class="stat-value"><?= $group_count ?></p>
        <a href="groups.php">Hap grupet <span aria-hidden="true">↗</span></a>
      </div></article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <article class="card stat-card h-100"><div class="card-body">
        <p class="stat-label">Pa grup</p><p class="stat-value"><?= $unassigned_count ?></p>
        <a href="participants.php?group_filter=ungrouped">Shiko pjesëmarrësit <span aria-hidden="true">↗</span></a>
      </div></article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <article class="card stat-card h-100"><div class="card-body">
        <p class="stat-label">Grupi më i madh</p>
        <?php if ($largest_group): ?>
          <p class="stat-value stat-group-name"><?= htmlspecialchars($largest_group['name'], ENT_QUOTES, 'UTF-8') ?></p>
          <a href="group.php?id=<?= (int) $largest_group['id'] ?>"><?= (int) $largest_group['member_count'] ?> pjesëmarrës <span aria-hidden="true">↗</span></a>
        <?php else: ?>
          <p class="stat-value stat-group-name">Ende pa grupe</p>
          <a href="groups.php">Krijo grupin e parë <span aria-hidden="true">↗</span></a>
        <?php endif; ?>
      </div></article>
    </div>
  </div>

  <section class="card list-card dashboard-groups-card mb-4" aria-labelledby="dashboard-groups-title">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 list-heading">
        <div>
          <p class="eyebrow mb-2">Klubet dhe anëtarësimet</p>
          <h2 class="card-title mb-0" id="dashboard-groups-title">Grupet e krijuara</h2>
        </div>
        <a class="btn button button-small" href="groups.php">Krijo ose shiko grupet <span aria-hidden="true">↗</span></a>
      </div>
      <?php if (count($dashboard_groups) === 0): ?>
        <p class="empty-state mb-0">Nuk ka ende grupe. Krijo grupin e parë dhe më pas shto pjesëmarrësit e regjistruar.</p>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($dashboard_groups as $group): ?>
            <div class="col-12 col-md-6 col-xl-4">
              <article class="dashboard-group h-100">
                <div class="d-flex justify-content-between align-items-start gap-3">
                  <div>
                    <h3><?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($group['description'] ?? 'Pa përshkrim', ENT_QUOTES, 'UTF-8') ?></p>
                  </div>
                  <span class="group-member-count"><?= (int) $group['member_count'] ?><small><?= (int) $group['member_count'] === 1 ? 'anëtar' : 'anëtarë' ?></small></span>
                </div>
                <a class="dashboard-group-link" href="group.php?id=<?= (int) $group['id'] ?>">
                  Shiko anëtarët / menaxho grupin <span aria-hidden="true">→</span>
                </a>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="card list-card">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 list-heading">
        <h2 class="card-title mb-0">Të regjistruarit së fundmi</h2>
        <a class="text-link" href="participants.php">Shiko të gjithë <span aria-hidden="true">→</span></a>
      </div>
      <?php if (count($recent_participants) === 0): ?>
        <p class="empty-state mb-0">Nuk ka ende pjesëmarrës. <a href="participants.php">Regjistro të parin</a>.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle participant-table mb-0">
            <thead><tr><th scope="col">Emri</th><th scope="col">Email</th><th scope="col">Regjistruar</th><th scope="col"><span class="visually-hidden">Detajet</span></th></tr></thead>
            <tbody>
              <?php foreach ($recent_participants as $participant): ?>
                <tr>
                  <td><?= htmlspecialchars($participant['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($participant['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars(date('d.m.Y', strtotime($participant['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-end"><a class="btn btn-sm btn-outline-success" href="participant.php?id=<?= (int) $participant['id'] ?>">Detajet</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </section>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
