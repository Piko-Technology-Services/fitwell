<?php
/** Serves uploaded class photos from the database with long-lived caching. */
require __DIR__ . '/app/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$row = null;
if ($id > 0 && db_ready()) {
    try {
        $st = db()->prepare('SELECT mime, data, created_at FROM media WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
    } catch (PDOException $e) {
        $row = null;
    }
}
if (!$row) {
    http_response_code(404);
    exit;
}
$data = is_resource($row['data']) ? stream_get_contents($row['data']) : $row['data'];
$etag = '"m' . $id . '-' . strtotime($row['created_at']) . '"';
if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
    http_response_code(304);
    exit;
}
header('Content-Type: ' . (in_array($row['mime'], ['image/jpeg', 'image/png', 'image/webp'], true) ? $row['mime'] : 'application/octet-stream'));
header('Content-Length: ' . strlen($data));
header('Cache-Control: public, max-age=31536000, immutable');
header('ETag: ' . $etag);
header('X-Content-Type-Options: nosniff');
echo $data;
