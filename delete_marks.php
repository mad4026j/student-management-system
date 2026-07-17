<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $del = $conn->prepare("DELETE FROM grades WHERE id = ?");
    $del->bind_param("i", $id);
    $del->execute();
}
header('Location: manage_marks.php?msg=deleted');
exit();
?>
