<?php
// 引入认证工具
require_once __DIR__ . '/app/includes/auth.php';

// 检查是否已登录
if (!isLoggedIn()) {
    redirectToLogin();
}

// 获取用户图片的函数
function getUserImages() {
    require_once __DIR__ . '/app/config/database.php';
    $db = getDB();
    $stmt = $db->prepare('SELECT user_id, img FROM user_images');
    $stmt->execute();
    $userImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $images = [];
    foreach ($userImages as $image) {
        $images[$image['user_id']] = $image['img'];
    }
    return $images;
}

// 处理画板元素保存
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_canvas_element') {
    $currentUserId = $_SESSION['user_id'];
    $posX = $_POST['pos_x'];
    $posY = $_POST['pos_y'];
    
    require_once __DIR__ . '/app/config/database.php';
    $db = getDB();
    
    // 先删除该用户的所有旧记录
    $deleteStmt = $db->prepare('DELETE FROM canvas_elements WHERE user_id = :user_id');
    $deleteStmt->bindParam(':user_id', $currentUserId);
    $deleteStmt->execute();
    
    // 插入新记录
    $insertStmt = $db->prepare('INSERT INTO canvas_elements (user_id, pos_x, pos_y) VALUES (:user_id, :pos_x, :pos_y)');
    $insertStmt->bindParam(':user_id', $currentUserId);
    $insertStmt->bindParam(':pos_x', $posX);
    $insertStmt->bindParam(':pos_y', $posY);
    
    if ($insertStmt->execute()) {
        echo json_encode(['success' => true, 'message' => '元素保存成功！', 'id' => $db->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'message' => '元素保存失败，请重试。']);
    }
    exit;
}

// 处理留言保存
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_blessing') {
    $currentUserId = $_SESSION['user_id'];
    $nickname = $_POST['nickname'] ?? '';
    $content = $_POST['content'] ?? '';
    $place = $_POST['place'] ?? '';
    
    if (empty($nickname) || empty($content) || empty($place)) {
        echo json_encode(['success' => false, 'message' => '昵称、留言内容和位置不能为空']);
        exit;
    }
    
    require_once __DIR__ . '/app/config/database.php';
    $db = getDB();
    
    $insertStmt = $db->prepare('INSERT INTO blessings (user_id, nickname, content, place) VALUES (?, ?, ?, ?)');
    $success = $insertStmt->execute([$currentUserId, $nickname, $content, $place]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => '留言保存成功！']);
    } else {
        echo json_encode(['success' => false, 'message' => '留言保存失败，请重试。']);
    }
    exit;
}

