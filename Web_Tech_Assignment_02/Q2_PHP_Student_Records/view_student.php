<?php
require_once 'db.php';

$id = $_GET['id'] ?? '';
if (!ctype_digit((string)$id)) {
    header("Location: index.php?err=" . urlencode("Invalid student ID."));
    exit;
}
$q = $conn->prepare("SELECT * FROM students WHERE id = ?");
$q->execute([$id]);
$student = $q->fetch();
if (!$student) {
    header("Location: index.php?err=" . urlencode("Student not found."));
    exit;
}
$d = $conn->prepare("SELECT * FROM student_documents WHERE student_id = ? ORDER BY uploaded_at DESC");
$d->execute([$id]);
$docs = $d->fetchAll();

$photo = null;
foreach ($docs as $row) {
    if ($row['doc_category'] === 'profile_image' && file_exists($row['file_path'])) { $photo = $row['file_path']; break; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student - <?= htmlspecialchars($student['name']) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="top-bar">
    <h1>KPRIET &ndash; Student Records Portal</h1>
    <nav class="menu">
      <a href="index.php">&larr; Back to Directory</a>
      <a href="edit_student.php?id=<?= (int)$student['id'] ?>">Edit</a>
    </nav>
  </header>
  <main class="container">
    <?php if (isset($_GET['msg'])): ?><div class="notice notice-success"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
    <div class="panel">
      <div class="profile-row">
        <?php if ($photo): ?>
          <img src="<?= htmlspecialchars($photo) ?>" alt="Photo" class="profile-img">
        <?php else: ?>
          <div class="profile-img profile-letter"><?= strtoupper(substr($student['name'], 0, 2)) ?></div>
        <?php endif; ?>
        <div class="profile-text">
          <h3><?= htmlspecialchars($student['name']) ?></h3>
          <p><strong>Roll Number:</strong> <?= htmlspecialchars($student['roll_no']) ?></p>
          <p><strong>Email Address:</strong> <?= htmlspecialchars($student['email']) ?></p>
          <p><strong>Programme:</strong> <span class="chip"><?= htmlspecialchars($student['course']) ?></span></p>
        </div>
      </div>
    </div>
    <div class="panel">
      <h3>Uploaded Documents &amp; Metadata (<?= count($docs) ?>)</h3>
      <?php if (empty($docs)): ?>
        <p>No documents uploaded.</p>
      <?php else: ?>
      <table>
        <thead><tr><th>Category</th><th>File Name</th><th>MIME Type</th><th>Size</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($docs as $x): ?>
          <tr>
            <td><span class="chip chip-warm"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $x['doc_category']))) ?></span></td>
            <td><?= htmlspecialchars($x['file_name']) ?></td>
            <td><code><?= htmlspecialchars($x['file_type']) ?></code></td>
            <td><?= round($x['file_size'] / 1024, 1) ?> KB</td>
            <td><a href="<?= htmlspecialchars($x['file_path']) ?>" download class="btn btn-view">Download</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
