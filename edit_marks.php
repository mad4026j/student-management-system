<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT g.*, s.full_name, s.roll_no FROM grades g JOIN students s ON g.student_id = s.id WHERE g.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$grade_record = $stmt->get_result()->fetch_assoc();

if (!$grade_record) { die("Record target missing."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name']);
    $marks_obtained = intval($_POST['marks_obtained']);
    $total_marks = intval($_POST['total_marks']);

    if (!empty($subject_name) && $marks_obtained >= 0 && $total_marks > 0) {
        $update = $conn->prepare("UPDATE grades SET subject_name = ?, marks_obtained = ?, total_marks = ? WHERE id = ?");
        $update->bind_param("siii", $subject_name, $marks_obtained, $total_marks, $id);
        $update->execute();
        header('Location: manage_marks.php?msg=updated');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modify Marks</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; padding: 40px 0; }
        .form-card { max-width: 550px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; color: #7f8c8d; font-weight: bold; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { background: #3498db; color: white; border: none; padding: 12px 20px; font-weight: bold; cursor: pointer; border-radius: 5px; }
        .btn-cancel { background: #95a5a6; color: white; text-decoration: none; padding: 12px 20px; border-radius: 5px; margin-left: 10px; display: inline-block; font-weight: bold; }
    </style>
</head>
<body>
    <div class="form-card">
        <h2>Modify Score Allocation</h2>
        <form method="POST">
            <div class="form-group"><label>Student Target</label><input type="text" value="<?php echo htmlspecialchars($grade_record['full_name'] . ' (' . $grade_record['roll_no'] . ')'); ?>" disabled style="background: #f1f2f6;"></div>
            <div class="form-group"><label>Course Subject Title</label><input type="text" name="subject_name" value="<?php echo htmlspecialchars($grade_record['subject_name']); ?>" required></div>
            <div class="form-group"><label>Marks Scored</label><input type="number" name="marks_obtained" value="<?php echo $grade_record['marks_obtained']; ?>" min="0" required></div>
            <div class="form-group"><label>Maximum Scale Limit Score</label><input type="number" name="total_marks" value="<?php echo $grade_record['total_marks']; ?>" min="1" required></div>
            <button type="submit" class="btn-submit">Update Log Record</button>
            <a href="manage_marks.php" class="btn-cancel">Cancel</a>
        </form>
    </div>
</body>
</html>
