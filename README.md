# 信奥赛一本通答案系统

> 由 **SZY创新工作室** 开发并维护

## 📖 项目简介

这是一个专为信息学奥赛一本通题目设计的答案管理与展示系统。系统采用PHP+MySQL架构，支持Markdown格式答案编写和LaTeX数学公式渲染，提供完善的前台展示和后台管理功能。
仓库地址：[]()

## ✨ 主要功能

### 前台功能
- 📚 **分类浏览**：四级分类结构（大部分 → 小部分 → 章节 → 题目）
- 🔍 **快速搜索**：支持题号和标题搜索
- 📝 **答案展示**：Markdown格式渲染，支持LaTeX公式
- 📱 **响应式设计**：完美适配PC、平板、手机

### 后台功能
- 🔐 **安全登录**：管理员身份验证
- 🗂️ **分类管理**：添加/删除子分类和章节
- ✏️ **题目管理**：添加/编辑/删除题目答案
- 📊 **数据统计**：实时显示题目和分类数量
- 💾 **自动保存**：防止编辑内容丢失
- 👁️ **实时预览**：Markdown答案实时预览

## 🚀 安装步骤

### 环境要求
- PHP 7.4 或更高版本
- MySQL 5.7 或更高版本
- Apache/Nginx Web服务器
- PDO扩展支持

### 安装流程

1. **上传文件**
   ```bash
   # 将整个ybt_answers文件夹上传到Web服务器
   # 确保Web服务器有读写权限
   ```

2. **访问安装页面**
   ```
   https://example.com/install.php
   ```

3. **填写数据库信息**
   - 数据库主机：通常为 `localhost`
   - 数据库名称：建议使用 `ybt_answers`
   - 数据库用户名：您的MySQL用户名
   - 数据库密码：您的MySQL密码

4. **完成安装**
   - 点击"开始安装"按钮
   - 系统将自动创建数据库表和初始数据
   - 安装成功后，**请立即删除install.php文件**以确保安全

5. **登录管理后台**
   ```
   地址: https://example.com/admin/login.php
   用户名: admin
   密码: 12345678
   (用户名密码可通过修改config.php完成)
   ```

## 📂 目录结构

```
ybt_answers/
├── index.php              # 前台首页
├── problem_show.php       # 答案详情页
├── config.php             # 配置文件
├── install.php            # 安装程序（安装后删除）
├── README.md              # 使用说明
├── css/
│   └── custom.css         # 自定义样式（800+行）
├── js/
│   ├── main.js            # 前台交互脚本
│   └── admin.js           # 后台管理脚本
├── admin/
│   ├── login.php          # 管理员登录
│   ├── index.php          # 管理控制台
│   ├── category_manage.php # 分类管理
│   └── problem_manage.php  # 题目管理
└── vendor/
    ├── Parsedown.php      # Markdown解析库
    └── katex/             # LaTeX公式渲染库
        ├── katex.min.css
        ├── katex.min.js
        ├── auto-render.min.js
        └── fonts/         # 数学字体文件
```

## 🎯 使用指南

### 添加题目

1. 登录管理后台
2. 进入"题目管理"页面
3. 填写题目信息：
   - **题号**：4位数字（如：4096）
   - **所属章节**：选择对应章节
   - **题目标题**：题目名称
   - **答案**：使用Markdown格式编写

### Markdown语法示例

```markdown
# 一级标题
## 二级标题

**粗体文本** *斜体文本*

- 列表项1
- 列表项2

`行内代码`

\`\`\`cpp
// 代码块
#include <iostream>
using namespace std;

int main() {
    cout << "Hello World!" << endl;
    return 0;
}
\`\`\`
```

### LaTeX公式示例

```markdown
行内公式：$x^2 + y^2 = z^2$

块级公式：
$$
\int_0^1 x^2 dx = \frac{1}{3}
$$

分数：$\frac{a}{b}$

求和：$\sum_{i=1}^{n} i = \frac{n(n+1)}{2}$
```

## 🔧 配置说明

### 修改管理员密码

编辑 `config.php` 文件：

```php
// 管理员配置
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', '12345678');  // 修改此处
```

### 修改网站信息

编辑 `config.php` 文件：

```php
// 网站配置
define('SITE_NAME', '信奥赛一本通答案');
define('SITE_URL', 'https://ybt.szystudio.cn');
define('DEVELOPER', 'SZY创新工作室');
```

## 📊 数据库结构

系统使用4个主要数据表：

1. **categories** - 大分类表
   - 一、语言及算法基础篇
   - 二、算法提高篇
   - 三、高手训练
   - 四、官方真题

2. **subcategories** - 子分类表
   - 存储各大分类下的小部分

3. **chapters** - 章节表
   - 存储各子分类下的章节

4. **problems** - 题目表
   - 存储题目详细信息和答案

## 🎨 特色功能

### 前台特色
- ✅ 优雅的渐变色设计
- ✅ 流畅的动画效果
- ✅ 智能的侧边栏折叠
- ✅ 实时搜索过滤
- ✅ 返回顶部按钮
- ✅ 代码一键复制

### 后台特色
- ✅ 直观的统计面板
- ✅ 强大的CRUD功能
- ✅ 自动保存草稿
- ✅ Markdown实时预览
- ✅ 表格智能排序
- ✅ 快捷键支持

## 🔒 安全建议

1. **安装后立即删除install.php文件**
2. **修改默认管理员密码**
3. **定期备份数据库**
4. **使用HTTPS协议**
5. **限制admin目录访问权限**

## 🐛 常见问题

### Q: 安装后无法访问？
A: 检查Web服务器配置，确保PHP和MySQL服务正常运行。

### Q: 公式无法显示？
A: 确保katex库文件完整，检查浏览器控制台是否有错误。

### Q: 无法上传图片？
A: 当前版本暂不支持图片上传，可使用图床外链。

### Q: 如何备份数据？
A: 使用phpMyAdmin或mysqldump命令导出数据库。

## 📝 更新日志

### v1.0.0 (2024-11-20)
- ✅ 初始版本发布
- ✅ 完整的前后台功能
- ✅ Markdown和LaTeX支持
- ✅ 响应式设计
- ✅ 自动保存功能

## 👨‍💻 技术栈

- **后端**：PHP 7.4+ / PDO / MySQL
- **前端**：HTML5 / CSS3 / JavaScript ES6
- **库**：Parsedown / KaTeX
- **设计**：响应式布局 / CSS Grid / Flexbox

## 📧 联系方式

- **开发者**：SZY创新工作室
- **网站**：https://www.szystudio.cn
- **邮箱**: support@szystudio.cn

## 📄 许可证

本项目仅供学习交流使用，请勿用于商业用途。

---

**感谢使用信奥赛一本通答案系统！** 🎉

如有问题或建议，欢迎提交issue反馈。
