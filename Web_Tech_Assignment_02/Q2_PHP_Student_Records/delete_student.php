<?php
require_once 'db.php';

$id = $_GET['id'] ?? '';
if (!ctype_digit((string)$id)) {
    header("Location: index.php?err=" . urlencode("Invalid student ID."));
    exit;
}

$files = $conn->prepare("SELECT file_path FROM student_documents WHERE student_id = ?");
$files->execute([$id]);
foreach ($files->fetchAll() as $f) {
    $full = __DIR__ . '/' . $f['file_path'];
    if (is_file($full)) { @unlink($full); }
}

// ON DELETE CASCADE removes the student_documents rows automatically
$conn->prepare("DELETE FROM students WHERE id = ?")->execute([$id]);
header("Location: index.php?msg=" . urlencode("Student and uploaded files deleted."));
exit;
