<?php
/**
 * 信奥赛一本通答案 - 前台首页
 * 开发者: SZY创新工作室
 */

require_once 'config.php';

$pdo = getDBConnection();

// 获取所有分类及其子分类和章节
$stmt = $pdo->query("
    SELECT 
        c.id as cat_id, c.name as cat_name, c.sort_order as cat_sort,
        s.id as sub_id, s.name as sub_name, s.sort_order as sub_sort,
        ch.id as chap_id, ch.name as chap_name, ch.sort_order as chap_sort
    FROM categories c
    LEFT JOIN subcategories s ON c.id = s.category_id
    LEFT JOIN chapters ch ON s.id = ch.subcategory_id
    ORDER BY c.sort_order, s.sort_order, ch.sort_order
");

$data = [];
while ($row = $stmt->fetch()) {
    $cat_id = $row['cat_id'];
    $sub_id = $row['sub_id'];
    
    if (!isset($data[$cat_id])) {
        $data[$cat_id] = [
            'name' => $row['cat_name'],
            'subcategories' => []
        ];
    }
    
    if ($sub_id && !isset($data[$cat_id]['subcategories'][$sub_id])) {
        $data[$cat_id]['subcategories'][$sub_id] = [
            'name' => $row['sub_name'],
            'chapters' => []
        ];
    }
    
    if ($row['chap_id']) {
        $data[$cat_id]['subcategories'][$sub_id]['chapters'][] = [
            'id' => $row['chap_id'],
            'name' => $row['chap_name']
        ];
    }
}

// 获取选中章节的题目
$selected_chapter = isset($_GET['chapter']) ? intval($_GET['chapter']) : 0;
$problems = [];
$chapter_name = '';

if ($selected_chapter > 0) {
    $stmt = $pdo->prepare("SELECT name FROM chapters WHERE id = ?");
    $stmt->execute([$selected_chapter]);
    $chapter_info = $stmt->fetch();
    $chapter_name = $chapter_info ? $chapter_info['name'] : '';
    
    $stmt = $pdo->prepare("SELECT pid, title FROM problems WHERE chapter_id = ? ORDER BY pid");
    $stmt->execute([$selected_chapter]);
    $problems = $stmt->fetchAll();
}

// 获取所有题目用于全局搜索
$stmt = $pdo->query("SELECT p.pid, p.title, ch.name as chapter_name FROM problems p LEFT JOIN chapters ch ON p.chapter_id = ch.id ORDER BY p.pid");
$all_problems = $stmt->fetchAll();
$all_problems_json = json_encode($all_problems, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/custom.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <h1 class="site-title">📚 <?php echo SITE_NAME; ?></h1>
            <p class="site-subtitle">由 <?php echo DEVELOPER; ?> 开发并维护</p>
        </div>
    </header>

    <!-- 全局搜索栏 -->
    <div class="container" style="margin-top: 20px;">
        <div class="global-search-container">
            <input type="text" id="globalSearch" placeholder="🔍 搜索题号或标题（支持全局搜索）..." class="global-search-input">
            <div id="searchResults" class="search-results" style="display: none;"></div>
        </div>
    </div>

    <div class="container main-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>📑 题目分类</h2>
            </div>
            <div class="category-tree">
                <?php foreach ($data as $category): ?>
                    <div class="category-item">
                        <div class="category-title"><?php echo htmlspecialchars($category['name']); ?></div>
                        <?php foreach ($category['subcategories'] as $subcategory): ?>
                            <div class="subcategory-item">
                                <div class="subcategory-title"><?php echo htmlspecialchars($subcategory['name']); ?></div>
                                <div class="chapter-list">
                                    <?php foreach ($subcategory['chapters'] as $chapter): ?>
                                        <a href="?chapter=<?php echo $chapter['id']; ?>" 
                                           class="chapter-link <?php echo $selected_chapter == $chapter['id'] ? 'active' : ''; ?>">
                                            <?php echo htmlspecialchars($chapter['name']); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="content-area">
            <?php if ($selected_chapter > 0): ?>
                <div class="content-header">
                    <h2><?php echo htmlspecialchars($chapter_name); ?></h2>
                    <p class="problem-count">共 <?php echo count($problems); ?> 道题目</p>
                </div>
                
                <?php if (count($problems) > 0): ?>
                    <div class="problem-grid">
                        <?php foreach ($problems as $problem): ?>
                            <a href="problem_show.php?pid=<?php echo $problem['pid']; ?>" class="problem-card">
                                <div class="problem-id">#<?php echo $problem['pid']; ?></div>
                                <div class="problem-title"><?php echo htmlspecialchars($problem['title']); ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>😊 该章节暂无题目</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="welcome-screen">
                    <div class="welcome-icon">🎯</div>
                    <h2>欢迎使用信奥赛一本通答案系统</h2>
                    <p>请从左侧选择章节查看题目</p>
                    <div class="stats">
                        <?php
                        $total_problems = $pdo->query("SELECT COUNT(*) FROM problems")->fetchColumn();
                        $total_chapters = $pdo->query("SELECT COUNT(*) FROM chapters")->fetchColumn();
                        ?>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $total_problems; ?></div>
                            <div class="stat-label">题目总数</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $total_chapters; ?></div>
                            <div class="stat-label">章节总数</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="site-footer">
        <p>&copy; 2024 <?php echo DEVELOPER; ?> | <a href="admin/login.php">管理后台</a></p>
    </footer>

    <script>
        // 全局题目数据
        window.allProblems = <?php echo $all_problems_json; ?>;
    </script>
    <script src="js/main.js"></script>
</body>
</html>
