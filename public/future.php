<?php
// 引入认证工具
require_once __DIR__ . '/../app/includes/auth.php';

// 检查是否已登录
if (!isLoggedIn()) {
    redirectToLogin();
}
?>

<?php
// 引入公共头部
require_once __DIR__ . '/../app/includes/header.php';
?>

        <header>
            <h1>星屿共筑</h1>
            <p class="subtitle">我们的未来星空</p>
        </header>
        
        <div class="future-container">
            <div class="tabs">
                <div class="tab active" data-tab="starry-sky">未来星空</div>
                <div class="tab" data-tab="starlight-enhance">星光增辉</div>
            </div>
            
            <!-- 未来星空 -->
            <div class="tab-content active" id="starry-sky">
                <div class="star-creation">
                    <h3>创建你的专属星星</h3>
                    
                    <div class="color-picker">
                        <div class="color-option selected" style="background: #ff6b6b;"></div>
                        <div class="color-option" style="background: #4ecdc4;"></div>
                        <div class="color-option" style="background: #45b7d1;"></div>
                        <div class="color-option" style="background: #96ceb4;"></div>
                        <div class="color-option" style="background: #ffeaa7;"></div>
                        <div class="color-option" style="background: #dfe6e9;"></div>
                        <div class="color-option" style="background: #e17055;"></div>
                        <div class="color-option" style="background: #00b894;"></div>
                    </div>
                    
                    <div class="wish-input">
                        <textarea placeholder="写下你的新年祝福..."></textarea>
                    </div>
                    
                    <button class="btn">发射星星</button>
                </div>
                
                <div class="starry-sky" id="starry-sky-container">
                    <!-- 动态生成的星星和用户星星 -->
                </div>
            </div>
            
            <!-- 星光增辉 -->
            <div class="tab-content" id="starlight-enhance">
                <div class="starlight-enhance">
                    <h3>为好友写下祝福</h3>
                    
                    <div class="message-board">
                        <div class="message-input">
                            <textarea placeholder="在这里为你的好朋友写一段祝福..."></textarea>
                        </div>
                        <button class="btn">发布祝福</button>
                    </div>
                    
                    <div class="messages">
                        <div class="message">
                            <div class="message-author">张三 → 李四</div>
                            <div class="message-content">李四，新的一年希望你能保持你的幽默，继续给我们带来欢乐！祝你学习进步，天天开心！</div>
                        </div>
                        <div class="message">
                            <div class="message-author">王五 → 赵六</div>
                            <div class="message-content">赵六，你是我见过最负责的班长，新的一年希望你能对自己好一点，不要太辛苦了！我们一起加油！</div>
                        </div>
                        <div class="message">
                            <div class="message-author">孙七 → 张三</div>
                            <div class="message-content">张三，谢谢你一直以来的帮助，特别是我的饭卡忘记带的时候。新的一年祝你心想事成，万事如意！</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php
// 引入公共页脚
require_once __DIR__ . '/../app/includes/footer.php';
?>

