<?php
/**
 * 信奥赛一本通答案 - 题目管理
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
$edit_problem = null;

// 处理添加题目
if (isset($_POST['add_problem'])) {
    $pid = intval($_POST['pid']);
    $chapter_id = intval($_POST['chapter_id']);
    $title = trim($_POST['title']);
    $answer = trim($_POST['answer']);
    
    if ($pid > 0 && $title) {
        try {
            $stmt = $pdo->prepare("INSERT INTO problems (pid, chapter_id, title, answer) VALUES (?, ?, ?, ?)");
            $stmt->execute([$pid, $chapter_id, $title, $answer]);
            $message = '题目添加成功';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = '题号已存在';
            } else {
                $error = '添加失败: ' . $e->getMessage();
            }
        }
    } else {
        $error = '请填写完整信息';
    }
}

// 处理更新题目
if (isset($_POST['update_problem'])) {
    $pid = intval($_POST['pid']);
    $chapter_id = intval($_POST['chapter_id']);
    $title = trim($_POST['title']);
    $answer = trim($_POST['answer']);
    
    if ($pid > 0 && $title) {
        $stmt = $pdo->prepare("UPDATE problems SET chapter_id = ?, title = ?, answer = ? WHERE pid = ?");
        $stmt->execute([$chapter_id, $title, $answer, $pid]);
        $message = '题目更新成功';
    }
}

// 处理删除题目
if (isset($_GET['delete']) && isset($_GET['confirm'])) {
    $pid = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM problems WHERE pid = ?");
    $stmt->execute([$pid]);
    $message = '题目已删除';
}

// 获取编辑的题目
if (isset($_GET['edit'])) {
    $pid = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM problems WHERE pid = ?");
    $stmt->execute([$pid]);
    $edit_problem = $stmt->fetch();
}

// 获取所有章节
$chapters = $pdo->query("
    SELECT 
        ch.id, ch.name as chapter_name,
        s.name as subcategory_name,
        c.name as category_name
    FROM chapters ch
    LEFT JOIN subcategories s ON ch.subcategory_id = s.id
    LEFT JOIN categories c ON s.category_id = c.id
    ORDER BY c.sort_order, s.sort_order, ch.sort_order
")->fetchAll();

// 搜索题目
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_chapter = isset($_GET['filter_chapter']) ? intval($_GET['filter_chapter']) : 0;

$query = "
    SELECT 
        p.pid, p.title, p.updated_at,
        ch.name as chapter_name
    FROM problems p
    LEFT JOIN chapters ch ON p.chapter_id = ch.id
    WHERE 1=1
";

$params = [];

if ($search) {
    $query .= " AND (p.pid LIKE ? OR p.title LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($filter_chapter > 0) {
    $query .= " AND p.chapter_id = ?";
    $params[] = $filter_chapter;
}

$query .= " ORDER BY p.pid DESC LIMIT 100";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$problems = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>题目管理 - <?php echo SITE_NAME; ?></title>
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
            <a href="category_manage.php" class="nav-item">分类管理</a>
            <a href="problem_manage.php" class="nav-item active">题目管理</a>
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

        <?php if ($edit_problem): ?>
            <!-- 编辑题目表单 -->
            <div class="admin-section">
                <h2>✏️ 编辑题目 #<?php echo $edit_problem['pid']; ?></h2>
                <form method="POST" class="problem-form">
                    <input type="hidden" name="pid" value="<?php echo $edit_problem['pid']; ?>">
                    
                    <div class="form-group">
                        <label>题号（不可修改）</label>
                        <input type="text" value="<?php echo $edit_problem['pid']; ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label>所属章节</label>
                        <select name="chapter_id" required>
                            <?php foreach ($chapters as $chapter): ?>
                                <option value="<?php echo $chapter['id']; ?>" 
                                    <?php echo $chapter['id'] == $edit_problem['chapter_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($chapter['category_name'] . ' - ' . $chapter['subcategory_name'] . ' - ' . $chapter['chapter_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>题目标题</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($edit_problem['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>答案（支持Markdown和LaTeX公式）</label>
                        <textarea name="answer" rows="15" class="code-editor"><?php echo htmlspecialchars($edit_problem['answer']); ?></textarea>
                        <div class="form-hint">
                            提示：使用 $ 包裹行内公式，使用 $$ 包裹块级公式<br>
                            示例：行内公式 $x^2 + y^2 = z^2$，块级公式 $$\int_0^1 x^2 dx$$
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="update_problem" class="btn btn-primary">保存修改</button>
                        <a href="problem_manage.php" class="btn btn-secondary">取消</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- 添加题目表单 -->
            <div class="admin-section">
                <h2>➕ 添加新题目</h2>
                <form method="POST" class="problem-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>题号（4位数）</label>
                            <input type="number" name="pid" min="1000" max="9999" required>
                        </div>
                        
                        <div class="form-group">
                            <label>所属章节</label>
                            <select name="chapter_id" required>
                                <?php foreach ($chapters as $chapter): ?>
                                    <option value="<?php echo $chapter['id']; ?>">
                                        <?php echo htmlspecialchars($chapter['category_name'] . ' - ' . $chapter['subcategory_name'] . ' - ' . $chapter['chapter_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>题目标题</label>
                        <input type="text" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label>答案（支持Markdown和LaTeX公式）</label>
                        <textarea name="answer" rows="15" class="code-editor"></textarea>
                        <div class="form-hint">
                            提示：使用 $ 包裹行内公式，使用 $$ 包裹块级公式<br>
                            示例：行内公式 $x^2 + y^2 = z^2$，块级公式 $$\int_0^1 x^2 dx$$
                        </div>
                    </div>
                    
                    <button type="submit" name="add_problem" class="btn btn-primary">添加题目</button>
                </form>
            </div>

            <!-- 题目列表 -->
            <div class="admin-section">
                <h2>📝 题目列表</h2>
                
                <div class="filter-bar">
                    <form method="GET" class="filter-form">
                        <input type="text" name="search" placeholder="搜索题号或标题..." value="<?php echo htmlspecialchars($search); ?>">
                        
                        <select name="filter_chapter">
                            <option value="0">所有章节</option>
                            <?php foreach ($chapters as $chapter): ?>
                                <option value="<?php echo $chapter['id']; ?>" <?php echo $filter_chapter == $chapter['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($chapter['category_name'] . ' - ' . $chapter['chapter_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button type="submit" class="btn btn-primary">搜索</button>
                        <a href="problem_manage.php" class="btn btn-secondary">重置</a>
                    </form>
                </div>

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
                            <?php if (count($problems) > 0): ?>
                                <?php foreach ($problems as $problem): ?>
                                    <tr>
                                        <td>#<?php echo $problem['pid']; ?></td>
                                        <td><?php echo htmlspecialchars($problem['title']); ?></td>
                                        <td><?php echo htmlspecialchars($problem['chapter_name']); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($problem['updated_at'])); ?></td>
                                        <td>
                                            <a href="../problem_show.php?pid=<?php echo $problem['pid']; ?>" target="_blank" class="btn-link">查看</a>
                                            <a href="?edit=<?php echo $problem['pid']; ?>" class="btn-link">编辑</a>
                                            <a href="#" onclick="deleteProblem(<?php echo $problem['pid']; ?>)" class="btn-link btn-danger">删除</a>
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
        <?php endif; ?>
    </div>

    <footer class="admin-footer">
        <p>&copy; 2024 <?php echo DEVELOPER; ?></p>
    </footer>

    <script src="../js/admin.js"></script>
</body>
</html>
