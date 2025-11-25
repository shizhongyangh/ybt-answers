<?php
/**
 * 信奥赛一本通答案 - 配置文件
 * 开发者: SZY创新工作室
 */

// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'ybt_answers');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// 网站配置
define('SITE_NAME', '信奥赛一本通答案');
define('SITE_URL', 'https://ybt.szystudio.cn');
define('DEVELOPER', 'SZY创新工作室');

// 管理员配置
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', '12345678');

// 数据库连接
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die("数据库连接失败: " . $e->getMessage());
    }
}

// 启动会话
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
