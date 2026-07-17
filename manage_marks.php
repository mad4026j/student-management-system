<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$filter_student = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

// CORRECTED JOIN MAPPING QUERY: Fixed row isolation mismatch
$sql = "SELECT g.id, g.subject_name, g.marks_obtained, g.total_marks, s.full_name, s.roll_no 
        FROM grades g 
        INNER JOIN students s ON g.student_id = s.id";
if ($filter_student > 0) {
    $sql .= " WHERE g.student_id = " . $filter_student;
}
$sql .= " ORDER BY g.id DESC";
$grades_result = $conn->query($sql);

$students_dropdown = $conn->query("SELECT id, full_name, roll_no FROM students ORDER BY full_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage All Marks</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; margin: 0; padding: 0; color: #2c3e50; }
        .navbar { background: #2c3e50; padding: 15px 40px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .back-btn { color: white; text-decoration: none; font-weight: bold; background: #34495e; padding: 8px 16px; border-radius: 4px; }
        .container { max-width: 1250px; margin: 30px auto; padding: 0 20px; }
        .filter-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .filter-form { display: flex; gap: 15px; align-items: flex-end; }
        select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-width: 250px; }
        .btn { padding: 10px 18px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-filter { background: #3498db; color: white; }
        .btn-clear { background: #95a5a6; color: white; }
        .card { background: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 14px; border-bottom: 1px solid #f1f2f6; }
        th { background-color: #34495e; color: white; }
        .btn-edit { background: #f1c40f; color: #2c3e50; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: bold; margin-right: 5px; }
        .btn-delete { background: #e74c3c; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: bold; }
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>Published Marks Directory</h2>
        <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>
    <div class="container">
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert-success"><?php echo ($_GET['msg']=='updated') ? "Marks updated successfully." : "Marks deleted successfully."; ?></div>
        <?php endif; ?>

        <div class="filter-card">
            <form method="GET" action="" class="filter-form">
                <div class="filter-group">
                    <select name="student_id">
                        <option value="">-- View All Registered Student Marks --</option>
                        <?php while($st = $students_dropdown->fetch_assoc()): ?>
                            <option value="<?php echo $st['id']; ?>" <?php echo ($filter_student == $st['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($st['full_name'] . " (" . $st['roll_no'] . ")"); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-filter">Filter View</button>
                <a href="manage_marks.php" class="btn btn-clear">Clear</a>
            </form>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Grade ID</th>
                        <th>Student Name</th>
                        <th>Roll Number</th>
                        <th>Subject Title</th>
                        <th>Marks Obtained</th>
                        <th>Max Marks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($grades_result && $grades_result->num_rows > 0): ?>
                        <?php while ($row = $grades_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['roll_no']); ?></td>
                                <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                <td><?php echo $row['marks_obtained']; ?></td>
                                <td><?php echo $row['total_marks']; ?></td>
                                <td>
                                    <a href="edit_marks.php?id=<?php echo $row['id']; ?>" class="btn-edit">Modify</a>
                                    <a href="delete_marks.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Delete this record?');">Remove</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; color: #7f8c8d; padding: 20px;">No custom marks published yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
