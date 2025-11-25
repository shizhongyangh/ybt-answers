/**
 * 信奥赛一本通答案 - 后台管理脚本
 * 开发者: SZY创新工作室
 */

// 页面加载完成后执行
document.addEventListener('DOMContentLoaded', function() {
    
    // 初始化表单验证
    initFormValidation();
    
    // 初始化确认对话框
    initConfirmDialogs();
    
    // 初始化自动保存
    initAutoSave();
    
    // 初始化Markdown预览
    initMarkdownPreview();
    
    // 初始化表格排序
    initTableSort();
    
});

/**
 * 显示模态框
 */
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

/**
 * 隐藏模态框
 */
function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

/**
 * 删除项目确认
 */
function deleteItem(type, id) {
    const messages = {
        'delete_sub': '确定要删除这个子分类吗？这将同时删除其下的所有章节和题目！',
        'delete_chapter': '确定要删除这个章节吗？这将同时删除其下的所有题目！'
    };
    
    if (confirm(messages[type] || '确定要删除吗？')) {
        window.location.href = `?${type}=${id}&confirm=1`;
    }
}

/**
 * 删除题目确认
 */
function deleteProblem(pid) {
    if (confirm(`确定要删除题目 #${pid} 吗？此操作不可恢复！`)) {
        window.location.href = `?delete=${pid}&confirm=1`;
    }
}

/**
 * 表单验证
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#f56565';
                    
                    // 添加错误提示
                    let errorMsg = field.nextElementSibling;
                    if (!errorMsg || !errorMsg.classList.contains('error-message')) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message';
                        errorMsg.style.cssText = 'color: #f56565; font-size: 12px; margin-top: 5px;';
                        errorMsg.textContent = '此字段为必填项';
                        field.parentNode.insertBefore(errorMsg, field.nextSibling);
                    }
                } else {
                    field.style.borderColor = '';
                    const errorMsg = field.nextElementSibling;
                    if (errorMsg && errorMsg.classList.contains('error-message')) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('请填写所有必填字段！');
            }
        });
        
        // 实时验证
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '#48bb78';
                } else {
                    this.style.borderColor = '#f56565';
                }
            });
        });
    });
}

/**
 * 确认对话框
 */
function initConfirmDialogs() {
    const deleteLinks = document.querySelectorAll('[data-confirm]');
    
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const message = this.dataset.confirm || '确定要执行此操作吗？';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * 自动保存功能
 */
function initAutoSave() {
    const textareas = document.querySelectorAll('textarea[name="answer"]');
    
    textareas.forEach(textarea => {
        const key = 'autosave_' + window.location.pathname;
        
        // 加载自动保存的内容
        const saved = localStorage.getItem(key);
        if (saved && !textarea.value) {
            if (confirm('检测到未保存的内容，是否恢复？')) {
                textarea.value = saved;
            }
        }
        
        // 自动保存
        let saveTimeout;
        textarea.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                localStorage.setItem(key, this.value);
                showSaveIndicator();
            }, 1000);
        });
        
        // 提交后清除自动保存
        const form = textarea.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                localStorage.removeItem(key);
            });
        }
    });
}

/**
 * 显示保存指示器
 */
