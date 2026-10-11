<?php
if (!isset($page_title)) {
    $page_title = 'Club Hub';
}
if (!isset($active_page)) {
    $active_page = '';
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?> | Club Hub</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
    crossorigin="anonymous">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <a class="skip-link" href="#main-content">Kalo te përmbajtja</a>
  <header class="site-header">
    <nav class="navbar navbar-expand-lg">
      <div class="container">
        <a class="navbar-brand brand" href="index.php" aria-label="Club Hub, faqja kryesore">
          <span class="brand-mark" aria-hidden="true">C</span>
          <span>club<span class="brand-accent">hub</span></span>
        </a>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#main-navigation"
          aria-controls="main-navigation"
          aria-expanded="false"
          aria-label="Hap ose mbyll navigimin">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="main-navigation">
          <ul class="navbar-nav ms-lg-auto align-items-lg-center gap-lg-3">
            <?php if (!empty($_SESSION['admin_id'])): ?>
              <li class="nav-item">
                <a class="nav-link app-nav-link<?= $active_page === 'dashboard' ? ' active' : '' ?>" href="index.php">Përmbledhja</a>
              </li>
              <li class="nav-item">
                <a class="nav-link app-nav-link<?= $active_page === 'participants' ? ' active' : '' ?>" href="participants.php">Pjesëmarrësit</a>
              </li>
              <li class="nav-item">
                <a class="nav-link app-nav-link<?= $active_page === 'groups' || $active_page === 'group' ? ' active' : '' ?>" href="groups.php">Grupet</a>
              </li>
              <li class="nav-item">
                <a class="nav-link app-nav-link" href="index.html">Faqja publike</a>
              </li>
              <li class="nav-item">
                <form method="post" action="logout.php" class="m-0">
                  <?php csrf_field(); ?>
                  <button class="btn btn-link nav-link app-nav-link logout-link" type="submit">Dil</button>
                </form>
              </li>
            <?php else: ?>
              <li class="nav-item"><a class="nav-link app-nav-link" href="login.php">Hyr</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>
  </header>
  <main class="app-main" id="main-content">
