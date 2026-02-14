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
            overflow: hidden;
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
    </style>
</head>
<body>
    <div class="stars" id="stars"></div>
    <div class="splash-container">
        <div class="line" id="line1">转眼间，相聚在11班的我们，已经共度一年半的晨昏。</div>
        <div class="line" id="line2">课上的专注，课间的玩笑</div>
        <div class="line" id="line3">运动会身旁的风，大舞台夜晚的光</div>
        <div class="line" id="line4">窗外和煦的朝阳，共同淋过的每一场雨</div>
        <div class="line" id="line5">点滴的日常，汇聚在一起</div>
        <div class="line" id="line6">缓缓编织起我们独一无二的青春叙事</div>
        <div class="line" id="line7">光影不过一瞬，却又何其珍贵</div>
        <div class="line" id="line8">片刻感动间的时光，青春记忆里的我们</div>
        <div class="line" id="line9">却因为那些<span class="special">同在</span>的时刻</div>
        <div class="line special" id="line10">而永不褪色</div>
        <div class="footer-note" id="footer">
            <div class="back-link">
                <a href="splash2.php">✨ 开启『同在』计划 ✨</a>
            </div>
        </div>
    </div>

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
                document.getElementById('line6'),
                document.getElementById('line7'),
                document.getElementById('line8'),
                document.getElementById('line9'),
                document.getElementById('line10')
            ];
            const footer = document.getElementById('footer');
            let index = 0;

            function showNextLine() {
                if (index < lines.length) {
                    lines[index].classList.add('visible');
                    index++;
                    setTimeout(showNextLine, 1200); // 每句间隔0.8秒
                } else {
                    // 所有句子显示完后，显示底部提示
                    footer.classList.add('show');
                    // 3秒后跳转到 dashboard.php
                }
            }

            // 开始逐句浮现
            showNextLine();
        });
    </script>
</body>
</html>