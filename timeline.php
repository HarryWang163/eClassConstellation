<?php
// 引入认证工具
require_once __DIR__ . '/app/includes/auth.php';

// 检查是否已登录
if (!isLoggedIn()) {
    redirectToLogin();
}
?>

<?php
// 引入公共头部
require_once __DIR__ . '/app/includes/header.php';
?>

        <header>
            <h1>时光同轨</h1>
            <p class="subtitle">我们的记忆交互轴线</p>
        </header>
        
        <main>
            <p class="subtitle">欢迎来到“时光同轨”！在这里，我们将共同回顾和分享那些珍贵的瞬间。每一条时间线都承载着我们的故事和回忆，邀请你一同参与，点赞、留言，甚至@你的好友，让我们的记忆更加丰富多彩。</p>
            <p class="subtitle">开发中...</p>
        </main >
<?php
// 引入公共页脚
require_once __DIR__ . '/app/includes/footer.php';
?>

<style>
        .timeline-container {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 25px;
            padding: 50px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            margin-top: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            animation: slideUp 0.8s ease-out;
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
        
        .timeline {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .timeline::after {
            content: '';
            position: absolute;
            width: 6px;
            background: linear-gradient(180deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -3px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
        }
        
        .timeline-item {
            padding: 10px 40px;
            position: relative;
            width: 50%;
            box-sizing: border-box;
            animation: fadeIn 1s ease-out forwards;
            opacity: 0;
        }
        
        .timeline-item:nth-child(1) {
            animation-delay: 0.2s;
        }
        
        .timeline-item:nth-child(2) {
            animation-delay: 0.4s;
        }
        
        .timeline-item:nth-child(3) {
            animation-delay: 0.6s;
        }
        
        .timeline-item:nth-child(4) {
            animation-delay: 0.8s;
        }
        
        .timeline-item:nth-child(5) {
            animation-delay: 1s;
        }
        
        .timeline-item::after {
            content: '';
            position: absolute;
            width: 25px;
            height: 25px;
            background: #0a0a23;
            border: 4px solid #ffd700;
            border-radius: 50%;
            top: 15px;
            z-index: 1;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
            animation: pulse 2s ease-in-out infinite alternate;
        }
        
        @keyframes pulse {
            from {
                box-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
            }
            to {
                box-shadow: 0 0 30px rgba(255, 215, 0, 1), 0 0 40px rgba(255, 215, 0, 0.6);
            }
        }
        
        .left {
            left: 0;
        }
        
        .right {
            left: 50%;
        }
        
        .left::after {
            right: -12px;
        }
        
        .right::after {
            left: -12px;
        }
        
        .timeline-content {
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .timeline-content:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 40px rgba(255, 215, 0, 0.3);
            border-color: rgba(255, 215, 0, 0.5);
        }
        
        .timeline-date {
            font-size: 1rem;
            color: #ffd700;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .timeline-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #f8f9fa;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        .timeline-description {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .timeline-image {
            width: 100%;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }
        
        .timeline-image:hover {
            transform: scale(1.02);
        }
        
        .interaction-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn-like {
            background: rgba(255, 255, 255, 0.1);
            color: #f8f9fa;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-like:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }
        
        .btn-comment {
            background: rgba(255, 215, 0, 0.2);
            color: #ffd700;
            border: 1px solid rgba(255, 215, 0, 0.3);
        }
        
        .btn-comment:hover {
            background: rgba(255, 215, 0, 0.3);
            transform: scale(1.1);
        }
        
        .btn-tag {
            background: rgba(102, 126, 234, 0.2);
            color: #667eea;
            border: 1px solid rgba(102, 126, 234, 0.3);
        }
        
        .btn-tag:hover {
            background: rgba(102, 126, 234, 0.3);
            transform: scale(1.1);
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 2.2rem;
            }
            
            .timeline-container {
                padding: 20px;
            }
            
            .timeline::after {
                left: 31px;
            }
            
            .timeline-item {
                width: 100%;
                padding-left: 70px;
                padding-right: 25px;
            }
            
            .timeline-item::after {
                left: 18px;
            }
            
            .right {
                left: 0;
            }
            
            .timeline-content {
                padding: 20px;
            }
        }
    </style>
    
    <script>
        // 模拟交互功能
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function() {
                if (this.classList.contains('btn-like')) {
                    this.textContent = '👍 已点赞 (25)';
                } else if (this.classList.contains('btn-comment')) {
                    alert('留言功能开发中，敬请期待！');
                } else if (this.classList.contains('btn-tag')) {
                    alert('@某人功能开发中，敬请期待！');
                }
            });
        });
    </script>