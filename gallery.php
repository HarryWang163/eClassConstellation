<?php
// 引入认证工具
require_once __DIR__ . '/app/includes/auth.php';

// 检查是否已登录
if (!isLoggedIn()) {
    redirectToLogin();
}

// 处理祝福提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_blessing') {
    // 获取当前用户ID
    $currentUserId = $_SESSION['user_id'];
    $content = $_POST['content'];
    
    // 引入数据库连接
    require_once __DIR__ . '/app/config/database.php';
    $db = getDB();
    
    // 保存祝福到数据库
    $stmt = $db->prepare('INSERT INTO blessings (user_id, content) VALUES (:user_id, :content)');
    $stmt->bindParam(':user_id', $currentUserId);
    $stmt->bindParam(':content', $content);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '祝福保存成功！']);
    } else {
        echo json_encode(['success' => false, 'message' => '祝福保存失败，请重试。']);
    }
    exit;
}

// 获取所有祝福
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_blessings') {
    // 引入数据库连接
    require_once __DIR__ . '/app/config/database.php';
    $db = getDB();
    
    // 从数据库获取所有祝福
    $stmt = $db->prepare('SELECT content FROM blessings');
    $stmt->execute();
    $blessings = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode(['success' => true, 'blessings' => $blessings]);
    exit;
}

// 获取图片的函数
function getImages() {
    // 获取当前用户ID
    $currentUserId = $_SESSION['user_id'];
    
    // 引入数据库连接
    require_once __DIR__ . '/app/config/database.php';
    $db = getDB();
    
    // 从数据库获取所有图片
    $stmt = $db->prepare('SELECT id, user_id, img FROM user_images');
    $stmt->execute();
    $imagesFromDb = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 重要图片（当前用户的图片）
    $importantImage = null;
    
    // 其他图片
    $otherImages = [];
    
    // 处理数据库返回的图片
    foreach ($imagesFromDb as $image) {
        if ($image['user_id'] == $currentUserId && !$importantImage) {
            // 当前用户的图片作为重要图片
            $importantImage = [
                'id' => 'important-' . $image['id'],
                'url' => $image['img'],
                'isImportant' => true,
                'db_id' => $image['id'],
                'user_id' => $image['user_id']
            ];
        } else {
            // 其他用户的图片
            $otherImages[] = [
                'id' => 'image-' . $image['id'],
                'url' => $image['img'],
                'isImportant' => false,
                'db_id' => $image['id'],
                'user_id' => $image['user_id']
            ];
        }
    }
    
    // 如果没有当前用户的图片，使用默认重要图片
    if (!$importantImage) {
        $importantImage = [
            'id' => 'important-default',
            'url' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=golden%20star%20in%20center%20of%20starry%20night%20sky%2C%20glowing%20brightly%2C%20magical%20atmosphere&image_size=square',
            'title' => '重要图片',
            'isImportant' => true
        ];
    }
    
    // 合并所有图片
    return array_merge([$importantImage], $otherImages);
}

// 获取图片数据
$images = getImages();
?>

<?php
// 引入公共头部
require_once __DIR__ . '/app/includes/headerWithoutBar.php';
?>

        <!-- 全局星光背景 -->
        <div class="global-star-background" id="global-star-background"></div>
        
        <div class="gallery-container">
            <div class="gallery-content" id="gallery-content">
                <!-- 重要图片（置于页面正中央） -->
                <div class="important-image-container" id="important-image-container">
                    <img src="<?php echo $images[0]['url']; ?>" alt="主要图片" class="important-image" id="important-image">
                </div>
                
                <!-- 其他图片 -->
                <div class="other-images-container" id="other-images-container">
                    <?php foreach (array_slice($images, 1) as $image): ?>
                        <div class="other-image-wrapper" id="wrapper-<?php echo $image['id']; ?>">
                            <img src="<?php echo $image['url']; ?>" alt="其他图片" class="other-image" id="image-<?php echo $image['id']; ?>" data-id="<?php echo $image['id']; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- 新年祝福按钮 -->
                <div class="new-year-section" id="new-year-section">
                    <button class="new-year-button" id="new-year-button" style="display: none;">新年祝福</button>
                    
                    <!-- 祝福输入区域 -->
                    <div class="blessing-container" id="blessing-container" style="display: none;">
                        <div class="blessing-text" id="blessing-text">
                            <span class="blessing-char" data-index="0">_</span>
                            <span class="blessing-char" data-index="1">_</span>
                            <span class="blessing-char" data-index="2">_</span>
                            <span class="blessing-char" data-index="3">_</span>
                        </div>
                        <div class="blessing-hint">键入一个送给大家的四字祝福</div>
                    </div>
                </div>
                
                <!-- 祝福显示区域 -->
                <div class="blessing-overlay" id="blessing-overlay"></div>
                <div class="blessings-container" id="blessings-container"></div>
            </div>
        </div>



