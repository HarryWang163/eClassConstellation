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
            <h1>同在见证</h1>
            <p class="subtitle">领航者之音</p>
        </header>
        
        <div class="witness-container">
            <!-- 林老师寄语 -->
            <div class="teacher-message">
                <div class="teacher-header">
                    <div class="teacher-avatar">林</div>
                    <div class="teacher-info">
                        <div class="teacher-name">林老师</div>
                        <div class="teacher-title">班主任</div>
                    </div>
                </div>
                <div class="message-content">
                    亲爱的同学们：
                    <br><br>
                    时光荏苒，我们已经共同度过了一年半的高中时光。在这一年半里，我见证了你们的成长与蜕变，从刚入学的青涩少年，到如今的成熟稳重。你们的每一次进步，每一次突破，都让我感到无比欣慰。
                    <br><br>
                    新的一年即将到来，我希望你们能够保持这份热情与活力，继续努力学习，不断超越自我。记住，高中时光是人生中最宝贵的阶段之一，它不仅是知识的积累，更是人格的塑造。
                    <br><br>
                    无论未来遇到什么困难与挑战，都要记得你们是一个集体，是高二11班的一份子。团结一心，相互支持，你们一定能够创造属于自己的辉煌。
                    <br><br>
                    最后，祝大家新年快乐，学业有成，心想事成！
                </div>
                <div class="message-date">2026年1月1日</div>
            </div>
            
            <!-- 许老师寄语 -->
            <div class="teacher-message">
                <div class="teacher-header">
                    <div class="teacher-avatar">许</div>
                    <div class="teacher-info">
                        <div class="teacher-name">许老师</div>
                        <div class="teacher-title">数学老师</div>
                    </div>
                </div>
                <div class="message-content">
                    亲爱的11班同学们：
                    <br><br>
                    作为你们的数学老师，我很高兴看到大家在数学学习上的进步。数学不仅是一门学科，更是一种思维方式，它教会我们如何严谨地思考问题，如何有条理地解决问题。
                    <br><br>
                    新的一年，希望你们能够保持对数学的兴趣，多思考，多练习，不断提高自己的数学素养。同时，也要注意劳逸结合，保持良好的学习状态。
                    <br><br>
                    记住，数学的世界是充满乐趣的，每解决一个问题，每掌握一个知识点，都是一种成长。相信自己，你们一定能够在数学的道路上越走越远。
                    <br><br>
                    祝大家新年快乐，数学成绩更上一层楼！
                </div>
                <div class="message-date">2026年1月1日</div>
            </div>
            
            <!-- Fiona老师寄语 -->
            <div class="teacher-message">
                <div class="teacher-header">
                    <div class="teacher-avatar">F</div>
                    <div class="teacher-info">
                        <div class="teacher-name">Fiona</div>
                        <div class="teacher-title">英语老师</div>
                    </div>
                </div>
                <div class="message-content">
                    Dear Class 11 Students:
                    <br><br>
                    It's been a wonderful journey teaching all of you over the past year and a half. I've been impressed by your enthusiasm for English learning and your willingness to challenge yourselves.
                    <br><br>
                    As we enter the new year, I hope you continue to embrace English not just as a subject, but as a tool to connect with the world. Remember, language learning is a marathon, not a sprint. Every word you learn, every sentence you speak, brings you one step closer to fluency.
                    <br><br>
                    Don't be afraid to make mistakes—they are an essential part of the learning process. Keep practicing, keep exploring, and most importantly, keep enjoying the journey!
                    <br><br>
                    Wishing you all a happy new year filled with joy, growth, and success!
                    <br><br>
                    Warm regards,
                    <br>
                    Fiona
                </div>
                <div class="message-date">January 1, 2026</div>
            </div>
        </div>

<?php
// 引入公共页脚
require_once __DIR__ . '/app/includes/footer.php';
?>

<style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        header {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        h1 {
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .subtitle {
            color: rgba(255, 255, 255, 0.9);
        }
        
        .witness-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }
        
        .teacher-message {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-top: 5px solid #667eea;
            position: relative;
            overflow: hidden;
        }
        
        .teacher-message::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top right, rgba(102, 126, 234, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .teacher-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .teacher-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-right: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .teacher-info {
            flex: 1;
        }
        
        .teacher-name {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        .teacher-title {
            font-size: 1rem;
            color: #667eea;
        }
        
        .message-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
            text-align: justify;
            margin-bottom: 30px;
        }
        
        .message-date {
            font-size: 0.9rem;
            color: #999;
            text-align: right;
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }
            
            .witness-container {
                padding: 20px;
            }
            
            .teacher-message {
                padding: 20px;
            }
            
            .teacher-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .teacher-avatar {
                margin-right: 0;
            }
        }
    </style>