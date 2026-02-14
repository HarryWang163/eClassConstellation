<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>同在计划 | 时光同溯，星辰同辉</title>
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
        
        .container {
            max-width: 1500px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            text-align: center;
            margin-bottom: 60px;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
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
        
        h1 {
            font-size: 3.5rem;
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
        
        .subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 20px;
            animation: fadeIn 1.5s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        .header-description {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.7);
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
            animation: fadeIn 2s ease-out;
        }
        
        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 35px;
            margin-top: 60px;
        }
        
        .activity-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 25px;
            padding: 40px 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            transition: all 0.4s ease;
            cursor: pointer;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            animation: slideUp 0.8s ease-out forwards;
            opacity: 0;
        }
        
        .activity-card:nth-child(1) {
            animation-delay: 0.2s;
        }
        
        .activity-card:nth-child(2) {
            animation-delay: 0.4s;
        }
        
        .activity-card:nth-child(3) {
            animation-delay: 0.6s;
        }
        
        .activity-card:nth-child(4) {
            animation-delay: 0.8s;
        }
        
        .activity-card:nth-child(5) {
            animation-delay: 1s;
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
        
        .activity-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 25px 50px rgba(102, 126, 234, 0.3);
            border-color: rgba(102, 126, 234, 0.5);
        }
        
        .activity-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #0a0a23;
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.6);
            animation: pulse 2s ease-in-out infinite alternate;
        }
        
        @keyframes pulse {
            from {
                box-shadow: 0 0 30px rgba(255, 215, 0, 0.6);
            }
            to {
                box-shadow: 0 0 50px rgba(255, 215, 0, 0.8), 0 0 70px rgba(255, 215, 0, 0.4);
            }
        }
        
        .activity-title {
            font-size: 1.6rem;
            margin-bottom: 20px;
            color: #f8f9fa;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        .activity-description {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 30px;
            line-height: 1.7;
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
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.6);
        }
        
        footer {
            text-align: center;
            margin-top: 80px;
            padding: 30px;
            color: rgba(255, 255, 255, 0.6);
            background: rgba(0, 0, 0, 0.2);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            animation: fadeIn 2.5s ease-out;
        }
        
        .nav-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 15px 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .nav-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #f8f9fa;
            text-decoration: none;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: #f8f9fa;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 2.5rem;
            }
            
            .activities-grid {
                grid-template-columns: 1fr;
                gap: 25px;
            }
            
            .activity-card {
                padding: 30px 20px;
            }
            
            header {
                padding: 40px 20px;
            }
            
            .nav-bar {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- 星光背景 -->
    <div class="stars-bg" id="stars-bg"></div>
    
    <div class="container">
        <!-- 导航栏 -->
        <div class="nav-bar">
            <a href="/index.php" class="nav-brand">同在计划</a>
            <div class="nav-links">
                <a href="/dashboard.php" class="nav-link">首页</a>
                <a href="/passdemo.php" class="nav-link">同在密码</a>
                <a href="/timeline.php" class="nav-link">时光同轨</a>
                <a href="/stars.php" class="nav-link">星光互映</a>
                <a href="/future.php" class="nav-link">星屿共筑</a>
            </div>
        </div>