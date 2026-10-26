<?php
require __DIR__ . '/app/bootstrap.php';

if (current_user()) {
    redirect('account.php');
}
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? null, 'timetable.php');
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!db_ready()) {
        $errors['form'] = 'Registration is not available yet — the studio database still needs to be set up.';
    }
    if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
        $errors['name'] = 'Please enter your full name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $errors['email'] = 'Please enter a valid email address, like name@example.com.';
    }
    if ($phone !== '' && !preg_match('/^[0-9+()\-\s]{7,20}$/', $phone)) {
        $errors['phone'] = 'That phone number doesn\'t look quite right.';
    }
    if (strlen($password) < 8) {
        $errors['password'] = 'Use at least 8 characters for your password.';
    } elseif (strlen($password) > 200) {
        $errors['password'] = 'That password is too long.';
    }

    if (!$errors) {
        $st = db()->prepare('SELECT id FROM users WHERE email = ?');
        $st->execute([$email]);
        if ($st->fetch()) {
            $errors['email'] = 'An account with this email already exists. Try logging in instead.';
        }
    }

    if (!$errors) {
        try {
            $st = db()->prepare('INSERT INTO users (name, email, phone, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?, ?)');
            $st->execute([$name, $email, $phone !== '' ? $phone : null, password_hash($password, PASSWORD_DEFAULT), 'member', now_sql()]);
            login_user((int) db()->lastInsertId());
            clear_old();
            flash('success', 'Welcome to Fitwell, ' . explode(' ', $name)[0] . '! Your account is ready — let\'s book your first class.');
            redirect($next);
        } catch (PDOException $ex) {
            $errors['email'] = 'An account with this email already exists. Try logging in instead.';
        }
    }
    keep_old($_POST);
}

$title = 'Join Fitwell';
$description = 'Create your free Fitwell Studio account in under a minute and book aerobics, dance and group fitness classes online.';
require APP_DIR . '/partials/header.php';
?>
<section class="auth">
  <div class="auth__media">
    <?= img('highfive', '(min-width: 900px) 50vw, 100vw', ['loading' => 'eager', 'alt' => ''], null, 1600) ?>
    <p class="auth__quote">Your first class<br>starts <span>here.</span></p>
  </div>
  <div class="auth__form-wrap">
    <div class="auth__form hero-anim">
      <p class="eyebrow">Join Fitwell</p>
      <h1>Create your account</h1>
      <p>It's free and takes less than a minute. Then you can book classes straight from your phone.</p>

      <?php if (isset($errors['form'])): ?><div class="form-alert" role="alert"><?= icon('x') ?><span><?= e($errors['form']) ?></span></div><?php endif; ?>

      <form class="form" method="post" action="<?= url('register.php') ?>" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= e($next) ?>">
        <div class="field<?= isset($errors['name']) ? ' has-error' : '' ?>">
          <label class="label" for="name">Full name</label>
          <input class="input" id="name" name="name" type="text" autocomplete="name" required maxlength="80" value="<?= old('name') ?>"<?= isset($errors['name']) ? ' aria-invalid="true" aria-describedby="name-error"' : '' ?>>
          <?= field_error($errors, 'name') ?>
        </div>
        <div class="field<?= isset($errors['email']) ? ' has-error' : '' ?>">
          <label class="label" for="email">Email address</label>
          <input class="input" id="email" name="email" type="email" autocomplete="email" inputmode="email" required value="<?= old('email') ?>"<?= isset($errors['email']) ? ' aria-invalid="true" aria-describedby="email-error"' : '' ?>>
          <?= field_error($errors, 'email') ?>
        </div>
        <div class="field<?= isset($errors['phone']) ? ' has-error' : '' ?>">
          <label class="label" for="phone">Phone number <span class="opt">Optional</span></label>
          <input class="input" id="phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" value="<?= old('phone') ?>"<?= isset($errors['phone']) ? ' aria-invalid="true" aria-describedby="phone-error"' : '' ?>>
          <?= field_error($errors, 'phone') ?>
        </div>
        <div class="field<?= isset($errors['password']) ? ' has-error' : '' ?>">
          <label class="label" for="password">Password</label>
          <div class="input-wrap">
            <input class="input" id="password" name="password" type="password" autocomplete="new-password" required minlength="8" aria-describedby="<?= isset($errors['password']) ? 'password-error ' : '' ?>password-help"<?= isset($errors['password']) ? ' aria-invalid="true"' : '' ?>>
            <button class="pw-toggle" type="button" data-pw-toggle aria-controls="password" aria-pressed="false" aria-label="Show password"><?= icon('eye') ?></button>
          </div>
          <?= field_error($errors, 'password') ?>
          <p class="field__help" id="password-help">At least 8 characters.</p>
        </div>
        <button class="btn btn--primary btn--lg btn--block" type="submit"><span>Create account</span> <?= icon('arrow') ?></button>
        <p class="field__help center">By creating an account you agree to our <a href="<?= url('terms.php') ?>">Terms</a> and <a href="<?= url('privacy.php') ?>">Privacy Policy</a>.</p>
      </form>
      <p class="auth__switch">Already a member? <a href="<?= url('login.php' . ($next !== 'timetable.php' ? '?next=' . rawurlencode($next) : '')) ?>">Log in</a></p>
    </div>
  </div>
</section>
<?php clear_old(); ?>
<?php require APP_DIR . '/partials/footer.php'; ?>
