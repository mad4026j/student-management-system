<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id']);
    $subject_name = trim($_POST['subject_name']);
    $marks_obtained = intval($_POST['marks_obtained']);
    $total_marks = intval($_POST['total_marks']);

    if ($student_id > 0 && !empty($subject_name) && $marks_obtained >= 0 && $total_marks > 0) {
        $stmt = $conn->prepare("INSERT INTO grades (student_id, subject_name, marks_obtained, total_marks) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isii", $student_id, $subject_name, $marks_obtained, $total_marks);
        $stmt->execute();
        header('Location: admin_dashboard.php?msg=marks_added');
        exit();
    } else {
        $error = "Please fill in all grading options.";
    }
}

$students_dropdown = $conn->query("SELECT id, full_name, roll_no FROM students ORDER BY full_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Marks</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; padding: 40px 0; }
        .form-card { max-width: 550px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; color: #7f8c8d; font-weight: bold; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { background: #3498db; color: white; border: none; padding: 12px 25px; font-weight: bold; cursor: pointer; border-radius: 5px; }
        .btn-cancel { background: #95a5a6; color: white; text-decoration: none; padding: 12px 25px; border-radius: 5px; margin-left: 10px; display: inline-block; font-weight: bold; }
        .error { color: #e74c3c; background: #fce4e4; padding: 12px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="form-card">
        <h2>Publish Academic Course Marks</h2>
        <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Select Target Student</label>
                <select name="student_id" required>
                    <option value="">-- Choose Profile Record --</option>
                    <?php while($st = $students_dropdown->fetch_assoc()): ?>
                        <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['full_name'] . " (".$st['roll_no'].")"); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group"><label>Subject Title</label><input type="text" name="subject_name" required placeholder="e.g. PHP Web Programming"></div>
            <div class="form-group"><label>Marks Scored</label><input type="number" name="marks_obtained" min="0" required></div>
            <div class="form-group"><label>Maximum Limit Mark</label><input type="number" name="total_marks" value="100" required></div>
            <button type="submit" class="btn-submit">Publish Grade</button>
            <a href="admin_dashboard.php" class="btn-cancel">Go Back</a>
        </form>
    </div>
</body>
</html>
