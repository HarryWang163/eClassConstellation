<?php
// 引入认证工具
require_once __DIR__ . '/app/includes/auth.php';

// 检查是否已登录
if (!isLoggedIn()) {
    // 未登录，显示登录提示
    redirectToLogin();
}
?>

<?php
// 引入公共头部
require_once __DIR__ . '/app/includes/header.php';
?>

<style>
/* 外层容器 */
.activities-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 50px;
}

/* 品牌标识区域 */
.brand-section {
    text-align: center;
}

.brand-image {
    max-width: 200px;       /* 根据实际图片调整 */
    height: auto;
    filter: drop-shadow(0 0 30px rgba(255, 215, 0, 0.6));
    animation: float 4s ease-in-out infinite;
}

/* 四个活动图片水平行 */
.activities-row {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;        /* 移动端自动折行 */
    gap: 40px 100px;         /* 间距 */
    width: 100%;
}

.activity-item {
    display: flex;
    justify-content: center;
    align-items: center;
    background: none !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0;
}

.activity-icon-img {
    max-width: 170px;       /* 统一大小，可按需调整 */
    height: auto;
    display: block;
    transition: transform 0.3s ease, filter 0.3s ease;
}

.activity-icon-img:hover {
    transform: scale(1.08);
    filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.6));
}

/* 同在见证区域 */
.witness-section {
    margin-top: 20px;
    text-align: center;
}

.witness-image {
    max-width: 180px;
    height: auto;
    transition: transform 0.3s ease;
}

.witness-image:hover {
    transform: scale(1.05);
}

/* 浮动动画（品牌标识） */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

/* 移动端适配 */
@media (max-width: 768px) {
    .activities-row {
        gap: 30px;
    }
    .activity-icon-img {
        max-width: 100px;
    }
    .brand-image {
        max-width: 200px;
    }
}

/* ---------- 品牌图片：脉动光晕 ---------- */
.brand-section {
    position: relative;
}

.brand-image {
    position: relative;
    z-index: 2;
    animation: float 4s ease-in-out infinite, pulseGlow 3s ease-in-out infinite alternate;
}

@keyframes pulseGlow {
    0% { filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.6)); }
    100% { filter: drop-shadow(0 0 45px rgba(255, 215, 0, 0.9)); }
}

/* ---------- 活动图标：旋转光环 ---------- */
.activity-item {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* 光环伪元素 - 每个图标背后的旋转圆环 */
.activity-item::before {
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
}

/* 第二个光环，反向旋转，增加层次感 */
.activity-item::after {
    content: '';
    position: absolute;
    width: 130%;
    height: 130%;
    border: 1px dashed rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    animation: rotateReverse 12s linear infinite;
    opacity: 0.5;
}

@keyframes rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes rotateReverse {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(-360deg); }
}

/* 悬停时光环高亮并加速 */
.activity-item:hover::before {
    border-color: rgba(255, 215, 0, 0.8);
    border-top-color: #fff;
    border-bottom-color: rgba(255, 215, 0, 0.5);
    animation-duration: 4s;
    box-shadow: 0 0 30px rgba(255, 215, 0, 0.8);
    width: 130%;
    height: 130%;
    transition: all 0.3s;
}

.activity-item:hover::after {
    border-color: rgba(255, 255, 255, 0.5);
    animation-duration: 6s;
    width: 140%;
    height: 140%;
}

/* 调整图片层级，不被伪元素遮挡 */
.activity-icon-img {
    position: relative;
    z-index: 3;
}

/* 为每个活动图标创建星光粒子容器 */
.activity-item {
    position: relative;
}

/* 粒子容器 */
.star-field {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 200px;
    height: 200px;
    transform: translate(-50%, -50%);
    pointer-events: none;
    animation: rotateField 20s linear infinite;
}

.star-particle {
    position: absolute;
    background: white;
    border-radius: 50%;
    box-shadow: 0 0 10px gold;
    animation: twinkle 2s infinite alternate;
}

@keyframes rotateField {
    from { transform: translate(-50%, -50%) rotate(0deg); }
    to { transform: translate(-50%, -50%) rotate(360deg); }
}

@keyframes twinkle {
    0% { opacity: 0.3; transform: scale(1); }
    100% { opacity: 1; transform: scale(1.5); }
}
</style>

<!-- 主体内容开始 -->
<main>
<div class="activities-wrapper">
    <!-- 中心图片（品牌标识） -->
    <header>
            <h1>『同在』计划</h1>
            <p class="subtitle">时光同溯，星辰同辉</p>
            <p class="header-description">
                "同在"，不仅是时间与空间的重叠，更是记忆的共享、心灵的照见与未来的共赴。<br>
                我们相聚于高二11班，已共度一年半的星辰与晨昏。那些黑板上的涂鸦、课堂上的笑声、运动会上的呐喊，共同编织成我们独一无二的青春叙事。
            </p>
        </header>
    
    <!-- 四个活动图片水平一排 -->
    <div class="activities-row">
        <a href="passdemo.php" class="activity-link">
        <div class="activity-item">
            <img src="images/icon-password.png" alt="同在密码" class="activity-icon-img">
        </div>
        </a>
        <a href="timeline.php" class="activity-link">
        <div class="activity-item">
            <img src="images/icon-timeline.png" alt="时光同轨" class="activity-icon-img">
        </div>
        </a>
        <a href="stars.php" class="activity-link">
        <div class="activity-item">
            <img src="images/icon-stars.png" alt="星光互映" class="activity-icon-img">
        </div>
        </a>
        <a href="future.php" class="activity-link">
        <div class="activity-item">
            <img src="images/icon-future.png" alt="星屿共筑" class="activity-icon-img">
        </div>
        </a>
    </div>
</div>
</main>

<script>
    // 为环形区域添加星光装饰
    function createRingStars() {
        const starsContainer = document.getElementById('ring-stars');
        if (!starsContainer) return;
        
        const starCount = 100;
        for (let i = 0; i < starCount; i++) {
            const star = document.createElement('div');
            star.className = 'star-decoration';
            star.style.width = Math.random() * 4 + 1 + 'px';
            star.style.height = star.style.width;
            star.style.left = Math.random() * 100 + '%';
            star.style.top = Math.random() * 100 + '%';
            star.style.animationDelay = Math.random() * 5 + 's';
            star.style.animationDuration = Math.random() * 3 + 2 + 's';
            starsContainer.appendChild(star);
        }
    }
    
    window.addEventListener('load', createRingStars);
</script>
<script>
    function addStarField() {
        const items = document.querySelectorAll('.activity-item');
        items.forEach(item => {
            // 避免重复添加
            if (item.querySelector('.star-field')) return;
            
            const field = document.createElement('div');
            field.className = 'star-field';
            
            // 生成20个粒子
            for (let i = 0; i < 20; i++) {
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
            item.appendChild(field);
        });
    }
    window.addEventListener('load', addStarField);
</script>
<?php
// 引入公共页脚
require_once __DIR__ . '/app/includes/footer.php';
?>