// 获取所有画板元素（包含用户名和最新留言）
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_canvas_elements') {
    require_once __DIR__ . '/app/config/database.php';
    $db = getDB();
    
    // 联表查询获取用户名，并获取每个用户的最新留言
    $stmt = $db->prepare('
        SELECT 
            e.id, e.user_id, e.pos_x, e.pos_y, u.username,
            (SELECT nickname FROM blessings WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as nickname,
            (SELECT content FROM blessings WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as blessing_content,
            (SELECT place FROM blessings WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as blessing_place
        FROM canvas_elements e 
        JOIN users u ON e.user_id = u.id 
        ORDER BY e.created_at ASC
    ');
    $stmt->execute();
    $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $userImages = getUserImages();
    foreach ($elements as &$element) {
        if (isset($userImages[$element['user_id']])) {
            $element['image_url'] = $userImages[$element['user_id']];
        } else {
            $element['image_url'] = 'https://via.placeholder.com/100x100?text=Test+Image';
        }
    }
    
    echo json_encode(['success' => true, 'elements' => $elements]);
    exit;
}

// 获取当前用户的图片URL
$currentUserImage = 'https://via.placeholder.com/100x100?text=Test+Image';
if (isLoggedIn()) {
    $userImages = getUserImages();
    $currentUserId = $_SESSION['user_id'];
    if (isset($userImages[$currentUserId])) {
        $currentUserImage = $userImages[$currentUserId];
    }
}
?>

<?php
// 引入公共头部（无导航栏）
require_once __DIR__ . '/app/includes/headerWithoutBar.php';
?>
<!-- 引导遮罩 -->
<div id="guide-overlay" class="guide-overlay">
    <div class="guide-content">
        <p class="guide-text">选择任意喜欢的位置</p>
        <p class="guide-text">放置专属与你的星空图案...</p>
        <p class="guide-subtext">点击任意处继续</p>
        <p class="guide-subtext">点击其他人的图案查看留言哦~</p>
    </div>
</div>

<!-- 浮动工具栏 -->
<div class="float-toolbar" id="float-toolbar">
    <div class="toolbar-icon" id="toolbar-icon">
        <span>★</span>
    </div>
    <div class="toolbar-menu" id="toolbar-menu">
        <div class="menu-item" data-action="reposition">重新放置图案</div>
        <div class="menu-item" data-action="blessing">留言...</div>
        <div class="menu-item" data-action="redraw">重新绘制图案</div>
        <div class="menu-item" data-action="continue">继续同在计划</div>
    </div>
</div>

<!-- 留言模态框 -->
<div id="blessing-modal" class="blessing-modal">
    <div class="blessing-content">
        <h3>写下你的留言</h3>
        <div class="form-group">
            <label>昵称</label>
            <input type="text" id="blessing-nickname">
        </div>
        <div class="form-group">
            <label>留言内容</label>
            <textarea id="blessing-content" rows="4"></textarea>
        </div>
        <div class="form-group">
            <label>此时此刻</label>
            <input type="text" id="blessing-place" placeholder="年夜饭、在家里、旅途中...">
        </div>
        <div class="modal-actions">
            <button class="btn" id="blessing-submit">提交留言</button>
            <button class="btn btn-secondary" id="blessing-cancel">取消</button>
        </div>
    </div>
</div>

<!-- 横屏提示（仅移动端竖屏显示） -->
<div class="special-message" id="rotate-message">请将手机横屏以获得最佳绘画体验~</div>
<div class="special-message" id="zoom-message">可以按住ctrl+鼠标滚轮或者浏览器选项放大页面查看哦~</div>
<div class="canvas-wrapper">
    <div class="canvas-container" id="canvas-container">
        <div class="canvas-board" id="canvas-board">
            <!-- 画板元素将在这里动态生成 -->
        </div>
    </div>
</div>

<?php
// 引入公共页脚
require_once __DIR__ . '/app/includes/footer.php';
?>

<style>
    /* 全局重置，使画板占满视口 */
    body, html {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow: hidden;
    }
    /* 引导遮罩 */
    .guide-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.5s ease;
        opacity: 1;
        pointer-events: auto;
    }
    .guide-overlay.hidden {
        opacity: 0;
        pointer-events: none;
    }
    .guide-content {
        text-align: center;
        color: #ffd700;
        text-shadow: 0 0 20px rgba(255,215,0,0.5);
    }
    .guide-text {
        font-size: 2rem;
        margin: 0.5rem 0;
        font-family: 'ShouXie', 'Microsoft YaHei', '楷体', serif;
    }
    .guide-subtext {
        font-size: 1rem;
        color: rgba(255,255,255,0.7);
        margin-top: 2rem;
    }

    /* 浮动工具栏 */
    .float-toolbar {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1500;
    }
    .toolbar-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #ffd700, #ffaa00);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        color: #0a0a23;
        box-shadow: 0 5px 20px rgba(255,215,0,0.6);
        cursor: pointer;
        transition: transform 0.3s;
    }
    .toolbar-icon:hover {
        transform: scale(1.1);
    }
    .toolbar-menu {
        position: absolute;
        bottom: 70px;
        right: 0;
        background: rgba(20,20,50,0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,215,0,0.3);
        border-radius: 15px;
        padding: 10px 0;
        min-width: 150px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: all 0.3s;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    .float-toolbar.open .toolbar-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .menu-item {
        padding: 12px 20px;
        color: #fff;
        cursor: pointer;
        transition: background 0.2s;
        font-size: 1rem;
    }
    .menu-item:hover {
        background: rgba(255,215,0,0.2);
        color: #ffd700;
    }

    /* 留言模态框 */
    .blessing-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(8px);
        z-index: 2500;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s;
    }
    .blessing-modal.active {
        opacity: 1;
        visibility: visible;
    }
    .blessing-content {
        background: rgba(30,30,70,0.95);
        border: 1px solid rgba(255,215,0,0.5);
        border-radius: 30px;
        padding: 40px;
        max-width: 500px;
        width: 90%;
        color: #fff;
        box-shadow: 0 0 60px rgba(255,215,0,0.3);
        max-height: 80vh;
        overflow-y: auto;
    }
    .blessing-content h3 {
        color: #ffd700;
        margin-bottom: 30px;
        text-align: center;
    }
    .blessing-content .form-group {
        margin-bottom: 20px;
    }
    .blessing-content label {
        display: block;
        margin-bottom: 8px;
        color: rgba(255,255,255,0.9);
    }
    .blessing-content input,
    .blessing-content textarea {
        width: 100%;
        padding: 12px 20px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 25px;
        color: #fff;
        font-size: 1rem;
    }
    .blessing-content textarea {
        resize: vertical;
        min-height: 100px;
    }
    .blessing-content .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
    }
    .btn-secondary {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .btn-secondary:hover {
        background: rgba(255,255,255,0.2);
    }

    .container{
        display: flex;
        flex-direction: column;
    }

    .info {
        font-size: 1.4rem;
        margin-bottom: 30px;
        font-family: 'ShouXie', 'Microsoft YaHei', '楷体', 'KaiTi', serif;
        color: #f8f9fa;
        justify-content: center;
    }
    .canvas-wrapper {
        display: flex;
        align-items: center;
    }
    
    .canvas-container {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        overflow: visible;
    }
    
    /* 画板原始尺寸 800x400，通过 max-width/height 等比例缩放 */
    .canvas-board {
        position: relative;
        width: 800px;
        height: 400px;
        max-width: 100vw;
        max-height: 100vh;
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 215, 0, 0.3);
        border-radius: 10px;
        cursor: crosshair;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        object-fit: contain;
    }
    
    .canvas-element {
        position: absolute;
        width: 80px;
        height: 80px;
        border-radius: 10px;
        overflow: visible;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        animation: fadeIn 0.3s ease-out;
    }
    
    .canvas-element:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.5);
        z-index: 10;
    }
    
    .canvas-element.moving {
        opacity: 0.6;
        box-shadow: 0 0 30px gold;
        transform: scale(1.05);
    }
    
    .canvas-element::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,215,0,0.3) 0%, rgba(255,215,0,0) 70%);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    
    .canvas-element:hover::before {
        opacity: 1;
        animation: glow 2s ease-in-out infinite alternate;
    }
    
    @keyframes glow {
        from { transform: scale(1); opacity: 0.5; }
        to   { transform: scale(1.1); opacity: 0.8; }
    }
    
    /* 改进的 tooltip 显示留言信息 */
    .tooltip {
        position: absolute;
        left: 50%;
        background: rgba(255, 255, 255, 0.95);
        color: #333;
        padding: 12px 16px;
        border-radius: 20px;
        font-size: 14px;
        z-index: 2000;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease;
        pointer-events: none;
        box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        border: 1px solid #ffd700;
        min-width: 200px;
        text-align: left;
        white-space: normal;
        word-break: break-word;
    }
    
    .tooltip .nickname {
        font-weight: bold;
        color: #ffd700;
        font-size: 1.1rem;
    }
    .tooltip .username-small {
        font-size: 0.85rem;
        color: #666;
        margin-left: 5px;
    }
    .tooltip .content {
        margin: 8px 0;
        color: #222;
    }
    .tooltip .place {
        font-size: 0.9rem;
        color: #888;
        font-style: italic;
    }
    .tooltip .place::before {
        content: "📍 ";
    }
    
    .canvas-element img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 10px;
    }
        /* 显示在上方 */
    .tooltip.tooltip-top {
        bottom: 110%;
        transform:  translateY(0);
    }

    /* 显示在下方 */
    .tooltip.tooltip-bottom {
        top: 110%;
        transform:  translateY(0);
    }

    /* 水平方向定位 */
    .tooltip-left {
        left: 0;
        transform: translateX(0);
    }
    .tooltip-right {
        right: 0;
        left: auto;
        transform: translateX(0);
    }
    .tooltip-center {
        left: 50%;
        transform: translateX(-50%);
    }

    /* 增加最大宽度，防止 tooltip 过宽 */
    .tooltip {
        max-width: 300px;
        word-wrap: break-word;
    }


    /* 悬停时显示 */
    .canvas-element:hover .tooltip {
        opacity: 1;
        visibility: visible;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.8); }
        to   { opacity: 1; transform: scale(1); }
    }
    
    /* 横屏提示样式 */
    .special-message {
        display: none;
        position: fixed;
        top: 10px;
        left: 0;
        width: 100%;
        text-align: center;
        color: #ffd700;
        background: rgba(0,0,0,0.7);
        padding: 10px;
        z-index: 1000;
        font-size: 14px;
        backdrop-filter: blur(5px);
        border-bottom: 1px solid rgba(255,215,0,0.3);
    }
    @media (orientation: portrait) {
        #rotate-message {
            display: block;
        }
    }
    @media (min-width: 1200px) {
        #zoom-message {
            display: block;
        }
    }
