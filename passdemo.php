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
                    <div class="progress-fill" style="width: 0%;"></div>
                </div>
                <div class="progress-text">第 0 题 / 共 0 题</div>
            </div>
            
            <form id="test-form">
                <div id="question-container"></div>
                <div id="options-container"></div>
                <div id="feedback" class="feedback"></div>
                <div id="score-container" class="score-container" style="display: none;">
                    <div class="score-text">得分: <span id="score">0</span> / <span id="total">0</span></div>
                </div>
                
                <div class="navigation">
                    <button type="button" class="btn" id="submit-btn">提交答案</button>
                    <button type="button" class="btn" id="next-btn" disabled>下一题</button>
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
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        
        .option.selected {
            border-color: #ffd700;
            background: rgba(255, 215, 0, 0.2);
        }
        
        .option.correct {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.2);
        }
        
        .option.incorrect {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.2);
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
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
        
        .feedback {
            margin: 20px 0;
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
            display: none;
        }
        
        .feedback.success {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 2px solid rgba(40, 167, 69, 0.4);
        }
        
        .feedback.error {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 2px solid rgba(220, 53, 69, 0.4);
        }
        
        .feedback.warning {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 2px solid rgba(255, 193, 7, 0.4);
        }
        
        .score-container {
            margin: 30px 0;
            padding: 20px;
            background: rgba(255, 215, 0, 0.1);
            border-radius: 15px;
            text-align: center;
            border: 2px solid rgba(255, 215, 0, 0.3);
        }
        
        .score-text {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ffd700;
        }
        
        /* 帮助页面样式 */
        .help-page {
            text-align: center;
            padding: 40px 20px;
            animation: fadeIn 1s ease-out;
        }
        
        .help-content {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .help-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 30px;
            color: #ffd700;
        }
        
        .help-text {
            font-size: 1.2rem;
            margin-bottom: 20px;
            line-height: 1.6;
            color: #f8f9fa;
        }
        
        .help-button {
            margin-top: 30px;
            font-size: 1.1rem;
            padding: 15px 30px;
            background: linear-gradient(90deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
            color: #333;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }
        
        .help-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.5);
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
        // 题目数据配置
        const questions = [
            {
                id: 1,
                question: "11班黑板上面的秦始皇是什么时候出现的？",
                options: [
                    "A 从军训开始",
                    "B 高一下开始时",
                    "C 高二上开始时",
                    "D 有出现过吗？"
                ],
                correctAnswer: 0
            },
            {
                id: 2,
                question: "以下哪两个字母曾经出现在Fiona的帽子上？",
                options: [
                    "A C和A",
                    "B P和T",
                    "C C和P",
                    "D A和E"
                ],
                correctAnswer: 0
            },
            {
                id: 3,
                question: "彭老师认为化学卷子上有（）的题目是可以在（）内以（）的正确率完成的",
                options: [
                    "A 70% 20min 100%",
                    "B 60% 30min 95%",
                    "C 100% 5min 50%",
                    "D 20% 40min 70%"
                ],
                correctAnswer: 0
            },
            {
                id: 4,
                question: "娃娃塔上哪个娃娃通常在最顶层？",
                options: [
                    "A 饺子",
                    "B 云溪溪",
                    "C 果苏苏",
                    "D 吸管杯子"
                ],
                correctAnswer: 0
            },
            {
                id: 5,
                question: "柏弘涛在930班会上唱的歌是",
                options: [
                    "A What makes me handsome",
                    "B What makes her beautiful",
                    "C What makes you beautiful",
                    "D What makes him handsome"
                ],
                correctAnswer: 2
            },
            {
                id: 6,
                question: "班内放的电影《罗小黑战记2》中主角小黑的师傅是",
                options: [
                    "A 大白(●—●)",
                    "B 大黑",
                    "C 有限",
                    "D 无限"
                ],
                correctAnswer: 3
            },
            {
                id: 7,
                question: "11班第一次值周的值周主题是",
                options: [
                    "A 创新驱动艺术 智慧引领文化",
                    "B 创新驱动智慧 艺术引领文化",
                    "C 创新驱动文化 智慧引领艺术",
                    "D 智慧驱动文化 创新引领艺术"
                ],
                correctAnswer: 1
            }
        ];

        // 获取DOM元素
        const questionContainer = document.getElementById('question-container');
        const optionsContainer = document.getElementById('options-container');
        const feedback = document.getElementById('feedback');
        const scoreContainer = document.getElementById('score-container');
        const scoreElement = document.getElementById('score');
        const totalElement = document.getElementById('total');
        const submitBtn = document.getElementById('submit-btn');
        const nextBtn = document.getElementById('next-btn');
        const progressFill = document.querySelector('.progress-fill');
        const progressText = document.querySelector('.progress-text');

        // 初始化变量
        let currentQuestionIndex = 0;
        let score = 0;
        let userAnswers = [];

        // 初始化页面
        const init = () => {
            // 设置总分
            totalElement.textContent = questions.length;
            
            // 加载第一题
            loadQuestion(currentQuestionIndex);
        };

        // 加载题目
        const loadQuestion = (index) => {
            // 清空容器
            questionContainer.innerHTML = '';
            optionsContainer.innerHTML = '';
            feedback.style.display = 'none';
            scoreContainer.style.display = 'none';
            
            // 获取当前题目
            const question = questions[index];
            
            // 确保userAnswers数组长度足够
            if (userAnswers.length < questions.length) {
                userAnswers = new Array(questions.length).fill(undefined);
            }

            // 创建题目元素
            const questionElement = document.createElement('div');
            questionElement.className = 'question';
            questionElement.innerHTML = `
                <div class="question-number">题目 ${index + 1}</div>
                <div class="question-text">${question.question}</div>
            `;
            
            // 添加题目到容器
            questionContainer.appendChild(questionElement);
            
            // 创建选项元素
            const optionsElement = document.createElement('div');
            optionsElement.className = 'options';
            
            question.options.forEach((option, optionIndex) => {
                const optionElement = document.createElement('div');
                optionElement.className = 'option';
                
                // 如果用户已经回答过这个问题，显示之前的选择
                if (userAnswers[index] !== undefined && userAnswers[index] === optionIndex) {
                    optionElement.classList.add('selected');
                }
                
                optionElement.innerHTML = `
                    <input type="radio" id="q${index+1}-${optionIndex}" name="q${index+1}" value="${optionIndex}">
                    <label for="q${index+1}-${optionIndex}">${option}</label>
                `;
                
                // 添加点击事件
                optionElement.addEventListener('click', () => {
                    selectOption(optionElement, optionIndex);
                });
                
                // 添加选项到容器
                optionsElement.appendChild(optionElement);
            });
            
            // 添加选项到容器
            optionsContainer.appendChild(optionsElement);
            
            // 更新进度条
            updateProgress(index);
            
            // 更新导航按钮状态
            updateNavigationButtons(index);
            
            // 显示/隐藏按钮
            if (userAnswers[index] !== undefined) {
                // 如果已经回答过，显示下一题按钮
                submitBtn.style.display = 'none';
                nextBtn.style.display = 'block';
            } else {
                // 如果未回答，显示提交按钮
                submitBtn.style.display = 'block';
                nextBtn.style.display = 'none';
            }
        };

        // 选择选项
        const selectOption = (selectedElement, optionIndex) => {
            // 移除其他选项的选中状态
            const options = selectedElement.parentElement.querySelectorAll('.option');
            options.forEach(option => {
                option.classList.remove('selected');
                option.querySelector('input[type="radio"]').checked = false;
            });
            
            // 添加选中状态
            selectedElement.classList.add('selected');
            selectedElement.querySelector('input[type="radio"]').checked = true;
            
            // 存储用户答案
            userAnswers[currentQuestionIndex] = optionIndex;
        };

        // 更新进度条
        const updateProgress = (index) => {
            const progress = ((index + 1) / questions.length) * 100;
            progressFill.style.width = `${progress}%`;
            progressText.textContent = `第 ${index + 1} 题 / 共 ${questions.length} 题`;
        };

        // 更新导航按钮状态
        const updateNavigationButtons = (index) => {
            // 下一题按钮
            if (index === questions.length - 1) {
                nextBtn.disabled = true;
            } else {
                nextBtn.disabled = false;
            }
        };

        // 检查是否为空答案
        const checkVoidAnswer = () => {
            // 检查用户是否选择了答案
            if (userAnswers[currentQuestionIndex] === undefined) {
                showFeedback('请选择一个答案', 'warning');
                return true;
            }
            return false;
        };

        // 检查答案
        const checkAnswer = () => {
            const question = questions[currentQuestionIndex];
            const userAnswer = userAnswers[currentQuestionIndex];
            
            // 检查答案是否正确
            const isCorrect = userAnswer === question.correctAnswer;
            
            // 显示正确/错误样式
            const options = optionsContainer.querySelectorAll('.option');
            options.forEach((option, optionIndex) => {
                if (optionIndex === question.correctAnswer) {
                    // 正确答案
                    option.classList.add('correct');
                } else if (optionIndex === userAnswer && !isCorrect) {
                    // 错误选择
                    option.classList.add('incorrect');
                }
            });
            
            return isCorrect;
        };

        // 计算总分
        const calculateScore = () => {
            score = 0;
            for (let i = 0; i < questions.length; i++) {
                if (userAnswers[i] === questions[i].correctAnswer) {
                    score++;
                }
            }
            scoreElement.textContent = score;
        };

        // 显示分数
        const displayScore = () => {
            // 显示分数
            scoreContainer.style.display = 'block';
            // 隐藏提交按钮和下一题按钮
            submitBtn.style.display = 'none';
            nextBtn.style.display = 'none';
            
            // 2秒后显示帮助页面
            setTimeout(() => {
                showHelpPage();
            }, 2000);
        };
        
        // 显示帮助页面
        const showHelpPage = () => {
            // 清空容器
            questionContainer.innerHTML = '';
            optionsContainer.innerHTML = '';
            feedback.style.display = 'none';
            scoreContainer.style.display = 'none';
            
            // 创建帮助页面内容
            const helpPageElement = document.createElement('div');
            helpPageElement.className = 'help-page';
            helpPageElement.innerHTML = `
                <div class="help-content">
                    <h2 class="help-title">我们需要你的帮助</h2>
                    <p class="help-text">为了完善11班的"同在密码"系统，我们需要你的参与！</p>
                    <p class="help-text">请点击下方按钮，填写11班的"同在密码"问卷，帮助我们收集更多班级信息。</p>
                    <button class="btn help-button" id="help-button">填写"同在密码"</button>
                </div>
            `;
            
            // 添加帮助页面到容器
            questionContainer.appendChild(helpPageElement);
            
            // 添加按钮点击事件
            const helpButton = document.getElementById('help-button');
            helpButton.addEventListener('click', (e) => {
                // 阻止按钮的默认行为
                e.preventDefault();
                // 跳转到填写链接
                window.location.href = 'https://v.wjx.cn/vm/wFyHkOM.aspx';
            });
        };

        // 显示反馈信息
        const showFeedback = (message, type) => {
            feedback.textContent = message;
            feedback.className = `feedback ${type}`;
            feedback.style.display = 'block';
            
            // 3秒后自动隐藏反馈信息
            setTimeout(() => {
                feedback.style.display = 'none';
            }, 3000);
        };

        // 下一题
        const nextQuestion = () => {
            if (currentQuestionIndex < questions.length - 1) {
                currentQuestionIndex++;
                loadQuestion(currentQuestionIndex);
            }
        };

        // 处理提交
        const handleSubmit = () => {
            // 检查是否选择了答案
            if (checkVoidAnswer()) {
                return;
            }
            
            // 检查答案并显示结果（仅显示颜色提示，不显示文字提示）
            checkAnswer();
            
            // 隐藏提交按钮，显示下一题按钮
            submitBtn.style.display = 'none';
            nextBtn.style.display = 'block';
            
            // 如果是最后一题，计算总分并显示
            if (currentQuestionIndex === questions.length - 1) {
                calculateScore();
                displayScore();
            }
        };

        // 添加事件监听器
        submitBtn.addEventListener('click', handleSubmit);
        nextBtn.addEventListener('click', nextQuestion);

        // 初始化页面
        window.addEventListener('load', init);
    </script>