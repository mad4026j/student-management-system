<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$filter_semester = isset($_GET['semester']) ? trim($_GET['semester']) : '';
$filter_course = isset($_GET['course']) ? trim($_GET['course']) : '';

$sql = "SELECT * FROM students WHERE 1=1";
if ($filter_semester !== '') {
    $sql .= " AND semester = " . intval($filter_semester);
}
if ($filter_course !== '') {
    $sql .= " AND course = '" . $conn->real_escape_string($filter_course) . "'";
}
$sql .= " ORDER BY id DESC";
$result = $conn->query($sql);

$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$total_grades = $conn->query("SELECT COUNT(*) as count FROM grades")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; margin: 0; padding: 0; color: #2c3e50; }
        .navbar { background: #2c3e50; padding: 15px 40px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .logout-btn { color: white; text-decoration: none; font-weight: bold; background: #e74c3c; padding: 8px 16px; border-radius: 4px; }
        .container { max-width: 1300px; margin: 30px auto; padding: 0 20px; }
        .stats-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 25px; }
        .stats-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid #3498db; }
        .stats-card.grades { border-left-color: #2ecc71; }
        .stats-card h4 { margin: 0; color: #7f8c8d; font-size: 12px; text-transform: uppercase; }
        .stats-card .value { font-size: 28px; font-weight: bold; }
        .top-action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .action-buttons { display: flex; gap: 12px; }
        .btn { text-decoration: none; padding: 12px 20px; border-radius: 5px; font-weight: bold; font-size: 14px; color: white; }
        .btn-add-student { background: #2ecc71; }
        .btn-add-marks { background: #3498db; }
        .btn-manage-marks { background: #9b59b6; }
        .filter-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .filter-form { display: flex; gap: 20px; align-items: flex-end; }
        .filter-group { display: flex; flex-direction: column; gap: 6px; }
        .filter-group label { font-size: 12px; font-weight: bold; color: #7f8c8d; }
        .filter-group select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-width: 180px; }
        .btn-filter { background: #34495e; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .btn-clear { background: #95a5a6; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; }
        .card { background: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 14px; border-bottom: 1px solid #f1f2f6; }
        th { background-color: #34495e; color: white; }
        .btn-action { text-decoration: none; padding: 6px 14px; border-radius: 4px; font-size: 13px; font-weight: bold; color: white; margin-right: 5px; }
        .btn-edit { background: #f1c40f; color: #2c3e50; }
        .btn-delete { background: #e74c3c; }
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; text-align: center; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>Admin Management Panel</h2>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    <div class="container">
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert-success">
                <?php
                if ($_GET['msg'] == 'added') echo "Student profile registered and activated successfully.";
                if ($_GET['msg'] == 'updated') echo "Student database row updated successfully.";
                if ($_GET['msg'] == 'deleted') echo "Student profile and marks permanently deleted.";
                if ($_GET['msg'] == 'marks_added') echo "Academic marks details saved successfully.";
                ?>
            </div>
        <?php endif; ?>

        <div class="stats-row">
            <div class="stats-card"><h4>Total Profiles</h4><div class="value"><?php echo $total_students; ?></div></div>
            <div class="stats-card grades"><h4>Total Marks Logs</h4><div class="value"><?php echo $total_grades; ?></div></div>
        </div>

        <div class="top-action-bar">
            <h3>Student Master Directory</h3>
            <div class="action-buttons">
                <a href="add_student.php" class="btn btn-add-student">+ Register Student</a>
                <a href="add_marks.php" class="btn btn-add-marks">+ Input Marks</a>
                <a href="manage_marks.php" class="btn btn-manage-marks">⚙ Manage All Marks</a>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="" class="filter-form">
                <div class="filter-group">
                    <label>Stream</label>
                    <select name="course">
                        <option value="">-- All Streams --</option>
                        <option value="BCA" <?php echo ($filter_course == 'BCA') ? 'selected' : ''; ?>>BCA</option>
                        <option value="BBA" <?php echo ($filter_course == 'BBA') ? 'selected' : ''; ?>>BBA</option>
                        <option value="B.Sc IT" <?php echo ($filter_course == 'B.Sc IT') ? 'selected' : ''; ?>>B.Sc IT</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Semester</label>
                    <select name="semester">
                        <option value="">-- All Semesters --</option>
                        <?php for($i=1; $i<=6; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($filter_semester == (string)$i) ? 'selected' : ''; ?>>Semester <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" class="btn-filter">Apply Filters</button>
                <a href="admin_dashboard.php" class="btn-clear">Reset</a>
            </form>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Roll Number</th>
                        <th>Full Name</th>
                        <th>Email ID</th>
                        <th>Stream</th>
                        <th>Semester</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['roll_no']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['course']); ?></td>
                                <td>Semester <?php echo htmlspecialchars($row['semester']); ?></td>
                                <td>
                                    <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit">Edit</a>
                                    <a href="delete_student.php?id=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Permanently delete student?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; color: #7f8c8d; padding: 20px;">No records found matching filters.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
