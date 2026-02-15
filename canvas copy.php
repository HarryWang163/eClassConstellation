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

// 获取所有画板元素（包含用户名）
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_canvas_elements') {
    require_once __DIR__ . '/app/config/database.php';
    $db = getDB();
    
    // 联表查询获取用户名
    $stmt = $db->prepare('
        SELECT e.id, e.user_id, e.pos_x, e.pos_y, u.username 
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
            $element['image_url'] = 'https://via.placeholder.com/150x150?text=Test+Image';
        }
    }
    
    echo json_encode(['success' => true, 'elements' => $elements]);
    exit;
}

// 获取当前用户的图片URL
$currentUserImage = 'https://via.placeholder.com/150x150?text=Test+Image';
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

<!-- 移除原有标题，只保留画板主体 -->
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
        overflow: hidden; /* 防止滚动条干扰全屏 */
    }
    
    .canvas-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;          /* 占满整个视口高度 */
        width: 100vw;           /* 占满整个视口宽度 */
        background: linear-gradient(135deg, #0a0a23 0%, #1e1e4a 50%, #3a3a7a 100%);
        position: relative;
    }
    
    .canvas-container {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        overflow: visible;
    }
    
    .canvas-board {
        position: relative;
        width: 100%;
        height: auto;
        aspect-ratio: 1280 / 800;          /* 固定宽高比 */
        max-width: 100vw;
        max-height: 100vh;
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 215, 0, 0.3);
        border-radius: 10px;
        cursor: crosshair;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        margin: auto;                        /* 确保居中 */
    }
    
    .canvas-element {
        position: absolute;
        width: 150px;            /* 放大到150px */
        height: 150px;
        border-radius: 10px;
        overflow: visible;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        animation: fadeIn 0.3s ease-out;
    }
    
    /* 悬停高亮 */
    .canvas-element:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.5);
        z-index: 10;
    }
    
    /* 移动模式样式 */
    .canvas-element.moving {
        opacity: 0.6;
        box-shadow: 0 0 30px gold;
        transform: scale(1.05);
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
    
    /* 提示框 */
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
    
    .tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: rgba(255, 255, 255, 0.9) transparent transparent transparent;
    }
    
    .canvas-element img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 10px;
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
    
    /* 移动端适配 - 画板旋转90度（保持原逻辑） */
    @media (max-width: 768px) {
        .canvas-board {
            /* 旋转处理已在JS中完成，此处无需额外样式 */
        }
    }
</style>

