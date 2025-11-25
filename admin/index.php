<?php
/**
 * 信奥赛一本通答案 - 管理控制台
 * 开发者: SZY创新工作室
 */

require_once '../config.php';

// 检查登录
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// 处理登出
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$pdo = getDBConnection();

// 获取统计数据
$stats = [
    'categories' => $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
    'subcategories' => $pdo->query("SELECT COUNT(*) FROM subcategories")->fetchColumn(),
    'chapters' => $pdo->query("SELECT COUNT(*) FROM chapters")->fetchColumn(),
    'problems' => $pdo->query("SELECT COUNT(*) FROM problems")->fetchColumn(),
];

// 获取最近更新的题目
$recent_problems = $pdo->query("
    SELECT p.pid, p.title, p.updated_at, ch.name as chapter_name
    FROM problems p
    LEFT JOIN chapters ch ON p.chapter_id = ch.id
    ORDER BY p.updated_at DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理控制台 - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/custom.css">
</head>
<body class="admin-page">
    <div class="admin-header">
        <div class="container">
            <h1>⚙️ 管理控制台</h1>
            <div class="admin-user">
                <span>欢迎，<?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="?logout=1" class="btn-logout">退出</a>
            </div>
        </div>
    </div>

    <div class="admin-nav">
        <div class="container">
            <a href="index.php" class="nav-item active">控制台</a>
            <a href="category_manage.php" class="nav-item">分类管理</a>
            <a href="problem_manage.php" class="nav-item">题目管理</a>
            <a href="../index.php" class="nav-item" target="_blank">查看网站</a>
        </div>
    </div>

    <div class="container admin-container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['categories']; ?></div>
                    <div class="stat-label">大分类</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📑</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['subcategories']; ?></div>
                    <div class="stat-label">小分类</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📖</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['chapters']; ?></div>
                    <div class="stat-label">章节</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✏️</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $stats['problems']; ?></div>
                    <div class="stat-label">题目</div>
                </div>
            </div>
        </div>

        <div class="admin-section">
            <h2>最近更新的题目</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>题号</th>
                            <th>标题</th>
                            <th>章节</th>
                            <th>更新时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_problems) > 0): ?>
                            <?php foreach ($recent_problems as $problem): ?>
                                <tr>
                                    <td>#<?php echo $problem['pid']; ?></td>
                                    <td><?php echo htmlspecialchars($problem['title']); ?></td>
                                    <td><?php echo htmlspecialchars($problem['chapter_name']); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($problem['updated_at'])); ?></td>
                                    <td>
                                        <a href="../problem_show.php?pid=<?php echo $problem['pid']; ?>" target="_blank" class="btn-link">查看</a>
                                        <a href="problem_manage.php?edit=<?php echo $problem['pid']; ?>" class="btn-link">编辑</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">暂无题目</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-section">
            <h2>快速操作</h2>
            <div class="quick-actions">
                <a href="problem_manage.php?action=add" class="action-card">
                    <div class="action-icon">➕</div>
                    <div class="action-title">添加题目</div>
                </a>
                <a href="category_manage.php?action=add_chapter" class="action-card">
                    <div class="action-icon">📝</div>
                    <div class="action-title">添加章节</div>
                </a>
                <a href="category_manage.php" class="action-card">
                    <div class="action-icon">🗂️</div>
                    <div class="action-title">管理分类</div>
                </a>
            </div>
        </div>
    </div>

    <footer class="admin-footer">
        <p>&copy; 2024 <?php echo DEVELOPER; ?></p>
    </footer>

    <script src="../js/admin.js"></script>
</body>
</html>
