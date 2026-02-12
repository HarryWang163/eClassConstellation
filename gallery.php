<?php
// 引入认证工具
require_once __DIR__ . '/app/includes/auth.php';

// 检查是否已登录
if (!isLoggedIn()) {
    redirectToLogin();
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
require_once __DIR__ . '/app/includes/header.php';
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
            
            // 计算目标位置（以重要图片为圆心向外扩散，避免重叠）
            function getRandomPosition() {
                const containerWidth = galleryContent.offsetWidth;
                const containerHeight = galleryContent.offsetHeight;
                
                // 以重要图片为中心
                const centerX = containerWidth / 2;
                const centerY = containerHeight / 2;
                const minRadius = 150; // 最小距离中心的半径
                const maxRadius = Math.min(containerWidth, containerHeight) / 2 - imageSize;
                const minDistance = imageSize + 50; // 最小距离（图片宽度+间距）
                
                let x, y;
                let attempts = 0;
                const maxAttempts = 50; // 增加尝试次数以找到更好的位置
                
                do {
                    // 生成以中心为原点的极坐标
                    const radius = minRadius + Math.random() * 2 * (maxRadius - minRadius);
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
        
        window.addEventListener('load', addStarField);
        window.addEventListener('load', addGlobalStarBackground);
    </script>