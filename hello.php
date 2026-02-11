<?php
session_start();
echo "<h1>欢迎, " . ($_SESSION['username'] ?? '游客') . "!</h1>";
echo "<p>会话ID: " . session_id() . "</p>";
echo "<p>用户ID: " . ($_SESSION['user_id'] ?? '未设置') . "</p>";
?>