</style>

<script>
    // 从PHP获取当前用户的图片URL和用户ID
    const currentUserImage = '<?php echo addslashes($currentUserImage); ?>';
    const currentUserId = <?php echo $_SESSION['user_id']; ?>;
    
    class CanvasManager {
        constructor() {
            this.canvasBoard = document.getElementById('canvas-board');
            this.elements = [];
            this.isMobile = window.innerWidth <= 768;
            this.isPlacementMode = false; // 放置模式开关
            this.userHasElement = false; // 新增：当前用户是否有图案
            this.init();
        }

        init() {
            this.loadElements();
            this.bindEvents();
        }

        removeAllUserElements(userId) {
            const elementsToRemove = document.querySelectorAll(`.canvas-element[data-user-id="${userId}"]`);
            elementsToRemove.forEach(el => el.remove());
            this.elements = this.elements.filter(el => el.user_id != userId);
        }

        bindEvents() {
            this.canvasBoard.addEventListener('click', (e) => {
                const elementDiv = e.target.closest('.canvas-element');

                if (elementDiv) {
                    // 点击图案：什么也不做，仅保留悬停显示留言（已在CSS实现）
                    // 如果需要退出放置模式，可以取消注释下一行
                    // this.exitPlacementMode();
                } else {
                    this.handleCanvasClick(e);
                }
            });

            window.addEventListener('resize', () => {
                this.isMobile = window.innerWidth <= 768;
            });
        }

        handleCanvasClick(e) {
            if (!this.isPlacementMode) return; // 非放置模式忽略点击

            const rect = this.canvasBoard.getBoundingClientRect();

            let clickX, clickY;

            if (this.isMobile) {
                const touch = e.touches ? e.touches[0] : e;
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                const rotatedX = touch.clientY - centerY;
                const rotatedY = centerX - touch.clientX;
                const boardX = rotatedX + rect.width / 2;
                const boardY = rotatedY + rect.height / 2;

                const scaleX = 800 / rect.width;
                const scaleY = 400 / rect.height;
                clickX = boardX * scaleX;
                clickY = boardY * scaleY;
            } else {
                const relativeX = e.clientX - rect.left;
                const relativeY = e.clientY - rect.top;

                const scaleX = 800 / rect.width;
                const scaleY = 400 / rect.height;
                clickX = relativeX * scaleX;
                clickY = relativeY * scaleY;
            }

            clickX = Math.max(0, Math.min(800, clickX));
            clickY = Math.max(0, Math.min(400, clickY));

            // 放置模式：添加元素（覆盖旧位置）
            this.addElement(clickX, clickY);
            this.exitPlacementMode();
            window.location.reload();
        }

        addElement(posX, posY) {
            this.removeAllUserElements(currentUserId);

            const element = {
                pos_x: posX,
                pos_y: posY,
                image_url: currentUserImage,
                user_id: currentUserId,
                username: 'wlh'
            };

            this.saveElement(element);

            element.id = Date.now();
            this.elements.push(element);
            this.renderElement(element);
        }

        saveElement(element) {
            fetch('canvas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=save_canvas_element&pos_x=${element.pos_x}&pos_y=${element.pos_y}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const existingIndex = this.elements.findIndex(e => e.id == element.id);
                    if (existingIndex !== -1) {
                        this.elements[existingIndex].id = data.id;
                    }
                } else {
                    alert('保存失败: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        loadElements() {
        fetch('canvas.php?action=get_canvas_elements')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.elements = data.elements;
                this.renderAllElements();
                // 检查当前用户是否有图案
                this.userHasElement = this.elements.some(el => el.user_id == currentUserId);
            }
        })
        .catch(error => {
            console.error('Error loading elements:', error);
        });
    }

        renderAllElements() {
            this.canvasBoard.innerHTML = '';
            this.elements.forEach(element => this.renderElement(element));
        }

        renderElement(element) {
            const elementDiv = document.createElement('div');
            elementDiv.className = 'canvas-element';
            elementDiv.style.left = `${element.pos_x - 40}px`;
            elementDiv.style.top = `${element.pos_y - 40}px`;
            elementDiv.dataset.id = element.id;
            elementDiv.dataset.userId = element.user_id;

            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';

            // 垂直方向判断（阈值150可调整）
            if (element.pos_y < 150) {
                tooltip.classList.add('tooltip-bottom');
            } else {
                tooltip.classList.add('tooltip-top');
            }

            // 水平方向判断（画板宽度800，阈值100可调整）
            const leftDistance = element.pos_x;
            const rightDistance = 800 - element.pos_x;
            if (leftDistance < 200) {
                // 太靠左：左对齐
                tooltip.classList.add('tooltip-left');
            } else if (rightDistance < 200) {
                // 太靠右：右对齐
                tooltip.classList.add('tooltip-right');
            } else {
                // 正常居中
                tooltip.classList.add('tooltip-center');
            }

            // 判断是否有留言数据
            if (element.nickname && element.blessing_content && element.blessing_place) {
                tooltip.innerHTML = `
                    <span class="nickname">${escapeHtml(element.nickname)}</span><span class="username-small">@${escapeHtml(element.username)}</span>
                    <div class="content">${escapeHtml(element.blessing_content)}</div>
                    <div class="place">${escapeHtml(element.blessing_place)}</div>
                `;
            } else {
                // 没有留言时只显示用户名
                tooltip.innerHTML = `<span class="nickname">${escapeHtml(element.username)}</span>`;
            }
            elementDiv.appendChild(tooltip);

            const img = document.createElement('img');
            img.src = element.image_url || 'https://via.placeholder.com/100x100?text=Test+Image';
            img.alt = '画板元素';
            elementDiv.appendChild(img);

            this.canvasBoard.appendChild(elementDiv);
        }

        // 进入放置模式
        enterPlacementMode() {
            this.isPlacementMode = true;
            this.canvasBoard.style.cursor = 'copy'; // 视觉提示
            // 可在此添加浮动提示（可选）
        }

        // 退出放置模式
        exitPlacementMode() {
            if (this.isPlacementMode) {
                this.isPlacementMode = false;
                this.canvasBoard.style.cursor = 'crosshair'; // 恢复默认
            }
        }
    }
    
    let canvasManager;
    window.addEventListener('load', () => {
        canvasManager = new CanvasManager();
    });

    // 引导遮罩关闭
    const overlay = document.getElementById('guide-overlay');
    if (overlay) {
        overlay.addEventListener('click', function() {
            this.classList.add('hidden');
            // 如果用户还没有放置图案，自动进入放置模式
            if (canvasManager && !canvasManager.userHasElement) {
                canvasManager.enterPlacementMode();
                // 可选：短暂提示
                // alert('点击画板任意位置放置你的星空图案');
            }
        });
    }

    // 浮动工具栏
    const toolbar = document.getElementById('float-toolbar');
    const icon = document.getElementById('toolbar-icon');
    if (toolbar && icon) {
        icon.addEventListener('click', function(e) {
            e.stopPropagation();
            toolbar.classList.toggle('open');
        });
        document.addEventListener('click', function(e) {
            if (!toolbar.contains(e.target)) {
                toolbar.classList.remove('open');
            }
        });
        
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                const action = this.dataset.action;
                switch (action) {
                    case 'reposition':
                        // 进入放置模式
                        if (canvasManager) { // 需要获取 CanvasManager 实例
                            canvasManager.enterPlacementMode();
                        }
                        toolbar.classList.remove('open');
                        break;
                    case 'blessing':
                        showBlessingModal();
                        toolbar.classList.remove('open');
                        break;
                    case 'redraw':
                        if (confirm('确定吗？旧的图案会完全删除哦')) {
                            window.location.href = 'futureForNewYear.php';
                        }
                        toolbar.classList.remove('open');
                        break;
                    case 'continue':
                        window.location.href = 'splashs/splash6.php';
                        toolbar.classList.remove('open');
                        break;
                }
            });
        });
    }

    // 留言模态框
