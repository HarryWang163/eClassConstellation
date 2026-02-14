<?php
// 引入认证工具
require_once __DIR__ . '/app/includes/auth.php';

// 检查是否已登录
if (!isLoggedIn()) {
    redirectToLogin();
}

// 获取用户图片的函数
function getUserImages() {
    // 引入数据库连接
    require_once __DIR__ . '/app/config/database.php';
    $db = getDB();
    
    // 从数据库获取所有用户图片
    $stmt = $db->prepare('SELECT user_id, img FROM user_images');
    $stmt->execute();
    $userImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 转为数组格式，user_id为key
    $images = [];
    foreach ($userImages as $image) {
        $images[$image['user_id']] = $image['img'];
    }
    
    return $images;
}

// 处理画板元素保存
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_canvas_element') {
    // 获取当前用户ID
    $currentUserId = $_SESSION['user_id'];
    $posX = $_POST['pos_x'];
    $posY = $_POST['pos_y'];
    
    // 引入数据库连接
    require_once __DIR__ . '/app/config/database.php';
    $db = getDB();
    
    // 检查用户是否已存在画板元素
    $stmt = $db->prepare('SELECT id FROM canvas_elements WHERE user_id = :user_id');
    $stmt->bindParam(':user_id', $currentUserId);
    $stmt->execute();
    $existingElement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingElement) {
        // 如果存在，更新现有元素
        $stmt = $db->prepare('UPDATE canvas_elements SET pos_x = :pos_x, pos_y = :pos_y WHERE user_id = :user_id');
        $stmt->bindParam(':user_id', $currentUserId);
        $stmt->bindParam(':pos_x', $posX);
        $stmt->bindParam(':pos_y', $posY);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => '元素更新成功！', 'id' => $existingElement['id']]);
        } else {
            echo json_encode(['success' => false, 'message' => '元素更新失败，请重试。']);
        }
    } else {
        // 如果不存在，插入新元素
        $stmt = $db->prepare('INSERT INTO canvas_elements (user_id, pos_x, pos_y) VALUES (:user_id, :pos_x, :pos_y)');
        $stmt->bindParam(':user_id', $currentUserId);
        $stmt->bindParam(':pos_x', $posX);
        $stmt->bindParam(':pos_y', $posY);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => '元素保存成功！', 'id' => $db->lastInsertId()]);
        } else {
            echo json_encode(['success' => false, 'message' => '元素保存失败，请重试。']);
        }
    }
    exit;
}

