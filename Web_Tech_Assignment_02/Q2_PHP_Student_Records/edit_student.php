<?php
require_once 'db.php';

$id = $_GET['id'] ?? '';
if (!ctype_digit((string)$id)) { header("Location: index.php"); exit; }

$q = $conn->prepare("SELECT * FROM students WHERE id = ?");
$q->execute([$id]);
$student = $q->fetch();
if (!$student) { header("Location: index.php?err=" . urlencode("Student not found.")); exit; }

$problems = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $roll   = trim($_POST['roll_no'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');

    if ($name === '' || $roll === '' || $email === '' || $course === '') {
        $problems[] = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $problems[] = "The email address is not valid.";
    }

    if (!$problems) {
        $dup = $conn->prepare("SELECT id FROM students WHERE (roll_no = ? OR email = ?) AND id <> ?");
        $dup->execute([$roll, $email, $id]);
        if ($dup->fetch()) { $problems[] = "Another student already uses this roll number or email."; }
    }

    $newPhoto = null;
    if (!$problems) {
        try {
            $newPhoto = storeUpload('profile_image', 'profile_image', ['image/jpeg', 'image/png'], 2 * 1024 * 1024);
        } catch (RuntimeException $ex) {
            $problems[] = $ex->getMessage();
        }
    }

    if (!$problems) {
        try {
            $conn->beginTransaction();
            $conn->prepare("UPDATE students SET name = ?, roll_no = ?, email = ?, course = ? WHERE id = ?")
                 ->execute([$name, $roll, $email, $course, $id]);

            $oldFile = null;
            if ($newPhoto) {
                $old = $conn->prepare("SELECT id, file_path FROM student_documents WHERE student_id = ? AND doc_category = 'profile_image'");
                $old->execute([$id]);
                $prev = $old->fetch();
                if ($prev) {
                    $oldFile = $prev['file_path'];
                    $conn->prepare("UPDATE student_documents SET file_name=?, file_path=?, file_type=?, file_size=?, uploaded_at=NOW() WHERE id=?")
                         ->execute([$newPhoto['file_name'], $newPhoto['file_path'], $newPhoto['file_type'], $newPhoto['file_size'], $prev['id']]);
                } else {
                    $conn->prepare("INSERT INTO student_documents (student_id, file_name, file_path, file_type, file_size, doc_category) VALUES (?, ?, ?, ?, ?, 'profile_image')")
                         ->execute([$id, $newPhoto['file_name'], $newPhoto['file_path'], $newPhoto['file_type'], $newPhoto['file_size']]);
                }
            }
            $conn->commit();
            if ($oldFile && file_exists(__DIR__ . '/' . $oldFile)) { @unlink(__DIR__ . '/' . $oldFile); }
            header("Location: view_student.php?id=$id&msg=" . urlencode("Student details updated."));
            exit;
        } catch (PDOException $ex) {
            $conn->rollBack();
            if ($newPhoto) { @unlink(__DIR__ . '/' . $newPhoto['file_path']); }
            $problems[] = "Database error: " . $ex->getMessage();
        }
    }
    $student = array_merge($student, ['name' => $name, 'roll_no' => $roll, 'email' => $email, 'course' => $course]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit - <?= htmlspecialchars($student['name']) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="top-bar">
    <h1>KPRIET &ndash; Student Records Portal</h1>
    <nav class="menu"><a href="index.php">&larr; Back to Directory</a></nav>
  </header>
  <main class="container">
    <div class="panel">
      <h3>Edit Student Details</h3>
      <?php if ($problems): ?>
        <div class="notice notice-error"><ul>
          <?php foreach ($problems as $p) echo '<li>' . htmlspecialchars($p) . '</li>'; ?>
        </ul></div>
      <?php endif; ?>
      <form method="POST" action="edit_student.php?id=<?= (int)$student['id'] ?>" enctype="multipart/form-data">
        <div class="field"><label>Full Name</label>
          <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required></div>
        <div class="field"><label>Roll Number</label>
          <input type="text" name="roll_no" value="<?= htmlspecialchars($student['roll_no']) ?>" required></div>
        <div class="field"><label>Email Address</label>
          <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required></div>
        <div class="field"><label>Programme</label>
          <select name="course" required>
            <?php foreach (PROGRAMMES as $p): ?>
              <option value="<?= htmlspecialchars($p) ?>" <?= $student['course'] === $p ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="field"><label>Replace Photo (optional)</label>
          <input type="file" name="profile_image" accept="image/jpeg,image/png"></div>
        <div class="form-buttons">
          <button type="submit" class="btn btn-edit">Save Changes</button>
          <a href="view_student.php?id=<?= (int)$student['id'] ?>" class="btn btn-cancel">Cancel</a>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
