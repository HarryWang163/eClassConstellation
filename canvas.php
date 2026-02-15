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
    
    $deleteStmt = $db->prepare('DELETE FROM canvas_elements WHERE user_id = :user_id');
    $deleteStmt->bindParam(':user_id', $currentUserId);
    $deleteStmt->execute();
    
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

<div class="canvas-wrapper">
    <div class="canvas-container">
        <div class="canvas-board" id="canvas-board">
            <!-- 所有元素通过 JS 动态添加到此容器内 -->
        </div>
    </div>
    <div class="zoom-indicator" id="zoom-indicator">缩放: 100%</div>
</div>

<?php
// 引入公共页脚
require_once __DIR__ . '/app/includes/footer.php';
?>

<style>
    body, html {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow: hidden;
        background: linear-gradient(135deg, #0a0a23 0%, #1e1e4a 50%, #3a3a7a 100%);
    }
    
    .canvas-wrapper {
        width: 100vw;
        height: 100vh;
        overflow: auto;           /* 允许滚动，适应小屏幕 */
        display: block;
    }
    
    .canvas-container {
        width: 1200px;            /* 固定宽度 */
        height: 800px;            /* 固定高度 */
        margin: 0 auto;           /* 水平居中 */
        position: relative;
    }
    
    .canvas-board {
        width: 1200px;
        height: 800px;
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 215, 0, 0.3);
        border-radius: 10px;
        cursor: grab;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        transform-origin: 0 0;
        will-change: transform;
    }
    
    .canvas-board:active {
        cursor: grabbing;
    }
    
    .canvas-element {
        position: absolute;
        width: 150px;
        height: 150px;
        border-radius: 10px;
        overflow: visible;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        animation: fadeIn 0.3s ease-out;
        will-change: transform, left, top;
        pointer-events: auto;
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
        pointer-events: none;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.8); }
        to   { opacity: 1; transform: scale(1); }
    }
    
    .zoom-indicator {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(0,0,0,0.6);
        color: #ffd700;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        pointer-events: none;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255,215,0,0.3);
        z-index: 1000;
    }
</style>

