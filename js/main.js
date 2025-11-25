/**
 * 信奥赛一本通答案 - 前台交互脚本
 * 开发者: SZY创新工作室
 */

// 页面加载完成后执行
document.addEventListener('DOMContentLoaded', function() {
    
    // 初始化侧边栏折叠功能
    initSidebarCollapse();
    
    // 初始化平滑滚动
    initSmoothScroll();
    
    // 初始化题目卡片动画
    initProblemCardAnimation();
    
    // 初始化搜索功能
    initSearch();
    
    // 初始化全局搜索
    initGlobalSearch();
    
    // 初始化代码高亮
    initCodeHighlight();
    
});

/**
 * 侧边栏折叠功能
 */
function initSidebarCollapse() {
    const categoryTitles = document.querySelectorAll('.category-title');
    
    // 默认折叠所有分类
    categoryTitles.forEach(title => {
        title.style.cursor = 'pointer';
        const categoryItem = title.parentElement;
        const subcategories = categoryItem.querySelectorAll('.subcategory-item');
        
        // 默认隐藏所有子分类
        subcategories.forEach(sub => {
            sub.style.display = 'none';
        });
        title.style.opacity = '0.7';
        
        // 添加点击切换事件
        title.addEventListener('click', function() {
            const categoryItem = this.parentElement;
            const subcategories = categoryItem.querySelectorAll('.subcategory-item');
            const isExpanded = this.classList.contains('expanded');
            
            subcategories.forEach(sub => {
                if (isExpanded) {
                    sub.style.display = 'none';
                    this.style.opacity = '0.7';
                    this.classList.remove('expanded');
                } else {
                    sub.style.display = 'block';
                    this.style.opacity = '1';
                    this.classList.add('expanded');
                }
            });
        });
    });
    
    // 子分类折叠
    const subcategoryTitles = document.querySelectorAll('.subcategory-title');
    
    subcategoryTitles.forEach(title => {
        title.style.cursor = 'pointer';
        
        // 默认隐藏所有章节
        const chapterList = title.nextElementSibling;
        if (chapterList && chapterList.classList.contains('chapter-list')) {
            chapterList.style.display = 'none';
            title.style.opacity = '0.7';
        }
        
        title.addEventListener('click', function() {
            const chapterList = this.nextElementSibling;
            
            if (chapterList && chapterList.classList.contains('chapter-list')) {
                const isExpanded = this.classList.contains('expanded');
                
                if (isExpanded) {
                    chapterList.style.display = 'none';
                    this.style.opacity = '0.7';
                    this.classList.remove('expanded');
                } else {
                    chapterList.style.display = 'block';
                    this.style.opacity = '1';
                    this.classList.add('expanded');
                }
            }
        });
    });
    
    // 如果有选中的章节，自动展开对应的分类
    const activeChapter = document.querySelector('.chapter-link.active');
    if (activeChapter) {
        // 展开父级分类
        let parent = activeChapter.closest('.subcategory-item');
        if (parent) {
            parent.style.display = 'block';
            const subcategoryTitle = parent.querySelector('.subcategory-title');
            if (subcategoryTitle) {
                subcategoryTitle.style.opacity = '1';
                subcategoryTitle.classList.add('expanded');
            }
            const chapterList = parent.querySelector('.chapter-list');
            if (chapterList) {
                chapterList.style.display = 'block';
            }
        }
        
        // 展开爷级分类
        let grandParent = activeChapter.closest('.category-item');
        if (grandParent) {
            const categoryTitle = grandParent.querySelector('.category-title');
            if (categoryTitle) {
                categoryTitle.style.opacity = '1';
                categoryTitle.classList.add('expanded');
            }
        }
    }
}

/**
 * 平滑滚动
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * 题目卡片动画
 */
function initProblemCardAnimation() {
    const cards = document.querySelectorAll('.problem-card');
    
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 50);
    });
}

/**
 * 全局搜索功能
 */
function initGlobalSearch() {
    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('searchResults');
    
    if (!searchInput || !searchResults) return;
    
    let searchTimeout;
    
    // 搜索输入事件
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim().toLowerCase();
        
        if (query.length === 0) {
            searchResults.style.display = 'none';
            return;
        }
        
        // 防抖动
        searchTimeout = setTimeout(() => {
            performGlobalSearch(query);
        }, 300);
    });
    
    // 点击外部关闭搜索结果
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    
    // 点击搜索框显示结果
    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length > 0) {
            searchResults.style.display = 'block';
        }
    });
}

/**
 * 执行全局搜索
 */
function performGlobalSearch(query) {
    const searchResults = document.getElementById('searchResults');
    
    if (!window.allProblems || window.allProblems.length === 0) {
        searchResults.innerHTML = '<div class="search-no-results">暂无题目数据</div>';
        searchResults.style.display = 'block';
        return;
    }
    
    // 搜索匹配
    const results = window.allProblems.filter(problem => {
        const pidStr = String(problem.pid).toLowerCase();
        const titleStr = problem.title.toLowerCase();
        return pidStr.includes(query) || titleStr.includes(query);
    });
    
    // 显示结果
    if (results.length === 0) {
        searchResults.innerHTML = '<div class="search-no-results">😢 未找到匹配的题目</div>';
    } else {
        let html = `<div class="search-result-count">找到 ${results.length} 道题目</div>`;
        
        // 最多显示50个结果
        const displayResults = results.slice(0, 50);
        
        displayResults.forEach(problem => {
            const highlightedPid = highlightText(String(problem.pid), query);
            const highlightedTitle = highlightText(problem.title, query);
            
            html += `
                <a href="problem_show.php?pid=${problem.pid}" class="search-result-item">
                    <div class="search-result-left">
                        <div class="search-result-pid">#${highlightedPid}</div>
                        <div class="search-result-title">${highlightedTitle}</div>
                        ${problem.chapter_name ? `<div class="search-result-chapter">📚 ${problem.chapter_name}</div>` : ''}
                    </div>
                </a>
            `;
        });
        
        if (results.length > 50) {
            html += `<div class="search-result-count">还有 ${results.length - 50} 个结果未显示，请细化搜索条件</div>`;
        }
        
        searchResults.innerHTML = html;
    }
    
    searchResults.style.display = 'block';
}

