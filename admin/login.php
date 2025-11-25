<?php
/**
 * 信奥赛一本通答案 - 管理员登录
 * 开发者: SZY创新工作室
 */

require_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/custom.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 管理员登录</h1>
            <p><?php echo SITE_NAME; ?></p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <div class="form-group">
                <label>用户名</label>
                <input type="text" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label>密码</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">登录</button>
        </form>
        
        <div class="login-footer">
            <a href="../index.php">← 返回首页</a>
        </div>
    </div>
</body>
</html>
