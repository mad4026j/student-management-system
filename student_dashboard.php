<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$student_stmt = $conn->prepare("SELECT * FROM students WHERE user_id = ?");
$student_stmt->bind_param("i", $user_id);
$student_stmt->execute();
$student_res = $student_stmt->get_result();

if($student_res->num_rows > 0) {
    $student = $student_res->fetch_assoc();
    $student_id = $student['id'];
} else {
    die("Student profile record not found. Please contact the administrator.");
}

$grade_stmt = $conn->prepare("SELECT * FROM grades WHERE student_id = ? ORDER BY id DESC");
$grade_stmt->bind_param("i", $student_id);
$grade_stmt->execute();
$grades_result = $grade_stmt->get_result();

$total_obtained = 0; $total_maximum = 0; $subject_count = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; margin: 0; padding: 0; color: #2c3e50; }
        .header-bar { background: #2c3e50; padding: 18px 40px; color: #fff; display: flex; justify-content: space-between; align-items: center; }
        .logout-btn { color: #fff; text-decoration: none; font-weight: bold; background: #e74c3c; padding: 10px 20px; border-radius: 5px; }
        .main-content { max-width: 1100px; margin: 40px auto; padding: 0 25px; }
        .info-card { background: #fff; border-radius: 8px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        h3 { border-bottom: 2px solid #f1f2f6; padding-bottom: 12px; margin-top: 0; color: #34495e; }
        .profile-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
        .grid-item { font-size: 15px; }
        .grid-item strong { color: #7f8c8d; display: inline-block; width: 140px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 14px 18px; border-bottom: 1px solid #f1f2f6; }
        th { background-color: #f8f9fa; color: #34495e; }
        .badge { background: #2ecc71; color: white; padding: 4px 10px; border-radius: 4px; font-weight: bold; }
        .summary-row { background-color: #ecf0f1; font-weight: bold; }
        .summary-box-container { display: flex; gap: 20px; margin-top: 20px; justify-content: flex-end; }
        .summary-box { background: #34495e; color: white; padding: 15px 25px; border-radius: 6px; text-align: center; }
        .summary-box span { display: block; font-size: 22px; font-weight: bold; color: #2ecc71; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="header-bar">
        <h2>Welcome, <?php echo htmlspecialchars($student['full_name']); ?></h2>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    <div class="main-content">
        <div class="info-card">
            <h3>Academic Student Profile</h3>
            <div class="profile-grid">
                <div class="grid-item"><strong>Roll Number:</strong> <?php echo htmlspecialchars($student['roll_no']); ?></div>
                <div class="grid-item"><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></div>
                <div class="grid-item"><strong>Stream:</strong> <?php echo htmlspecialchars($student['course']); ?></div>
                <div class="grid-item"><strong>Current Semester:</strong> Semester <?php echo htmlspecialchars($student['semester']); ?></div>
            </div>
        </div>

        <div class="info-card">
            <h3>Semester Performance Report Card</h3>
            <table>
                <thead>
                    <tr>
                        <th>Subject Title</th>
                        <th>Marks Obtained</th>
                        <th>Maximum Marks</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($grades_result && $grades_result->num_rows > 0): ?>
                        <?php while($row = $grades_result->fetch_assoc()): 
                            $percentage = ($row['marks_obtained'] / $row['total_marks']) * 100;
                            $total_obtained += $row['marks_obtained'];
                            $total_maximum += $row['total_marks'];
                            $subject_count++;
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['subject_name']); ?></strong></td>
                                <td><?php echo $row['marks_obtained']; ?></td>
                                <td><?php echo $row['total_marks']; ?></td>
                                <td><span class="badge"><?php echo number_format($percentage, 1); ?>%</span></td>
                            </tr>
                        <?php endwhile; ?>
                        <tr class="summary-row">
                            <td>AGGREGATE SCORES</td>
                            <td><?php echo $total_obtained; ?></td>
                            <td><?php echo $total_maximum; ?></td>
                            <td><?php echo number_format(($total_obtained / $total_maximum) * 100, 1); ?>%</td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align: center; color: #7f8c8d; padding: 20px;">No assessment marks published yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($subject_count > 0): 
                $overall_percentage = ($total_obtained / $total_maximum) * 100;
                $cgpa = min(($overall_percentage / 9.5), 10.0);
            ?>
                <div class="summary-box-container">
                    <div class="summary-box">Overall Average <span><?php echo number_format($overall_percentage, 1); ?>%</span></div>
                    <div class="summary-box">Calculated CGPA <span><?php echo number_format($cgpa, 2); ?> / 10</span></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
