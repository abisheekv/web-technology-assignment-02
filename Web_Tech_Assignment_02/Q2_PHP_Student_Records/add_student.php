<?php
require_once 'db.php';

$problems = [];
$saved = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $roll   = trim($_POST['roll_no'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');

    if ($name === '' || $roll === '' || $email === '' || $course === '') {
        $problems[] = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $problems[] = "The email address is not valid.";
    } elseif (!in_array($course, PROGRAMMES, true)) {
        $problems[] = "Please choose a programme from the list.";
    }

    if (!$problems) {
        $dup = $conn->prepare("SELECT id FROM students WHERE roll_no = ? OR email = ?");
        $dup->execute([$roll, $email]);
        if ($dup->fetch()) {
            $problems[] = "A student with this roll number or email already exists.";
        }
    }

    if (!$problems) {
        try {
            $photo = storeUpload('profile_image', 'profile_image', ['image/jpeg', 'image/png'], 2 * 1024 * 1024);
            if ($photo) $saved[] = $photo;
            $cert = storeUpload('document', 'certificate', ['application/pdf', 'image/jpeg', 'image/png'], 5 * 1024 * 1024);
            if ($cert) $saved[] = $cert;
        } catch (RuntimeException $ex) {
            $problems[] = $ex->getMessage();
        }
    }

    if (!$problems) {
        try {
            $conn->beginTransaction();
            $conn->prepare("INSERT INTO students (name, roll_no, email, course) VALUES (?, ?, ?, ?)")
                 ->execute([$name, $roll, $email, $course]);
            $newId = $conn->lastInsertId();

            $ins = $conn->prepare("INSERT INTO student_documents
                (student_id, file_name, file_path, file_type, file_size, doc_category)
                VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($saved as $d) {
                $ins->execute([$newId, $d['file_name'], $d['file_path'], $d['file_type'], $d['file_size'], $d['doc_category']]);
            }
            $conn->commit();
            header("Location: index.php?msg=" . urlencode("Student added with " . count($saved) . " document(s)."));
            exit;
        } catch (PDOException $ex) {
            $conn->rollBack();
            foreach ($saved as $d) { @unlink(__DIR__ . '/' . $d['file_path']); }   // clean up orphan files
            $problems[] = "Database error: " . $ex->getMessage();
        }
    } else {
        foreach ($saved as $d) { @unlink(__DIR__ . '/' . $d['file_path']); }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Student</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="top-bar">
    <h1>KPRIET &ndash; Student Records Portal</h1>
    <nav class="menu"><a href="index.php">&larr; Back to Directory</a></nav>
  </header>
  <main class="container">
    <div class="panel">
      <h3>Add Student &amp; Upload Documents</h3>
      <?php if ($problems): ?>
        <div class="notice notice-error"><ul>
          <?php foreach ($problems as $p) echo '<li>' . htmlspecialchars($p) . '</li>'; ?>
        </ul></div>
      <?php endif; ?>

      <form method="POST" action="add_student.php" enctype="multipart/form-data">
        <div class="field"><label>Full Name *</label>
          <input type="text" name="name" required placeholder="e.g. Akilan MK" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"></div>
        <div class="field"><label>Roll Number *</label>
          <input type="text" name="roll_no" required placeholder="e.g. 24CS014" value="<?= htmlspecialchars($_POST['roll_no'] ?? '') ?>"></div>
        <div class="field"><label>Email Address *</label>
          <input type="email" name="email" required placeholder="e.g. akilan.mk@kpriet.ac.in" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></div>
        <div class="field"><label>Programme *</label>
          <select name="course" required>
            <option value="">-- Select Programme --</option>
            <?php foreach (PROGRAMMES as $p): ?>
              <option value="<?= htmlspecialchars($p) ?>" <?= (($_POST['course'] ?? '') === $p) ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="field"><label>Profile Photo (JPG/PNG, max 2 MB)</label>
          <input type="file" name="profile_image" accept="image/jpeg,image/png"></div>
        <div class="field"><label>Certificate / ID Proof (PDF/JPG/PNG, max 5 MB)</label>
          <input type="file" name="document" accept="application/pdf,image/jpeg,image/png"></div>
        <div class="form-buttons">
          <button type="submit" class="btn btn-primary">Save Student</button>
          <a href="index.php" class="btn btn-cancel">Cancel</a>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
