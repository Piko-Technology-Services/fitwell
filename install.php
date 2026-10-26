<?php
/**
 * One-time installer.
 *  Step 1 — Connect to the database you created in your hosting panel
 *           (saved to app/config.local.php). Skipped if already connected.
 *  Step 2 — Create the tables, optional sample content and the first admin.
 * Locks itself once an administrator exists.
 */
require __DIR__ . '/app/bootstrap.php';
require APP_DIR . '/schema.php';

function admin_exists(): bool
{
    try {
        return (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

/** Try to open the database. Returns [PDO|null, friendly error|null]. */
function try_connect(array $c): array
{
    if (($c['driver'] ?? 'mysql') === 'sqlite') {
        if (!is_dir(dirname($c['sqlite_path']))) {
            @mkdir(dirname($c['sqlite_path']), 0775, true);
        }
        return [new PDO('sqlite:' . $c['sqlite_path'], null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]), null];
    }
    try {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], (int) $c['port'], $c['name']);
        return [new PDO($dsn, $c['user'], $c['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]), null];
    } catch (PDOException $e) {
        $code = (int) ($e->errorInfo[1] ?? 0) ?: (int) $e->getCode();
        $msg = $e->getMessage();
        if (strpos($msg, '[1045]') !== false || $code === 1045) {
            $why = 'The database username or password is incorrect.';
        } elseif (strpos($msg, '[1049]') !== false || $code === 1049) {
            $why = 'A database with that name does not exist. Check the exact name (including any prefix such as u123456789_).';
        } elseif (strpos($msg, '[1044]') !== false || $code === 1044) {
            $why = 'This user is not allowed to use that database. In your hosting panel, make sure the user is assigned to the database.';
        } elseif (strpos($msg, '[2002]') !== false || $code === 2002) {
            $why = 'The database server could not be reached. On Hostinger the host is usually "localhost".';
        } else {
            $why = 'The database connection failed.';
        }
        return [null, $why . ' (' . $msg . ')'];
    }
}

$installed = admin_exists();
$errors = [];
$c = cfg('db');
[$pdoTest, $connError] = $installed ? [null, null] : try_connect($c);

// On a local XAMPP machine, create the database automatically if it is missing.
if (!$installed && $connError && !empty($isLocal) && strpos($connError, 'does not exist') !== false) {
    try {
        $server = new PDO(sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $c['host'], (int) $c['port']), $c['user'], $c['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $server->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '', $c['name']) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        [$pdoTest, $connError] = try_connect($c);
    } catch (Throwable $e) {
        // fall through to the connection form
    }
}
$needsDb = !$installed && $connError !== null;
$localConfig = APP_DIR . '/config.local.php';
$manualConfig = null;

/* ---------- Step 1: save database details ---------- */
if ($needsDb && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['step'] ?? '') === 'db') {
    verify_csrf();
    $new = [
        'driver' => 'mysql',
        'host'   => trim((string) ($_POST['db_host'] ?? 'localhost')) ?: 'localhost',
        'port'   => (int) ($_POST['db_port'] ?? 3306) ?: 3306,
        'name'   => trim((string) ($_POST['db_name'] ?? '')),
        'user'   => trim((string) ($_POST['db_user'] ?? '')),
        'pass'   => (string) ($_POST['db_pass'] ?? ''),
    ];
    if ($new['name'] === '' || $new['user'] === '') {
        $errors['db'] = 'Please enter the database name and username from your hosting panel.';
    } else {
        [$ok, $why] = try_connect($new);
        if (!$ok) {
            $errors['db'] = $why;
        } else {
            $existing = is_file($localConfig) ? (array) (require $localConfig) : [];
            $existing['db'] = $new;
            $php = "<?php\n// Server-specific settings — created by install.php. Keep this file private.\nreturn " . var_export($existing, true) . ";\n";
            if (@file_put_contents($localConfig, $php) !== false) {
                @chmod($localConfig, 0640);
                flash('success', 'Database connected. Now create your administrator account.');
                redirect('install.php');
            }
            $manualConfig = $php; // could not write: show it so it can be created by hand
        }
    }
}

/* ---------- Step 2: tables + admin ---------- */
if (!$installed && !$needsDb && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['step'] ?? '') === 'admin') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $seed = !empty($_POST['seed']);

    if (mb_strlen($name) < 2) { $errors['name'] = 'Please enter the administrator\'s name.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Please enter a valid email address.'; }
    if (strlen($password) < 10) { $errors['password'] = 'Use at least 10 characters for the admin password.'; }

    if (!$errors) {
        try {
            $pdo = db();
            create_schema($pdo, is_mysql());
            $result = $seed ? seed_sample_content($pdo) : null;

            $st = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $st->execute([$email]);
            if ($existingId = $st->fetchColumn()) {
                $pdo->prepare("UPDATE users SET role = 'admin', name = ?, password_hash = ? WHERE id = ?")
                    ->execute([$name, password_hash($password, PASSWORD_DEFAULT), $existingId]);
                $adminId = (int) $existingId;
            } else {
                $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, role, created_at) VALUES (?, ?, NULL, ?, 'admin', ?)")
                    ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), now_sql()]);
                $adminId = (int) $pdo->lastInsertId();
            }
            login_user($adminId);
            flash('success', 'Fitwell is installed' . ($result ? sprintf(' with %d sample classes and %d sample sessions', $result['classes'], $result['sessions']) : '') . '. Welcome to the studio admin.');
            redirect('admin/index.php');
        } catch (Throwable $e) {
            $errors['form'] = 'Installation failed: ' . $e->getMessage();
        }
    }
}