function showSaveIndicator() {
    let indicator = document.getElementById('saveIndicator');
    
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'saveIndicator';
        indicator.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #48bb78;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s;
        `;
        indicator.textContent = '✓ 已自动保存';
        document.body.appendChild(indicator);
    }
    
    indicator.style.opacity = '1';
    
    setTimeout(() => {
        indicator.style.opacity = '0';
    }, 2000);
}

/**
 * Markdown预览
 */
function initMarkdownPreview() {
    const answerTextareas = document.querySelectorAll('textarea[name="answer"]');
    
    answerTextareas.forEach(textarea => {
        // 创建预览按钮
        const previewBtn = document.createElement('button');
        previewBtn.type = 'button';
        previewBtn.className = 'btn btn-secondary';
        previewBtn.textContent = '预览';
        previewBtn.style.marginTop = '10px';
        
        textarea.parentNode.insertBefore(previewBtn, textarea.nextSibling);
        
        // 创建预览容器
        const previewDiv = document.createElement('div');
        previewDiv.className = 'markdown-preview';
        previewDiv.style.cssText = `
            display: none;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-top: 10px;
            background: white;
            max-height: 500px;
            overflow-y: auto;
        `;
        
        previewBtn.parentNode.insertBefore(previewDiv, previewBtn.nextSibling);
        
        // 切换预览
        previewBtn.addEventListener('click', function() {
            if (previewDiv.style.display === 'none') {
                previewDiv.style.display = 'block';
                previewDiv.innerHTML = '<div class="markdown-body">' + 
                    markdownToHtml(textarea.value) + 
                    '</div>';
                this.textContent = '隐藏预览';
                
                // 渲染LaTeX公式
                if (window.renderMathInElement) {
                    renderMathInElement(previewDiv, {
                        delimiters: [
                            {left: "$$", right: "$$", display: true},
                            {left: "$", right: "$", display: false}
                        ]
                    });
                }
            } else {
                previewDiv.style.display = 'none';
                this.textContent = '预览';
            }
        });
    });
}

/**
 * 简单的Markdown转HTML（用于预览）
 */
function markdownToHtml(markdown) {
    if (!markdown) return '<p>暂无内容</p>';
    
    let html = markdown;
    
    // 标题
    html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
    html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
    html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');
    
    // 粗体
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    
    // 斜体
    html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
    
    // 代码块
    html = html.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
    
    // 行内代码
    html = html.replace(/`(.*?)`/g, '<code>$1</code>');
    
    // 链接
    html = html.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2">$1</a>');
    
    // 段落
    html = html.replace(/\n\n/g, '</p><p>');
    html = '<p>' + html + '</p>';
    
    return html;
}

/**
 * 表格排序
 */
function initTableSort() {
    const tables = document.querySelectorAll('.admin-table');
    
    tables.forEach(table => {
        const headers = table.querySelectorAll('th');
        
        headers.forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.style.userSelect = 'none';
            
            header.addEventListener('click', function() {
                sortTable(table, index);
            });
        });
    });
}

/**
 * 排序表格
 */
function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const isAscending = table.dataset.sortOrder !== 'asc';
    table.dataset.sortOrder = isAscending ? 'asc' : 'desc';
    
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        // 尝试数字比较
        const aNum = parseFloat(aValue.replace(/[^0-9.-]/g, ''));
        const bNum = parseFloat(bValue.replace(/[^0-9.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? aNum - bNum : bNum - aNum;
        }
        
        // 字符串比较
        return isAscending ? 
            aValue.localeCompare(bValue, 'zh-CN') : 
            bValue.localeCompare(aValue, 'zh-CN');
    });
    
    // 重新插入排序后的行
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * 批量操作
 */
function initBatchOperations() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="selected[]"]');
    const selectAll = document.getElementById('selectAll');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBatchActions();
        });
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBatchActions);
    });
}

/**
 * 更新批量操作按钮状态
 */
function updateBatchActions() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="selected[]"]:checked');
    const batchActions = document.getElementById('batchActions');
    
    if (batchActions) {
        if (checkboxes.length > 0) {
            batchActions.style.display = 'block';
            batchActions.querySelector('.count').textContent = checkboxes.length;
        } else {
            batchActions.style.display = 'none';
        }
    }
}

/**
 * 统计图表（预留功能）
 */
function initCharts() {
    // 可以集成Chart.js等图表库
    console.log('图表功能待实现');
}

/**
 * 快捷键
 */
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S: 保存
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        const form = document.querySelector('form');
        if (form) {
            form.submit();
        }
    }
    
    // Ctrl/Cmd + P: 预览
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        const previewBtn = document.querySelector('.btn-secondary');
        if (previewBtn && previewBtn.textContent.includes('预览')) {
            previewBtn.click();
        }
    }
});

/**
 * 拖拽排序（预留功能）
 */
function initDragSort() {
    // 可以实现拖拽排序功能
    console.log('拖拽排序功能待实现');
}

/**
 * 图片上传预览
 */
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * 导出数据
 */
function exportData(format) {
    alert(`导出${format}格式功能开发中...`);
}

/**
 * 导入数据
 */
function importData() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json,.csv';
    
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = JSON.parse(e.target.result);
                    console.log('导入的数据:', data);
                    alert('数据导入成功！');
                } catch (err) {
                    alert('数据格式错误！');
                }
            };
            reader.readAsText(file);
        }
    });
    
    input.click();
}

/**
 * 富文本编辑器初始化（预留）
 */
function initRichEditor() {
    // 可以集成TinyMCE、CKEditor等富文本编辑器
    console.log('富文本编辑器功能待实现');
}

/**
 * 通知提示
 */
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? '#48bb78' : '#f56565'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// 添加动画样式
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

/**
 * 导出功能（供其他脚本使用）
 */
window.AdminTools = {
    showModal: showModal,
    hideModal: hideModal,
    deleteItem: deleteItem,
    deleteProblem: deleteProblem,
    previewImage: previewImage,
    exportData: exportData,
    importData: importData,
    showNotification: showNotification
};
