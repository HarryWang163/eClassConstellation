<?php
// 启动Session
session_start();

// 检查是否已登录
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// 重定向到登录页面
function redirectToLogin() {
    header('Location: /public/login.php');
    exit;
}

// 验证用户登录
function authenticate($username, $password) {
    require_once __DIR__ . '/../config/database.php';
    
    try {
        $db = getDB();
        $stmt = $db->prepare('SELECT id, username, password FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && $password == $user['password']) {
            // 登录成功，存储用户信息到Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

// 注销用户
function logout() {
    session_unset();
    session_destroy();
    redirectToLogin();
}

// 获取当前登录用户信息
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username']
        ];
    }
    return null;
}
?>