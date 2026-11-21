<?php
$uploadDir = __DIR__ . '/uploads/';

function back(string $param, string $text): void {
    header('Location: index.php?' . $param . '=' . urlencode($text));
    exit;
}

// Проверка превышения
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && empty($_FILES)
    && ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    back('error', 'Размер запроса больше post_max_size (' . ini_get('post_max_size') . ')');
}

$file = $_FILES['userfile'] ?? null;
if (!$file) {
    back('error', 'Файл не получен');
}

// Коды ошибок
switch ($file['error']) {
    case UPLOAD_ERR_NO_FILE:
        back('error', 'Файл не выбран');
    case UPLOAD_ERR_INI_SIZE:
        back('error', 'Файл больше upload_max_filesize (' . ini_get('upload_max_filesize') . ')');
    case UPLOAD_ERR_FORM_SIZE:
        back('error', 'Файл больше лимита формы');
    case UPLOAD_ERR_PARTIAL:
        back('error', 'Файл загружен частично');
    case UPLOAD_ERR_NO_TMP_DIR:
        back('error', 'Нет временной папки');
    case UPLOAD_ERR_CANT_WRITE:
        back('error', 'Не удалось записать файл');
    case UPLOAD_ERR_EXTENSION:
        back('error', 'Загрузка остановлена расширением PHP');
}

// Проверка размера
$maxSize = 5 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    back('error', 'Файл больше 5 МБ');
}

// Проверка расширения
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'docx', 'xlsx', 'zip'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    back('error', 'Недопустимый тип файла: .' . $ext);
}

// Безопасное имя
$name = basename($file['name']);
$name = preg_replace('/[^\w.\-]/u', '_', $name);
if (file_exists($uploadDir . $name)) {
    $name = time() . '_' . $name;
}

if (move_uploaded_file($file['tmp_name'], $uploadDir . $name)) {
    back('msg', 'Файл загружен');
}
back('error', 'Не удалось сохранить файл');