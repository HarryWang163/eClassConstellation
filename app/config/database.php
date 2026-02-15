<?php
define('DB_USER', value: 'root');
define('DB_PASS', '');
// define('DB_USER', 'eClass_web');
// define('DB_PASS', 'kGVOz9mF');
// 创建数据库连接
function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new PDO(
                'mysql:host=127.0.0.1;port=3306;dbname=eClassConstellation;charset=utf8mb4',
                // 'mysql:host=127.0.0.1;port=3307;dbname=eClass;charset=utf8mb4',
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