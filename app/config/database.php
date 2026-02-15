<?php
// 数据库配置
define('DB_USER', 'eClass_constellation');
define('DB_PASS', 'b.z4Xf*G*9v(0Vlt');
define('DB_HOST', '192.168.1.23');
define('DB_PORT', '3307');
define('DB_NAME', 'eClassConstellation');

// 创建数据库连接
function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new PDO(
                'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch(PDOException $e) {
            die("数据库连接失败: " . $e->getMessage());
        }
    }
    return $db;
}
?>