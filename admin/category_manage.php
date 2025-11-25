<?php
/**
 * 信奥赛一本通答案 - 分类管理
 * 开发者: SZY创新工作室
 */

require_once '../config.php';

// 检查登录
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$pdo = getDBConnection();
$message = '';
$error = '';

// 处理添加子分类
if (isset($_POST['add_subcategory'])) {
    $category_id = intval($_POST['category_id']);
    $name = trim($_POST['name']);
    $sort_order = intval($_POST['sort_order']);
    
    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO subcategories (category_id, name, sort_order) VALUES (?, ?, ?)");
        $stmt->execute([$category_id, $name, $sort_order]);
        $message = '子分类添加成功';
    }
}

// 处理添加章节
if (isset($_POST['add_chapter'])) {
    $subcategory_id = intval($_POST['subcategory_id']);
    $name = trim($_POST['name']);
    $sort_order = intval($_POST['sort_order']);
    
    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO chapters (subcategory_id, name, sort_order) VALUES (?, ?, ?)");
        $stmt->execute([$subcategory_id, $name, $sort_order]);
        $message = '章节添加成功';
    }
}

// 处理删除子分类
if (isset($_GET['delete_sub']) && isset($_GET['confirm'])) {
    $id = intval($_GET['delete_sub']);
    $stmt = $pdo->prepare("DELETE FROM subcategories WHERE id = ?");
    $stmt->execute([$id]);
    $message = '子分类已删除';
}

// 处理删除章节
if (isset($_GET['delete_chapter']) && isset($_GET['confirm'])) {
    $id = intval($_GET['delete_chapter']);
    $stmt = $pdo->prepare("DELETE FROM chapters WHERE id = ?");
    $stmt->execute([$id]);
    $message = '章节已删除';
}

// 获取所有分类数据
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$subcategories = $pdo->query("SELECT * FROM subcategories ORDER BY category_id, sort_order")->fetchAll();
$chapters = $pdo->query("SELECT * FROM chapters ORDER BY subcategory_id, sort_order")->fetchAll();

// 组织数据结构
$data = [];
foreach ($categories as $cat) {
    $data[$cat['id']] = [
        'info' => $cat,
        'subcategories' => []
    ];
}

foreach ($subcategories as $sub) {
    if (isset($data[$sub['category_id']])) {
        $data[$sub['category_id']]['subcategories'][$sub['id']] = [
            'info' => $sub,
            'chapters' => []
        ];
    }
}

foreach ($chapters as $chap) {
    foreach ($data as &$cat) {
        if (isset($cat['subcategories'][$chap['subcategory_id']])) {
            $cat['subcategories'][$chap['subcategory_id']]['chapters'][] = $chap;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>分类管理 - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/custom.css">
</head>
<body class="admin-page">
    <div class="admin-header">
        <div class="container">
            <h1>⚙️ 管理控制台</h1>
            <div class="admin-user">
                <span>欢迎,<?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="?logout=1" class="btn-logout">退出</a>
            </div>
        </div>
    </div>

    <div class="admin-nav">
        <div class="container">
            <a href="index.php" class="nav-item">控制台</a>
            <a href="category_manage.php" class="nav-item active">分类管理</a>
            <a href="problem_manage.php" class="nav-item">题目管理</a>
            <a href="../index.php" class="nav-item" target="_blank">查看网站</a>
        </div>
    </div>

    <div class="container admin-container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="admin-section">
            <div class="section-header">
                <h2>📚 分类结构</h2>
                <button class="btn btn-primary" onclick="showModal('addSubModal')">添加子分类</button>
                <button class="btn btn-primary" onclick="showModal('addChapterModal')">添加章节</button>
            </div>

            <div class="category-management">
                <?php foreach ($data as $category): ?>
                    <div class="category-block">
                        <div class="category-header-admin">
                            <h3><?php echo htmlspecialchars($category['info']['name']); ?></h3>
                        </div>
                        
                        <?php foreach ($category['subcategories'] as $sub_id => $subcategory): ?>
                            <div class="subcategory-block">
                                <div class="subcategory-header-admin">
                                    <h4><?php echo htmlspecialchars($subcategory['info']['name']); ?></h4>
                                    <button class="btn-delete" onclick="deleteItem('delete_sub', <?php echo $sub_id; ?>)">删除</button>
                                </div>
                                
                                <div class="chapter-list-admin">
                                    <?php foreach ($subcategory['chapters'] as $chapter): ?>
                                        <div class="chapter-item-admin">
                                            <span><?php echo htmlspecialchars($chapter['name']); ?></span>
                                            <button class="btn-delete-small" onclick="deleteItem('delete_chapter', <?php echo $chapter['id']; ?>)">删除</button>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (count($subcategory['chapters']) == 0): ?>
                                        <div class="empty-hint">暂无章节</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($category['subcategories']) == 0): ?>
                            <div class="empty-hint">暂无子分类</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- 添加子分类模态框 -->
    <div id="addSubModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="hideModal('addSubModal')">&times;</span>
            <h2>添加子分类</h2>
            <form method="POST">
                <div class="form-group">
                    <label>所属大分类</label>
                    <select name="category_id" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>子分类名称</label>
                    <input type="text" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>排序</label>
                    <input type="number" name="sort_order" value="0">
                </div>
                
                <button type="submit" name="add_subcategory" class="btn btn-primary">添加</button>
            </form>
        </div>
    </div>

    <!-- 添加章节模态框 -->
    <div id="addChapterModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="hideModal('addChapterModal')">&times;</span>
            <h2>添加章节</h2>
            <form method="POST">
                <div class="form-group">
                    <label>所属子分类</label>
                    <select name="subcategory_id" required>
                        <?php foreach ($data as $category): ?>
                            <?php foreach ($category['subcategories'] as $sub_id => $subcategory): ?>
                                <option value="<?php echo $sub_id; ?>">
                                    <?php echo htmlspecialchars($category['info']['name'] . ' - ' . $subcategory['info']['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>章节名称</label>
                    <input type="text" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>排序</label>
                    <input type="number" name="sort_order" value="0">
                </div>
                
                <button type="submit" name="add_chapter" class="btn btn-primary">添加</button>
            </form>
        </div>
    </div>

    <footer class="admin-footer">
        <p>&copy; 2024 <?php echo DEVELOPER; ?></p>
    </footer>

    <script src="../js/admin.js"></script>
</body>
</html>
