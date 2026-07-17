<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $roll_no = trim($_POST['roll_no']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $semester = intval($_POST['semester']);

    if (!empty($username) && !empty($password) && !empty($roll_no) && !empty($full_name)) {
        $conn->begin_transaction();
        try {
            $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check->bind_param("s", $username);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                throw new Exception("Username already exists.");
            }

            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $role = 'student';

            $ins_user = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $ins_user->bind_param("sss", $username, $hashed_pass, $role);
            $ins_user->execute();
            $new_user_id = $conn->insert_id;

            $ins_student = $conn->prepare("INSERT INTO students (user_id, roll_no, full_name, email, course, semester) VALUES (?, ?, ?, ?, ?, ?)");
            $ins_student->bind_param("issssi", $new_user_id, $roll_no, $full_name, $email, $course, $semester);
            $ins_student->execute();

            $conn->commit();
            header('Location: admin_dashboard.php?msg=added');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    } else {
        $error = "Please fill in all layout rows.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student Account</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; padding: 40px 0; margin: 0; }
        .form-card { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; color: #7f8c8d; font-weight: bold; font-size: 14px; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { background: #2ecc71; color: white; border: none; padding: 12px 25px; font-weight: bold; cursor: pointer; border-radius: 5px; }
        .btn-cancel { background: #95a5a6; color: white; text-decoration: none; padding: 12px 25px; border-radius: 5px; margin-left: 10px; display: inline-block; font-weight: bold; }
        .error { color: #e74c3c; background: #fce4e4; padding: 12px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="form-card">
        <h2>Register New Student</h2>
        <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group"><label>Portal Username Login ID</label><input type="text" name="username" required></div>
            <div class="form-group"><label>Portal Access Password</label><input type="password" name="password" required></div>
            <div class="form-group"><label>Full Student Name</label><input type="text" name="full_name" required></div>
            <div class="form-group"><label>Official Email Address</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Enrollment Roll Number</label><input type="text" name="roll_no" required></div>
            <div class="form-group">
                <label>Stream / Degree Track</label>
                <select name="course">
                    <option value="BCA">BCA</option>
                    <option value="BBA">BBA</option>
                    <option value="B.Sc IT">B.Sc IT</option>
                </select>
            </div>
            <div class="form-group">
                <label>Current Term Semester</label>
                <select name="semester">
                    <?php for($i=1; $i<=6; $i++): ?><option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option><?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="btn-submit">Register Profile</button>
            <a href="admin_dashboard.php" class="btn-cancel">Cancel</a>
        </form>
    </div>
</body>
</html>