// 获取所有画板元素
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_canvas_elements') {
    // 引入数据库连接
    require_once __DIR__ . '/app/config/database.php';
    $db = getDB();
    
    // 从数据库获取所有画板元素
    $stmt = $db->prepare('SELECT id, user_id, pos_x, pos_y FROM canvas_elements ORDER BY created_at ASC');
    $stmt->execute();
    $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 获取所有用户图片
    $userImages = getUserImages();
    
    // 为每个元素添加图片URL
    foreach ($elements as &$element) {
        if (isset($userImages[$element['user_id']])) {
            $element['image_url'] = $userImages[$element['user_id']];
        } else {
            // 默认占位图片
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
// 引入公共头部
require_once __DIR__ . '/app/includes/header.php';
?>

        <header>
            <h1>班级画板</h1>
            <p class="subtitle">共同创作，记录美好时光</p>
        </header>
        
        <div class="canvas-wrapper">
            <div class="canvas-container" id="canvas-container">
                <div class="canvas-board" id="canvas-board">
                    <!-- 画板元素将在这里动态生成 -->
                </div>
            </div>
            
            <div class="canvas-info">
                <p>点击画板添加图片元素</p>
                <p>画板尺寸: 1280 x 800</p>
            </div>
        </div>

<?php
// 引入公共页脚
require_once __DIR__ . '/app/includes/footer.php';
?>

<style>
        .canvas-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            min-height: calc(100vh - 200px);
        }
        
        .canvas-container {
            position: relative;
            width: 100%;
            max-width: 1280px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: visible;
        }
        
        .canvas-board {
            position: relative;
            width: 1280px;
            height: 800px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 215, 0, 0.3);
            border-radius: 10px;
            cursor: crosshair;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .canvas-element {
            position: absolute;
            width: 100px;
            height: 100px;
            border: 2px solid rgba(255, 215, 0, 0.5);
            border-radius: 10px;
            overflow: visible;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            animation: fadeIn 0.3s ease-out;
        }
        
        .canvas-element:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
            z-index: 10;
        }
        
        /* 星光光晕效果 */
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
            from {
                transform: scale(1);
                opacity: 0.5;
            }
            to {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }
        
        /* 超椭圆提示框 */
        .tooltip {
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease;
            transform: translateX(-50%) translateY(10px);
            pointer-events: none;
        }
        
        .canvas-element:hover .tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }
        
        /* 提示框箭头 */
        .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: rgba(255, 255, 255, 0.9) transparent transparent transparent;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .canvas-element:hover .tooltip::after {
            opacity: 1;
        }
        
        .canvas-element img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .canvas-info {
            margin-top: 15px;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* 移动端适配 - 画板旋转90度 */
        @media (max-width: 768px) {
            .canvas-wrapper {
                padding: 10px;
                min-height: 100vh;
                justify-content: center;
            }
            
            .canvas-container {
                width: 100vh;
                height: 100vw;
                transform: rotate(90deg);
                transform-origin: center center;
                position: absolute;
                top: 50%;
                left: 50%;
                margin-top: -50vw;
                margin-left: -50vh;
            }
            
            .canvas-board {
                width: 100%;
                height: 100%;
                max-width: 1280px;
                max-height: 800px;
            }
            
            .canvas-info {
                font-size: 0.8rem;
                position: relative;
                z-index: 10;
            }
        }
        
        /* 小屏幕移动端适配 */
        @media (max-width: 480px) {
            .canvas-info {
                font-size: 0.7rem;
            }
        }
    </style>
    
    <script>
        // 从PHP获取当前用户的图片URL和用户ID
        const currentUserImage = '<?php echo addslashes($currentUserImage); ?>';
        const currentUserId = <?php echo $_SESSION['user_id']; ?>;
        
        // 获取元素提示文本的函数
        function getElementTooltipText(userId) {
            // 示例数据：实际应该从数据库或其他来源获取
            const userTexts = {
                1: '班级之星 - 张三',
                2: '绘画高手 - 李四',
                3: '创意达人 - 王五',
                4: '学习委员 - 赵六',
                5: '体育健将 - 孙七'
            };
            
            // 如果有对应用户的文本，返回该文本
            if (userTexts[userId]) {
                return userTexts[userId];
            }
            
            // 默认文本
            return `用户 ${userId}`;
        }
        
        // 画板管理类
        class CanvasManager {
            constructor() {
                this.canvasBoard = document.getElementById('canvas-board');
                
                this.elements = [];
                this.isMobile = window.innerWidth <= 768;
                
                this.init();
            }
            
            init() {
                this.loadElements();
                this.bindEvents();
            }
            
            bindEvents() {
                // 画板点击事件
                this.canvasBoard.addEventListener('click', (e) => {
                    if (e.target === this.canvasBoard || e.target.closest('.canvas-board')) {
                        // 检查是否点击的是元素内部
                        const elementDiv = e.target.closest('.canvas-element');
                        if (elementDiv) {
                            // 如果点击的是元素，不创建新元素
                            return;
                        }
                        
                        const rect = this.canvasBoard.getBoundingClientRect();
                        let x, y;
                        
                        if (this.isMobile) {
                            // 移动端：考虑旋转后的坐标转换
                            const touch = e.touches ? e.touches[0] : e;
                            const centerX = rect.left + rect.width / 2;
                            const centerY = rect.top + rect.height / 2;
                            
                            // 旋转90度后的坐标转换
                            const rotatedX = touch.clientY - centerY;
                            const rotatedY = centerX - touch.clientX;
                            
                            x = rotatedX + rect.width / 2;
                            y = rotatedY + rect.height / 2;
                        } else {
                            // 桌面端：直接计算坐标
                            x = e.clientX - rect.left;
                            y = e.clientY - rect.top;
                        }
                        
                        // 直接添加图片元素
                        this.addElement(x, y);
                    }
                });
                
                // 窗口大小改变时更新移动端状态
                window.addEventListener('resize', () => {
                    this.isMobile = window.innerWidth <= 768;
                });
            }
            
            addElement(posX, posY) {
                // 删除用户先前创建的所有旧元素
                const userId = this.getCurrentUserId();
                
                // 首先从DOM中移除所有旧元素
                this.elements.forEach(element => {
                    if (element.user_id == userId) {
                        const elementDiv = document.querySelector(`[data-id="${element.id}"]`);
                        elementDiv.remove();
                    }
                });
                
                // 从数组中移除所有旧元素
                this.elements = this.elements.filter(e => e.user_id != userId);
                
                const element = {
                    pos_x: posX,
                    pos_y: posY,
                    image_url: currentUserImage
                };
                
                // 保存到数据库
                this.saveElement(element);
                
                // 立即在画板上显示
                element.id = Date.now(); // 临时ID
                element.user_id = userId;
                this.elements.push(element);
                this.renderElement(element);
            }
            
            saveElement(element) {
                fetch('canvas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=save_canvas_element&pos_x=${element.pos_x}&pos_y=${element.pos_y}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 更新元素ID
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
                    alert('保存失败，请重试');
                });
            }
            
            loadElements() {
                fetch('canvas.php?action=get_canvas_elements')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.elements = data.elements;
                        this.renderAllElements();
                    }
                })
                .catch(error => {
                    console.error('Error loading elements:', error);
                });
            }
            
            renderAllElements() {
                this.canvasBoard.innerHTML = '';
                this.elements.forEach(element => {
                    this.renderElement(element);
                });
            }
            
            renderElement(element) {
                const elementDiv = document.createElement('div');
                elementDiv.className = 'canvas-element';
                elementDiv.style.left = `${element.pos_x - 50}px`;
                elementDiv.style.top = `${element.pos_y - 50}px`;
                elementDiv.dataset.id = element.id;
                
                // 创建tooltip元素
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = getElementTooltipText(element.user_id);
                elementDiv.appendChild(tooltip);
                
                const img = document.createElement('img');
                img.src = element.image_url || 'https://via.placeholder.com/100x100?text=Test+Image';
                img.alt = '画板元素';
                
                elementDiv.appendChild(img);
                
                // 添加移动端触摸事件
                if (this.isMobile) {
                    this.addMobileTouchEvents(elementDiv);
                }
                
                this.canvasBoard.appendChild(elementDiv);
            }
            
            getCurrentUserId() {
                // 从session中获取当前用户ID
                return currentUserId;
            }
            
            addMobileTouchEvents(elementDiv) {
                let isTouching = false;
                let touchTimer;
                
                // 触摸开始
                elementDiv.addEventListener('touchstart', (e) => {
                    isTouching = true;
                    
                    // 清除之前的定时器
                    if (touchTimer) {
                        clearTimeout(touchTimer);
                    }
                    
                    // 1秒后显示tooltip（模拟长按）
                    touchTimer = setTimeout(() => {
                        if (isTouching) {
                            const tooltip = elementDiv.querySelector('.tooltip');
                            if (tooltip) {
                                tooltip.style.opacity = '1';
                                tooltip.style.visibility = 'visible';
                                tooltip.style.transform = 'translateX(-50%) translateY(0)';
                            }
                            
                            // 同时触发光晕效果
                            const element = elementDiv;
                            element.style.boxShadow = '0 5px 15px rgba(255, 215, 0, 0.3)';
                        }
                    }, 1000);
                });
                
                // 触摸移动
                elementDiv.addEventListener('touchmove', () => {
                    isTouching = false;
                    if (touchTimer) {
                        clearTimeout(touchTimer);
                    }
                    
                    // 隐藏tooltip
                    const tooltip = elementDiv.querySelector('.tooltip');
                    if (tooltip) {
                        tooltip.style.opacity = '0';
                        tooltip.style.visibility = 'hidden';
                        tooltip.style.transform = 'translateX(-50%) translateY(10px)';
                    }
                    
                    // 隐藏光晕效果
                    const element = elementDiv;
                    element.style.boxShadow = '';
                });
                
                // 触摸结束
                elementDiv.addEventListener('touchend', () => {
                    isTouching = false;
                    if (touchTimer) {
                        clearTimeout(touchTimer);
                    }
                    
                    // 延迟隐藏tooltip
                    setTimeout(() => {
                        const tooltip = elementDiv.querySelector('.tooltip');
                        if (tooltip) {
                            tooltip.style.opacity = '0';
                            tooltip.style.visibility = 'hidden';
                            tooltip.style.transform = 'translateX(-50%) translateY(10px)';
                        }
                        
                        // 隐藏光晕效果
                        const element = elementDiv;
                        element.style.boxShadow = '';
                    }, 2000);
                });
            }
        }
        
        // 初始化画板管理器
        window.addEventListener('load', () => {
            new CanvasManager();
        });
    </script>