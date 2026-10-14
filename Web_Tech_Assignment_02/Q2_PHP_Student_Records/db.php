<?php
$host = 'localhost';
$schema = 'campus_portal';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$schema;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $ex) {
    die("Unable to connect to database: " . htmlspecialchars($ex->getMessage()));
}

const PROGRAMMES = [
    'B.E. Computer Science and Engineering',
    'B.Tech Information Technology',
    'B.Tech Artificial Intelligence and Data Science',
    'B.E. Electronics and Communication Engineering',
];

/** Validate and store one uploaded file. Returns metadata array, or null if no file was chosen. */
function storeUpload(string $field, string $category, array $mimes, int $maxBytes): ?array
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Upload failed for $category (error code {$f['error']}).");
    }
    if ($f['size'] > $maxBytes) {
        throw new RuntimeException("The $category file is larger than " . ($maxBytes / 1048576) . " MB.");
    }
    $detector = new finfo(FILEINFO_MIME_TYPE);
    $mime = $detector->file($f['tmp_name']);
    if (!in_array($mime, $mimes, true)) {
        throw new RuntimeException("File type $mime is not allowed for $category.");
    }
    $dir = __DIR__ . '/uploads/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $ext  = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $safe = $category . '_' . time() . '_' . uniqid() . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dir . $safe)) {
        throw new RuntimeException("Could not save the uploaded file.");
    }
    return [
        'file_name'    => $f['name'],
        'file_path'    => 'uploads/' . $safe,
        'file_type'    => $mime,
        'file_size'    => $f['size'],
        'doc_category' => $category,
    ];
}
?>
