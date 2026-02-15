<?php
// 引入认证工具
require_once __DIR__ . '/app/includes/auth.php';

// 检查是否已登录
if (!isLoggedIn()) {
    redirectToLogin();
}

// 启动会话
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 引入公共头部（无导航栏）
require_once __DIR__ . '/app/includes/headerWithoutBar.php';
?>

<style>
     @font-face {
            font-family: 'ShouXie';
            src: url('/../fonts/shouxie.ttf') format('truetype');
            font-display: swap;
    }
    /* 页面专属样式 */
    .witness-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 30px 20px 50px;
        min-height: 70vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    /* 主卡片区域 */
    .witness-card {
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 215, 0, 0.2);
        border-radius: 40px;
        padding: 40px;
        width: 100%;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        transition: all 0.3s ease;
        animation: fadeScale 0.6s ease-out;
    }

    @keyframes fadeScale {
        0% { opacity: 0; transform: scale(0.98); }
        100% { opacity: 1; transform: scale(1); }
    }

    /* 页码指示器 */
    .page-indicator {
        display: flex;
        gap: 15px;
        margin: 30px 0 20px;
    }

    .indicator-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        cursor: pointer;
        transition: all 0.3s;
        border: 1px solid rgba(255, 215, 0, 0.3);
    }

    .indicator-dot.active {
        background: #ffd700;
        box-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
        transform: scale(1.3);
    }

    .indicator-dot:hover {
        background: rgba(255, 215, 0, 0.5);
    }

    /* 视频容器 - 适应竖屏视频 */
    .video-wrapper {
        position: relative;
        width: 100%;
        max-height: 80vh;          /* 限制最大高度，避免过高 */
        background: #000;           /* 背景黑色，用于填充留白 */
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .video-wrapper video {
        width: 100%;
        height: auto;
        max-height: 80vh;
        object-fit: contain;        /* 完整显示视频，保留比例，黑边填充 */
        border-radius: 25px;
        display: block;
    }

    /* 寄语卡片样式 */
    .message-header {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .teacher-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        overflow: hidden;
        background: linear-gradient(135deg, #ffd700, #ffaa00);
        box-shadow: 0 0 30px rgba(255, 215, 0, 0.6);
        border: 3px solid rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .teacher-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .teacher-name {
        font-size: 2.2rem;
        color: #ffd700;
        text-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
    }

    .message-content {
        font-size: 1.4rem;
        line-height: 1.8;
        color: #f8f9fa;
        text-align: justify;
        white-space: pre-line;  /* 保留换行，配合打字机效果 */
        background: rgba(255, 255, 255, 0.03);
        padding: 25px;
        border-radius: 25px;
        border-left: 5px solid #ffd700;
        font-family: 'ShouXie', 'Microsoft YaHei', '楷体', 'KaiTi', serif;
        letter-spacing: 0.5px;
        min-height: 200px;      /* 确保有足够高度 */
    }

    .math-quote {
        color: #ffd700;
        font-size: 1.6rem;
        font-weight: bold;
        text-align: center;
        margin: 30px 0 0;
        font-style: italic;
        text-shadow: 0 0 15px rgba(255,215,0,0.6);
    }

    /* 导航按钮 */
    .nav-buttons {
        display: flex;
        gap: 20px;
        margin-top: 30px;
    }

    .nav-btn {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 215, 0, 0.4);
        color: white;
        padding: 12px 30px;
        border-radius: 50px;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s;
        backdrop-filter: blur(5px);
    }

    .nav-btn:hover:not(:disabled) {
        background: rgba(255, 215, 0, 0.2);
        border-color: #ffd700;
        transform: translateX(-3px);
        box-shadow: 0 0 20px rgba(255,215,0,0.3);
    }

    .nav-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    .back-link {
        margin-top: 40px;
        text-align: center;
    }
    .back-link a {
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        font-size: 1rem;
        transition: 0.3s;
    }
    .back-link a:hover {
        color: #ffd700;
        text-shadow: 0 0 10px rgba(255,215,0,0.5);
    }

    .special {
            color: #ffd700;
            text-shadow: 0 0 20px rgba(255,215,0,0.7);
            font-weight: 400;
        }

    @media (max-width: 700px) {
        .witness-card { padding: 25px; }
        .message-header { flex-direction: column; text-align: center; }
        .teacher-name { font-size: 1.8rem; }
        .message-content { font-size: 1.2rem; }
        .nav-buttons { flex-direction: column; width: 100%; }
        .nav-btn { width: 100%; }
    }
</style>

<main>
    <div class="witness-container">
        <!-- 主卡片：动态切换内容 -->
        <div class="witness-card" id="witness-card">
            <!-- 内容将通过 JS 动态填充 -->
        </div>

        <!-- 页码指示器 -->
        <div class="page-indicator" id="indicator">
            <span class="indicator-dot active" data-page="0"></span>
            <span class="indicator-dot" data-page="1"></span>
            <span class="indicator-dot" data-page="2"></span>
        </div>

        <!-- 上一页/下一页按钮 -->
        <div class="nav-buttons">
            <button class="nav-btn" id="prevBtn" disabled>← 上一页</button>
            <button class="nav-btn" id="nextBtn">下一页 →</button>
        </div>

        <div class="back-link" id="backLink">
            <a href="dashboard.php">← 返回主页面</a>
        </div>
    </div>
</main>

<script>
    // 三页内容定义
    const pages = [
        {
            type: 'video',
            title: '🎬 新年视频',
            videoSrc: 'video/newyear.mp4'  // 请将视频文件放在此路径
        },
        {
            type: 'message',
            teacher: '许毅',
            avatarSrc: 'images/xuyi.jpg',
            content: '祝福同学们在新的一年中，快乐是<span class="special">指数增长</span>、烦恼是<span class="special">对数衰减</span>、幸福是在整个定义域上<span class="special">恒正且严格增</span>！',
            extra: '—— 许毅 老师'
        },
        {
            type: 'message',
            teacher: 'Fiona',
            avatarSrc: 'images/fiona.jpg',
            content: '亲爱的十一班的同学们：\n很高兴和你们一起度过了美好的高中时光。我们互相陪伴，共同成长。我看见了你们的<span class="special">热情，真诚，友善，自律</span>，也感受到了你们的<span class="special">温暖和阳光</span>。和十一班同学们在一起总是很开心。很喜欢和同学们一起探寻生活的意义和美好。<span class="special">但行好事，莫问前程</span>。<span class="special">Every step counts!</span> 当你不渴望成功，而只是去做。你的人生才真正开始了。祝同学们新的一年里<span class="special">学业进步，开开心心，马年大吉！</span>',
            extra: '—— Fiona 老师'
        }
    ];

    let currentPage = 0;
    let typingTimer = null;  // 打字机定时器

    const card = document.getElementById('witness-card');
    const dots = document.querySelectorAll('.indicator-dot');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const backLink = document.getElementById('backLink');

    // 打字机函数
    function typeWriter(container, htmlString, speed = 50, callback) {
    // 清除之前的定时器
    if (typingTimer) clearInterval(typingTimer);

    // 清空容器
    container.innerHTML = '';

    // 创建一个临时容器，将 HTML 解析为 DOM 树
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = htmlString;

    // 递归遍历节点，构建一个包含所有节点（包括标签）的线性序列
    const nodeQueue = [];
    function traverse(node) {
        if (node.nodeType === Node.TEXT_NODE) {
            // 文本节点：将每个字符拆分成独立的任务
            const text = node.textContent;
            for (let i = 0; i < text.length; i++) {
                nodeQueue.push({
                    type: 'char',
                    char: text.charAt(i)
                });
            }
        } else if (node.nodeType === Node.ELEMENT_NODE) {
            // 元素节点：先插入开标签，再递归子节点，最后插入闭标签
            nodeQueue.push({
                type: 'openTag',
                tagName: node.tagName,
                attributes: node.attributes
            });
            // 遍历子节点
            for (let child of node.childNodes) {
                traverse(child);
            }
            nodeQueue.push({
                type: 'closeTag',
                tagName: node.tagName
            });
        }
    }
    traverse(tempDiv);

    let index = 0;

    function step() {
        if (index >= nodeQueue.length) {
            // 完成
            typingTimer = null;
            if (callback) callback();
            return;
        }

        const item = nodeQueue[index];
        if (item.type === 'char') {
            // 追加一个字符
            container.innerHTML += item.char;
            index++;
            typingTimer = setTimeout(step, speed);
        } else if (item.type === 'openTag') {
            // 创建开标签并追加
            const tag = document.createElement(item.tagName);
            // 设置属性
            for (let attr of item.attributes) {
                tag.setAttribute(attr.name, attr.value);
            }
            container.appendChild(tag);
            index++;
            // 立即处理下一个（因为标签不占用时间）
            step();
        } else if (item.type === 'closeTag') {
            // 闭标签：实际上在 DOM 结构中不需要手动添加，因为 appendChild 已经维护了层次
            // 但我们需要将当前焦点移回父容器
            // 在递归实现中，我们不需要显式处理闭标签，因为开标签已经创建了元素，后续字符会添加到最后打开的元素内
            // 但为了保持递归顺序，我们需要在闭标签时“关闭”当前元素，即设置当前容器为父元素
            // 通过维护一个栈来实现更好，但我们的方法是在遍历时已经构建了线性序列，实际 DOM 操作会由开标签和字符完成，闭标签只是逻辑标记
            // 这里简单地忽略闭标签，因为我们通过开标签创建元素，后续字符自动添加到该元素内
            index++;
            step();
        }
    }

    typingTimer = setTimeout(step, speed);
}

    // 渲染页面
    function renderPage(index) {
        const page = pages[index];

        // 清除之前的打字机
        if (typingTimer) {
            clearInterval(typingTimer);
            typingTimer = null;
        }

        let html = '';

        if (page.type === 'video') {
            html = `
                <h2 style="color:#ffd700; margin-bottom:20px; text-align:center;">${page.title}</h2>
                <div class="video-wrapper">
                    <video controls preload="metadata" poster="images/video-poster.jpg">
                        <source src="${page.videoSrc}" type="video/mp4">
                        您的浏览器不支持视频播放。
                    </video>
                </div>
                <p style="text-align:center; color:rgba(255,255,255,0.6); margin-top:20px;">点击播放，接收来自林老师的祝福( •̀ ω •́ )✧</p>
            `;
        } else {
            html = `
                <div class="message-header">
                    <div class="teacher-avatar">
                        <img src="${page.avatarSrc}" alt="${page.teacher}" onerror="this.onerror=null; this.src='images/default-avatar.png';">
                    </div>
                    <div class="teacher-name">${page.teacher}</div>
                </div>
                <div class="message-content" id="message-content"></div>
                <div class="math-quote">${page.extra}</div>
            `;
        }

        // 触发动画
        card.style.animation = 'none';
        card.offsetHeight;
        card.style.animation = 'fadeScale 0.6s ease-out';
        card.innerHTML = html;

        if (page.type === 'message') {
            const contentDiv = document.getElementById('message-content');
            // 开始打字效果，速度 50ms/字
            typeWriter(contentDiv, page.content, 100);
        }

        // 更新指示器
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });

        // 更新按钮状态和文本
        prevBtn.disabled = index === 0;
        if (index === pages.length - 1) {
            // 最后一页：下一页按钮变为“星屿共筑”，可点击，不disabled
            nextBtn.disabled = false;
            nextBtn.textContent = '星屿共筑 →';
            // 隐藏主返回页面链接
            backLink.style.display = 'none';
        } else {
            nextBtn.disabled = false;
            nextBtn.textContent = '下一页 →';
            backLink.style.display = 'none';
        }
    }

    // 下一页点击事件
    nextBtn.addEventListener('click', () => {
        if (currentPage === pages.length - 1) {
            // 最后一页，跳转到 splashs/splashs5.php
            window.location.href = 'splashs/splash5.php';
        } else if (currentPage < pages.length - 1) {
            currentPage++;
            renderPage(currentPage);
        }
    });

    // 上一页点击事件
    prevBtn.addEventListener('click', () => {
        if (currentPage > 0) {
            currentPage--;
            renderPage(currentPage);
        }
    });

    // 点圆点切换
    dots.forEach(dot => {
        dot.addEventListener('click', (e) => {
            const page = parseInt(e.target.dataset.page);
            if (page !== currentPage) {
                currentPage = page;
                renderPage(currentPage);
            }
        });
    });

    // 初始渲染
    renderPage(0);
</script>

<?php
// 引入公共页脚
require_once __DIR__ . '/app/includes/footer.php';
?>