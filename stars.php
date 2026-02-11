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
            <h1>星光互映</h1>
            <p class="subtitle">你在我眼中的样子</p>
        </header>
        
        <div class="stars-container">
            <div class="tabs">
                <div class="tab active" data-tab="tagging">为好友贴标签</div>
                <div class="tab" data-tab="star-map">查看星光图谱</div>
            </div>
            
            <!-- 为好友贴标签 -->
            <div class="tab-content active" id="tagging">
                <div class="tagging-section">
                    <h2>选择好友并为TA贴标签</h2>
                    
                    <div class="friend-selector">
                        <label for="friend">选择好友：</label>
                        <select id="friend">
                            <option value="">请选择好友</option>
                            <option value="zhangsan">张三</option>
                            <option value="lisi">李四</option>
                            <option value="wangwu">王五</option>
                            <option value="zhaoliu">赵六</option>
                            <option value="sunqi">孙七</option>
                        </select>
                    </div>
                    
                    <div class="tags-input">
                        <label>选择标签（可多选）：</label>
                        <div class="tags-container">
                            <div class="tag-option">幽默</div>
                            <div class="tag-option">负责</div>
                            <div class="tag-option">温暖</div>
                            <div class="tag-option">可爱</div>
                            <div class="tag-option">善良有爱心</div>
                            <div class="tag-option">饭卡救助大使</div>
                            <div class="tag-option">古希腊掌管英语默写的神</div>
                            <div class="tag-option">运动健将</div>
                            <div class="tag-option">学习委员</div>
                            <div class="tag-option">文艺青年</div>
                        </div>
                    </div>
                    
                    <div class="custom-tag">
                        <input type="text" placeholder="输入自定义标签">
                        <button class="btn">添加自定义标签</button>
                    </div>
                    
                    <button class="btn">提交标签</button>
                </div>
            </div>
            
            <!-- 查看星光图谱 -->
            <div class="tab-content" id="star-map">
                <div class="star-map">
                    <h2 class="star-map-title">张三的星光图谱</h2>
                    
                    <div class="star-canvas">
                        <div class="star-node" style="top: 20%; left: 20%;">
                            <div>幽默</div>
                            <div class="star-tag">28人</div>
                        </div>
                        <div class="star-node" style="top: 40%; left: 60%;">
                            <div>负责</div>
                            <div class="star-tag">22人</div>
                        </div>
                        <div class="star-node" style="top: 70%; left: 30%;">
                            <div>温暖</div>
                            <div class="star-tag">18人</div>
                        </div>
                        <div class="star-node" style="top: 30%; left: 80%;">
                            <div>饭卡救助</div>
                            <div class="star-tag">15人</div>
                        </div>
                        <div class="star-node" style="top: 60%; left: 70%;">
                            <div>运动健将</div>
                            <div class="star-tag">12人</div>
                        </div>
                    </div>
                    
                    <div class="tag-summary">
                        <h3>大家眼中的你</h3>
                        <div class="tag-cloud">
                            <div class="tag-cloud-item">幽默</div>
                            <div class="tag-cloud-item">负责</div>
                            <div class="tag-cloud-item">温暖</div>
                            <div class="tag-cloud-item">饭卡救助大使</div>
                            <div class="tag-cloud-item">运动健将</div>
                            <div class="tag-cloud-item">善良有爱心</div>
                            <div class="tag-cloud-item">可爱</div>
                        </div>
                    </div>
                    
                    <button class="btn">查看我的星光图谱</button>
                </div>
            </div>
        </div>

<?php
// 引入公共页脚
require_once __DIR__ . '/app/includes/footer.php';
?>

<style>
        .stars-container {
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
        
        /* 贴标签部分 */
        .tagging-section {
            text-align: center;
        }
        
        .tagging-section h2 {
            font-size: 1.8rem;
            margin-bottom: 40px;
            color: #f8f9fa;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        .friend-selector {
            margin-bottom: 40px;
        }
        
        .friend-selector label {
            font-size: 1.2rem;
            font-weight: bold;
            margin-right: 15px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .friend-selector select {
            padding: 12px 25px;
            border-radius: 15px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            font-size: 1.1rem;
            background: rgba(255, 255, 255, 0.1);
            color: #f8f9fa;
            backdrop-filter: blur(5px);
        }
        
        .tags-input {
            margin-bottom: 40px;
        }
        
        .tags-input label {
            display: block;
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
            justify-content: center;
        }
        
        .tag-option {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.8);
        }
        
        .tag-option:hover {
            background: rgba(255, 215, 0, 0.2);
            border-color: rgba(255, 215, 0, 0.5);
            transform: scale(1.1);
        }
        
        .tag-option.selected {
            background: rgba(255, 215, 0, 0.3);
            color: #ffd700;
            border-color: #ffd700;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
        }
        
        .custom-tag {
            margin-top: 30px;
        }
        
        .custom-tag input {
            padding: 12px 25px;
            border-radius: 15px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            font-size: 1.1rem;
            width: 350px;
            margin-right: 15px;
            background: rgba(255, 255, 255, 0.1);
            color: #f8f9fa;
            backdrop-filter: blur(5px);
        }
        
        /* 星光图谱部分 */
        .star-map {
            text-align: center;
            padding: 40px;
            background: rgba(255, 215, 0, 0.05);
            border-radius: 25px;
            margin-top: 40px;
            border: 1px solid rgba(255, 215, 0, 0.1);
        }
        
        .star-map-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 40px;
            color: #ffd700;
            text-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
        }
        
        .star-canvas {
            position: relative;
            width: 100%;
            height: 450px;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.1) 0%, rgba(10, 10, 35, 0.8) 100%);
            border-radius: 20px;
            margin-bottom: 40px;
            overflow: hidden;
            border: 1px solid rgba(255, 215, 0, 0.2);
        }
        
        .star-canvas::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="rgba(255,215,0,0.3)"/></svg>');
            opacity: 0.3;
        }
        
        .star-node {
            position: absolute;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: bold;
            color: #0a0a23;
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.8);
            animation: pulse 2s ease-in-out infinite alternate;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .star-node:hover {
            transform: scale(1.2);
            box-shadow: 0 0 50px rgba(255, 215, 0, 1);
        }
        
        @keyframes pulse {
            from {
                transform: scale(1);
                box-shadow: 0 0 30px rgba(255, 215, 0, 0.8);
            }
            to {
                transform: scale(1.1);
                box-shadow: 0 0 50px rgba(255, 215, 0, 1), 0 0 70px rgba(255, 215, 0, 0.5);
            }
        }
        
        .star-tag {
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .tag-summary {
            margin-top: 40px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .tag-summary h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: #f8f9fa;
            text-align: center;
        }
        
        .tag-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }
        
        .tag-cloud-item {
            padding: 10px 20px;
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
            color: #0a0a23;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4);
            transition: all 0.3s ease;
        }
        
        .tag-cloud-item:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.6);
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 2.2rem;
            }
            
            .stars-container {
                padding: 25px;
            }
            
            .tabs {
                flex-direction: column;
                gap: 15px;
            }
            
            .custom-tag input {
                width: 100%;
                margin-bottom: 15px;
            }
            
            .star-canvas {
                height: 400px;
            }
            
            .star-node {
                width: 60px;
                height: 60px;
                font-size: 0.8rem;
            }
        }
    </style>
    
    <script>
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
        
        // 标签选择功能
        document.querySelectorAll('.tag-option').forEach(option => {
            option.addEventListener('click', function() {
                this.classList.toggle('selected');
            });
        });
        
        // 模拟提交功能
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function() {
                alert('功能开发中，敬请期待！');
            });
        });
    </script>