<script>
    const currentUserImage = '<?php echo addslashes($currentUserImage); ?>';
    const currentUserId = <?php echo $_SESSION['user_id']; ?>;

    class CanvasManager {
        constructor() {
            this.board = document.getElementById('canvas-board');
            this.elements = [];
            this.isMovingMode = false;
            this.movingElementId = null;

            this.scale = 1.0;
            this.translateX = 0;
            this.translateY = 0;

            this.isDragging = false;
            this.dragStart = { x: 0, y: 0 };
            this.lastTranslate = { x: 0, y: 0 };

            this.worldWidth = 1200;
            this.worldHeight = 800;

            this.init();
        }

        init() {
            this.loadElements();
            this.bindEvents();
            this.updateTransform();
        }

        bindEvents() {
            this.board.addEventListener('wheel', (e) => {
                e.preventDefault();
                const delta = e.deltaY > 0 ? 0.9 : 1.1;
                const newScale = this.scale * delta;
                if (newScale < 0.2 || newScale > 5) return;

                const rect = this.board.getBoundingClientRect();
                const mouseX = e.clientX - rect.left;
                const mouseY = e.clientY - rect.top;

                const worldX = (mouseX - this.translateX) / this.scale;
                const worldY = (mouseY - this.translateY) / this.scale;

                this.scale = newScale;
                this.translateX = mouseX - worldX * this.scale;
                this.translateY = mouseY - worldY * this.scale;

                this.updateTransform();
                this.updateZoomIndicator();
            });

            this.board.addEventListener('mousedown', (e) => {
                if (e.target.closest('.canvas-element')) return;
                e.preventDefault();
                this.isDragging = true;
                this.dragStart = { x: e.clientX, y: e.clientY };
                this.lastTranslate = { x: this.translateX, y: this.translateY };
                this.board.style.cursor = 'grabbing';
            });

            window.addEventListener('mousemove', (e) => {
                if (!this.isDragging) return;
                e.preventDefault();
                const dx = e.clientX - this.dragStart.x;
                const dy = e.clientY - this.dragStart.y;
                this.translateX = this.lastTranslate.x + dx;
                this.translateY = this.lastTranslate.y + dy;
                this.updateTransform();
            });

            window.addEventListener('mouseup', () => {
                if (this.isDragging) {
                    this.isDragging = false;
                    this.board.style.cursor = 'grab';
                }
            });

            this.board.addEventListener('click', (e) => {
                if (e.target.closest('.canvas-element')) return;
                if (this.isDragging) return;
                this.handleBoardClick(e);
            });

            // 触摸事件
            this.board.addEventListener('touchstart', (e) => {
                e.preventDefault();
                if (e.target.closest('.canvas-element')) return;
                const touch = e.touches[0];
                this.isDragging = true;
                this.dragStart = { x: touch.clientX, y: touch.clientY };
                this.lastTranslate = { x: this.translateX, y: this.translateY };
            });

            window.addEventListener('touchmove', (e) => {
                if (!this.isDragging) return;
                e.preventDefault();
                const touch = e.touches[0];
                const dx = touch.clientX - this.dragStart.x;
                const dy = touch.clientY - this.dragStart.y;
                this.translateX = this.lastTranslate.x + dx;
                this.translateY = this.lastTranslate.y + dy;
                this.updateTransform();
            });

            window.addEventListener('touchend', () => {
                this.isDragging = false;
            });
        }

        updateTransform() {
            this.board.style.transform = `translate(${this.translateX}px, ${this.translateY}px) scale(${this.scale})`;
        }

        updateZoomIndicator() {
            const indicator = document.getElementById('zoom-indicator');
            if (indicator) {
                indicator.textContent = `缩放: ${Math.round(this.scale * 100)}%`;
            }
        }

        screenToWorld(screenX, screenY) {
            const worldX = (screenX - this.translateX) / this.scale;
            const worldY = (screenY - this.translateY) / this.scale;
            return { x: worldX, y: worldY };
        }

        handleBoardClick(e) {
            const rect = this.board.getBoundingClientRect();
            const screenX = e.clientX - rect.left;
            const screenY = e.clientY - rect.top;

            const world = this.screenToWorld(screenX, screenY);
            let worldX = world.x;
            let worldY = world.y;

            worldX = Math.max(0, Math.min(this.worldWidth, worldX));
            worldY = Math.max(0, Math.min(this.worldHeight, worldY));

            if (this.isMovingMode) {
                this.updateElementPosition(worldX, worldY);
                this.exitMovingMode();
            } else {
                this.addElement(worldX, worldY);
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
        }

        addElement(x, y) {
            this.removeAllUserElements(currentUserId);

            const element = {
                pos_x: x,
                pos_y: y,
                image_url: currentUserImage,
                user_id: currentUserId,
                username: '<?php echo addslashes($_SESSION['username']); ?>'
            };

            this.saveElement(element);
            element.id = Date.now();
            this.elements.push(element);
            this.renderElement(element);
        }

        removeAllUserElements(userId) {
            const elementsToRemove = document.querySelectorAll(`.canvas-element[data-user-id="${userId}"]`);
            elementsToRemove.forEach(el => el.remove());
            this.elements = this.elements.filter(el => el.user_id != userId);
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

        renderAllElements() {
            this.board.innerHTML = '';
            this.elements.forEach(element => {
                this.renderElement(element);
            });
        }

        renderElement(element) {
            const elementDiv = document.createElement('div');
            elementDiv.className = 'canvas-element';
            elementDiv.style.left = (element.pos_x - 75) + 'px';
            elementDiv.style.top = (element.pos_y - 75) + 'px';
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

            elementDiv.addEventListener('click', (e) => {
                e.stopPropagation();
                const el = this.elements.find(e => e.id == element.id);
                if (el && el.user_id == currentUserId) {
                    this.handleOwnElementClick(elementDiv, el);
                } else {
                    this.exitMovingMode();
                }
            });

            this.board.appendChild(elementDiv);
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