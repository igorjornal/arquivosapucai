<?php
declare(strict_types=1);

// Envio de imagens: confere se é imagem de verdade, reduz e converte para WebP.

const MAX_UPLOAD_BYTES = 15 * 1024 * 1024;
const MAX_IMAGE_WIDTH = 2000;

/**
 * @return array{id:int,path:string,width:int,height:int}
 */
function store_uploaded_image(array $file, ?int $userId, string $alt = ''): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException(upload_error_message((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE)));
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('A imagem passa de 15 MB.');
    }
    $info = @getimagesize($file['tmp_name']);
    $allowed = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF];
    if (!$info || !in_array($info[2], $allowed, true)) {
        throw new RuntimeException('Formato não aceito. Use JPG, PNG, WebP ou GIF.');
    }

    $dir = 'uploads/' . date('Y/m');
    if (!is_dir(ROOT . '/' . $dir) && !mkdir(ROOT . '/' . $dir, 0755, true)) {
        throw new RuntimeException('Não consegui criar a pasta de uploads.');
    }
    $base = slugify(pathinfo((string) $file['name'], PATHINFO_FILENAME));
    $name = mb_substr($base, 0, 50) . '-' . bin2hex(random_bytes(4));

    if ($info[2] === IMAGETYPE_GIF) {
        // GIF fica como está para não perder a animação
        $path = $dir . '/' . $name . '.gif';
        move_uploaded_file($file['tmp_name'], ROOT . '/' . $path);
        [$w, $h] = [$info[0], $info[1]];
        $mime = 'image/gif';
    } else {
        $src = imagecreatefromstring((string) file_get_contents($file['tmp_name']));
        if (!$src) {
            throw new RuntimeException('Não consegui ler a imagem.');
        }
        $src = fix_orientation($src, $file['tmp_name'], $info[2]);
        [$w, $h] = [imagesx($src), imagesy($src)];
        if ($w > MAX_IMAGE_WIDTH) {
            $nh = (int) round($h * MAX_IMAGE_WIDTH / $w);
            $resized = imagecreatetruecolor(MAX_IMAGE_WIDTH, $nh);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, MAX_IMAGE_WIDTH, $nh, $w, $h);
            imagedestroy($src);
            $src = $resized;
            [$w, $h] = [MAX_IMAGE_WIDTH, $nh];
        }
        imagepalettetotruecolor($src);
        imagesavealpha($src, true);
        $path = $dir . '/' . $name . '.webp';
        if (!imagewebp($src, ROOT . '/' . $path, 84)) {
            throw new RuntimeException('Não consegui salvar a imagem.');
        }
        imagedestroy($src);
        $mime = 'image/webp';
    }

    $id = db_insert('media', [
        'path'          => $path,
        'original_name' => mb_substr((string) $file['name'], 0, 255),
        'mime'          => $mime,
        'width'         => $w,
        'height'        => $h,
        'size'          => (int) filesize(ROOT . '/' . $path),
        'alt'           => $alt !== '' ? mb_substr($alt, 0, 255) : null,
        'uploaded_by'   => $userId,
        'created_at'    => now(),
    ]);
    return ['id' => $id, 'path' => $path, 'width' => $w, 'height' => $h];
}

// Fotos de celular vêm "deitadas" com a informação de giro no EXIF
function fix_orientation(GdImage $img, string $file, int $type): GdImage
{
    if ($type !== IMAGETYPE_JPEG || !function_exists('exif_read_data')) {
        return $img;
    }
    $exif = @exif_read_data($file);
    $angle = [3 => 180, 6 => -90, 8 => 90][(int) ($exif['Orientation'] ?? 1)] ?? 0;
    return $angle ? (imagerotate($img, $angle, 0) ?: $img) : $img;
}

function upload_error_message(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'A imagem é maior do que a hospedagem permite.',
        UPLOAD_ERR_PARTIAL => 'O envio foi interrompido. Tente de novo.',
        UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi escolhido.',
        default => 'Não foi possível enviar o arquivo.',
    };
}

function delete_media(int $id): void
{
    $media = db_one('SELECT * FROM media WHERE id = ?', [$id]);
    if (!$media) {
        return;
    }
    $file = ROOT . '/' . $media['path'];
    if (str_starts_with($media['path'], 'uploads/') && is_file($file)) {
        unlink($file);
    }
    db_exec('DELETE FROM media WHERE id = ?', [$id]);
}
