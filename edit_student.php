<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) { die("Target profile index missing."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $semester = intval($_POST['semester']);

    if (!empty($full_name) && !empty($email)) {
        $update_stmt = $conn->prepare("UPDATE students SET full_name = ?, email = ?, course = ?, semester = ? WHERE id = ?");
        $update_stmt->bind_param("sssii", $full_name, $email, $course, $semester, $id);
        $update_stmt->execute();
        header('Location: admin_dashboard.php?msg=updated');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; padding: 40px 0; }
        .form-card { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; color: #7f8c8d; font-weight: bold; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { background: #f1c40f; color: #2c3e50; border: none; padding: 12px 25px; font-weight: bold; cursor: pointer; border-radius: 5px; }
        .btn-cancel { background: #95a5a6; color: white; text-decoration: none; padding: 12px 25px; border-radius: 5px; margin-left: 10px; display: inline-block; font-weight: bold; }
    </style>
</head>
<body>
    <div class="form-card">
        <h2>Modify Student Directory Data</h2>
        <form method="POST">
            <div class="form-group"><label>Enrollment Roll Number (Locked)</label><input type="text" value="<?php echo htmlspecialchars($student['roll_no']); ?>" disabled style="background:#f1f2f6;"></div>
            <div class="form-group"><label>Modify Full Name</label><input type="text" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required></div>
            <div class="form-group"><label>Modify Email Address</label><input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required></div>
            <div class="form-group">
                <label>Stream Track</label>
                <select name="course">
                    <option value="BCA" <?php echo ($student['course'] == 'BCA') ? 'selected' : ''; ?>>BCA</option>
                    <option value="BBA" <?php echo ($student['course'] == 'BBA') ? 'selected' : ''; ?>>BBA</option>
                    <option value="B.Sc IT" <?php echo ($student['course'] == 'B.Sc IT') ? 'selected' : ''; ?>>B.Sc IT</option>
                </select>
            </div>
            <div class="form-group">
                <label>Semester</label>
                <select name="semester">
                    <?php for($i=1; $i<=6; $i++): ?><option value="<?php echo $i; ?>" <?php echo ($student['semester'] == $i) ? 'selected' : ''; ?>>Semester <?php echo $i; ?></option><?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="btn-submit">Save System Updates</button>
            <a href="admin_dashboard.php" class="btn-cancel">Cancel</a>
        </form>
    </div>
</body>
</html>
