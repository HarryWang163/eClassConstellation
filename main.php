<?php
// 引入认证工具和数据库
require_once __DIR__ . '/app/includes/auth.php';

// 检查是否已登录
if (!isLoggedIn()) {
    redirectToLogin();
}

// 获取当前用户ID
session_start();
$user_id = $_SESSION['user_id'] ?? 0;

// 查询 user_finished 状态
require_once __DIR__ . '/app/config/database.php';
$db = getDB();
$stmt = $db->prepare("SELECT user_finished FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_finished = $stmt->fetchColumn();

if ($user_finished == 1) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: splashs/splash1.php');
    exit;
}
?>