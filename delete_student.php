<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $get_user = $conn->prepare("SELECT user_id FROM students WHERE id = ?");
    $get_user->bind_param("i", $id);
    $get_user->execute();
    $res = $get_user->get_result()->fetch_assoc();

    if ($res) {
        $user_id = $res['user_id'];
        $del_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $del_stmt->bind_param("i", $user_id);
        $del_stmt->execute();
    }
}
header('Location: admin_dashboard.php?msg=deleted');
exit();
?>
