<?php
// 引入认证工具
require_once __DIR__ . '/app/includes/auth.php';

// 检查是否已登录
if (!isLoggedIn()) {
    redirectToLogin();
}

// 获取图片的函数
function getImages() {
    // 这里可以替换为从数据库获取图片的逻辑
    // 目前使用占位符图片作为示例
    
    // 重要图片（置于页面正中央）
    $importantImage = [
        'id' => 'important',
        'url' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=golden%20star%20in%20center%20of%20starry%20night%20sky%2C%20glowing%20brightly%2C%20magical%20atmosphere&image_size=square',
        'title' => '重要图片',
        'isImportant' => true
    ];
    
    // 其他图片（从重要图片出发移动）
    $otherImages = [
        [
            'id' => 'image1',
            'url' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=small%20star%20with%20blue%20glow%2C%20starry%20background&image_size=square',
            'title' => '图片1',
            'isImportant' => false
        ],
        [
            'id' => 'image2',
            'url' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=small%20star%20with%20purple%20glow%2C%20starry%20background&image_size=square',
            'title' => '图片2',
            'isImportant' => false
        ],
        [
            'id' => 'image3',
            'url' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=small%20star%20with%20pink%20glow%2C%20starry%20background&image_size=square',
            'title' => '图片3',
            'isImportant' => false
        ],
        [
            'id' => 'image4',
            'url' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=small%20star%20with%20green%20glow%2C%20starry%20background&image_size=square',
            'title' => '图片4',
            'isImportant' => false
        ],
        [
            'id' => 'image5',
            'url' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=small%20star%20with%20orange%20glow%2C%20starry%20background&image_size=square',
            'title' => '图片5',
            'isImportant' => false
        ]
    ];
    
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

        <header>
            <h1>星屿共筑</h1>
            <p class="subtitle">我们的星空画廊</p>
        </header>
        
        <div class="gallery-container">
            <h3>星空中的星星</h3>
            
            <div class="gallery-content" id="gallery-content">
                <!-- 重要图片（置于页面正中央） -->
                <div class="important-image-container" id="important-image-container">
                    <img src="<?php echo $images[0]['url']; ?>" alt="<?php echo $images[0]['title']; ?>" class="important-image" id="important-image">
                    <div class="image-caption"><?php echo $images[0]['title']; ?></div>
                </div>
                
                <!-- 其他图片（从重要图片出发移动） -->
                <div class="other-images-container" id="other-images-container">
                    <?php foreach (array_slice($images, 1) as $image): ?>
                        <div class="other-image-wrapper" id="wrapper-<?php echo $image['id']; ?>">
                            <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['title']; ?>" class="other-image" id="image-<?php echo $image['id']; ?>" data-id="<?php echo $image['id']; ?>">
                            <div class="image-caption"><?php echo $image['title']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- 控制按钮 -->
            <div class="gallery-controls">
                <button class="btn" id="restart-animation">重新播放动画</button>
            </div>
        </div>

<?php
// 引入公共页脚
require_once __DIR__ . '/app/includes/footer.php';
?>

<style>
        .gallery-container {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 25px;
            padding: 50px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            margin-top: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            animation: slideUp 0.8s ease-out;
            min-height: 800px;
            position: relative;
            overflow: hidden;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .gallery-container h3 {
            font-size: 1.8rem;
            margin-bottom: 40px;
            color: #f8f9fa;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
            text-align: center;
        }
        
        .gallery-content {
            position: relative;
            width: 100%;
            height: 600px;
        }
        
        /* 重要图片容器（置于页面正中央） */
        .important-image-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 100;
            animation: fadeIn 1s ease-out;
        }
        
        .important-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.8);
            border: 5px solid rgba(255, 215, 0, 0.5);
            transition: transform 0.3s ease;
        }
        
        .important-image:hover {
            transform: scale(1.1);
            box-shadow: 0 0 40px rgba(255, 215, 0, 1);
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
            opacity: 0;
        }
        
        .other-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
            border: 3px solid rgba(255, 215, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .other-image:hover {
            transform: scale(1.2);
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.8);
            cursor: pointer;
        }
        
        .image-caption {
            text-align: center;
            margin-top: 10px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
        }
        
        /* 动画类 */
        .animate-move {
            animation: moveToPosition 6s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
        
        @keyframes moveToPosition {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.3) rotate(0deg);
                filter: blur(10px) brightness(0);
            }
            10% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1.3) rotate(15deg);
                filter: blur(0) brightness(1.5);
            }
            20% {
                transform: translate(var(--midpoint-x), var(--midpoint-y)) scale(1.1) rotate(-10deg);
                filter: blur(0) brightness(1.2);
            }
            30% {
                transform: translate(calc(var(--midpoint-x) * 1.2), calc(var(--midpoint-y) * 1.2)) scale(0.9) rotate(5deg);
            }
            50% {
                transform: translate(calc(var(--target-x) * 0.8), calc(var(--target-y) * 0.8)) scale(1.1) rotate(-2deg);
            }
            70% {
                transform: translate(calc(var(--target-x) * 1.05), calc(var(--target-y) * 1.05)) scale(0.95);
            }
            90% {
                transform: translate(calc(var(--target-x) * 0.98), calc(var(--target-y) * 0.98)) scale(1.02);
            }
            100% {
                opacity: 1;
                transform: translate(var(--target-x), var(--target-y)) scale(1) rotate(0deg);
                filter: blur(0) brightness(1);
            }
        }
        
        /* 发光轨迹效果 */
        .image-trail {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,215,0,0.8) 0%, rgba(255,215,0,0) 70%);
            animation: trailFade 3s ease-out forwards;
            pointer-events: none;
            z-index: 10;
        }
        
        @keyframes trailFade {
            0% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
            100% {
                opacity: 0;
                transform: translate(var(--target-x), var(--target-y)) scale(2);
            }
        }
        
        /* 缩放动画 */
        .animate-scale {
            animation: scaleToSize 2s ease-out forwards;
        }
        
        @keyframes scaleToSize {
            0% {
                transform: scale(0.5);
            }
            100% {
                transform: scale(1);
            }
        }
        
        /* 重要图片动画 */
        .animate-important {
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 30px rgba(255, 215, 0, 0.8);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 40px rgba(255, 215, 0, 1);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 30px rgba(255, 215, 0, 0.8);
            }
        }
        
        /* 控制按钮 */
        .gallery-controls {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }
        
        .btn {
            padding: 12px 35px;
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
            border: none;
            border-radius: 25px;
            color: #0a0a23;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.6);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            .gallery-container {
                padding: 30px;
                min-height: 600px;
            }
            
            .important-image {
                width: 120px;
                height: 120px;
            }
            
            .other-image {
                width: 120px;
                height: 120px;
            }
            
            .gallery-content {
                height: 400px;
            }
        }
        
        @media (max-width: 480px) {
            .gallery-container {
                padding: 20px;
                min-height: 500px;
            }
            
            .important-image {
                width: 100px;
                height: 100px;
            }
            
            .other-image {
                width: 100px;
                height: 100px;
            }
            
            .gallery-content {
                height: 300px;
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
            
            // 为重要图片添加脉冲动画
            importantImageContainer.classList.add('animate-important');
            
            // 已使用的位置，用于碰撞检测
            let usedPositions = [];
            const imageRadius = 75; // 图片半径（150px宽的一半）
            const minDistance = imageRadius * 2 + 30; // 最小距离（图片直径+间距）
            
            // 计算目标位置（随机分布在页面其余位置，避免重叠）
            function getRandomPosition() {
                const containerWidth = galleryContent.offsetWidth;
                const containerHeight = galleryContent.offsetHeight;
                
                // 避开中央区域（重要图片的位置）
                const centerX = containerWidth / 2;
                const centerY = containerHeight / 2;
                const avoidRadius = 200; // 避开半径
                
                let x, y;
                let attempts = 0;
                const maxAttempts = 200; // 增加尝试次数以找到更好的位置
                
                do {
                    x = Math.random() * (containerWidth - imageRadius * 2) + imageRadius;
                    y = Math.random() * (containerHeight - imageRadius * 2) + imageRadius;
                    
                    const distanceFromCenter = Math.sqrt(Math.pow(x - centerX, 2) + Math.pow(y - centerY, 2));
                    
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
                    
                    if ((distanceFromCenter > avoidRadius && !isOverlapping) || attempts >= maxAttempts) {
                        break;
                    }
                } while (true);
                
                // 记录使用的位置
                usedPositions.push({ x, y });
                
                return { x, y };
            }
            
            // 计算中间点，用于动画路径
            function getMidpoint(startX, startY, endX, endY) {
                return {
                    x: startX + (endX - startX) * 0.5,
                    y: startY + (endY - startY) * 0.3
                };
            }
            
            // 创建发光轨迹效果
            function createTrail(wrapper, offsetX, offsetY) {
                const trail = document.createElement('div');
                trail.className = 'image-trail';
                trail.style.setProperty('--target-x', `${offsetX}px`);
                trail.style.setProperty('--target-y', `${offsetY}px`);
                galleryContent.appendChild(trail);
                
                // 动画结束后移除轨迹
                setTimeout(() => {
                    if (trail.parentNode) {
                        trail.parentNode.removeChild(trail);
                    }
                }, 3000);
            }
            
            // 为每张图片设置随机位置并启动动画
            function animateImages() {
                // 重置已使用的位置
                usedPositions = [];
                
                // 清除所有现有的轨迹
                const existingTrails = document.querySelectorAll('.image-trail');
                existingTrails.forEach(trail => trail.remove());
                
                otherImages.forEach((image, index) => {
                    const wrapper = image.parentElement;
                    const position = getRandomPosition();
                    
                    // 计算相对于中心的偏移
                    const centerX = galleryContent.offsetWidth / 2;
                    const centerY = galleryContent.offsetHeight / 2;
                    const offsetX = position.x - centerX;
                    const offsetY = position.y - centerY;
                    
                    // 计算中间点
                    const midpoint = getMidpoint(centerX, centerY, position.x, position.y);
                    const midpointOffsetX = midpoint.x - centerX;
                    const midpointOffsetY = midpoint.y - centerY;
                    
                    // 设置CSS变量用于动画
                    wrapper.style.setProperty('--target-x', `${offsetX}px`);
                    wrapper.style.setProperty('--target-y', `${offsetY}px`);
                    wrapper.style.setProperty('--midpoint-x', `${midpointOffsetX}px`);
                    wrapper.style.setProperty('--midpoint-y', `${midpointOffsetY}px`);
                    
                    // 重置动画
                    wrapper.classList.remove('animate-move');
                    wrapper.style.opacity = '0';
                    
                    // 强制重排
                    void wrapper.offsetWidth;
                    
                    // 创建发光轨迹
                    createTrail(wrapper, offsetX, offsetY);
                    
                    // 为每张图片添加独特的动画延迟和效果
                    const delay = index * 200 + Math.random() * 100; // 随机延迟，使动画更自然
                    
                    // 延迟启动动画，使图片依次出现
                    setTimeout(() => {
                        wrapper.classList.add('animate-move');
                    }, delay);
                });
            }
            
            // 为重要图片添加点击效果
            importantImageContainer.addEventListener('click', function() {
                // 重置并重新启动动画
                animateImages();
            });
            
            // 为其他图片添加悬停效果
            otherImages.forEach(image => {
                image.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.3)';
                    this.style.boxShadow = '0 0 40px rgba(255, 215, 0, 1)';
                });
                
                image.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.boxShadow = '0 0 20px rgba(255, 215, 0, 0.5)';
                });
            });
            
            // 页面加载完成后启动动画
            setTimeout(animateImages, 1000);
            
            // 重新播放动画按钮
            document.getElementById('restart-animation').addEventListener('click', function() {
                animateImages();
            });
        });
    </script>
