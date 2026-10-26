<?php
/**
 * Class photo uploads. Images are validated, resized and stored in the
 * `media` table, then referenced from classes.image as "media:ID".
 */

function ensure_media_table(): void
{
    require_once APP_DIR . '/schema.php';
    db()->exec(media_table_sql(is_mysql()));
}

/** Returns [true, 'media:ID'] or [false, 'friendly error']. */
function store_uploaded_image(array $file): array
{
    $err = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
        return [false, 'That photo is too large. Please choose an image under 8 MB.'];
    }
    if ($err !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
        return [false, 'The photo could not be uploaded. Please try again.'];
    }
    if (($file['size'] ?? 0) > 8 * 1024 * 1024) {
        return [false, 'That photo is too large. Please choose an image under 8 MB.'];
    }
    $info = @getimagesize($file['tmp_name']);
    $allowed = [IMAGETYPE_JPEG => 'image/jpeg', IMAGETYPE_PNG => 'image/png', IMAGETYPE_WEBP => 'image/webp'];
    if (!$info || !isset($allowed[$info[2]])) {
        return [false, 'Please upload a JPG, PNG or WebP image.'];
    }
    [$w, $h] = $info;
    $raw = file_get_contents($file['tmp_name']);
    $mime = $allowed[$info[2]];
    $data = null;

    // Resize + re-encode with GD (keeps pages fast and strips metadata)
    if (function_exists('imagecreatefromstring')) {
        $src = @imagecreatefromstring($raw);
        if ($src) {
            foreach ([[1800, 80], [1400, 74], [1100, 68]] as [$maxW, $q]) {
                $nw = min($w, $maxW);
                $nh = (int) round($h * $nw / $w);
                $dst = imagecreatetruecolor($nw, $nh);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                ob_start();
                if (function_exists('imagewebp')) {
                    imagewebp($dst, null, $q);
                    $mime = 'image/webp';
                } else {
                    imagejpeg($dst, null, $q);
                    $mime = 'image/jpeg';
                }
                $data = ob_get_clean();
                imagedestroy($dst);
                $w2 = $nw; $h2 = $nh;
                if (strlen($data) < 900 * 1024) {
                    break;
                }
            }
            imagedestroy($src);
            $w = $w2; $h = $h2;
        }
    }
    if ($data === null) {
        if (strlen($raw) > 900 * 1024) {
            return [false, 'Please upload a smaller image (under 900 KB), or enable the GD extension in PHP.'];
        }
        $data = $raw;
    }

    try {
        ensure_media_table();
        $st = db()->prepare('INSERT INTO media (mime, data, width, height, created_at) VALUES (?, ?, ?, ?, ?)');
        $st->bindValue(1, $mime);
        $st->bindValue(2, $data, PDO::PARAM_LOB);
        $st->bindValue(3, $w, PDO::PARAM_INT);
        $st->bindValue(4, $h, PDO::PARAM_INT);
        $st->bindValue(5, now_sql());
        $st->execute();
        return [true, 'media:' . db()->lastInsertId()];
    } catch (PDOException $e) {
        error_log('Fitwell upload error: ' . $e->getMessage());
        return [false, 'The photo could not be saved. Try a smaller image.'];
    }
}

function delete_media_ref(?string $ref): void
{
    if ($ref && strpos($ref, 'media:') === 0) {
        try {
            db()->prepare('DELETE FROM media WHERE id = ?')->execute([(int) substr($ref, 6)]);
        } catch (PDOException $e) {
            // ignore
        }
    }
}
