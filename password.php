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
            <h1>同在密码</h1>
            <p class="subtitle">11班终极认证测试</p>
        </header>
        
        <div class="test-container">
            <div class="progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 20%;"></div>
                </div>
                <div class="progress-text">第 1 题 / 共 50 题</div>
            </div>
            
            <form id="test-form">
                <div class="question">
                    <div class="question-number">题目 1</div>
                    <div class="question-text">11班黑板上面的秦始皇是什么时候出现的？</div>
                    <div class="options">
                        <div class="option">
                            <input type="radio" id="q1-a" name="q1" value="A">
                            <label for="q1-a">A 从军训开始</label>
                        </div>
                        <div class="option">
                            <input type="radio" id="q1-b" name="q1" value="B">
                            <label for="q1-b">B 高一下开始时</label>
                        </div>
                        <div class="option">
                            <input type="radio" id="q1-c" name="q1" value="C">
                            <label for="q1-c">C 高二上开始时</label>
                        </div>
                        <div class="option">
                            <input type="radio" id="q1-d" name="q1" value="D">
                            <label for="q1-d">D 有出现过吗？</label>
                        </div>
                    </div>
                </div>
                
                <div class="question">
                    <div class="question-number">题目 2</div>
                    <div class="question-text">以下哪两个字母曾经出现在Fiona的帽子上？</div>
                    <div class="options">
                        <div class="option">
                            <input type="radio" id="q2-a" name="q2" value="A">
                            <label for="q2-a">A C和A</label>
                        </div>
                        <div class="option">
                            <input type="radio" id="q2-b" name="q2" value="B">
                            <label for="q2-b">B P 和 T</label>
                        </div>
                        <div class="option">
                            <input type="radio" id="q2-c" name="q2" value="C">
                            <label for="q2-c">C C和P</label>
                        </div>
                        <div class="option">
                            <input type="radio" id="q2-d" name="q2" value="D">
                            <label for="q2-d">D A和 E</label>
                        </div>
                    </div>
                </div>
                
                <div class="navigation">
                    <button type="button" class="btn btn-secondary">上一题</button>
                    <button type="button" class="btn">下一题</button>
                </div>
            </form>
        </div>

<?php
// 引入公共页脚
require_once __DIR__ . '/app/includes/footer.php';
?>

<style>
        .test-container {
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
        
        .question {
            margin-bottom: 40px;
            padding: 30px;
            background: rgba(255, 215, 0, 0.05);
            border-radius: 20px;
            border-left: 5px solid #ffd700;
            animation: fadeIn 1s ease-out;
        }
        
        .question-number {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #ffd700;
        }
        
        .question-text {
            font-size: 1.2rem;
            margin-bottom: 20px;
            line-height: 1.6;
            color: #f8f9fa;
        }
        
        .options {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .option {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            color: #f8f9fa;
        }
        
        .option:hover {
            border-color: #ffd700;
            background: rgba(255, 215, 0, 0.1);
            transform: translateX(10px);
        }
        
        .option input[type="radio"] {
            margin-right: 20px;
            transform: scale(1.3);
        }
        
        .option label {
            cursor: pointer;
            flex: 1;
            font-size: 1.1rem;
        }
        
        .navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #f8f9fa;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.2);
        }
        
        .progress {
            margin-bottom: 40px;
            animation: fadeIn 1.2s ease-out;
        }
        
        .progress-bar {
            width: 100%;
            height: 12px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
            border-radius: 10px;
            transition: width 0.5s ease;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.5);
        }
        
        .progress-text {
            text-align: center;
            margin-top: 15px;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 2.2rem;
            }
            
            .test-container {
                padding: 25px;
            }
            
            .navigation {
                flex-direction: column;
                gap: 15px;
            }
            
            header {
                padding: 40px 20px;
            }
        }
    </style>
    
    <script>
        // 模拟题目导航功能
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function() {
                alert('题目导航功能开发中，敬请期待！');
            });
        });
    </script>