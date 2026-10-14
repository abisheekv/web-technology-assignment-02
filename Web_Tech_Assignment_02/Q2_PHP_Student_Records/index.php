<?php
require_once 'db.php';

$sql = "SELECT s.*, d.file_path AS photo_url
        FROM students s
        LEFT JOIN student_documents d
               ON d.student_id = s.id AND d.doc_category = 'profile_image'
        ORDER BY s.id DESC";
$rows = $conn->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Records - Admin Panel</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="top-bar">
    <h1>KPRIET &ndash; Student Records Portal</h1>
    <nav class="menu">
      <a href="index.php">Students Directory</a>
      <a href="add_student.php">+ Add Student</a>
    </nav>
  </header>

  <main class="container">
    <div class="title-row">
      <h2>Enrolled Student Directory (PHP &amp; MySQL)</h2>
      <a href="add_student.php" class="btn btn-primary">+ Add New Student</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="notice notice-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['err'])): ?>
      <div class="notice notice-error"><?= htmlspecialchars($_GET['err']) ?></div>
    <?php endif; ?>

    <div class="panel">
      <h3>Student Records (<?= count($rows) ?>)</h3>
      <?php if (empty($rows)): ?>
        <p>No student records found.</p>
      <?php else: ?>
      <table>
        <thead>
          <tr><th>Photo</th><th>Roll No</th><th>Full Name</th><th>Programme</th><th>Email</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td>
              <?php if (!empty($r['photo_url']) && file_exists($r['photo_url'])): ?>
                <img src="<?= htmlspecialchars($r['photo_url']) ?>" alt="Photo" class="thumb">
              <?php else: ?>
                <div class="thumb-letter"><?= strtoupper(substr($r['name'], 0, 2)) ?></div>
              <?php endif; ?>
            </td>
            <td><strong><?= htmlspecialchars($r['roll_no']) ?></strong></td>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td><span class="chip"><?= htmlspecialchars($r['course']) ?></span></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td>
              <div class="actions">
                <a href="view_student.php?id=<?= (int)$r['id'] ?>" class="btn btn-view">View</a>
                <a href="edit_student.php?id=<?= (int)$r['id'] ?>" class="btn btn-edit">Edit</a>
                <a href="delete_student.php?id=<?= (int)$r['id'] ?>" class="btn btn-delete"
                   onclick="return confirm('Remove <?= htmlspecialchars($r['name'], ENT_QUOTES) ?> and all uploaded files?');">Delete</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