<style>
        .future-container {
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
        
        .tabs {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
        }
        
        .tab {
            padding: 12px 35px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            font-weight: bold;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .tab.active {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
            color: #0a0a23;
            border-color: #ffd700;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
        }
        
        .tab:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }
        
        .tab-content {
            display: none;
            animation: fadeIn 1s ease-out;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* 未来星空部分 */
        .starry-sky {
            position: relative;
            width: 100%;
            height: 500px;
            background: radial-gradient(circle at center, rgba(255, 215, 0, 0.1) 0%, #0a0a23 100%);
            border-radius: 25px;
            overflow: hidden;
            margin-bottom: 40px;
            border: 1px solid rgba(255, 215, 0, 0.2);
        }
        
        .starry-sky::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="rgba(255,215,0,0.3)"/></svg>');
            opacity: 0.3;
        }
        
        .user-star {
            position: absolute;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
            animation: pulse 2s ease-in-out infinite alternate;
        }
        
        .user-star:hover {
            transform: scale(1.2);
            box-shadow: 0 0 50px rgba(255, 215, 0, 1);
        }
        
        @keyframes pulse {
            from {
                transform: scale(1);
                box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
            }
            to {
                transform: scale(1.1);
                box-shadow: 0 0 40px rgba(255, 215, 0, 0.8);
            }
        }
        
        .firework {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #ffd700;
            border-radius: 50%;
            animation: explode 1s ease-out forwards;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
        }
        
        @keyframes explode {
            0% {
                transform: scale(0);
                opacity: 1;
                box-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
            }
            100% {
                transform: scale(3);
                opacity: 0;
                box-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
            }
        }
        
        .star-creation {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .star-creation h3 {
            font-size: 1.8rem;
            margin-bottom: 30px;
            color: #f8f9fa;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        .color-picker {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
        }
        
        .color-option {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .color-option:hover {
            transform: scale(1.2);
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
        }
        
        .color-option.selected {
            border-color: #ffd700;
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.8);
        }
        
        .wish-input {
            margin-bottom: 40px;
        }
        
        .wish-input textarea {
            width: 100%;
            max-width: 600px;
            height: 120px;
            padding: 20px;
            border-radius: 15px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            font-size: 1rem;
            resize: none;
            font-family: inherit;
            background: rgba(255, 255, 255, 0.1);
            color: #f8f9fa;
            backdrop-filter: blur(5px);
        }
        
        .wish-input textarea::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* 星光增辉部分 */
        .starlight-enhance {
            text-align: center;
        }
        
        .starlight-enhance h3 {
            font-size: 1.8rem;
            margin-bottom: 40px;
            color: #f8f9fa;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        .message-board {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .message-input {
            margin-bottom: 30px;
        }
        
        .message-input textarea {
            width: 100%;
            height: 150px;
            padding: 20px;
            border-radius: 15px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            font-size: 1rem;
            resize: none;
            font-family: inherit;
            background: rgba(255, 255, 255, 0.1);
            color: #f8f9fa;
            backdrop-filter: blur(5px);
        }
        
        .message-input textarea::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .messages {
            text-align: left;
        }
        
        .message {
            padding: 25px;
            background: rgba(255, 215, 0, 0.05);
            border-radius: 15px;
            margin-bottom: 25px;
            border-left: 5px solid #ffd700;
            animation: fadeIn 1s ease-out;
        }
        
        .message-author {
            font-size: 1rem;
            color: #ffd700;
            font-weight: bold;
            margin-bottom: 15px;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
        }
        
        .message-content {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 2.2rem;
            }
            
            .future-container {
                padding: 25px;
            }
            
            .tabs {
                flex-direction: column;
                gap: 15px;
            }
            
            .starry-sky {
                height: 400px;
            }
            
            .user-star {
                width: 60px;
                height: 60px;
                font-size: 0.9rem;
            }
        }
    </style>
    
    <script>
        // 生成随机星星
        function createStars() {
            const sky = document.getElementById('starry-sky-container');
            for (let i = 0; i < 200; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                star.style.width = Math.random() * 3 + 1 + 'px';
                star.style.height = star.style.width;
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.animationDelay = Math.random() * 3 + 's';
                sky.appendChild(star);
            }
        }
        
        // 创建用户星星
        function createUserStar() {
            const sky = document.getElementById('starry-sky-container');
            const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#ffeaa7'];
            
            // 模拟几个用户星星
            const users = ['张三', '李四', '王五', '赵六', '孙七'];
            users.forEach((user, index) => {
                const star = document.createElement('div');
                star.className = 'user-star';
                star.style.backgroundColor = colors[index % colors.length];
                star.style.left = (index * 20 + 10) + '%';
                star.style.top = (Math.random() * 60 + 20) + '%';
                star.textContent = user;
                star.addEventListener('click', function() {
                    createFirework(this.offsetLeft + this.offsetWidth / 2, this.offsetTop + this.offsetHeight / 2);
                });
                sky.appendChild(star);
            });
        }
        
        // 创建烟花效果
        function createFirework(x, y) {
            const sky = document.getElementById('starry-sky-container');
            for (let i = 0; i < 20; i++) {
                const firework = document.createElement('div');
                firework.className = 'firework';
                firework.style.left = x + 'px';
                firework.style.top = y + 'px';
                firework.style.backgroundColor = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#ffeaa7'][Math.floor(Math.random() * 5)];
                firework.style.animationDelay = Math.random() * 0.5 + 's';
                sky.appendChild(firework);
                
                // 动画结束后移除
                setTimeout(() => {
                    firework.remove();
                }, 1000);
            }
        }
        
        // 标签页切换
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // 移除所有标签页的活跃状态
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // 添加当前标签页的活跃状态
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // 颜色选择
        document.querySelectorAll('.color-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.color-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
        
        // 模拟发射星星
        document.querySelector('.btn').addEventListener('click', function() {
            alert('星星发射成功！');
        });
        
        // 初始化星空
        createStars();
        createUserStar();
    </script>