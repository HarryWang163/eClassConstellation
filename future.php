<?php
// 引入认证工具
require_once __DIR__ . '/app/includes/auth.php';

// 检查是否已登录
if (!isLoggedIn()) {
    redirectToLogin();
}

// 处理图片上传API
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_user_image') {
    header('Content-Type: application/json');
    
    try {
        // 连接数据库
        require_once __DIR__ . '/app/config/database.php';
        $db = getDB();
        
        $user_id = $_SESSION['user_id'];
        $img_data = $_POST['img_data'] ?? '';
        
        if (empty($img_data)) {
            echo json_encode(['success' => false, 'message' => '图片数据不能为空']);
            exit;
        }
        
        // 检查用户是否已有绘画记录
        $checkStmt = $db->prepare('SELECT id FROM user_images WHERE user_id = ? LIMIT 1');
        $checkStmt->execute([$user_id]);
        $existingRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingRecord) {
            // 如果存在记录，则更新
            $updateStmt = $db->prepare('UPDATE user_images SET img = ?, created_at = CURRENT_TIMESTAMP WHERE user_id = ?');
            $result = $updateStmt->execute([$img_data, $user_id]);
            $operation = 'updated';
        } else {
            // 如果不存在记录，则插入新记录
            $insertStmt = $db->prepare('INSERT INTO user_images (user_id, img) VALUES (?, ?)');
            $result = $insertStmt->execute([$user_id, $img_data]);
            $operation = 'inserted';
        }
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => '图片保存成功', 'operation' => $operation]);
        } else {
            echo json_encode(['success' => false, 'message' => '图片保存失败']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '数据库错误: ' . $e->getMessage()]);
    }
    exit;
}
?>

