<?php
require __DIR__ . '/app/bootstrap.php';

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? null, 'account.php');
if (current_user()) {
    redirect($next);
}
$errors = [];

const LOGIN_MAX_ATTEMPTS = 6;
const LOGIN_WINDOW_MIN = 15;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 45);

    if (!db_ready()) {
        $errors['form'] = 'Login is not available yet — the studio database still needs to be set up.';
    } elseif ($email === '' || $password === '') {
        $errors['form'] = 'Please enter your email and password.';
    } else {
        $since = date('Y-m-d H:i:s', time() - LOGIN_WINDOW_MIN * 60);
        $st = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE (email = ? OR ip = ?) AND attempted_at > ?');
        $st->execute([$email, $ip, $since]);
        if ((int) $st->fetchColumn() >= LOGIN_MAX_ATTEMPTS) {
            $errors['form'] = 'Too many attempts. Please wait ' . LOGIN_WINDOW_MIN . ' minutes and try again.';
        } else {
            $st = db()->prepare('SELECT id, name, password_hash, role FROM users WHERE email = ?');
            $st->execute([$email]);
            $u = $st->fetch();
            if ($u && password_verify($password, $u['password_hash'])) {
                if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
                    db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $u['id']]);
                }
                db()->prepare('DELETE FROM login_attempts WHERE email = ?')->execute([$email]);
                login_user((int) $u['id']);
                clear_old();
                flash('success', 'Welcome back, ' . explode(' ', $u['name'])[0] . '.');
                if ($u['role'] === 'admin' && $next === 'account.php') {
                    $next = 'admin/index.php';
                }
                redirect($next);
            }
            db()->prepare('INSERT INTO login_attempts (email, ip, attempted_at) VALUES (?, ?, ?)')->execute([$email, $ip, now_sql()]);
            $errors['form'] = 'That email and password don\'t match our records. Please check and try again.';
        }
    }
    keep_old($_POST);
}

$title = 'Member Login';
$description = 'Log in to your Fitwell Studio account to book classes and manage your upcoming sessions.';
$noindex = true;
require APP_DIR . '/partials/header.php';
?>
<section class="auth">
  <div class="auth__media">
    <?= img('class-dance', '(min-width: 900px) 50vw, 100vw', ['loading' => 'eager', 'alt' => ''], null, 1600) ?>
    <p class="auth__quote">Good to see<br>you <span>again.</span></p>
  </div>
  <div class="auth__form-wrap">
    <div class="auth__form hero-anim">
      <p class="eyebrow">Member login</p>
      <h1>Welcome back</h1>
      <p>Log in to book classes and manage your upcoming sessions.</p>

      <?php if (isset($errors['form'])): ?><div class="form-alert" role="alert"><?= icon('x') ?><span><?= e($errors['form']) ?></span></div><?php endif; ?>

      <form class="form" method="post" action="<?= url('login.php') ?>" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= e($next) ?>">
        <div class="field">
          <label class="label" for="email">Email address</label>
          <input class="input" id="email" name="email" type="email" autocomplete="email" inputmode="email" required value="<?= old('email') ?>" autofocus>
        </div>
        <div class="field">
          <label class="label" for="password">Password</label>
          <div class="input-wrap">
            <input class="input" id="password" name="password" type="password" autocomplete="current-password" required>
            <button class="pw-toggle" type="button" data-pw-toggle aria-controls="password" aria-pressed="false" aria-label="Show password"><?= icon('eye') ?></button>
          </div>
          <p class="field__help">Forgotten your password? <?php if (whatsapp_url()): ?><a href="<?= e(whatsapp_url("Hi Fitwell! I need help resetting my member account password.")) ?>" target="_blank" rel="noopener">Message us on WhatsApp</a> and we'll help you reset it.<?php else: ?>Contact the studio and we'll help you reset it.<?php endif; ?></p>
        </div>
        <button class="btn btn--primary btn--lg btn--block" type="submit"><span>Log in</span> <?= icon('arrow') ?></button>
      </form>
      <p class="auth__switch">New to Fitwell? <a href="<?= url('register.php' . ($next !== 'account.php' ? '?next=' . rawurlencode($next) : '')) ?>">Create a free account</a></p>
    </div>
  </div>
</section>
<?php clear_old(); ?>
<?php require APP_DIR . '/partials/footer.php'; ?>