$title = 'Install Fitwell';
$noindex = true;
require APP_DIR . '/partials/header.php';
?>
<section class="page-hero" style="padding-bottom:clamp(40px,5vw,64px)">
  <div class="container container--narrow">
    <p class="eyebrow">Setup · Step <?= $needsDb ? '1' : '2' ?> of 2</p>
    <h1 class="h1">Install <span class="accent">Fitwell.</span></h1>
    <p class="lead muted" style="margin-top:20px"><?= $needsDb
        ? 'First, connect the website to the MySQL database you created in your hosting panel.'
        : 'Database connected. Now create the booking tables and your administrator account.' ?></p>
  </div>
</section>
<section class="section section--tight">
  <div class="container container--narrow">
    <?php if ($installed): ?>
      <div class="empty">
        <div class="empty__icon"><?= icon('check') ?></div>
        <h3>Already installed</h3>
        <p>An administrator account exists, so the installer is locked. You can delete install.php from the server.</p>
        <a class="btn btn--primary" href="<?= url('login.php?next=admin/index.php') ?>">Admin login <?= icon('arrow') ?></a>
      </div>

    <?php elseif ($needsDb): ?>
      <div class="panel" style="max-width:600px">
        <?php if ($manualConfig): ?>
          <div class="form-alert" role="alert"><?= icon('x') ?><span>The details work, but the server wouldn't let the installer save them. Create the file <strong>app/config.local.php</strong> in File Manager with exactly this content, then refresh this page:</span></div>
          <textarea class="textarea" rows="14" readonly style="font-family:monospace;font-size:.85rem"><?= e($manualConfig) ?></textarea>
        <?php else: ?>
          <?php if (isset($errors['db'])): ?>
            <div class="form-alert" role="alert"><?= icon('x') ?><span><?= e($errors['db']) ?></span></div>
          <?php elseif (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'): ?>
            <p class="notice">Find these in <strong>hPanel → Databases → Management</strong>. Database names and usernames on Hostinger start with a prefix such as <code>u123456789_</code>.</p>
          <?php endif; ?>
          <form class="form" method="post" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="step" value="db">
            <h2 class="h3">Database connection</h2>
            <div class="field"><label class="label" for="db_name">Database name</label><input class="input" id="db_name" name="db_name" required placeholder="u123456789_fitwell" value="<?= e($_POST['db_name'] ?? '') ?>" autocomplete="off"></div>
            <div class="field"><label class="label" for="db_user">Database username</label><input class="input" id="db_user" name="db_user" required placeholder="u123456789_fitwell" value="<?= e($_POST['db_user'] ?? '') ?>" autocomplete="off"></div>
            <div class="field"><label class="label" for="db_pass">Database password</label>
              <div class="input-wrap"><input class="input" id="db_pass" name="db_pass" type="password" autocomplete="new-password"><button class="pw-toggle" type="button" data-pw-toggle aria-controls="db_pass" aria-pressed="false" aria-label="Show password"><?= icon('eye') ?></button></div>
            </div>
            <div class="field__row">
              <div class="field"><label class="label" for="db_host">Host</label><input class="input" id="db_host" name="db_host" value="<?= e($_POST['db_host'] ?? 'localhost') ?>"></div>
              <div class="field"><label class="label" for="db_port">Port</label><input class="input" id="db_port" name="db_port" type="number" value="<?= e($_POST['db_port'] ?? '3306') ?>"></div>
            </div>
            <button class="btn btn--primary btn--lg btn--block" type="submit"><span>Connect database</span> <?= icon('arrow') ?></button>
          </form>
        <?php endif; ?>
      </div>

    <?php else: ?>
      <div class="panel" style="max-width:560px">
        <?php if (isset($errors['form'])): ?><div class="form-alert" role="alert"><?= icon('x') ?><span><?= e($errors['form']) ?></span></div><?php endif; ?>
        <form class="form" method="post" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" name="step" value="admin">
          <h2 class="h3">Administrator account</h2>
          <div class="field<?= isset($errors['name']) ? ' has-error' : '' ?>">
            <label class="label" for="name">Name</label>
            <input class="input" id="name" name="name" required value="<?= e($_POST['name'] ?? '') ?>">
            <?= field_error($errors, 'name') ?>
          </div>
          <div class="field<?= isset($errors['email']) ? ' has-error' : '' ?>">
            <label class="label" for="email">Email</label>
            <input class="input" id="email" name="email" type="email" required value="<?= e($_POST['email'] ?? '') ?>">
            <?= field_error($errors, 'email') ?>
          </div>
          <div class="field<?= isset($errors['password']) ? ' has-error' : '' ?>">
            <label class="label" for="password">Password</label>
            <div class="input-wrap">
              <input class="input" id="password" name="password" type="password" autocomplete="new-password" required minlength="10">
              <button class="pw-toggle" type="button" data-pw-toggle aria-controls="password" aria-pressed="false" aria-label="Show password"><?= icon('eye') ?></button>
            </div>
            <?= field_error($errors, 'password') ?>
            <p class="field__help">At least 10 characters.</p>
          </div>
          <label class="checkbox"><input type="checkbox" name="seed" value="1" checked> <span>Add sample classes and three weeks of sample sessions (you can edit or remove them in the admin area).</span></label>
          <button class="btn btn--primary btn--lg btn--block" type="submit"><span>Install</span> <?= icon('arrow') ?></button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require APP_DIR . '/partials/footer.php'; ?>
