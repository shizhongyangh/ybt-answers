<?php
/**
 * 信奥赛一本通答案 - 答案详情页
 * 开发者: SZY创新工作室
 */

require_once 'config.php';
require_once 'vendor/Parsedown.php';

$pdo = getDBConnection();
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

if ($pid <= 0) {
    header('Location: index.php');
    exit;
}

// 获取题目信息
$stmt = $pdo->prepare("
    SELECT 
        p.pid, p.title, p.answer, p.updated_at,
        ch.name as chapter_name,
        s.name as subcategory_name,
        c.name as category_name
    FROM problems p
    LEFT JOIN chapters ch ON p.chapter_id = ch.id
    LEFT JOIN subcategories s ON ch.subcategory_id = s.id
    LEFT JOIN categories c ON s.category_id = c.id
    WHERE p.pid = ?
");
$stmt->execute([$pid]);
$problem = $stmt->fetch();

if (!$problem) {
    $not_found = true;
} else {
    $not_found = false;
    // 解析Markdown
    $parsedown = new Parsedown();
    $answer_html = $parsedown->text($problem['answer'] ?? '暂无答案');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $not_found ? '题目未找到' : '#' . $problem['pid'] . ' ' . htmlspecialchars($problem['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="vendor/katex/katex.min.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <h1 class="site-title">📚 <?php echo SITE_NAME; ?></h1>
            <p class="site-subtitle">由 <?php echo DEVELOPER; ?> 开发并维护</p>
        </div>
    </header>

    <div class="container problem-container">
        <div class="breadcrumb">
            <a href="index.php">首页</a>
            <?php if (!$not_found): ?>
                <span>/</span>
                <span><?php echo htmlspecialchars($problem['category_name']); ?></span>
                <span>/</span>
                <span><?php echo htmlspecialchars($problem['subcategory_name']); ?></span>
                <span>/</span>
                <span><?php echo htmlspecialchars($problem['chapter_name']); ?></span>
            <?php endif; ?>
        </div>

        <?php if ($not_found): ?>
            <div class="error-box">
                <div class="error-icon">❌</div>
                <h2>题目未找到</h2>
                <p>题号 #<?php echo $pid; ?> 不存在</p>
                <a href="index.php" class="btn-back">返回首页</a>
            </div>
        <?php else: ?>
            <div class="problem-detail">
                <div class="problem-header">
                    <div class="problem-meta">
                        <span class="problem-badge">#<?php echo $problem['pid']; ?></span>
                        <span class="update-time">更新于 <?php echo date('Y-m-d H:i', strtotime($problem['updated_at'])); ?></span>
                    </div>
                    <h1 class="problem-title-large"><?php echo htmlspecialchars($problem['title']); ?></h1>
                </div>

                <div class="answer-section">
                    <h2 class="section-title">📝 题解</h2>
                    <div class="answer-content markdown-body">
                        <?php echo $answer_html; ?>
                    </div>
                </div>

                <div class="problem-actions">
                    <a href="index.php" class="btn btn-secondary">返回首页</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="site-footer">
        <p>&copy; 2024 <?php echo DEVELOPER; ?> | <a href="admin/login.php">管理后台</a></p>
    </footer>

    <script src="vendor/katex/katex.min.js"></script>
    <script src="vendor/katex/auto-render.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        // 渲染LaTeX公式
        document.addEventListener("DOMContentLoaded", function() {
            renderMathInElement(document.body, {
                delimiters: [
                    {left: "$$", right: "$$", display: true},
                    {left: "$", right: "$", display: false},
                    {left: "\\[", right: "\\]", display: true},
                    {left: "\\(", right: "\\)", display: false}
                ],
                throwOnError: false
            });
        });
    </script>
</body>
</html>
