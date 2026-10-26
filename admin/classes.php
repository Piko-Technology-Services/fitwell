<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require_admin();
require APP_DIR . '/uploads.php';

$cats = cfg('categories', []);
$errors = [];
$editing = null;

function slugify(string $s): string
{
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $s), '-'));
    return $s ?: 'class';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'toggle' && $id) {
        db()->prepare('UPDATE classes SET is_published = 1 - is_published WHERE id = ?')->execute([$id]);
        flash('success', 'Class visibility updated.');
        redirect('admin/classes.php');
    }

    if ($action === 'save') {
        $data = [
            'name'        => trim((string) ($_POST['name'] ?? '')),
            'category'    => (string) ($_POST['category'] ?? ''),
            'summary'     => trim((string) ($_POST['summary'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'duration_min'=> (int) ($_POST['duration_min'] ?? 0),
            'level'       => trim((string) ($_POST['level'] ?? '')),
            'intensity'   => trim((string) ($_POST['intensity'] ?? '')),
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
            'is_published'=> !empty($_POST['is_published']) ? 1 : 0,
        ];
        if (mb_strlen($data['name']) < 2) { $errors['name'] = 'Give the class a name.'; }
        if (!isset($cats[$data['category']])) { $errors['category'] = 'Choose a category.'; }
        if (mb_strlen($data['summary']) < 10 || mb_strlen($data['summary']) > 255) { $errors['summary'] = 'Write a short summary (10–255 characters).'; }
        if ($data['duration_min'] < 10 || $data['duration_min'] > 240) { $errors['duration_min'] = 'Duration should be between 10 and 240 minutes.'; }
        if ($data['level'] === '') { $data['level'] = 'All levels'; }

        // Photo: keep current, replace with an upload, or remove (-> gradient placeholder)
        $current = null;
        if ($id) {
            $st = db()->prepare('SELECT image FROM classes WHERE id = ?');
            $st->execute([$id]);
            $current = $st->fetchColumn() ?: null;
        }
        $data['image'] = $current;
        $upload = $_FILES['photo'] ?? null;
        $hasUpload = $upload && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
        if (!$errors && $hasUpload) {
            [$ok, $result] = store_uploaded_image($upload);
            if ($ok) {
                $data['image'] = $result;
            } else {
                $errors['photo'] = $result;
            }
        } elseif (!empty($_POST['remove_photo'])) {
            $data['image'] = null;
        }

        if (!$errors) {
            if ($current && $current !== $data['image']) {
                delete_media_ref($current);
            }
            if ($id) {
                db()->prepare('UPDATE classes SET name=?, category=?, summary=?, description=?, duration_min=?, level=?, intensity=?, image=?, sort_order=?, is_published=? WHERE id=?')
                    ->execute([$data['name'], $data['category'], $data['summary'], $data['description'], $data['duration_min'], $data['level'], $data['intensity'] ?: null, $data['image'] ?: null, $data['sort_order'], $data['is_published'], $id]);
                flash('success', '“' . $data['name'] . '” saved.');
            } else {
                $base = slugify($data['name']);
                $slug = $base;
                $n = 2;
                $chk = db()->prepare('SELECT 1 FROM classes WHERE slug = ?');
                while (true) { $chk->execute([$slug]); if (!$chk->fetch()) break; $slug = $base . '-' . $n++; }
                db()->prepare('INSERT INTO classes (slug, name, category, summary, description, duration_min, level, intensity, image, is_published, sort_order, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
                    ->execute([$slug, $data['name'], $data['category'], $data['summary'], $data['description'], $data['duration_min'], $data['level'], $data['intensity'] ?: null, $data['image'] ?: null, $data['is_published'], $data['sort_order'], now_sql()]);
                flash('success', '“' . $data['name'] . '” created. Now schedule some sessions for it.');
            }
            redirect('admin/classes.php');
        }
        $editing = $data + ['id' => $id];
    }
}

if (!$editing && isset($_GET['edit'])) {
    $st = db()->prepare('SELECT * FROM classes WHERE id = ?');
    $st->execute([(int) $_GET['edit']]);
    $editing = $st->fetch() ?: null;
}
$f = $editing ?: ['id' => 0, 'name' => '', 'category' => '', 'summary' => '', 'description' => '', 'duration_min' => 45, 'level' => 'All levels', 'intensity' => '', 'image' => '', 'sort_order' => 0, 'is_published' => 1];

$classes = db()->query('SELECT c.*, (SELECT COUNT(*) FROM class_sessions s WHERE s.class_id = c.id AND s.starts_at >= ' . db()->quote(now_sql()) . " AND s.status = 'scheduled') AS upcoming FROM classes c ORDER BY sort_order, name")->fetchAll();

$title = 'Classes · Admin';
require APP_DIR . '/partials/header.php';
require APP_DIR . '/partials/admin-bar.php';
?>
<section class="section section--tight">
  <div class="container admin-grid">
    <div class="panel" id="form">
      <h1 class="panel__title"><?= $f['id'] ? 'Edit class' : 'New class' ?><?php if ($f['id']): ?><a class="link-arrow" style="font-size:.85rem" href="<?= url('admin/classes.php') ?>">New instead</a><?php endif; ?></h1>
      <form class="form" method="post" action="<?= url('admin/classes.php') ?>" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
        <input type="hidden" name="return" value="admin/classes.php">
        <div class="field<?= isset($errors['name']) ? ' has-error' : '' ?>"><label class="label" for="name">Class name</label><input class="input" id="name" name="name" value="<?= e($f['name']) ?>" required><?= field_error($errors, 'name') ?></div>
        <div class="field__row">
          <div class="field<?= isset($errors['category']) ? ' has-error' : '' ?>"><label class="label" for="category">Category</label>
            <select class="select" id="category" name="category"><option value="">Choose…</option><?php foreach ($cats as $k => $l): ?><option value="<?= e($k) ?>"<?= $f['category'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select><?= field_error($errors, 'category') ?></div>
          <div class="field<?= isset($errors['duration_min']) ? ' has-error' : '' ?>"><label class="label" for="duration_min">Duration (min)</label><input class="input" id="duration_min" name="duration_min" type="number" min="10" max="240" value="<?= (int) $f['duration_min'] ?>"><?= field_error($errors, 'duration_min') ?></div>
        </div>
        <div class="field__row">
          <div class="field"><label class="label" for="level">Level</label><input class="input" id="level" name="level" value="<?= e($f['level']) ?>" placeholder="All levels"></div>
          <div class="field"><label class="label" for="intensity">Intensity <span class="opt">Optional</span></label><input class="input" id="intensity" name="intensity" value="<?= e($f['intensity'] ?? '') ?>" placeholder="Moderate"></div>
        </div>
        <div class="field<?= isset($errors['summary']) ? ' has-error' : '' ?>"><label class="label" for="summary">Short summary</label><input class="input" id="summary" name="summary" maxlength="255" value="<?= e($f['summary']) ?>"><?= field_error($errors, 'summary') ?></div>
        <div class="field"><label class="label" for="description">Description <span class="opt">Blank line = new paragraph</span></label><textarea class="textarea" id="description" name="description" rows="5"><?= e($f['description'] ?? '') ?></textarea></div>
        <div class="field<?= isset($errors['photo']) ? ' has-error' : '' ?>">
          <span class="label">Class photo <span class="opt">Optional</span></span>
          <div class="upload">
            <div class="upload__preview" data-upload-preview><?= img($f['image'] ?: '', '160px', ['alt' => 'Current class photo'], 1.25, 480) ?></div>
            <div class="upload__body">
              <label class="btn btn--ghost btn--sm upload__btn" for="photo"><?= icon('spark') ?> <?= $f['image'] ? 'Replace photo' : 'Upload photo' ?></label>
              <input class="sr-only" id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" data-upload-input>
              <p class="field__help" data-upload-name>JPG, PNG or WebP, up to 8 MB. Landscape photos work best.</p>
              <?php if ($f['image']): ?><label class="checkbox"><input type="checkbox" name="remove_photo" value="1"> <span>Remove photo (show the brand gradient instead)</span></label><?php else: ?><p class="field__help">No photo? The class shows an animated brand gradient.</p><?php endif; ?>
            </div>
          </div>
          <?= field_error($errors, 'photo') ?>
        </div>
        <div class="field__row">
          <div class="field"><label class="label" for="sort_order">Display order</label><input class="input" id="sort_order" name="sort_order" type="number" value="<?= (int) $f['sort_order'] ?>"></div>
          <div class="field" style="align-content:end"><label class="checkbox"><input type="checkbox" name="is_published" value="1"<?= $f['is_published'] ? ' checked' : '' ?>> <span>Published on the website</span></label></div>
        </div>
        <button class="btn btn--primary btn--block" type="submit"><span><?= $f['id'] ? 'Save changes' : 'Create class' ?></span></button>
      </form>
    </div>

    <div>
      <h2 class="h3" style="margin-bottom:16px">All classes</h2>
      <div class="table-scroll">
        <table class="table">
          <thead><tr><th>Class</th><th>Upcoming</th><th>Status</th><th><span class="sr-only">Actions</span></th></tr></thead>
          <tbody>
            <?php foreach ($classes as $c): ?>
              <tr>
                <td><div style="display:flex;gap:14px;align-items:center"><span class="thumb"><?= img($c['image'] ?: '', '64px', ['alt' => ''], 1, 480) ?></span><span><strong><?= e($c['name']) ?></strong><br><span class="muted"><?= e(category_label($c['category'])) ?> · <?= (int) $c['duration_min'] ?> min</span></span></div></td>
                <td><?= (int) $c['upcoming'] ?></td>
                <td><?= $c['is_published'] ? '<span class="badge badge--success">Published</span>' : '<span class="badge">Hidden</span>' ?></td>
                <td><div class="actions">
                  <a class="btn btn--ghost btn--sm" href="<?= url('admin/classes.php?edit=' . (int) $c['id']) ?>#form">Edit</a>
                  <form method="post" action="<?= url('admin/classes.php') ?>"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><input type="hidden" name="return" value="admin/classes.php"><button class="btn btn--quiet btn--sm" type="submit"><?= $c['is_published'] ? 'Hide' : 'Publish' ?></button></form>
                </div></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$classes): ?><tr><td colspan="4" class="muted">No classes yet — create your first one.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
<?php require APP_DIR . '/partials/footer.php'; ?>