/**
 * 高亮匹配文本
 */
function highlightText(text, query) {
    if (!query) return text;
    
    const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
    return text.replace(regex, '<span class="highlight">$1</span>');
}

/**
 * 转义正则表达式特殊字符
 */
function escapeRegex(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

/**
 * 搜索功能
 */
function initSearch() {
    // 创建搜索框（如果需要）
    const contentHeader = document.querySelector('.content-header');
    
    if (contentHeader && document.querySelectorAll('.problem-card').length > 0) {
        const searchBox = document.createElement('div');
        searchBox.className = 'search-box';
        searchBox.innerHTML = `
            <input type="text" id="problemSearch" placeholder="搜索题号或标题..." 
                   style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; margin-top: 15px;">
        `;
        contentHeader.appendChild(searchBox);
        
        // 搜索功能
        const searchInput = document.getElementById('problemSearch');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const problemCards = document.querySelectorAll('.problem-card');
            
            problemCards.forEach(card => {
                const id = card.querySelector('.problem-id').textContent.toLowerCase();
                const title = card.querySelector('.problem-title').textContent.toLowerCase();
                
                if (id.includes(searchTerm) || title.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
}

/**
 * 代码高亮
 */
function initCodeHighlight() {
    const codeBlocks = document.querySelectorAll('pre code');
    
    codeBlocks.forEach(block => {
        // 添加行号
        const lines = block.textContent.split('\n');
        if (lines.length > 1) {
            block.classList.add('line-numbers');
        }
    });
}

/**
 * 复制代码功能
 */
function copyCode(button) {
    const codeBlock = button.parentElement.querySelector('code');
    const text = codeBlock.textContent;
    
    navigator.clipboard.writeText(text).then(() => {
        const originalText = button.textContent;
        button.textContent = '已复制!';
        button.style.background = '#48bb78';
        
        setTimeout(() => {
            button.textContent = originalText;
            button.style.background = '';
        }, 2000);
    }).catch(err => {
        console.error('复制失败:', err);
    });
}

/**
 * 添加复制按钮到代码块
 */
document.addEventListener('DOMContentLoaded', function() {
    const preBlocks = document.querySelectorAll('pre');
    
    preBlocks.forEach(pre => {
        const copyButton = document.createElement('button');
        copyButton.textContent = '复制代码';
        copyButton.className = 'copy-button';
        copyButton.style.cssText = `
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s;
        `;
        
        pre.style.position = 'relative';
        pre.appendChild(copyButton);
        
        pre.addEventListener('mouseenter', () => {
            copyButton.style.opacity = '1';
        });
        
        pre.addEventListener('mouseleave', () => {
            copyButton.style.opacity = '0';
        });
        
        copyButton.addEventListener('click', function() {
            copyCode(this);
        });
    });
});

/**
 * 返回顶部按钮
 */
window.addEventListener('scroll', function() {
    let backToTop = document.getElementById('backToTop');
    
    if (!backToTop) {
        backToTop = document.createElement('button');
        backToTop.id = 'backToTop';
        backToTop.innerHTML = '↑';
        backToTop.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            display: none;
            z-index: 999;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        `;
        
        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        backToTop.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        backToTop.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
        
        document.body.appendChild(backToTop);
    }
    
    if (window.pageYOffset > 300) {
        backToTop.style.display = 'block';
    } else {
        backToTop.style.display = 'none';
    }
});

/**
 * 图片懒加载
 */
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
});

/**
 * 打印功能
 */
function printPage() {
    window.print();
}

/**
 * 分享功能
 */
function sharePage() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: window.location.href
        }).catch(err => console.log('分享失败:', err));
    } else {
        // 复制链接
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('链接已复制到剪贴板！');
        });
    }
}

/**
 * 主题切换（预留功能）
 */
function toggleTheme() {
    const body = document.body;
    body.classList.toggle('dark-theme');
    
    const theme = body.classList.contains('dark-theme') ? 'dark' : 'light';
    localStorage.setItem('theme', theme);
}

// 加载保存的主题
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
    }
});

/**
 * 键盘快捷键
 */
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K: 聚焦搜索框
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.getElementById('problemSearch');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // ESC: 清除搜索
    if (e.key === 'Escape') {
        const searchInput = document.getElementById('problemSearch');
        if (searchInput) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        }
    }
});

/**
 * 性能监控
 */
window.addEventListener('load', function() {
    if (window.performance) {
        const perfData = window.performance.timing;
        const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
        
        console.log('页面加载时间:', pageLoadTime + 'ms');
    }
});

/**
 * 错误处理
 */
window.addEventListener('error', function(e) {
    console.error('页面错误:', e.message);
});

/**
 * 导出功能（供其他脚本使用）
 */
window.YBTAnswers = {
    copyCode: copyCode,
    printPage: printPage,
    sharePage: sharePage,
    toggleTheme: toggleTheme
};