<style>
        .gallery-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1;
            pointer-events: none;
        }
        
        .gallery-content {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        /* 重要图片容器（置于页面正中央） */
        .important-image-container {
            position: fixed;
            z-index: 100;
            display: flex;
            justify-content: center;
            align-items: center;
            max-width: 90vw;
            max-height: 90vh;
            will-change: transform, width, height;
        }
        
        /* 旋转光环效果 */
        .important-image-container {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        /* 光环伪元素 - 图片背后的旋转圆环 */
        .important-image-container::before {
            content: '';
            position: absolute;
            width: 120%;            /* 比图片稍大 */
            height: 120%;
            border: 2px solid rgba(255, 215, 0, 0.3);
            border-radius: 50%;
            border-top-color: rgba(255, 255, 255, 0.8);
            border-bottom-color: rgba(255, 255, 255, 0.2);
            animation: rotate 8s linear infinite;
            opacity: 0.7;
            filter: blur(1px);
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.3);
            will-change: transform;
        }
        
        /* 第二个光环，反向旋转，增加层次感 */
        .important-image-container::after {
            content: '';
            position: absolute;
            width: 130%;
            height: 130%;
            border: 1px dashed rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            animation: rotateReverse 12s linear infinite;
            opacity: 0.5;
            will-change: transform;
        }
        
        /* 调整图片层级，不被伪元素遮挡 */
        .important-image {
            position: relative;
            z-index: 3;
            width: 100%;
            height: 100%;
            border-radius: 10px;
            object-fit: cover;
        }
        
        /* 其他图片容器的旋转光环效果 */
        .other-image-wrapper {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            will-change: opacity, transform;
        }
        
        /* 其他图片的光环伪元素 */
        .other-image-wrapper::before {
            content: '';
            position: absolute;
            width: 120%;
            height: 120%;
            border: 2px solid rgba(255, 215, 0, 0.3);
            border-radius: 50%;
            border-top-color: rgba(255, 255, 255, 0.8);
            border-bottom-color: rgba(255, 255, 255, 0.2);
            animation: rotate 8s linear infinite;
            opacity: 0.7;
            filter: blur(1px);
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.3);
            will-change: transform;
        }
        
        /* 其他图片的第二个光环 */
        .other-image-wrapper::after {
            content: '';
            position: absolute;
            width: 130%;
            height: 130%;
            border: 1px dashed rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            animation: rotateReverse 12s linear infinite;
            opacity: 0.5;
            will-change: transform;
        }
        
        /* 调整其他图片层级 */
        .other-image {
            position: relative;
            z-index: 3;
            width: 150px;
            height: 150px;
            border-radius: 10px;
            transition: transform 0.3s ease;
            will-change: transform;
        }
        
        /* 旋转动画 */
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes rotateReverse {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(-360deg); }
        }
        
        /* 悬停时光环高亮并加速 */
        .important-image-container:hover::before,
        .other-image-wrapper:hover::before {
            border-color: rgba(255, 215, 0, 0.8);
            border-top-color: #fff;
            border-bottom-color: rgba(255, 215, 0, 0.5);
            animation-duration: 4s;
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.8);
            width: 130%;
            height: 130%;
            transition: all 0.3s;
        }
        
        .important-image-container:hover::after,
        .other-image-wrapper:hover::after {
            border-color: rgba(255, 255, 255, 0.5);
            animation-duration: 6s;
            width: 140%;
            height: 140%;
        }
        
        /* 星光粒子效果 */
        .star-field {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 200px;
            height: 200px;
            transform: translate(-50%, -50%);
            pointer-events: none;
            animation: rotateField 20s linear infinite;
            will-change: transform;
        }
        
        .star-particle {
            position: absolute;
            background: white;
            border-radius: 50%;
            box-shadow: 0 0 10px gold;
            animation: twinkle 2s infinite alternate;
            will-change: opacity, transform;
        }
        
        /* 全局星光背景 */
        .global-star-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }
        
        .global-star {
            position: absolute;
            background: white;
            border-radius: 50%;
            box-shadow: 0 0 5px gold;
            will-change: opacity, transform;
        }
        
        /* 不同大小的星星动画 */
        .global-star.small {
            width: 1px;
            height: 1px;
            animation: twinkle 3s infinite alternate;
        }
        
        .global-star.medium {
            width: 2px;
            height: 2px;
            animation: twinkle 4s infinite alternate;
        }
        
        .global-star.large {
            width: 3px;
            height: 3px;
            animation: twinkle 2s infinite alternate;
            box-shadow: 0 0 8px gold;
        }
        
        @keyframes rotateField {
            from { transform: translate(-50%, -50%) rotate(0deg); }
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }
        
        @keyframes twinkle {
            0% { opacity: 0.3; transform: scale(1); }
            100% { opacity: 1; transform: scale(1.5); }
        }
        
        /* 响应式调整星光粒子容器大小 */
        @media (max-width: 768px) {
            .star-field {
                width: 150px;
                height: 150px;
            }
        }
        
        @media (max-width: 480px) {
            .star-field {
                width: 120px;
                height: 120px;
            }
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            .important-image {
                max-width: 85vw;
                max-height: 85vh;
            }
        }
        
        @media (max-width: 480px) {
            .important-image {
                max-width: 80vw;
                max-height: 80vh;
            }
        }
        
        /* 其他图片容器 */
        .other-images-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        
        .other-image-wrapper {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 50;
        }
        
        .other-image:hover {
            transform: scale(1.2);
            cursor: pointer;
        }
        
        /* 新年祝福相关样式 */
        .new-year-section {
            position: absolute;
            bottom: 50px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 200;
        }
        
        .new-year-button {
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 30px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeInUp 1s ease forwards;
        }
        
        .new-year-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.6);
        }
        
        .blessing-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }
        
        .blessing-text {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
        }
        
        .blessing-char {
            font-size: 36px;
            font-weight: bold;
            color: #ffd700;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
            cursor: pointer;
            position: relative;
            min-width: 40px;
            text-align: center;
        }
        
        .blessing-char::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #ffd700;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
        }
        
        .blessing-char:hover {
            color: #fff;
            text-shadow: 0 0 15px rgba(255, 255, 255, 0.9);
        }
        
        .blessing-hint {
            color: #fff;
            font-size: 16px;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
            margin-top: 10px;
            background: rgba(0, 0, 0, 0.5);
            padding: 8px 16px;
            border-radius: 20px;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* 背景蒙版 */
        .blessing-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            display: none;
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        
        /* 祝福文字容器 */
        .blessings-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1001;
            display: none;
        }
        
        /* 祝福文字 */
        .blessing-text-item {
            position: absolute;
            font-size: 24px;
            font-weight: bold;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.5s ease;
            pointer-events: auto;
            cursor: pointer;
        }
        
        /* 祝福文字悬停效果 */
        .blessing-text-item:hover {
            transform: scale(1.1);
            filter: brightness(1.2);
        }
        
        /* 移动端适配 */
        @media (max-width: 768px) {
            /* 新年祝福按钮 */
            .new-year-button {
                font-size: 16px;
                padding: 10px 20px;
            }
            
            /* 祝福输入区域 */
            .blessing-container {
                padding: 20px;
            }
            
            .blessing-text {
                font-size: 24px;
            }
            
            .blessing-hint {
                font-size: 14px;
            }
            
            /* 祝福文字 */
            .blessing-text-item {
                font-size: 18px !important;
            }
            
            /* 图片容器 */
            #gallery-content {
                min-height: 60vh;
            }
        }
        
        /* 小屏幕移动端适配 */
        @media (max-width: 480px) {
            /* 祝福文字 */
            .blessing-text {
                font-size: 20px;
            }
            
            .blessing-hint {
                font-size: 12px;
            }
            
            /* 祝福文字 */
            .blessing-text-item {
                font-size: 16px !important;
            }
        }
        
        /* 图片容器高度适配 */
        @media (max-width: 768px) {
            #gallery-content {
                min-height: 80vh;
                position: relative;
            }
        }
    </style>
    
    <script>
        // 等待页面加载完成
        window.addEventListener('load', function() {
            // 获取所有其他图片
            const otherImages = document.querySelectorAll('.other-image');
            const galleryContent = document.getElementById('gallery-content');
            const importantImage = document.getElementById('important-image');
            const importantImageContainer = document.getElementById('important-image-container');
            
            // 已使用的位置，用于碰撞检测
            let usedPositions = [];
            let imageSize = 150; // 默认图片大小
            
            // 根据屏幕尺寸调整图片大小
            function adjustImageSize() {
                if (window.innerWidth <= 768) {
                    imageSize = 120;
                } else if (window.innerWidth <= 480) {
                    imageSize = 100;
                } else {
                    imageSize = 150;
                }
                
                // 更新图片大小
                otherImages.forEach(img => {
                    img.style.width = `${imageSize}px`;
                    img.style.height = `${imageSize}px`;
                });
            }
            
            // 计算目标位置
            function getRandomPosition() {
                const containerWidth = galleryContent.offsetWidth;
                const containerHeight = galleryContent.offsetHeight;
                
                let x, y;
                
                // 移动端使用网格布局（每行2个）
                if (window.innerWidth <= 768) {
                    const index = usedPositions.length;
                    const cols = 2; // 每行2个
                    const spacing = 10; // 间距
                    
                    // 计算行列位置
                    const row = Math.floor(index / cols);
                    const col = index % cols;
                    
                    // 计算位置（居中布局）
                    const totalWidth = cols * imageSize + (cols - 1) * spacing;
                    const startX = (containerWidth - totalWidth) / 2;
                    
                    x = startX + col * (imageSize + spacing) + imageSize / 2;
                    y = 180 + row * (imageSize + spacing) + imageSize / 2; // 180px 为重要图片下方的距离
                } else {
                    // 桌面端使用圆形分布
                    const centerX = containerWidth / 2;
                    const centerY = containerHeight / 2;
                    const minRadius = 150; // 最小距离中心的半径
                    const maxRadius = Math.min(containerWidth, containerHeight) / 2 - imageSize;
                    const minDistance = imageSize + 50; // 最小距离（图片宽度+间距）
                    
                    let attempts = 0;
                    const maxAttempts = 200; // 增加尝试次数以找到更好的位置
                    
                    do {
                        // 生成以中心为原点的极坐标
                        const radius = minRadius + Math.random() * (maxRadius - minRadius);
                        const angle = Math.random() * Math.PI * 2;
                        
                        // 转换为笛卡尔坐标
                        x = centerX + Math.cos(angle) * radius;
                        y = centerY + Math.sin(angle) * radius;
                        
                        // 确保位置在容器内
                        x = Math.max(imageSize / 2, Math.min(containerWidth - imageSize / 2, x));
                        y = Math.max(imageSize / 2, Math.min(containerHeight - imageSize / 2, y));
                        
                        // 检查是否与其他图片重叠
                        let isOverlapping = false;
                        for (const pos of usedPositions) {
                            const distance = Math.sqrt(Math.pow(x - pos.x, 2) + Math.pow(y - pos.y, 2));
                            if (distance < minDistance) {
                                isOverlapping = true;
                                break;
                            }
                        }
                        
                        attempts++;
                        
                        if (!isOverlapping || attempts >= maxAttempts) {
                            break;
                        }
                    } while (true);
                }
                
                // 记录使用的位置
                usedPositions.push({ x, y });
                
                return { x, y };
            }

            // 初始化图片位置（确保不重叠）
            function initializeImagePositions() {
                // 重置已使用的位置
                usedPositions = [];
                
                // 为每张图片设置初始位置
                otherImages.forEach((image, index) => {
                    const wrapper = image.parentElement;
                    const position = getRandomPosition();
                    
                    // 设置实际位置（不是偏移）
                    wrapper.style.position = 'absolute';
                    wrapper.style.left = `${position.x - imageSize / 2}px`;
                    wrapper.style.top = `${position.y - imageSize / 2}px`;
                    wrapper.style.transform = 'none';
                    
                    // 初始透明度为0
                    wrapper.style.opacity = '0';
                });
            }

            // 缓动函数
            function easeOutCubic(t) {
                return 1 - Math.pow(1 - t, 1);
            }
            
            function easeInOutCubic(t) {
                return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
            }
            
            function easeOutQuart(t) {
                return 1 - Math.pow(1 - t, 4);
            }

            // 启动透明度渐变动画
            function animateImages() {
                // 为重要图片添加缩小动画
                animateImportantImageShrink();
                
                // 为其他图片启动透明度渐变动画
                animateOtherImagesOpacity();
                
                // 动画结束后，允许交互
                setTimeout(() => {
                    galleryContent.style.pointerEvents = 'auto';
                }, 3500);
            }
            
            // 使用requestAnimationFrame实现其他图片的透明度变化动画
            function animateOtherImagesOpacity() {
                const duration = 3000; // 动画持续时间（毫秒）
                const startTime = performance.now();
                
                // 获取所有其他图片的容器
                const otherImageWrappers = document.querySelectorAll('.other-image-wrapper');
                
                // 动画函数
                function animate(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    // 使用缓动函数使动画更自然
                    const easeProgress = easeOutCubic(progress);
                    
                    // 应用当前透明度到所有其他图片容器
                    otherImageWrappers.forEach(wrapper => {
                        wrapper.style.opacity = easeProgress;
                    });
                    
                    // 如果动画未完成，继续下一帧
                    if (progress < 1) {
                        requestAnimationFrame(animate);
                    }
                }

                // 开始动画
                requestAnimationFrame(animate);
            }
            
            // 使用requestAnimationFrame重新实现主要图片的缩放动画
            function animateImportantImageShrink() {
                const duration = 3000; // 动画持续时间（毫秒）
                const startTime = performance.now();
                
                // 获取初始尺寸和位置（从容器获取）
                const initialWidth = importantImageContainer.offsetWidth;
                const initialHeight = importantImageContainer.offsetHeight;
                
                // 计算目标尺寸（根据屏幕尺寸调整）
                let targetWidth, targetHeight;
                if (window.innerWidth <= 768) {
                    targetWidth = 120;
                    targetHeight = 120;
                } else if (window.innerWidth <= 480) {
                    targetWidth = 100;
                    targetHeight = 100;
                } else {
                    targetWidth = 150;
                    targetHeight = 150;
                }
                
                // 动画函数
                function animate(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    // 使用缓动函数使动画更自然
                    const easeProgress = easeOutCubic(progress);
                    
                    // 计算当前尺寸
                    const currentWidth = initialWidth + (targetWidth - initialWidth) * easeProgress;
                    const currentHeight = initialHeight + (targetHeight - initialHeight) * easeProgress;
                    
                    // 应用当前尺寸到容器
                    importantImageContainer.style.width = `${currentWidth}px`;
                    importantImageContainer.style.height = `${currentHeight}px`;
                    importantImageContainer.style.maxWidth = `${currentWidth}px`;
                    importantImageContainer.style.maxHeight = `${currentHeight}px`;
                    
                    // 如果动画未完成，继续下一帧
                    if (progress < 1) {
                        requestAnimationFrame(animate);
                    }
                }
                
                // 开始动画
                requestAnimationFrame(animate);
            }
            
            // 为其他图片添加悬停效果
            otherImages.forEach(image => {
                image.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.2)';
                });
                
                image.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
            
            // 初始化
            adjustImageSize();
            initializeImagePositions();
            
            // 窗口大小改变时重新调整
            window.addEventListener('resize', function() {
                adjustImageSize();
                initializeImagePositions();
            });
            
            // 页面加载完成后启动动画
            setTimeout(animateImages, 500);
        });
        
        // 为图片添加星光粒子效果（减少粒子数量）
        function addStarField() {
            const importantImageContainer = document.getElementById('important-image-container');
            const otherImageWrappers = document.querySelectorAll('.other-image-wrapper');
            
            // 为重要图片容器添加星光粒子（减少到12个）
            if (importantImageContainer) {
                const field = document.createElement('div');
                field.className = 'star-field';
                
                // 生成12个粒子
                for (let i = 0; i < 12; i++) {
                    const star = document.createElement('div');
                    star.className = 'star-particle';
                    const size = Math.random() * 4 + 2;
                    star.style.width = size + 'px';
                    star.style.height = size + 'px';
                    star.style.left = Math.random() * 100 + '%';
                    star.style.top = Math.random() * 100 + '%';
                    star.style.animationDelay = Math.random() * 2 + 's';
                    star.style.animationDuration = Math.random() * 2 + 1.5 + 's';
                    field.appendChild(star);
                }
                importantImageContainer.appendChild(field);
            }
            
            // 为其他图片容器添加星光粒子（减少到8个）
            otherImageWrappers.forEach(wrapper => {
                const field = document.createElement('div');
                field.className = 'star-field';
                
                // 生成8个粒子
                for (let i = 0; i < 8; i++) {
                    const star = document.createElement('div');
                    star.className = 'star-particle';
                    const size = Math.random() * 3 + 1.5;
                    star.style.width = size + 'px';
                    star.style.height = size + 'px';
                    star.style.left = Math.random() * 100 + '%';
                    star.style.top = Math.random() * 100 + '%';
                    star.style.animationDelay = Math.random() * 2 + 's';
                    star.style.animationDuration = Math.random() * 2 + 1.5 + 's';
                    field.appendChild(star);
                }
                wrapper.appendChild(field);
            });
        }
        
        // 添加全局星光背景
        function addGlobalStarBackground() {
            const container = document.getElementById('global-star-background');
            const starCount = 100; // 星星数量
            
            for (let i = 0; i < starCount; i++) {
                const star = document.createElement('div');
                star.className = 'global-star';
                
                // 随机大小
                const sizeClass = Math.random() > 0.7 ? 'large' : (Math.random() > 0.5 ? 'medium' : 'small');
                star.classList.add(sizeClass);
                
                // 随机位置
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                
                // 随机透明度
                star.style.opacity = Math.random() * 0.8 + 0.2;
                
                // 随机动画延迟
                star.style.animationDelay = Math.random() * 5 + 's';
                
                container.appendChild(star);
            }
        }
        
        // 新年祝福功能
        function initNewYearBlessing() {
            const newYearButton = document.getElementById('new-year-button');
            const blessingContainer = document.getElementById('blessing-container');
            const blessingChars = document.querySelectorAll('.blessing-char');
            const blessingText = document.getElementById('blessing-text');
            
            // 祝福字符数组
            let blessingArray = ['_', '_', '_', '_'];
            
            // 等待5秒后显示新年祝福按钮
            setTimeout(() => {
                newYearButton.style.display = 'block';
                // 添加淡入动画
                setTimeout(() => {
                    newYearButton.style.opacity = '1';
                }, 100);
            }, 5000);
            
            // 点击新年祝福按钮
            newYearButton.addEventListener('click', () => {
                newYearButton.style.display = 'none';
                blessingContainer.style.display = 'flex';
                // 添加淡入动画
                setTimeout(() => {
                    blessingContainer.style.opacity = '1';
                }, 100);
            });
            
            // 点击祝福容器一次输入完整的四字祝福
            blessingText.addEventListener('click', () => {
                const userInput = prompt('请输入四字祝福（如：新年快乐、万事如意等）：');
                
                // 验证输入
                if (userInput && userInput.length === 4 && /^[\u4e00-\u9fa5]{4}$/.test(userInput)) {
                    // 更新所有字符
                    for (let i = 0; i < 4; i++) {
                        blessingChars[i].textContent = userInput[i];
                        blessingArray[i] = userInput[i];
                    }
                    
                    // 保存祝福到数据库
                    saveBlessingToDatabase(userInput);
                    
                    // 显示祝福提示
                    setTimeout(() => {
                        alert(`祝您${userInput}！`);
                    }, 300);
                } else if (userInput) {
                    alert('请输入完整的四字汉字祝福！');
                }
            });
            
            // 保存祝福到数据库
            function saveBlessingToDatabase(content) {
                fetch('gallery.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=save_blessing&content=${encodeURIComponent(content)}`
                })
                .then(response => response.json())
                .then(data => {
                    // 可以在这里添加保存成功的提示
                    console.log('祝福保存结果：', data);
                    // 保存成功后显示所有祝福
                    if (data.success) {
                        setTimeout(() => {
                            showAllBlessings();
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error('保存祝福失败：', error);
                });
            }
            
            // 获取所有祝福
            function getAllBlessings() {
                return fetch('gallery.php?action=get_blessings')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        return data.blessings;
                    } else {
                        console.error('获取祝福失败：', data.message);
                        return [];
                    }
                })
                .catch(error => {
                    console.error('获取祝福失败：', error);
                    return [];
                });
            }
            
            // 生成随机亮色
            function getRandomBrightColor() {
                const colors = [
                    '#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#ffeaa7',
                    '#dfe6e9', '#e17055', '#00b894', '#0984e3', '#6c5ce7',
                    '#a29bfe', '#fd79a8', '#fdcb6e', '#e17055', '#00b894'
                ];
                return colors[Math.floor(Math.random() * colors.length)];
            }
            
            // 显示所有祝福
            function showAllBlessings() {
                const overlay = document.getElementById('blessing-overlay');
                const blessingsContainer = document.getElementById('blessings-container');
                
                // 显示背景蒙版
                overlay.style.display = 'block';
                setTimeout(() => {
                    overlay.style.opacity = '1';
                }, 100);
                
                // 获取所有祝福
                getAllBlessings().then(blessings => {
                    // 清空容器
                    blessingsContainer.innerHTML = '';
                    
                    // 如果没有祝福，添加默认祝福
                    if (blessings.length === 0) {
                        blessings = ['新年快乐', '万事如意', '心想事成', '恭喜发财', '身体健康'];
                    }
                    
                    // 显示容器
                    blessingsContainer.style.display = 'block';
                    
                    // 均匀排布祝福
                    const containerWidth = window.innerWidth;
                    const containerHeight = window.innerHeight;
                    const blessingCount = blessings.length;
                    
                    // 根据屏幕尺寸调整字体大小和布局
                    let fontSize, radius;
                    if (window.innerWidth <= 768) {
                        // 移动端和平板
                        fontSize = Math.min(20, containerWidth * 0.05);
                        radius = Math.min(containerWidth, containerHeight) * 0.35;
                    } else {
                        // 桌面端
                        fontSize = 24;
                        radius = Math.min(containerWidth, containerHeight) * 0.4;
                    }
                    
                    // 计算祝福位置
                    for (let i = 0; i < blessingCount; i++) {
                        // 均匀分布在屏幕上
                        const angle = (i / blessingCount) * Math.PI * 2;
                        const x = containerWidth / 2 + Math.cos(angle) * radius;
                        const y = containerHeight / 2 + Math.sin(angle) * radius;
                        
                        // 创建祝福元素
                        const blessingElement = document.createElement('div');
                        blessingElement.className = 'blessing-text-item';
                        blessingElement.textContent = blessings[i];
                        
                        // 设置位置
                        blessingElement.style.left = `${x}px`;
                        blessingElement.style.top = `${y}px`;
                        
                        // 设置字体大小
                        blessingElement.style.fontSize = `${fontSize}px`;
                        
                        // 设置随机亮色
                        blessingElement.style.color = getRandomBrightColor();
                        
                        // 添加到容器
                        blessingsContainer.appendChild(blessingElement);
                        
                        // 添加动画效果（逐个显示）
                        setTimeout(() => {
                            blessingElement.style.opacity = '1';
                            blessingElement.style.transform = 'scale(1)';
                        }, i * 500);
                    }
                });
            }
            
            // 点击单个字符位置也实现一次性输入四个字符
            blessingChars.forEach((charElement, index) => {
                charElement.addEventListener('click', (e) => {
                    // 阻止事件冒泡，避免触发整个容器的点击事件
                    e.stopPropagation();
                    
                    const userInput = prompt('请输入四字祝福（如：新年快乐、万事如意等）：');
                    
                    // 验证输入
                    if (userInput && userInput.length === 4 && /^[\u4e00-\u9fa5]{4}$/.test(userInput)) {
                        // 更新所有字符
                        for (let i = 0; i < 4; i++) {
                            blessingChars[i].textContent = userInput[i];
                            blessingArray[i] = userInput[i];
                        }
                        
                        // 保存祝福到数据库
                        saveBlessingToDatabase(userInput);
                        
                        // 显示祝福提示
                        setTimeout(() => {
                            alert(`祝您${userInput}！`);
                        }, 300);
                    } else if (userInput) {
                        alert('请输入完整的四字汉字祝福！');
                    }
                });
            });
        }
        
        window.addEventListener('load', addStarField);
        window.addEventListener('load', addGlobalStarBackground);
        window.addEventListener('load', initNewYearBlessing);
    </script>