<script>
    // 从PHP获取当前用户的图片URL和用户ID
    const currentUserImage = '<?php echo addslashes($currentUserImage); ?>';
    const currentUserId = <?php echo $_SESSION['user_id']; ?>;
    
    // 画板管理类
    class CanvasManager {
        constructor() {
            this.canvasBoard = document.getElementById('canvas-board');
            this.elements = [];
            this.isMobile = window.innerWidth <= 768;
            this.isMovingMode = false;
            this.movingElementId = null;
            
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
                    const elementId = elementDiv.dataset.id;
                    const element = this.elements.find(el => el.id == elementId);
                    if (element && element.user_id == currentUserId) {
                        this.handleOwnElementClick(elementDiv, element);
                    } else {
                        this.exitMovingMode();
                    }
                } else {
                    this.handleCanvasClick(e);
                }
            });
            
            window.addEventListener('resize', () => {
                this.isMobile = window.innerWidth <= 768;
            });


            window.addEventListener('resize', () => {
                this.isMobile = window.innerWidth <= 768;
                this.renderAllElements(); // 重绘所有元素适应新尺寸
    });
}
        }
        
        handleOwnElementClick(elementDiv, element) {
            if (this.isMovingMode && this.movingElementId == element.id) {
                this.exitMovingMode();
            } else {
                this.exitMovingMode();
                this.isMovingMode = true;
                this.movingElementId = element.id;
                elementDiv.classList.add('moving');
            }
        }
        
        handleCanvasClick(e) {
            const rect = this.canvasBoard.getBoundingClientRect();
            
            // 计算点击位置相对于画板原始坐标系的坐标（考虑缩放）
            let clickX, clickY;
            
            if (this.isMobile) {
                // 移动端旋转逻辑（保持原有）
                const touch = e.touches ? e.touches[0] : e;
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                const rotatedX = touch.clientY - centerY;
                const rotatedY = centerX - touch.clientX;
                const boardX = rotatedX + rect.width / 2;
                const boardY = rotatedY + rect.height / 2;
                
                // 映射到原始尺寸
                const scaleX = 1280 / rect.width;
                const scaleY = 800 / rect.height;
                clickX = boardX * scaleX;
                clickY = boardY * scaleY;
            } else {
                // 桌面端：先获取相对于画板左上角的像素（缩放后）
                const relativeX = e.clientX - rect.left;
                const relativeY = e.clientY - rect.top;
                
                // 映射到原始尺寸
                const scaleX = 1280 / rect.width;
                const scaleY = 800 / rect.height;
                clickX = relativeX * scaleX;
                clickY = relativeY * scaleY;
            }
            
            // 边界限制
            clickX = Math.max(0, Math.min(1280, clickX));
            clickY = Math.max(0, Math.min(800, clickY));
            
            if (this.isMovingMode) {
                this.updateElementPosition(clickX, clickY);
                this.exitMovingMode();
            } else {
                this.addElement(clickX, clickY);
            }
        }
        
        updateElementPosition(x, y) {
            this.removeAllUserElements(currentUserId);
            
            const newElement = {
                pos_x: x,
                pos_y: y,
                image_url: currentUserImage,
                user_id: currentUserId,
                username: '<?php echo addslashes($_SESSION['username']); ?>'
            };
            
            this.saveElement(newElement);
            
            newElement.id = Date.now();
            this.elements.push(newElement);
            this.renderElement(newElement);
            
            this.exitMovingMode();
        }
        
        addElement(posX, posY) {
            this.removeAllUserElements(currentUserId);
            
            const element = {
                pos_x: posX,
                pos_y: posY,
                image_url: currentUserImage,
                user_id: currentUserId,
                username: '<?php echo addslashes($_SESSION['username']); ?>'
            };
            
            this.saveElement(element);
            
            element.id = Date.now();
            this.elements.push(element);
            this.renderElement(element);
            
            this.exitMovingMode();
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
        // 获取当前画板相对于原始尺寸的缩放比例
        getScale() {
            const rect = this.canvasBoard.getBoundingClientRect();
            return rect.width / 1280; // 由于宽高比固定，width/1280 = height/800
        }
            renderAllElements() {
        this.canvasBoard.innerHTML = '';
        this.elements.forEach(element => {
            this.renderElement(element);
        });
    }
        
        // 修改 renderElement，使用缩放比例计算位置和大小
        renderElement(element) {
            const scale = this.getScale();
            const size = 150 * scale; // 图案大小随画板缩放
            
            const elementDiv = document.createElement('div');
            elementDiv.className = 'canvas-element';
            elementDiv.style.width = size + 'px';
            elementDiv.style.height = size + 'px';
            elementDiv.style.left = (element.pos_x * scale - size / 2) + 'px';
            elementDiv.style.top = (element.pos_y * scale - size / 2) + 'px';
            elementDiv.dataset.id = element.id;
            elementDiv.dataset.userId = element.user_id;
            
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = element.username || `用户 ${element.user_id}`;
            elementDiv.appendChild(tooltip);
            
            const img = document.createElement('img');
            img.src = element.image_url || 'https://via.placeholder.com/150x150?text=Test+Image';
            img.alt = '画板元素';
            elementDiv.appendChild(img);
            
            this.canvasBoard.appendChild(elementDiv);
        }
        
        exitMovingMode() {
            if (this.isMovingMode) {
                const movingDiv = document.querySelector(`[data-id="${this.movingElementId}"]`);
                if (movingDiv) {
                    movingDiv.classList.remove('moving');
                }
                this.isMovingMode = false;
                this.movingElementId = null;
            }
        }
    }
    
    window.addEventListener('load', () => {
        new CanvasManager();
    });
</script>