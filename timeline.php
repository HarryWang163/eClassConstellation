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
        
        <div class="timeline-container">
            <div class="timeline">
                <!-- 军训时期 -->
                <div class="timeline-item left">
                    <div class="timeline-content">
                        <div class="timeline-date">2024年8月</div>
                        <div class="timeline-title">军训时光</div>
                        <div class="timeline-description">我们的第一次集体生活，顶着烈日训练，一起唱军歌，建立了最初的友谊。那些汗水与笑声，是我们青春的开始。</div>
                        <img class="timeline-image" src="https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=high%20school%20military%20training%20group%20photo%20students%20in%20uniform%20smiling%20outdoor&image_size=landscape_16_9" alt="军训合影">
                        <div class="interaction-buttons">
                            <button class="btn btn-like">👍 点赞 (24)</button>
                            <button class="btn btn-comment">💬 留言</button>
                            <button class="btn btn-tag">@ 某人</button>
                        </div>
                    </div>
                </div>
                
                <!-- 第一次班会 -->
                <div class="timeline-item right">
                    <div class="timeline-content">
                        <div class="timeline-date">2024年9月</div>
                        <div class="timeline-title">第一次班会</div>
                        <div class="timeline-description">班主任林老师主持的第一次班会，我们各自做了自我介绍，选出了班委。那一刻，我们正式成为了一个集体。</div>
                        <img class="timeline-image" src="https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=high%20school%20class%20meeting%20students%20sitting%20in%20classroom%20teacher%20speaking%20warm%20atmosphere&image_size=landscape_16_9" alt="第一次班会">
                        <div class="interaction-buttons">
                            <button class="btn btn-like">👍 点赞 (18)</button>
                            <button class="btn btn-comment">💬 留言</button>
                            <button class="btn btn-tag">@ 某人</button>
                        </div>
                    </div>
                </div>
                
                <!-- 运动会 -->
                <div class="timeline-item left">
                    <div class="timeline-content">
                        <div class="timeline-date">2024年10月</div>
                        <div class="timeline-title">秋季运动会</div>
                        <div class="timeline-description">我们在运动会上奋力拼搏，为班级荣誉而战，留下了许多精彩瞬间。那些加油声、呐喊声，至今仍在耳边回响。</div>
                        <img class="timeline-image" src="https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=high%20school%20sports%20day%20students%20participating%20in%20race%20cheering%20excitement&image_size=landscape_16_9" alt="运动会">
                        <div class="interaction-buttons">
                            <button class="btn btn-like">👍 点赞 (32)</button>
                            <button class="btn btn-comment">💬 留言</button>
                            <button class="btn btn-tag">@ 某人</button>
                        </div>
                    </div>
                </div>
                
                <!-- 元旦晚会 -->
                <div class="timeline-item right">
                    <div class="timeline-content">
                        <div class="timeline-date">2025年1月</div>
                        <div class="timeline-title">元旦晚会</div>
                        <div class="timeline-description">我们自编自演的节目，笑声不断，一起迎接新年的到来。那一刻，我们的心紧紧相连。</div>
                        <img class="timeline-image" src="https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=high%20school%20new%20year%20party%20students%20performing%20on%20stage%20colorful%20decorations&image_size=landscape_16_9" alt="元旦晚会">
                        <div class="interaction-buttons">
                            <button class="btn btn-like">👍 点赞 (28)</button>
                            <button class="btn btn-comment">💬 留言</button>
                            <button class="btn btn-tag">@ 某人</button>
                        </div>
                    </div>
                </div>
                
                <!-- 班级值周 -->
                <div class="timeline-item left">
                    <div class="timeline-content">
                        <div class="timeline-date">2025年3月</div>
                        <div class="timeline-title">第一次值周</div>
                        <div class="timeline-description">我们班第一次值周，主题是"创新驱动智慧 艺术引领文化"，大家都很认真负责。我们用行动证明了11班的凝聚力。</div>
                        <img class="timeline-image" src="https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=high%20school%20students%20on%20duty%20wearing%20armbands%20cleaning%20campus%20responsible&image_size=landscape_16_9" alt="值周">
                        <div class="interaction-buttons">
                            <button class="btn btn-like">👍 点赞 (21)</button>
                            <button class="btn btn-comment">💬 留言</button>
                            <button class="btn btn-tag">@ 某人</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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