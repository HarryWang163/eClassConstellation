<?php
// 启动Session
session_start();

// 引入配置文件
require_once __DIR__ . '/app/config/database.php';

// 处理登录请求
$showForm = true; // 控制是否显示表单
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = '用户名和密码不能为空';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare('SELECT id, username, password FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // 检查用户是否存在
                if ($password == $user['password']) {
                    // 登录成功，存储用户信息到Session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    
                    // 输出HTML和JavaScript进行重定向
                    echo '<!DOCTYPE html>
                    <html>
                    <head>
                        <title>重定向中...</title>
                        <script>
                            window.location.href = "dashboard.php";
                        </script>
                    </head>
                    <body>
                        <p>如果页面没有自动跳转，请<a href="dashboard.php">点击这里</a>。</p>
                    </body>
                    </html>';
                    exit();
                } else {
                    $error = '密码错误';
                }
            } else {
                $error = '用户名不存在';
            }
        } catch (Exception $e) {
            $error = '数据库连接错误: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>同在计划 | 登录</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            background: linear-gradient(135deg, #0a0a23 0%, #1e1e4a 50%, #3a3a7a 100%);
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* 星光背景动画 */
        .stars-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .star {
            position: absolute;
            background: white;
            border-radius: 50%;
            animation: twinkle 3s infinite alternate;
        }
        
        @keyframes twinkle {
            0% {
                opacity: 0.3;
                transform: scale(1);
            }
            100% {
                opacity: 1;
                transform: scale(1.2);
            }
        }
        
        .login-container {
            max-width: 450px;
            width: 90%;
            padding: 50px 40px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeInUp 1s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-title {
            font-size: 2.5rem;
            color: #f8f9fa;
            margin-bottom: 15px;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.5), 0 0 40px rgba(102, 126, 234, 0.5);
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes glow {
            from {
                text-shadow: 0 0 20px rgba(255, 255, 255, 0.5), 0 0 40px rgba(102, 126, 234, 0.5);
            }
            to {
                text-shadow: 0 0 30px rgba(255, 255, 255, 0.8), 0 0 60px rgba(102, 126, 234, 0.8);
            }
        }
        
        .login-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-label {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }
        
        .form-input {
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: #f8f9fa;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .btn {
            display: inline-block;
            padding: 15px 35px;
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
            color: #0a0a23;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4);
            position: relative;
            overflow: hidden;
            border: none;
            font-size: 1rem;
            cursor: pointer;
            text-align: center;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.6);
        }
        
        .error-message {
            background: rgba(255, 70, 70, 0.2);
            border: 1px solid rgba(255, 70, 70, 0.4);
            border-radius: 12px;
            padding: 15px;
            color: rgba(255, 150, 150, 0.9);
            font-size: 0.9rem;
            text-align: center;
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .back-link {
            text-align: center;
            margin-top: 25px;
        }
        
        .back-link a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .back-link a:hover {
            color: #f8f9fa;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
        
        @media (max-width: 768px) {
            .login-container {
                padding: 40px 30px;
            }
            
            .login-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- 星光背景 -->
    <div class="stars-bg" id="stars-bg"></div>
    
    <div class="login-container">
        <div class="login-header">
            <h1 class="login-title">同在计划</h1>
            <p class="login-subtitle">请登录以继续</p>
        </div>
        
        <form class="login-form" method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="username">用户名</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="请输入用户名" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">密码</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="请输入密码" required>
            </div>
            
            <button type="submit" class="btn">登录</button>
        </form>
        
    </div>
    
    <script>
        // 生成星光背景
        function createStars() {
            const starsBg = document.getElementById('stars-bg');
            const starCount = 200;
            
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
        
        // 页面加载完成后生成星光
        window.addEventListener('load', createStars);
    </script>
</body>
</html>