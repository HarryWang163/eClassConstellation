<?php
// 引入认证工具
require_once __DIR__ . '/../app/includes/auth.php';

// 检查是否已登录
if (!isLoggedIn()) {
    // 未登录，显示登录提示
    redirectToLogin();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>同在 · 时光叙事</title>
    <style>
        @font-face {
            font-family: 'ShouXie';
            src: url('/../fonts/shouxie.ttf') format('truetype');
            font-display: swap;
    }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'ShouXie', 'Microsoft YaHei', '楷体', 'KaiTi', serif;
            background: linear-gradient(135deg, #0a0a23 0%, #1e1e4a 50%, #3a3a7a 100%);
            min-height: 100vh;
            color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        /* 星光背景 */
        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        .star {
            position: absolute;
            background: white;
            border-radius: 50%;
            animation: twinkle 3s infinite alternate;
        }
        @keyframes twinkle {
            0% { opacity: 0.3; transform: scale(1); }
            100% { opacity: 1; transform: scale(1.2); }
        }
        .splash-container {
            position: relative;
            z-index: 10;
            max-width: 800px;
            width: 90%;
            padding: 2rem;
            align-items: center;
            display: flex;
            flex-direction: column;
            text-align: center;
            background: rgba(10, 10, 35, 0.6);
            backdrop-filter: blur(10px);
            border-radius: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        }
        .line {
            opacity: 0;
            font-size: 1.8rem;
            line-height: 2.2rem;
            margin: 1.2rem 0;
            text-shadow: 0 0 15px rgba(255,215,0,0.3);
            transition: opacity 1.2s ease;
            color: #fff;
            font-weight: 100;
            letter-spacing: 2px;
        }
        .line.visible {
            opacity: 1;
        }
        .special {
            color: #ffd700;
            text-shadow: 0 0 20px rgba(255,215,0,0.7);
            font-weight: 400;
        }
        .footer-note {
            margin-top: 2.5rem;
            font-size: 1.2rem;
            color: rgba(255,255,255,0.6);
            opacity: 0;
            transition: opacity 1s;
        }
        .footer-note.show {
            opacity: 1;
        }
        .back-link a {
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    font-size: 1.8rem;
    transition: 0.3s;
        }
        .back-link a:hover {
            color: #ffd700;
            text-shadow: 0 0 10px rgba(255,215,0,0.5);
        }
        @media (max-width: 600px) {
            .line {
                font-size: 1.3rem;
                line-height: 1.8rem;
                margin: 1rem 0;
            }
            .splash-container {
                padding: 1.5rem;
            }
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
    max-width: 170px;
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
</head>
<body>
    <div class="stars" id="stars"></div>

    <div class="splash-container">
        <div class="activity-item">
            <img src="/../images/icon-future.png" alt="星屿共筑" class="activity-icon-img">

        </div>
        <div class="line" id="line1">当我们</div>
        <div class="line" id="line2">因着同在的信念彼此守望</div>
        <div class="line" id="line3">让无数的小叙事汇聚在一起时</div>
        <div class="line" id="line4">呈现在我们面前的</div>
        <div class="line" id="line5">便会是</div>
        <div class="line special" id="line6">浩瀚银河、星辰大海</div>
        <div class="footer-note" id="footer">
            <div class="back-link">
                <a href="/../futureForNewYear.php">✨ 加入 星屿共筑 ✨ <br/> 在星空上留下我的痕迹</a>

            </div>
        </div>
    </div>
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
        // 生成星光背景
        function createStars() {
            const starsBg = document.getElementById('stars');
            const starCount = 150;
            for (let i = 0; i < starCount; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                star.style.width = Math.random() * 3 + 1 + 'px';
                star.style.height = star.style.width;
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.animationDelay = Math.random() * 3 + 's';
                starsBg.appendChild(star);
            }
        }

        // 逐句浮现
        window.addEventListener('load', function() {
            createStars();

            const lines = [
                document.getElementById('line1'),
                document.getElementById('line2'),
                document.getElementById('line3'),
                document.getElementById('line4'),
                document.getElementById('line5'),
                document.getElementById('line6')
            ];
            const footer = document.getElementById('footer');
            let index = 0;

            function showNextLine() {
                if (index < lines.length) {
                    lines[index].classList.add('visible');
                    index++;
                    setTimeout(showNextLine, 1200); // 每句间隔0.8秒
                } else {
                    setTimeout(() => {
                        footer.classList.add('show');
                    }, 2400);
                }
            }

            // 开始逐句浮现
            showNextLine();
        });
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
</body>
</html>