const blessingModal = document.getElementById('blessing-modal');
if (blessingModal) {
    // 使用事件委托监听模态框内的点击
    blessingModal.addEventListener('click', function(e) {
        const target = e.target;
        if (target.id === 'blessing-submit') {
            // 阻止默认行为和冒泡
            e.preventDefault();
            e.stopPropagation();

            // 显示提示
            submitButton();
        } else if (target.id === 'blessing-cancel') {
            e.preventDefault();
            e.stopPropagation();
            hideBlessingModal();
        } else if (target === blessingModal) {
            // 点击背景关闭
            hideBlessingModal();
        }
    });
}

    // 原来的 hideBlessingModal 和 showBlessingModal 保持不变
    function showBlessingModal() {
        blessingModal.classList.add('active');
    }

    function hideBlessingModal() {
        blessingModal.classList.remove('active');
        // 清空输入（保留昵称默认）
        document.getElementById('blessing-content').value = '';
        document.getElementById('blessing-place').value = '';
    }

    function submitButton() {
        const nickname = document.getElementById('blessing-nickname').value.trim();
        const content = document.getElementById('blessing-content').value.trim();
        const place = document.getElementById('blessing-place').value.trim();

        if (!nickname || !content || !place) {
            alert('请填写完整信息');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'save_blessing');
        formData.append('nickname', nickname);
        formData.append('content', content);
        formData.append('place', place);

        fetch('canvas.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('留言保存成功！');
                hideBlessingModal();
                window.location.reload(); // 刷新页面显示新留言
            } else {
                alert('保存失败: ' + data.message);
            }
        })
        .catch(error => {
            alert('网络错误，请重试');
        });
    }

    // 辅助函数：HTML转义
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>