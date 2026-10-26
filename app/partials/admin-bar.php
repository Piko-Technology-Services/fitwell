<?php $adminPage = current_page(); ?>
<div class="admin-bar">
  <div class="container">
    <strong>Studio admin</strong>
    <a href="<?= url('admin/index.php') ?>"<?= $adminPage === 'index.php' ? ' class="is-active"' : '' ?>>Overview</a>
    <a href="<?= url('admin/sessions.php') ?>"<?= in_array($adminPage, ['sessions.php', 'session.php'], true) ? ' class="is-active"' : '' ?>>Sessions</a>
    <a href="<?= url('admin/classes.php') ?>"<?= $adminPage === 'classes.php' ? ' class="is-active"' : '' ?>>Classes</a>
    <a href="<?= url('timetable.php') ?>">View site</a>
    <form method="post" action="<?= url('logout.php') ?>" style="display:inline">
      <?= csrf_field() ?>
      <button type="submit" style="background:none;border:0;color:rgba(255,255,255,.85);font:600 .9rem var(--font-display);cursor:pointer;padding:0">Log out</button>
    </form>
  </div>
</div>
