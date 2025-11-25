<?php
/**
 * 信奥赛一本通答案 - 数据库安装程序
 * 开发者: SZY创新工作室
 */

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? 'ybt_answers';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? '';
    
    try {
        // 连接数据库
        $dsn = "mysql:host={$db_host};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 创建数据库
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$db_name}`");
        
        // 创建分类表（大部分）
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `categories` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `sort_order` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // 创建子分类表（小部分）
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `subcategories` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `category_id` INT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `sort_order` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // 创建章节表
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `chapters` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `subcategory_id` INT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `sort_order` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // 创建题目表
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `problems` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `pid` INT UNIQUE NOT NULL COMMENT '题号(4位数)',
                `chapter_id` INT NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `answer` TEXT COMMENT 'Markdown格式答案',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`chapter_id`) REFERENCES `chapters`(`id`) ON DELETE CASCADE,
                INDEX `idx_pid` (`pid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // 插入初始数据
        $pdo->exec("
            INSERT INTO `categories` (`name`, `sort_order`) VALUES
            ('一、语言及算法基础篇', 1),
            ('二、算法提高篇', 2),
            ('三、高手训练', 3),
            ('四、官方真题', 4)
        ");
        
        // 更新config.php文件
        $config_content = "<?php
/**
 * 信奥赛一本通答案 - 配置文件
 * 开发者: SZY创新工作室
 */

// 数据库配置
define('DB_HOST', '{$db_host}');
define('DB_NAME', '{$db_name}');
define('DB_USER', '{$db_user}');
define('DB_PASS', '{$db_pass}');
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
        \$dsn = \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=\" . DB_CHARSET;
        \$pdo = new PDO(\$dsn, DB_USER, DB_PASS);
        \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return \$pdo;
    } catch(PDOException \$e) {
        die(\"数据库连接失败: \" . \$e->getMessage());
    }
}

// 启动会话
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>";
        
        file_put_contents(__DIR__ . '/config.php', $config_content);
        
        $success = '数据库安装成功！请删除install.php文件以确保安全。';
        
    } catch(PDOException $e) {
        $error = '安装失败: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装 - 信奥赛一本通答案</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <h1>📚 信奥赛一本通答案</h1>
        <p class="subtitle">数据库安装向导</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label>数据库主机</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label>数据库名称</label>
                    <input type="text" name="db_name" value="ybt_answers" required>
                </div>
                
                <div class="form-group">
                    <label>数据库用户名</label>
                    <input type="text" name="db_user" value="root" required>
                </div>
                
                <div class="form-group">
                    <label>数据库密码</label>
                    <input type="password" name="db_pass">
                </div>
                
                <button type="submit" class="btn">开始安装</button>
            </form>
        <?php endif; ?>
        
        <div class="footer">
            由 SZY创新工作室 开发并维护
        </div>
    </div>
</body>
</html>
