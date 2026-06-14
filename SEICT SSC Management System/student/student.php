<?php
session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
    header("Location: student_dashboard.php");
    exit;
}

header("Location: student/student login.php");
exit;
?>