<?php
// 引入公共头部
require_once __DIR__ . '/app/includes/header.php';
?>

        <header>
            <h1>星屿共筑</h1>
            <p class="subtitle">我们的未来星空</p>
            <p class="subtitle">本项目开发中@zsq 此处展示简单demo🫡</p>
        </header>
        
        <div class="future-container">
            <div class="tabs">
                <div class="tab active" data-tab="peer-evaluation">查看他评</div>
                <div class="tab" data-tab="we-together">我们同在</div>
            </div>
            
            <!-- 查看他评 -->
            <div class="tab-content active" id="peer-evaluation">
                <div class="peer-evaluation">
                    <h3>来自同学们的评价</h3>
                    
                    <div class="letter-container">
                        <div class="letter-header">
                            <div class="letter-from">亲爱的同学</div>
                            <div class="letter-date">2025年</div>
                        </div>
                        
                        <div class="letter-content" id="letter-content">
                            <!-- 逐字显示的内容将在这里生成 -->
                        </div>
                        
                        <div class="letter-footer">
                            <div class="letter-signature">你的同学</div>
                        </div>
                    </div>
                    
                    <div class="evaluation-controls">
                        <button class="btn" id="next-evaluation">下一条评价</button>
                        <button class="btn" id="capture-screenshot">一键生成截图</button>
                    </div>
                </div>
            </div>
            
            <!-- 我们同在 -->
            <div class="tab-content" id="we-together">
                <div class="we-together">
                    <h3>我们同在</h3>
                    
                    <!-- 颜色选择步骤 -->
                    <div class="step-content active" id="color-selection">
                        <div class="color-selection-container">
                            <h4>作为一颗星星，我的颜色是...</h4>
                            <div class="gradient-picker-container">
                                <!-- 左侧：圆形预览区域 -->
                                <div class="preview-section">
                                    <div class="gradient-preview" id="gradient-preview"></div>
                                </div>
                                
                                <!-- 右侧：控制面板 -->
                                <div class="control-section">
                                    <!-- 渐变条 -->
                                    <div class="gradient-bar-container">
                                        <div class="gradient-bar" id="gradient-bar"></div>
                                    </div>
                                    
                                    <!-- 色标属性控制 -->
                                    <div class="color-stop-controls" id="color-stop-controls">
                                        <h5>色标属性</h5>
                                        <div class="control-group">
                                            <label>颜色</label>
                                            <input type="color" id="color-picker" value="#ffd700">
                                        </div>
                                        <div class="control-group">
                                            <label>位置</label>
                                            <div class="input-with-slider">
                                                <input type="range" id="position-slider" min="0" max="100" value="50">
                                                <input type="number" id="position-input" min="0" max="100" value="50">
                                                <span>%</span>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label>透明度</label>
                                            <div class="input-with-slider">
                                                <input type="range" id="opacity-slider" min="0" max="100" value="100">
                                                <input type="number" id="opacity-input" min="0" max="100" value="100">
                                                <span>%</span>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label>亮度</label>
                                            <div class="input-with-slider">
                                                <input type="range" id="brightness-slider" min="0" max="100" value="50">
                                                <input type="number" id="brightness-input" min="0" max="100" value="50">
                                                <span>%</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- 操作按钮 -->
                                    <div class="action-buttons">
                                        <button class="btn" id="add-color-stop">添加色标</button>
                                        <button class="btn" id="remove-color-stop">删除选中色标</button>
                                    </div>
                                </div>
                            </div>
                            <button class="btn" id="next-to-creation">下一步：创造内容</button>
                        </div>
                    </div>
                    
                    <!-- 创造内容步骤 -->
                    <div class="step-content" id="creation">
                        <div class="creation-container">
                            <!-- <div class="creation-tabs">
                                <div class="creation-tab active" data-creation-type="draw">绘画</div>
                                <div class="creation-tab" data-creation-type="write">写字</div>
                            </div> -->
                            
                            <!-- 绘画模式 -->
                            <div class="creation-content active" id="draw-content">
                                <div class="canvas-container">
                                    <div class="canvas-wrapper">
                                        <canvas id="drawing-canvas" width="400" height="400"></canvas>
                                    </div>
                                    <div class="drawing-controls">
                                        <label>画笔大小</label>
                                        <input type="range" id="brush-size" min="2" max="20" value="5">
                                        <button class="btn small" id="clear-canvas">清空画布</button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 写字模式 -->
                            <!-- <div class="creation-content" id="write-content">
                                <div class="writing-container">
                                    <textarea id="message-input" placeholder="在这里写下你的话..."></textarea>
                                    <div class="writing-preview" id="writing-preview"></div>
                                </div>
                            </div> -->
                            
                            <div class="creation-navigation">
                                <button class="btn" id="back-to-color">上一步：选择颜色</button>
                                <button class="btn" id="next-to-preview">下一步：预览提交</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 预览提交步骤 -->
                    <div class="step-content" id="preview">
                        <div class="preview-container">
                            <h4>你的创作预览</h4>
                            <div class="final-preview" id="final-preview"></div>
                            <p class="preview-description">点击完成按钮，将你的创作保存到我们的星空中。</p>
                            <div class="preview-navigation">
                                <button class="btn" id="back-to-creation">上一步：修改内容</button>
                                <button class="btn" id="complete-creation">完成创作</button>
                            </div>
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
        
        /* 查看他评部分 */
        .peer-evaluation {
            text-align: center;
        }
        
        .peer-evaluation h3 {
            font-size: 1.8rem;
            margin-bottom: 30px;
            color: #f8f9fa;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        .letter-container {
            position: relative;
            max-width: 800px;
            margin: 0 auto 40px;
            padding: 60px;
            background: #f5e9d0; /* 米黄色纸张背景 */
            background-image: 
                radial-gradient(#d9c7a7 1px, transparent 1px),
                radial-gradient(#d9c7a7 1px, transparent 1px);
            background-size: 30px 30px;
            background-position: 0 0, 15px 15px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(217, 199, 167, 0.5); /* 使用类似纸张的颜色边框 */
            color: #333;
            text-align: left;
            min-height: 500px;
        }
        
        /* 添加纸张纹理效果 */
        .letter-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                linear-gradient(rgba(217, 199, 167, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(217, 199, 167, 0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            pointer-events: none;
            border-radius: 15px;
        }
        
        /* 添加装饰性水印效果 */
        .letter-container::after {
            content: '';
            position: absolute;
            top: 20px;
            right: 20px;
            width: 80px;
            height: 80px;
            background: 
                radial-gradient(circle, rgba(217, 199, 167, 0.2) 10%, transparent 10%),
                radial-gradient(circle, rgba(217, 199, 167, 0.2) 10%, transparent 10%);
            background-size: 4px 4px;
            opacity: 0.3;
            border-radius: 50%;
            pointer-events: none;
        }
        
        .letter-header {
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(139, 69, 19, 0.3);
        }
        
        .letter-from {
            font-size: 1.2rem;
            font-weight: bold;
            color: #8b4513;
            margin-bottom: 10px;
        }
        
        .letter-date {
            font-size: 0.9rem;
            color: #8b4513;
            opacity: 0.8;
        }
        
        .letter-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
            margin-bottom: 40px;
            min-height: 200px;
            font-family: 'SimSun', serif;
        }
        
        .letter-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid rgba(139, 69, 19, 0.3);
            text-align: right;
        }
        
        .letter-signature {
            font-size: 1.1rem;
            font-weight: bold;
            color: #8b4513;
        }
        
        .evaluation-controls {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }
        
        /* 我们同在部分 */
        .we-together {
            text-align: center;
            padding: 40px;
            background: rgba(255, 215, 0, 0.05);
            border-radius: 25px;
            border: 1px solid rgba(255, 215, 0, 0.2);
        }
        
        .we-together h3 {
            font-size: 1.8rem;
            margin-bottom: 30px;
            color: #f8f9fa;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        /* 步骤内容 */
        .step-content {
            display: none;
            animation: fadeIn 0.5s ease-out;
        }
        
        .step-content.active {
            display: block;
        }
        
        /* 颜色选择 */
        .color-selection-container {
            text-align: center;
        }
        
        .color-selection-container h4 {
            font-size: 1.4rem;
            margin-bottom: 40px;
            color: #f8f9fa;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        /* 渐变选择器容器 */
        .gradient-picker-container {
            display: flex;
            flex-direction: row;
            gap: 40px;
            margin-bottom: 40px;
            align-items: flex-start;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* 预览区域 */
        .preview-section {
            flex: 1;
            min-width: 300px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .gradient-preview {
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 5px solid rgba(255, 255, 255, 0.1);
        }
        
        /* 控制面板 */
        .control-section {
            flex: 1;
            min-width: 300px;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        /* 渐变条 */
        .gradient-bar-container {
            position: relative;
            width: 100%;
            height: 40px;
            margin-bottom: 20px;
        }
        
        .gradient-bar {
            width: 100%;
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(to right, #ff0000 0%, #ffff00 50%, #0000ff 100%);
            position: relative;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        /* 色标 */
        .color-stop {
            position: absolute;
            top: -10px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid white;
            cursor: pointer;
            transform: translateX(-50%);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }
        
        .color-stop.active {
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.8);
            transform: translateX(-50%) scale(1.2);
        }
        
        /* 色标属性控制 */
        .color-stop-controls {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px;
        }
        
        .color-stop-controls h5 {
            font-size: 1.1rem;
            margin-bottom: 20px;
            color: #f8f9fa;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        .control-group {
            margin-bottom: 20px;
        }
        
        .control-group label {
            display: block;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: bold;
        }
        
        .input-with-slider {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .input-with-slider input[type="range"] {
            flex: 1;
            height: 8px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            outline: none;
            -webkit-appearance: none;
        }
        
        .input-with-slider input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #ffd700;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
        }
        
        .input-with-slider input[type="number"] {
            width: 60px;
            padding: 8px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            color: rgba(255, 255, 255, 0.9);
            text-align: center;
        }
        
        .input-with-slider span {
            color: rgba(255, 255, 255, 0.9);
            font-weight: bold;
        }
        
        input[type="color"] {
            width: 100%;
            height: 50px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* 操作按钮 */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .action-buttons .btn {
            flex: 1;
            max-width: 200px;
        }
        
        /* 创造内容 */
        .creation-container {
            text-align: center;
        }
        
        .creation-tabs {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .creation-tab {
            padding: 12px 35px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            color: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }
        
        .creation-tab.active {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
            color: #0a0a23;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
        }
        
        .creation-content {
            display: none;
            animation: fadeIn 0.5s ease-out;
        }
        
        .creation-content.active {
            display: block;
        }
        
        /* 绘画模式 */
        .canvas-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .canvas-wrapper {
            position: relative;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 3px solid rgba(255, 215, 0, 0.2);
            background: rgba(0, 0, 0, 0.3);
        }
        
        #drawing-canvas {
            width: 100%;
            height: 100%;
            cursor: crosshair;
        }
        
        .drawing-controls {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            width: 100%;
            max-width: 400px;
        }
        
        .drawing-controls label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: bold;
        }
        
        .drawing-controls input[type="range"] {
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            outline: none;
            -webkit-appearance: none;
        }
        
        .drawing-controls input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #ffd700;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
        }
        
        .btn.small {
            padding: 8px 20px;
            font-size: 0.9rem;
        }
        
        /* 写字模式 */
        /* .writing-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
        } */
        
        #message-input {
            width: 100%;
            max-width: 600px;
            height: 200px;
            padding: 20px;
            border-radius: 15px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            font-size: 1.1rem;
            resize: none;
            font-family: inherit;
            background: rgba(255, 255, 255, 0.1);
            color: #f8f9fa;
            backdrop-filter: blur(5px);
        }
        
        #message-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* .writing-preview {
            width: 100%;
            max-width: 600px;
            min-height: 150px;
            padding: 20px;
            border-radius: 15px;
            background: rgba(255, 215, 0, 0.05);
            border: 1px solid rgba(255, 215, 0, 0.2);
            color: rgba(255, 255, 255, 0.9);
            text-align: left;
            line-height: 1.6;
        } */
        
        /* 导航按钮 */
        .creation-navigation,
        .preview-navigation {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }
        
        /* 预览提交 */
        .preview-container {
            text-align: center;
        }
        
        .preview-container h4 {
            font-size: 1.4rem;
            margin-bottom: 30px;
            color: #f8f9fa;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        .final-preview {
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.3);
            border: 3px solid rgba(255, 215, 0, 0.2);
            margin: 0 auto 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .preview-description {
            font-size: 1.1rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* 响应式设计 */
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
            
            .letter-container {
                padding: 40px 25px;
                min-height: 400px;
            }
            
            .evaluation-controls {
                flex-direction: column;
                align-items: center;
            }
            
            .we-together {
                padding: 25px;
            }
            
            .color-wheel {
                width: 250px;
                height: 250px;
            }
            
            .canvas-wrapper {
                width: 300px;
                height: 300px;
            }
            
            #drawing-canvas {
                width: 300px;
                height: 300px;
            }
            
            .final-preview {
                width: 250px;
                height: 250px;
            }
            
            .creation-navigation,
            .preview-navigation {
                flex-direction: column;
                align-items: center;
            }
            
            .creation-tabs {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
    
    <script>
        // =======================
        // 全局变量和初始化
        // =======================
        
        <?php
        // 获取当前用户ID
        $current_user_id = $_SESSION['user_id'] ?? 0;
        
        // 引入数据库配置
        require_once __DIR__ . '/app/config/database.php';
        
        // 调试信息输出
        // error_log("Current user ID: " . $current_user_id);
        
        // 从数据库获取评价数据
        $evaluationData = [];
        if ($current_user_id > 0) {
            try {
                $db = getDB();
                error_log("Attempting to fetch evaluations for user ID: " . $current_user_id);
                
                $stmt = $db->prepare('SELECT content, author FROM evaluation WHERE target_user_id = ? ORDER BY created_at DESC');
                $stmt->execute([$current_user_id]);
                
                $evaluationData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("Found " . count($evaluationData) . " evaluations for user ID: " . $current_user_id);
                
                // 如果没有找到评价数据，输出调试信息
                if (empty($evaluationData)) {
                    error_log("No evaluations found for user ID: " . $current_user_id . ". Checking if evaluation table exists...");
                    // TODO: 确定一下是不是空
                }
            } catch (PDOException $e) {
                error_log("Database error occurred: " . $e->getMessage());
                // 如果出错，返回空数组
                $evaluationData = [];
            }
        } else {
            error_log("No user logged in (user_id is 0)");
        }
        ?>
        
        // 从数据库获取的他评数据
        const evaluationData = <?php echo json_encode($evaluationData); ?>;
        const offscreen = document.createElement('canvas');
        
        // 输出调试信息到浏览器控制台
        console.log("Current user ID:", <?php echo json_encode($current_user_id); ?>);
        console.log("Evaluation data:", evaluationData);
        console.log("Number of evaluations:", evaluationData.length);
        
        // 他评功能变量
        let currentEvaluationIndex = 0;
        let typingInterval = null;
        
        // 我们同在功能变量
        let selectedColor = '#ffd700';
        let useGradient = true; // 默认使用渐变色
        let gradientStops = [
            { color: '#ff0000', position: 0, opacity: 100, brightness: 50 },
            { color: '#ffff00', position: 50, opacity: 100, brightness: 50 },
            { color: '#0000ff', position: 100, opacity: 100, brightness: 50 }
        ];
        let currentStopIndex = 1; // 当前选中的色标
        let currentStep = 'color-selection';
        let creationType = 'draw';
        let drawingCanvas = null;
        let ctx = null;
        let isDrawing = false;
        let brushSize = 5;
        
        // =======================
        // 标签页和步骤导航
        // =======================
        
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
                
                // 如果切换到他评标签，开始显示评价
                if (tabId === 'peer-evaluation') {
                    showEvaluation(currentEvaluationIndex);
                }
            });
        });
        
        // 步骤导航初始化
        function initStepNavigation() {
            // 下一步到创造内容
            document.getElementById('next-to-creation').addEventListener('click', function() {
                goToStep('creation');
            });
            
            // 上一步到颜色选择
            document.getElementById('back-to-color').addEventListener('click', function() {
                goToStep('color-selection');
            });
            
            // 下一步到预览
            document.getElementById('next-to-preview').addEventListener('click', function() {
                generatePreview();
                goToStep('preview');
            });
            
            // 上一步到创造内容
            document.getElementById('back-to-creation').addEventListener('click', function() {
                goToStep('creation');
            });
            
            // 完成创作
            document.getElementById('complete-creation').addEventListener('click', function() {
                submitCreation();
            });
            
            // 创造类型切换
            document.querySelectorAll('.creation-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    const type = this.getAttribute('data-creation-type');
                    switchCreationType(type);
                });
            });
        }
        
        // 切换步骤
        function goToStep(step) {            
            // 更新步骤内容
            document.querySelectorAll('.step-content').forEach(content => content.classList.remove('active'));
            document.getElementById(step).classList.add('active');
            
            currentStep = step;
            
            // 初始化对应步骤的功能
            if (step === 'creation') {
                initDrawing();
                // initWriting();
            }
        }
        
        // 切换创造类型
        function switchCreationType(type) {
            // 更新标签
            document.querySelectorAll('.creation-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelector(`.creation-tab[data-creation-type="${type}"]`).classList.add('active');
            
            // 更新内容
            document.querySelectorAll('.creation-content').forEach(content => content.classList.remove('active'));
            document.getElementById(`${type}-content`).classList.add('active');
            
            creationType = type;
        }
        
        // =======================
        // 他评功能
        // =======================
        
        // 逐字显示评价内容
        function typeWriter(text, element, speed = 50) {
            let i = 0;
            element.innerHTML = '';
            
            if (typingInterval) {
                clearInterval(typingInterval);
            }
            
            typingInterval = setInterval(function() {
                if (i < text.length) {
                    element.innerHTML += text.charAt(i);
                    i++;
                } else {
                    clearInterval(typingInterval);
                    typingInterval = null;
                }
            }, speed);
        }
        
        // 显示评价
        function showEvaluation(index) {
            const evaluation = evaluationData[index];
            const contentElement = document.getElementById('letter-content');
            const signatureElement = document.querySelector('.letter-signature');
            
            // 更新签名
            signatureElement.textContent = evaluation.author;
            
            // 逐字显示内容
            typeWriter(evaluation.content, contentElement);
        }
        
        // 下一条评价
        document.getElementById('next-evaluation').addEventListener('click', function() {
            currentEvaluationIndex = (currentEvaluationIndex + 1) % evaluationData.length;
            showEvaluation(currentEvaluationIndex);
        });
        
        // 一键生成截图
        document.getElementById('capture-screenshot').addEventListener('click', function() {
            // 这里使用html2canvas库来实现截图功能
            // 由于是演示，我们使用alert来模拟
            alert('截图功能已触发！实际项目中可以使用html2canvas库来实现。');
            
            // 实际实现代码示例：
            /*
            html2canvas(document.querySelector('.letter-container')).then(canvas => {
                // 创建下载链接
                const link = document.createElement('a');
                link.download = 'evaluation-' + new Date().getTime() + '.png';
                link.href = canvas.toDataURL();
                link.click();
            });
            */
        });
        
        // =======================
        // 渐变处理
        // =======================
        
        // 初始化渐变选择器
        function initGradientPicker() {
            const gradientBar = document.getElementById('gradient-bar');
            const gradientPreview = document.getElementById('gradient-preview');
            
            // 初始化色标
            updateGradientBar();
            updateGradientPreview();
            updateColorStopControls();
            
            // 渐变条点击事件 - 添加新色标
            gradientBar.addEventListener('click', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const position = Math.round((x / rect.width) * 100);
                
                // 添加新色标
                addColorStop(position);
            });
            
            // 添加色标按钮
            document.getElementById('add-color-stop').addEventListener('click', function() {
                // 在中间位置添加新色标
                addColorStop(50);
            });
            
            // 删除色标按钮
            document.getElementById('remove-color-stop').addEventListener('click', function() {
                if (gradientStops.length > 2) { // 至少保留2个色标
                    gradientStops.splice(currentStopIndex, 1);
                    currentStopIndex = Math.min(currentStopIndex, gradientStops.length - 1);
                    updateGradientBar();
                    updateGradientPreview();
                    updateColorStopControls();
                }
            });
            
            // 颜色选择器事件
            document.getElementById('color-picker').addEventListener('input', function() {
                if (currentStopIndex >= 0 && currentStopIndex < gradientStops.length) {
                    gradientStops[currentStopIndex].color = this.value;
                    updateGradientBar();
                    updateGradientPreview();
                }
            });
            
            // 位置滑块事件
            document.getElementById('position-slider').addEventListener('input', function() {
                const position = parseInt(this.value);
                document.getElementById('position-input').value = position;
                if (currentStopIndex >= 0 && currentStopIndex < gradientStops.length) {
                    gradientStops[currentStopIndex].position = position;
                    // 重新排序色标
                    gradientStops.sort((a, b) => a.position - b.position);
                    // 更新当前色标索引
                    currentStopIndex = gradientStops.findIndex(stop => stop.position === position);
                    updateGradientBar();
                    updateGradientPreview();
                }
            });
            
            // 位置输入框事件
            document.getElementById('position-input').addEventListener('input', function() {
                let position = parseInt(this.value);
                position = Math.max(0, Math.min(100, position));
                this.value = position;
                document.getElementById('position-slider').value = position;
                if (currentStopIndex >= 0 && currentStopIndex < gradientStops.length) {
                    gradientStops[currentStopIndex].position = position;
                    // 重新排序色标
                    gradientStops.sort((a, b) => a.position - b.position);
                    // 更新当前色标索引
                    currentStopIndex = gradientStops.findIndex(stop => stop.position === position);
                    updateGradientBar();
                    updateGradientPreview();
                }
            });
            
            // 透明度滑块事件
            document.getElementById('opacity-slider').addEventListener('input', function() {
                const opacity = parseInt(this.value);
                document.getElementById('opacity-input').value = opacity;
                if (currentStopIndex >= 0 && currentStopIndex < gradientStops.length) {
                    gradientStops[currentStopIndex].opacity = opacity;
                    updateGradientBar();
                    updateGradientPreview();
                }
            });
            
            // 透明度输入框事件
            document.getElementById('opacity-input').addEventListener('input', function() {
                let opacity = parseInt(this.value);
                opacity = Math.max(0, Math.min(100, opacity));
                this.value = opacity;
                document.getElementById('opacity-slider').value = opacity;
                if (currentStopIndex >= 0 && currentStopIndex < gradientStops.length) {
                    gradientStops[currentStopIndex].opacity = opacity;
                    updateGradientBar();
                    updateGradientPreview();
                }
            });
            
            // 亮度滑块事件
            document.getElementById('brightness-slider').addEventListener('input', function() {
                const brightness = parseInt(this.value);
                document.getElementById('brightness-input').value = brightness;
                if (currentStopIndex >= 0 && currentStopIndex < gradientStops.length) {
                    gradientStops[currentStopIndex].brightness = brightness;
                    updateGradientBar();
                    updateGradientPreview();
                }
            });
            
            // 亮度输入框事件
            document.getElementById('brightness-input').addEventListener('input', function() {
                let brightness = parseInt(this.value);
                brightness = Math.max(0, Math.min(100, brightness));
                this.value = brightness;
                document.getElementById('brightness-slider').value = brightness;
                if (currentStopIndex >= 0 && currentStopIndex < gradientStops.length) {
                    gradientStops[currentStopIndex].brightness = brightness;
                    updateGradientBar();
                    updateGradientPreview();
                }
            });
        }
        
        // 添加色标
        function addColorStop(position) {
            // 查找相邻的色标，获取颜色
            let leftStop = gradientStops[0];
            let rightStop = gradientStops[gradientStops.length - 1];
            
            for (let i = 0; i < gradientStops.length - 1; i++) {
                if (position >= gradientStops[i].position && position <= gradientStops[i + 1].position) {
                    leftStop = gradientStops[i];
                    rightStop = gradientStops[i + 1];
                    break;
                }
            }
            
            // 插值计算新色标的颜色
            const color = interpolateColor(leftStop.color, rightStop.color, (position - leftStop.position) / (rightStop.position - leftStop.position));
            
            // 添加新色标
            gradientStops.push({ color, position, opacity: 100, brightness: 50 });
            // 重新排序
            gradientStops.sort((a, b) => a.position - b.position);
            // 更新当前色标索引
            currentStopIndex = gradientStops.findIndex(stop => stop.position === position);
            
            // 更新界面
            updateGradientBar();
            updateGradientPreview();
            updateColorStopControls();
        }
        
        // 更新渐变条
        function updateGradientBar() {
            const gradientBar = document.getElementById('gradient-bar');
            const gradientBarContainer = gradientBar.parentElement;
            
            // 清除现有色标
            const existingStops = gradientBarContainer.querySelectorAll('.color-stop');
            existingStops.forEach(stop => stop.remove());
            
            // 创建渐变
            const gradient = gradientStops.map(stop => {
                const hsl = hexToHsl(stop.color);
                const adjustedColor = hslToHex(hsl.h, hsl.s, stop.brightness);
                return `${adjustedColor}${Math.round(stop.opacity / 100 * 255).toString(16).padStart(2, '0')} ${stop.position}%`;
            }).join(', ');
            
            gradientBar.style.background = `linear-gradient(to right, ${gradient})`;
            
            // 创建色标
            gradientStops.forEach((stop, index) => {
                const colorStop = document.createElement('div');
                colorStop.className = `color-stop ${index === currentStopIndex ? 'active' : ''}`;
                colorStop.style.left = `${stop.position}%`;
                colorStop.style.background = stop.color;
                
                // 点击事件
                colorStop.addEventListener('click', function(e) {
                    e.stopPropagation();
                    currentStopIndex = index;
                    updateColorStopControls();
                    // 更新所有色标的活跃状态
                    const allStops = gradientBarContainer.querySelectorAll('.color-stop');
                    allStops.forEach((s, i) => {
                        s.classList.toggle('active', i === currentStopIndex);
                    });
                });
                
                // 拖动事件
                let isDragging = false;
                
                // 鼠标事件
                colorStop.addEventListener('mousedown', function(e) {
                    e.stopPropagation();
                    startDragging(index);
                });
                
                // 触摸事件（移动端支持）
                colorStop.addEventListener('touchstart', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    startDragging(index);
                });
                
                function startDragging(index) {
                    isDragging = true;
                    currentStopIndex = index;
                    updateColorStopControls();
                    // 更新所有色标的活跃状态
                    const allStops = gradientBarContainer.querySelectorAll('.color-stop');
                    allStops.forEach((s, i) => {
                        s.classList.toggle('active', i === currentStopIndex);
                    });
                }
                
                // 鼠标移动事件
                document.addEventListener('mousemove', function(e) {
                    if (isDragging) {
                        handleDragMove(e.clientX, e.clientY);
                    }
                });
                
                // 触摸移动事件（移动端支持）
                document.addEventListener('touchmove', function(e) {
                    if (isDragging) {
                        e.preventDefault();
                        const touch = e.touches[0];
                        handleDragMove(touch.clientX, touch.clientY);
                    }
                }, { passive: false });
                
                function handleDragMove(clientX, clientY) {
                    const rect = gradientBar.getBoundingClientRect();
                    const x = Math.max(0, Math.min(clientX - rect.left, rect.width));
                    const position = Math.round((x / rect.width) * 100);
                    
                    // 更新色标位置
                    gradientStops[currentStopIndex].position = position;
                    // 重新排序
                    gradientStops.sort((a, b) => a.position - b.position);
                    // 更新当前色标索引
                    currentStopIndex = gradientStops.findIndex(s => s.position === position);
                    // 更新界面
                    updateGradientBar();
                    updateGradientPreview();
                    updateColorStopControls();
                }
                
                // 鼠标释放事件
                document.addEventListener('mouseup', function() {
                    isDragging = false;
                });
                
                // 触摸结束事件（移动端支持）
                document.addEventListener('touchend', function() {
                    isDragging = false;
                });
                
                gradientBarContainer.appendChild(colorStop);
            });
        }
        
        // 更新渐变预览
        function updateGradientPreview() {
            const gradientPreview = document.getElementById('gradient-preview');
            
            // 创建线性渐变（从左上到右下）
            gradientPreview.style.background = generateGradientCSS(gradientStops, '135deg');
            
            // 更新selectedColor为第一个色标的颜色（用于绘画）
            if (gradientStops.length > 0) {
                selectedColor = gradientStops[0].color;
            }
        }
        
        // 更新色标属性控制
        function updateColorStopControls() {
            if (currentStopIndex >= 0 && currentStopIndex < gradientStops.length) {
                const stop = gradientStops[currentStopIndex];
                document.getElementById('color-picker').value = stop.color;
                document.getElementById('position-slider').value = stop.position;
                document.getElementById('position-input').value = stop.position;
                document.getElementById('opacity-slider').value = stop.opacity;
                document.getElementById('opacity-input').value = stop.opacity;
                document.getElementById('brightness-slider').value = stop.brightness;
                document.getElementById('brightness-input').value = stop.brightness;
            }
        }
        
        // =======================
        // 绘画功能
        // =======================
        
        // 初始化绘画功能
        function initDrawing() {
            drawingCanvas = document.getElementById('drawing-canvas');
            ctx = drawingCanvas.getContext('2d');
            
            // 设置默认大小，防止获取不到元素尺寸
            drawingCanvas.width = drawingCanvas.width || 400;
            drawingCanvas.height = drawingCanvas.height || 400;
            
            // 尝试调整画布大小
            try {
                resizeCanvas();
            } catch (e) {
                console.error('调整画布大小失败:', e);
            }
            
            // 设置canvas背景为线性渐变
            drawingCanvas.style.background = generateGradientCSS(gradientStops, '135deg');
            
            // 清空画布并覆盖一层白色（稍后我们会用destination-out模式擦除）
            clearCanvas();
            // 绘制一层不透明的白色覆盖整个画布
            // ctx.fillStyle = '#ffffff';
            // ctx.fillRect(0, 0, drawingCanvas.width, drawingCanvas.height);
            
            // 画笔大小
            const brushSizeSlider = document.getElementById('brush-size');
            brushSizeSlider.addEventListener('input', function() {
                brushSize = parseInt(this.value);
            });
            
            // 清空画布按钮
            document.getElementById('clear-canvas').addEventListener('click', clearCanvas);
            
            // 绘画事件
            drawingCanvas.addEventListener('mousedown', startDrawing);
            drawingCanvas.addEventListener('mousemove', draw);
            drawingCanvas.addEventListener('mouseup', stopDrawing);
            drawingCanvas.addEventListener('mouseout', stopDrawing);
            
            // 触摸事件（移动端）
            drawingCanvas.addEventListener('touchstart', function(e) {
                e.preventDefault();
                const touch = e.touches[0];
                const mouseEvent = new MouseEvent('mousedown', {
                    clientX: touch.clientX,
                    clientY: touch.clientY
                });
                drawingCanvas.dispatchEvent(mouseEvent);
            });
            
            drawingCanvas.addEventListener('touchmove', function(e) {
                e.preventDefault();
                const touch = e.touches[0];
                const mouseEvent = new MouseEvent('mousemove', {
                    clientX: touch.clientX,
                    clientY: touch.clientY
                });
                drawingCanvas.dispatchEvent(mouseEvent);
            });
            
            drawingCanvas.addEventListener('touchend', function(e) {
                e.preventDefault();
                const mouseEvent = new MouseEvent('mouseup', {});
                drawingCanvas.dispatchEvent(mouseEvent);
            });
        }
        
        // 调整画布大小
        function resizeCanvas() {
            const canvasWrapper = drawingCanvas.parentElement;
            // 确保获取到有效的尺寸
            const width = canvasWrapper.offsetWidth || 400; // 默认宽度
            const height = canvasWrapper.offsetHeight || 400; // 默认高度
            drawingCanvas.width = width;
            drawingCanvas.height = height;
        }
        
        // 开始绘画
        function startDrawing(e) {
            isDrawing = true;
            const rect = drawingCanvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            ctx.beginPath();
            ctx.moveTo(x, y);
            
            // 确保使用正确的透明度和合成模式
            ctx.globalAlpha = 1;
            ctx.globalCompositeOperation = 'destination-out';
        }
        
        // 绘画
        function draw(e) {
            if (!isDrawing) return;
            const rect = drawingCanvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            ctx.lineWidth = brushSize;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            
            // 使用destination-out模式，这样绘制的地方会擦除白色，显示背景的渐变
            ctx.globalCompositeOperation = 'destination-out';
            
            // 使用不透明的颜色（颜色不重要，因为我们只是在擦除）
            ctx.strokeStyle = '#000000';
            
            // 设置不透明度为1，确保完全擦除
            ctx.globalAlpha = 1;
            
            ctx.lineTo(x, y);
            ctx.stroke();
        }
        
        // 停止绘画
        function stopDrawing() {
            isDrawing = false;
            ctx.closePath();
        }
        
        // 清空画布
        function clearCanvas() {
            // 保存当前状态
            ctx.save();
            // 重置复合操作，确保能正确绘制白色背景
            ctx.globalCompositeOperation = 'source-over';
            ctx.globalAlpha = 1;
            // 填充白色背景，确保白色蒙版正确重置
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, drawingCanvas.width, drawingCanvas.height);
            // 恢复原始状态
            ctx.restore();
        }
        
        // =======================
        // 写字功能
        // =======================
        
        // 初始化写字功能
        // function initWriting() {
        //     const messageInput = document.getElementById('message-input');
        //     const writingPreview = document.getElementById('writing-preview');
            
        //     messageInput.addEventListener('input', function() {
        //         writingPreview.textContent = this.value || '在这里写下你的话...';
        //     });
        // }
        
        // =======================
        // 颜色处理工具
        // =======================
        
        // 插值计算颜色
        function interpolateColor(color1, color2, factor) {
            const parseColor = (color) => {
                const hex = color.replace('#', '');
                return {
                    r: parseInt(hex.substring(0, 2), 16),
                    g: parseInt(hex.substring(2, 4), 16),
                    b: parseInt(hex.substring(4, 6), 16)
                };
            };
            
            const formatColor = (color) => {
                return '#' + [color.r, color.g, color.b].map(c => {
                    const hex = c.toString(16);
                    return hex.length === 1 ? '0' + hex : hex;
                }).join('');
            };
            
            const c1 = parseColor(color1);
            const c2 = parseColor(color2);
            
            return formatColor({
                r: Math.round(c1.r + (c2.r - c1.r) * factor),
                g: Math.round(c1.g + (c2.g - c1.g) * factor),
                b: Math.round(c1.b + (c2.b - c1.b) * factor)
            });
        }
        
        // HSL转HEX
        function hslToHex(h, s, l) {
            h /= 360;
            s /= 100;
            l /= 100;
            let r, g, b;
            if (s === 0) {
                r = g = b = l;
            } else {
                const hue2rgb = (p, q, t) => {
                    if (t < 0) t += 1;
                    if (t > 1) t -= 1;
                    if (t < 1/6) return p + (q - p) * 6 * t;
                    if (t < 1/2) return q;
                    if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                    return p;
                };
                const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                const p = 2 * l - q;
                r = hue2rgb(p, q, h + 1/3);
                g = hue2rgb(p, q, h);
                b = hue2rgb(p, q, h - 1/3);
            }
            const toHex = x => {
                const hex = Math.round(x * 255).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            };
            return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
        }
        
        // HEX转HSL
        function hexToHsl(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            let r = parseInt(result[1], 16) / 255;
            let g = parseInt(result[2], 16) / 255;
            let b = parseInt(result[3], 16) / 255;
            const max = Math.max(r, g, b);
            const min = Math.min(r, g, b);
            let h, s, l = (max + min) / 2;
            if (max === min) {
                h = s = 0;
            } else {
                const d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                switch (max) {
                    case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                    case g: h = (b - r) / d + 2; break;
                    case b: h = (r - g) / d + 4; break;
                }
                h /= 6;
            }
            return {
                h: h * 360,
                s: s * 100,
                l: l * 100
            };
        }
        
        // 生成渐变CSS字符串
        function generateGradientCSS(stops, direction = '135deg') {
            const gradient = stops.map(stop => {
                const hsl = hexToHsl(stop.color);
                const adjustedColor = hslToHex(hsl.h, hsl.s, stop.brightness);
                return `${adjustedColor} ${stop.position}%`;
            }).join(', ');
            return `linear-gradient(${direction}, ${gradient})`;
        }
        
        // 生成Canvas渐变对象
        function generateCanvasGradient(ctx, width, height, stops, direction = '135deg') {
            let gradient;
            if (direction === '135deg') {
                gradient = ctx.createLinearGradient(0, 0, width, height);
            } else if (direction === '90deg') {
                gradient = ctx.createLinearGradient(0, 0, width, 0);
            } else {
                gradient = ctx.createLinearGradient(0, 0, width, height);
            }
            
            stops.forEach(stop => {
                const hsl = hexToHsl(stop.color);
                const adjustedColor = hslToHex(hsl.h, hsl.s, stop.brightness);
                gradient.addColorStop(stop.position / 100, adjustedColor);
            });
            return gradient;
        }
        
        // =======================
        // 预览和提交
        // =======================
        
        // 生成预览
        function generatePreview() {
            const finalPreview = document.getElementById('final-preview');
            finalPreview.innerHTML = '';
            
            if (creationType === 'draw') {
                // 直接获取页面上的canvas元素
                const originalCanvas = document.getElementById('drawing-canvas');

                // 创建离屏 canvas 用于合并图层
                offscreen.width  = originalCanvas.width;
                offscreen.height = originalCanvas.height;
                const offCtx = offscreen.getContext('2d');

                // 1. 绘制背景渐变
                // 从 gradientStops 重新创建渐变，因为 Canvas API 不支持直接使用 CSS 渐变字符串
                const gradient = generateCanvasGradient(offCtx, offscreen.width, offscreen.height, gradientStops, '135deg');
                offCtx.fillStyle = gradient;
                offCtx.fillRect(0, 0, offscreen.width, offscreen.height);

                // 2. 将白色蒙版（originalCanvas）绘制到上方
                offCtx.globalCompositeOperation = 'source-over';
                offCtx.drawImage(originalCanvas, 0, 0);

                // 3. 获取 ImageData，逐像素把纯白(#ffffff)设为透明
                const imageData = offCtx.getImageData(0, 0, offscreen.width, offscreen.height);
                const data = imageData.data;
                for (let i = 0; i < data.length; i += 4) {
                    const r = data[i];
                    const g = data[i + 1];
                    const b = data[i + 2];
                    if (r === 255 && g === 255 && b === 255) {
                        data[i + 3] = 0; // 设置 alpha 为 0
                    }
                }
                offCtx.putImageData(imageData, 0, 0);

                // 4. 导出为 PNG 并创建 <img> 添加到预览
                const img = new Image();
                img.src = offscreen.toDataURL('image/png');
                img.style.maxWidth = '100%';
                img.style.borderRadius = '8px';
                finalPreview.appendChild(img);
                } 
            //     else {
            //     // 显示文字内容
            //     const textPreview = document.createElement('div');
            //     textPreview.className = 'text-preview';
                
            //     if (useGradient) {
            //         // 渐变文字效果（从左上到右下）
            //         textPreview.style.background = generateGradientCSS(gradientStops, '135deg');
            //         textPreview.style.webkitBackgroundClip = 'text';
            //         textPreview.style.webkitTextFillColor = 'transparent';
            //         textPreview.style.backgroundClip = 'text';
            //     } else {
            //         // 纯色文字
            //         textPreview.style.color = selectedColor;
            //     }
                
            //     textPreview.style.fontSize = '1rem';
            //     textPreview.style.lineHeight = '1.6';
            //     textPreview.style.padding = '20px';
            //     textPreview.style.textAlign = 'center';
            //     textPreview.textContent = document.getElementById('message-input').value || '在这里写下你的话...';
            //     finalPreview.appendChild(textPreview);
            // }
        }
        
        // 提交创作
        function submitCreation() {
            // 模拟提交到数据库
            let creationData = {
                color: selectedColor,
                useGradient: useGradient,
                gradientStops: gradientStops,
                type: creationType
            };
            
            if (creationType === 'draw') {
                // 获取画布数据
                creationData.image = drawingCanvas.toDataURL('image/png');
            } 
            // else {
            //     // 获取文字内容
            //     creationData.text = document.getElementById('message-input').value;
            // }
            
            // 如果是绘图类型，则保存图片到数据库
            if (creationType === 'draw') {
                saveImageToDatabase(offscreen.toDataURL('image/png'));
            } else {
                // 显示提交成功
                alert('创作已提交！');
                
                // 重置到第一步
                resetCreationProcess();
            }
        }
        
        // 将图片保存到数据库
        function saveImageToDatabase(imgData) {
            const formData = new FormData();
            formData.append('action', 'save_user_image');
            formData.append('img_data', imgData);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const operationText = data.operation === 'updated' ? '更新' : '保存';
                    alert(`创作已提交！图片已${operationText}到数据库。`);
                } else {
                    console.error('保存失败:', data.message);
                    alert('创作提交失败: ' + data.message);
                }
                
                // 重置到第一步
                resetCreationProcess();
            })
            .catch(error => {
                console.error('保存出错:', error);
                alert('创作提交失败，请查看控制台了解详情。');
                
                // 重置到第一步
                resetCreationProcess();
            });
        }
        
        // 重置创作过程
        function resetCreationProcess() {
            setTimeout(() => {
                // 重置选项
                gradientStops = [
                    { color: '#ff0000', position: 0, opacity: 100, brightness: 50 },
                    { color: '#ffff00', position: 50, opacity: 100, brightness: 50 },
                    { color: '#0000ff', position: 100, opacity: 100, brightness: 50 }
                ];
                currentStopIndex = 1;
                
                // 重置界面
                updateGradientBar();
                updateGradientPreview();
                updateColorStopControls();
                
                // 重置到颜色选择步骤
                goToStep('color-selection');
            }, 1000);
        }
        
        // 初始化我们同在功能
        function initWeTogether() {
            initGradientPicker();
            initStepNavigation();
        }
        
        // 初始化显示第一条评价
        window.addEventListener('load', function() {
            showEvaluation(currentEvaluationIndex);
            initWeTogether();
        });
    </script>