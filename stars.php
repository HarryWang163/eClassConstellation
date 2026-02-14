<?php
// 引入认证工具
require_once __DIR__ . '/app/includes/auth.php';

// 检查是否已登录
if (!isLoggedIn()) {
    redirectToLogin();
}

// 启动会话
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 引入数据库连接
require_once __DIR__ . '/app/config/database.php';

// 获取当前登录用户信息
$current_user_id = $_SESSION['user_id'] ?? 0;
$current_username = $_SESSION['username'] ?? '';

// ==================== 数据库操作函数 ====================

/**
 * 获取所有可被评价的用户（排除当前用户、不可标记用户）
 * 排序：教师优先，学生按学号升序
 */
function getAvailableUsers($current_user_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT id, username, ifteacher, number 
        FROM users 
        WHERE id != ? AND (if_not_tagable IS NULL OR if_not_tagable = 0)
        ORDER BY ifteacher DESC, number ASC, id ASC
    ");
    $stmt->execute([$current_user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 获取当前用户已评价过的用户ID列表
 */
function getRatedUserIds($current_user_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT DISTINCT selected_user_id 
        FROM user_be_selected 
        WHERE selecter_id = ?
    ");
    $stmt->execute([$current_user_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * 获取所有可用标签（按分类分组，每组内按id升序）
 * @param int $target_user_id 当前被评价用户ID，用于筛选专属标签
 */
/**
 * 获取所有可用标签（按分类分组，每组内随机排序）
 * @param int $target_user_id 当前被评价用户ID，用于筛选专属标签
 */
function getAllTagsGrouped($target_user_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT id, tagname, description, tagclass, if_special, if_for_one_user, one_user_id
        FROM tags 
        WHERE tagclass IS NOT NULL AND tagclass != ''
          AND (if_for_one_user = 0 OR (if_for_one_user = 1 AND one_user_id = ?))
        ORDER BY tagclass  -- 只需按分类排序，组内顺序由 shuffle 决定
    ");
    $stmt->execute([$target_user_id]);
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $grouped = [];
    foreach ($tags as $tag) {
        $grouped[$tag['tagclass']][] = $tag;
    }
    
    // 对每个分类内的标签数组进行随机打乱
    foreach ($grouped as &$tagList) {
        shuffle($tagList);  // 随机重排
    }
    
    return $grouped;
}

/**
 * 获取当前用户对被评价用户已使用的标签ID列表
 */
function getUsedTagIds($current_user_id, $target_user_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT tag_id 
        FROM user_be_selected 
        WHERE selecter_id = ? AND selected_user_id = ?
    ");
    $stmt->execute([$current_user_id, $target_user_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * 处理AJAX添加标签请求
 */
function handleAddTag($current_user_id, $selected_user_id, $tag_id) {
    $db = getDB();
    
    // 检查是否已存在
    $stmt = $db->prepare("
        SELECT id FROM user_be_selected 
        WHERE selecter_id = ? AND selected_user_id = ? AND tag_id = ?
    ");
    $stmt->execute([$current_user_id, $selected_user_id, $tag_id]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => '你已经添加过这个标签了'];
    }
    
    // 插入新评价
    $stmt = $db->prepare("
        INSERT INTO user_be_selected (selected_user_id, tag_id, selecter_id) 
        VALUES (?, ?, ?)
    ");
    $success = $stmt->execute([$selected_user_id, $tag_id, $current_user_id]);
    
    return [
        'success' => $success,
        'message' => $success ? '标签添加成功' : '添加失败，请重试'
    ];
}

/**
 * 处理AJAX取消标签请求
 */
function handleRemoveTag($current_user_id, $selected_user_id, $tag_id) {
    $db = getDB();
    
    $stmt = $db->prepare("
        DELETE FROM user_be_selected 
        WHERE selecter_id = ? AND selected_user_id = ? AND tag_id = ?
    ");
    $success = $stmt->execute([$current_user_id, $selected_user_id, $tag_id]);
    
    return [
        'success' => $success,
        'message' => $success ? '标签已取消' : '取消失败，请重试'
    ];
}

/**
 * 处理AJAX创建新标签请求
 */
function handleCreateTag($current_user_id, $target_user_id, $data) {
    $db = getDB();
    
    // 参数验证
    $tagname = trim($data['tagname'] ?? '');
    $description = trim($data['description'] ?? '');
    $tagclass = trim($data['tagclass'] ?? '');
    $if_for_one_user = isset($data['if_for_one_user']) ? (int)$data['if_for_one_user'] : 0;
    
    if (empty($tagname) || empty($tagclass)) {
        return ['success' => false, 'message' => '标签名和分类不能为空'];
    }
    
    // 插入新标签
    $stmt = $db->prepare("
        INSERT INTO tags 
            (tagname, description, tagclass, if_special, if_for_one_user, one_user_id, adder_id)
        VALUES (?, ?, ?, 1, ?, ?, ?)
    ");
    
    $one_user_id = $if_for_one_user ? $target_user_id : null;
    $success = $stmt->execute([$tagname, $description, $tagclass, $if_for_one_user, $one_user_id, $current_user_id]);
    
    if ($success) {
        $new_tag_id = $db->lastInsertId();
        return [
            'success' => true,
            'message' => '标签创建成功',
            'tag' => [
                'id' => $new_tag_id,
                'tagname' => $tagname,
                'description' => $description,
                'tagclass' => $tagclass,
                'if_for_one_user' => $if_for_one_user,
                'one_user_id' => $one_user_id,
                'if_special' => 1
            ]
        ];
    } else {
        return ['success' => false, 'message' => '标签创建失败，请重试'];
    }
}

/**
 * 获取当前用户已评价的不同用户数量
 */
function getRatedUserCount($current_user_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT selected_user_id) 
        FROM user_be_selected 
        WHERE selecter_id = ?
    ");
    $stmt->execute([$current_user_id]);
    return (int) $stmt->fetchColumn();
}

/**
 * 获取当前用户可评价的总用户数量（排除自己、不可标记）
 */
function getTotalEvaluatableUserCount($current_user_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM users 
        WHERE id != ? AND (if_not_tagable IS NULL OR if_not_tagable = 0)
    ");
    $stmt->execute([$current_user_id]);
    return (int) $stmt->fetchColumn();
}

// ==================== 请求处理 ====================

// 1. AJAX 请求处理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    // 添加/取消标签
    if ($action === 'add_tag' || $action === 'remove_tag') {
        $selected_user_id = intval($_POST['user_id'] ?? 0);
        $tag_id = intval($_POST['tag_id'] ?? 0);
        
        if (!$selected_user_id || !$tag_id) {
            echo json_encode(['success' => false, 'message' => '参数错误']);
            exit;
        }
        
        if ($action === 'add_tag') {
            $result = handleAddTag($current_user_id, $selected_user_id, $tag_id);
        } else {
            $result = handleRemoveTag($current_user_id, $selected_user_id, $tag_id);
        }
        echo json_encode($result);
        exit;
    }
    
    // 创建新标签
    if ($action === 'create_tag') {
        $target_user_id = intval($_POST['target_user_id'] ?? 0);
        if (!$target_user_id) {
            echo json_encode(['success' => false, 'message' => '目标用户参数缺失']);
            exit;
        }
        
        $result = handleCreateTag($current_user_id, $target_user_id, $_POST);
        echo json_encode($result);
        exit;
    }
}

// 2. 页面导航控制
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'rate' && isset($_GET['user_id'])) {
    $step = 5;
    $target_user_id = intval($_GET['user_id']);
}

// 引入公共头部
require_once __DIR__ . '/app/includes/header.php';
?>

<style>
/* ---------- 手写字体定义 ---------- */
@font-face {
    font-family: 'ShouXie';
    src: url('fonts/shouxie.ttf') format('truetype');
    font-display: swap;
}

/* ---------- 步骤卡片：应用手写字体 ---------- */
.step-card h2,
.step-card p {
    font-family: 'ShouXie', 'Microsoft YaHei', '楷体', cursive, sans-serif;
}
/* ---------- 全局动画 ---------- */
body {
    animation: pageFadeIn 0.6s ease-out;
}
@keyframes pageFadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes slideFadeIn {
    0% { opacity: 0; transform: translateY(30px); }
    100% { opacity: 1; transform: translateY(0); }
}

/* ---------- 主容器 ---------- */
.stars-container {
    max-width: 1300px;
    margin: 0 auto;
    padding: 40px 20px;
    color: #fff;
    min-height: 60vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* ---------- 步骤卡片 ---------- */
.step-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 30px;
    padding: 50px 40px;
    max-width: 1300px;
    width: 100%;
    text-align: center;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    animation: slideFadeIn 0.8s;
}
.step-card h2 {
    font-size: 5rem;
    margin-bottom: 30px;
    color: #ffd700;
    text-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
}
.step-card p {
    font-size: 3rem;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 40px;
}
.username-highlight {
    color: #ffd700;
    font-weight: bold;
    font-size: 5rem;
    text-shadow: 0 0 15px rgba(255, 215, 0, 0.8);
}
.btn-next {
    display: inline-block;
    padding: 15px 40px;
    background: linear-gradient(135deg, #ffd700, #ffed4e);
    color: #0a0a23;
    text-decoration: none;
    border-radius: 50px;
    font-weight: bold;
    font-size: 1.1rem;
    transition: 0.3s;
    box-shadow: 0 5px 20px rgba(255, 215, 0, 0.5);
    border: none;
    cursor: pointer;
}
.btn-next:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 30px rgba(255, 215, 0, 0.8);
}

/* ---------- 人物选择页面 ---------- */
.user-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 20px;
    width: 100%;
    margin-top: 30px;
}
.user-btn {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 40px;
    padding: 15px 10px;
    color: white;
    font-size: 1rem;
    text-decoration: none;
    text-align: center;
    transition: 0.3s;
    backdrop-filter: blur(5px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
.user-btn:hover {
    background: rgba(255, 215, 0, 0.2);
    border-color: rgba(255, 215, 0, 0.6);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(255, 215, 0, 0.3);
}
.user-btn.rated {
    background: rgba(255, 215, 0, 0.25);
    border-color: #ffd700;
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
    color: #fff;
    font-weight: bold;
}

/* ---------- 评价页面 ---------- */
.rate-header {
    text-align: center;
    margin-bottom: 40px;
}
.rate-header h1 {
    font-size: 2.2rem;
    color: #ffd700;
    text-shadow: 0 0 20px rgba(255,215,0,0.5);
}
.rate-header p {
    font-size: 1.2rem;
    color: rgba(255,255,255,0.8);
}

/* 标签组 */
.tag-group {
    margin-bottom: 40px;
    width: 100%;
}
.tag-group h3 {
    font-size: 1.5rem;
    color: #ffd700;
    border-bottom: 2px solid rgba(255,215,0,0.3);
    padding-bottom: 10px;
    margin-bottom: 20px;
    text-shadow: 0 0 10px rgba(255,215,0,0.3);
}
.tag-list {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

/* 基础标签样式 */
.tag-item {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 40px;
    padding: 12px 25px;
    color: white;
    font-size: 1rem;
    transition: 0.2s;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    position: relative;
}

/* 专属标签样式（紫色主题） */
.tag-item.special-tag {
    background: rgba(170, 0, 255, 0.15);
    border: 1px solid rgba(200, 0, 255, 0.5);
    box-shadow: 0 0 15px rgba(200, 0, 255, 0.3);
}
.tag-item.special-tag:hover {
    background: rgba(200, 0, 255, 0.25);
    border-color: #cc66ff;
    box-shadow: 0 0 25px rgba(200, 0, 255, 0.6);
}
.tag-item.special-tag .has-desc {
    color: #d9b3ff;
}

/* 用户自建标签样式（非专属，但if_special=1） - 青色点缀 */
.tag-item.user-created {
    background: rgba(0, 200, 255, 0.1);
    border: 1px solid rgba(0, 200, 255, 0.3);
}
.tag-item.user-created:hover {
    background: rgba(0, 200, 255, 0.2);
    border-color: #66ccff;
}

/* 悬停描述（纯CSS） */
.tag-item[data-description]:not([data-description=""])::after {
    content: attr(data-description);
    position: absolute;
    bottom: 110%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.9);
    color: #fff;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    white-space: normal;
    max-width: 250px;
    word-break: break-word;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.5);
    border: 1px solid rgba(255,215,0,0.5);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s, visibility 0.2s, transform 0.2s;
    pointer-events: none;
    z-index: 100;
    line-height: 1.4;
}
.tag-item[data-description]:not([data-description=""]):hover::after {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(-5px);
}

/* 已使用的标签 */
.tag-item.used {
    background: rgba(255, 215, 0, 0.25);
    border-color: #ffd700;
    opacity: 0.9;
    cursor: pointer;
}
.tag-item.used:hover {
    background: rgba(255, 100, 100, 0.25);
    border-color: rgba(255, 100, 100, 0.8);
}
.tag-item.special-tag.used {
    background: rgba(200, 0, 255, 0.35);
    border-color: #ff99ff;
}

.tag-name {
    font-weight: 500;
}
.has-desc {
    color: #ffd700;
    font-size: 1.1rem;
    pointer-events: none;
}

/* ---------- 创建标签按钮 ---------- */
.btn-create-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.1);
    border: 2px dashed rgba(255, 215, 0, 0.6);
    border-radius: 50px;
    padding: 15px 35px;
    color: #ffd700;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
    margin: 20px auto 40px;
    backdrop-filter: blur(5px);
}
.btn-create-tag:hover {
    background: rgba(255, 215, 0, 0.2);
    border-color: #ffd700;
    transform: translateY(-3px);
    box-shadow: 0 0 30px rgba(255, 215, 0, 0.3);
}

/* 创建标签模态框（弹出层） */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: 0.3s;
}
.modal-overlay.active {
    opacity: 1;
    visibility: visible;
}
.modal-content {
    background: rgba(20, 20, 50, 0.95);
    border: 1px solid rgba(255, 215, 0, 0.5);
    border-radius: 30px;
    padding: 40px;
    max-width: 550px;
    width: 90%;
    color: #fff;
    box-shadow: 0 0 60px rgba(255, 215, 0, 0.3);
    animation: slideFadeIn 0.4s;
}
.modal-content h2 {
    color: #ffd700;
    margin-bottom: 30px;
    text-align: center;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    color: rgba(255,255,255,0.9);
    font-weight: 500;
}
.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 20px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 25px;
    color: #fff;
    font-size: 1rem;
    transition: 0.3s;
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #ffd700;
    box-shadow: 0 0 20px rgba(255,215,0,0.3);
    background: rgba(255,255,255,0.15);
}
.form-group textarea {
    resize: vertical;
    min-height: 80px;
}
.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
}
.checkbox-group input {
    width: auto;
    margin-right: 8px;
}
.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 30px;
}
.modal-actions button {
    padding: 12px 30px;
    border-radius: 50px;
    font-size: 1rem;
    cursor: pointer;
    transition: 0.3s;
    border: none;
}
.btn-modal-submit {
    background: linear-gradient(135deg, #ffd700, #ffed4e);
    color: #0a0a23;
    font-weight: bold;
}
.btn-modal-cancel {
    background: rgba(255,255,255,0.1);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.2);
}
.btn-modal-submit:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 20px rgba(255,215,0,0.5);
}
.btn-modal-cancel:hover {
    background: rgba(255,255,255,0.2);
}

/* 消息提示 */
.message-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: rgba(0,0,0,0.9);
    color: #fff;
    padding: 15px 25px;
    border-radius: 50px;
    border-left: 5px solid #ffd700;
    box-shadow: 0 5px 20px rgba(0,0,0,0.5);
    z-index: 99999;
    animation: slideIn 0.3s;
}
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.back-link {
    margin-top: 40px;
    text-align: center;
}
.back-link a {
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    font-size: 1rem;
    transition: 0.3s;
}
.back-link a:hover {
    color: #ffd700;
    text-shadow: 0 0 10px rgba(255,215,0,0.5);
}

/* ---------- 评价进度统计卡片 ---------- */
.rating-stats {
    display: flex;
    justify-content: center;
    margin-top: 30px;
    margin-bottom: 20px;
    width: 100%;
}
.stats-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 215, 0, 0.3);
    border-radius: 60px;
    padding: 15px 30px;
    display: inline-flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
    transition: 0.2s;
}
.stats-card:hover {
    border-color: rgba(255, 215, 0, 0.8);
    box-shadow: 0 0 25px rgba(255, 215, 0, 0.3);
}
.stats-label {
    color: #ffd700;
    font-weight: 500;
    font-size: 1rem;
}
.stats-number {
    font-size: 1.8rem;
    font-weight: bold;
    color: #fff;
    text-shadow: 0 0 15px rgba(255, 215, 0, 0.6);
    line-height: 1;
}
.stats-unit {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.95rem;
}
/* ---------- 移动端适配：缩小前三步字体 ---------- */
@media (max-width: 768px) {
    .step-card {
        padding: 30px 20px; /* 减少内边距，让内容更紧凑 */
    }
    .step-card h2 {
        font-size: 2.8rem;   /* 从 5rem 缩小 */
        margin-bottom: 20px;
    }
    .step-card p {
        font-size: 1.8rem;   /* 从 3rem 缩小 */
        line-height: 1.4;
        margin-bottom: 30px;
    }
    .username-highlight {
        font-size: 2.8rem;   /* 从 5rem 缩小 */
    }
    .btn-next {
        font-size: 1rem;      /* 稍微缩小一点按钮文字 */
        padding: 12px 30px;
    }
}
</style>

<main>
<div class="stars-container">

<?php if ($step === 1): ?>
    <!-- 第一步 -->
    <div class="step-card">
        <h2>✨ 星光互映</h2>
        <p>“同在”，不止是坐在同一间教室</p>
        <p>它是空间的重叠，更是心灵的照映</p>
        <a href="?step=2" class="btn-next">>>></a>
    </div>

<?php elseif ($step === 2): ?>
    <!-- 第二步 -->
    <div class="step-card">
        <h2>🌙 时光之问</h2>
        <p>高中时光过半</p>
        <p>“我”在同学眼中是什么样子的呢？</p>
        <a href="?step=3" class="btn-next">>>></a>
    </div>

<?php elseif ($step === 3): ?>
    <!-- 第三步 -->
    <div class="step-card">
        <h1><span class="username-highlight">@<?php echo htmlspecialchars($current_username); ?></span></h1>
        <p>邀请你，</p>
        <p>参与一场温柔而奇妙的 “星光互映” 计划</p>
        <a href="?step=4" class="btn-next">>>>让我们开始<<<</a>
    </div>

<?php elseif ($step === 4): ?>
    <!-- 人物选择页 -->
    <div style="width:100%; text-align:center; animation: slideFadeIn 0.6s;">
        <h1 style="color:#ffd700; font-size:2.5rem; text-shadow:0 0 20px rgba(255,215,0,0.5); margin-bottom:20px;">🌟 选择一位老师或同学</h1>
        <p style="color:rgba(255,255,255,0.8); font-size:1.2rem; margin-bottom:40px;">已点亮的按钮表示你已评价过TA</p>
        
        <div class="user-grid">
            <?php
            $users = getAvailableUsers($current_user_id);
            $rated_user_ids = getRatedUserIds($current_user_id);
            foreach ($users as $user):
                $display_name = htmlspecialchars($user['username']);
                if ($user['ifteacher'] == 1) $display_name .= ' 老师';
                $is_rated = in_array($user['id'], $rated_user_ids);
                $btn_class = $is_rated ? 'user-btn rated' : 'user-btn';
            ?>
                <a href="?action=rate&user_id=<?php echo $user['id']; ?>" class="<?php echo $btn_class; ?>">
                    <?php echo $display_name; ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="back-link">
            <a href="?step=3">← 返回邀请</a>
        </div>
    </div>

<?php elseif ($step === 5 && isset($target_user_id)): ?>
    <!-- 评价页面（含创建标签功能） -->
    <?php
    $db = getDB();
    $stmt = $db->prepare("SELECT username, ifteacher FROM users WHERE id = ?");
    $stmt->execute([$target_user_id]);
    $target_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$target_user) {
        echo '<div class="step-card"><p>用户不存在</p><a href="?step=4" class="btn-next">返回</a></div>';
    } else {
        $target_name = htmlspecialchars($target_user['username']);
        if ($target_user['ifteacher'] == 1) $target_name .= ' 老师';
        
        // 获取分组标签（自动过滤专属）
        $grouped_tags = getAllTagsGrouped($target_user_id);
        $used_tag_ids = getUsedTagIds($current_user_id, $target_user_id);
    ?>
    
    <div style="width:100%; animation: slideFadeIn 0.6s;">
        <div class="rate-header">
            <h1>✨ 为 <?php echo $target_name; ?> 添加星光标签</h1>
            <p>点击标签：添加 / 再次点击取消</p>
            <p style="font-size:0.95rem; color:rgba(255,255,255,0.6);">
                <span style="color:#ffd700;">★</span> 通用标签 
                <span style="color:#cc66ff; margin-left:20px;">✦</span> 专属标签（仅对当前同学可见）
                <span style="color:#00ccff; margin-left:20px;">✨</span> 用户自建
            </p>
        </div>
        
        <!-- 标签分组展示 -->
        <?php foreach ($grouped_tags as $class => $tags): ?>
        <div class="tag-group" data-tagclass="<?php echo htmlspecialchars($class); ?>">
            <h3><?php echo htmlspecialchars($class); ?></h3>
            <div class="tag-list">
                <?php foreach ($tags as $tag): 
                    $is_used = in_array($tag['id'], $used_tag_ids);
                    $has_desc = !empty($tag['description']);
                    
                    // 标签样式计算
                    $tag_class = 'tag-item';
                    if ($is_used) $tag_class .= ' used';
                    if ($tag['if_for_one_user'] == 1) $tag_class .= ' special-tag';
                    elseif ($tag['if_special'] == 1) $tag_class .= ' user-created';
                ?>
                <div class="<?php echo $tag_class; ?>" 
                     data-tag-id="<?php echo $tag['id']; ?>"
                     data-user-id="<?php echo $target_user_id; ?>"
                     data-description="<?php echo htmlspecialchars($tag['description'] ?? ''); ?>"
                     data-used="<?php echo $is_used ? '1' : '0'; ?>"
                     data-special="<?php echo $tag['if_for_one_user']; ?>">
                    <span class="tag-name"><?php echo htmlspecialchars($tag['tagname']); ?></span>
                    <?php if ($has_desc): ?>
                        <span class="has-desc" title="鼠标悬停查看描述">📘</span>
                    <?php endif; ?>
                    <?php if ($tag['if_for_one_user'] == 1): ?>
                        <span style="margin-left:5px; font-size:0.9rem;">✨专属</span>
                    <?php elseif ($tag['if_special'] == 1): ?>
                        <span style="margin-left:5px; font-size:0.9rem;">✨</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- 创建新标签按钮 -->
        <div style="display: flex; justify-content: center;">
            <div class="btn-create-tag" id="btn-create-tag">
                <span style="font-size:1.5rem;">+</span> 创建新标签
            </div>
        </div>
        
        <!-- 创建标签模态框 -->
        <div class="modal-overlay" id="create-tag-modal">
            <div class="modal-content">
                <h2>✨ 创建新标签</h2>
                <div class="form-group">
                    <label>标签名 <span style="color:#ffd700;">*</span></label>
                    <input type="text" id="new-tagname" placeholder="例如：足球大神" maxlength="50">
                </div>
                <div class="form-group">
                    <label>描述（选填）</label>
                    <textarea id="new-description" placeholder="详细描述这个标签的含义..."></textarea>
                </div>
                <div class="form-group">
                    <label>分类 <span style="color:#ffd700;">*</span></label>
                    <input type="text" id="new-tagclass" placeholder="例如：学习与才华" list="existing-classes">
                    <datalist id="existing-classes">
                        <?php 
                        // 从现有标签中提取所有分类供快速选择
                        $all_classes = array_keys($grouped_tags);
                        foreach ($all_classes as $c): 
                            if (!empty($c)): 
                        ?>
                        <option value="<?php echo htmlspecialchars($c); ?>">
                        <?php endif; endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="new-if-for-one-user">
                    <label for="new-if-for-one-user">仅对当前同学可见（专属标签）</label>
                </div>
                <div class="modal-actions">
                    <button class="btn-modal-cancel" id="btn-cancel-modal">取消</button>
                    <button class="btn-modal-submit" id="btn-submit-tag">创建标签</button>
                </div>
            </div>
        </div>
        
        <!-- ---------- 评价进度统计卡片 ---------- -->
        <?php
        $rated_count = getRatedUserCount($current_user_id);
        $total_count = getTotalEvaluatableUserCount($current_user_id);
        ?>
        <div class="rating-stats">
            <div class="stats-card">
                <span class="stats-number"><?php echo $rated_count; ?> / <?php echo $total_count; ?></span>
                <span class="stats-unit">位老师/同学已评价</span>
            </div>
        </div>
        <div class="back-link">
            <a href="?step=4">← 返回人物选择</a>
        </div>
    </div>
    
    <?php } // endif target_user exists ?>

<?php else: ?>
    <?php header('Location: ?step=1'); exit; ?>
<?php endif; ?>

</div>
</main>

<!-- 消息提示容器 -->
<div id="message-toast" style="display:none;"></div>

<script>
// ==================== 全局交互脚本 ====================
document.addEventListener('DOMContentLoaded', function() {
    // 判断是否在评价页面（step=5）
    const urlParams = new URLSearchParams(window.location.search);
    const isRatePage = (urlParams.get('step') === '5' || urlParams.get('action') === 'rate');
    
    // ---------- 1. 标签点击添加/取消 ----------
    if (isRatePage) {
        const tagItems = document.querySelectorAll('.tag-item');
        tagItems.forEach(item => {
            item.removeEventListener('click', handleTagClick);
            item.addEventListener('click', handleTagClick);
        });
    }
    
    function handleTagClick(e) {
        e.preventDefault();
        const tagId = this.dataset.tagId;
        const userId = this.dataset.userId;
        const isUsed = this.classList.contains('used');
        const action = isUsed ? 'remove_tag' : 'add_tag';
        
        fetch(window.location.pathname, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action, user_id: userId, tag_id: tagId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (action === 'add_tag') {
                    this.classList.add('used');
                    this.dataset.used = '1';
                } else {
                    this.classList.remove('used');
                    this.dataset.used = '0';
                }
                showMessage('✨ ' + data.message, 'success');
            } else {
                showMessage('❌ ' + data.message, 'error');
            }
        })
        .catch(() => showMessage('网络错误，请重试', 'error'));
    }
    
    // ---------- 2. 创建标签模态框控制 ----------
    const modal = document.getElementById('create-tag-modal');
    const btnCreate = document.getElementById('btn-create-tag');
    const btnCancel = document.getElementById('btn-cancel-modal');
    const btnSubmit = document.getElementById('btn-submit-tag');
    
    if (btnCreate && modal) {
        btnCreate.addEventListener('click', function() {
            // 从URL获取当前被评价用户ID
            const userId = urlParams.get('user_id');
            if (userId) {
                modal.dataset.targetUserId = userId;
            }
            modal.classList.add('active');
        });
    }
    
    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            modal.classList.remove('active');
            // 清空表单
            document.getElementById('new-tagname').value = '';
            document.getElementById('new-description').value = '';
            document.getElementById('new-tagclass').value = '';
            document.getElementById('new-if-for-one-user').checked = false;
        });
    }
    
    // 点击模态框背景关闭
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }
    
    // ---------- 3. 提交新标签 ----------
    if (btnSubmit) {
        btnSubmit.addEventListener('click', function() {
            const tagname = document.getElementById('new-tagname').value.trim();
            const description = document.getElementById('new-description').value.trim();
            const tagclass = document.getElementById('new-tagclass').value.trim();
            const if_for_one_user = document.getElementById('new-if-for-one-user').checked ? 1 : 0;
            const targetUserId = modal.dataset.targetUserId || urlParams.get('user_id');
            
            if (!tagname) {
                showMessage('请输入标签名', 'error');
                return;
            }
            if (!tagclass) {
                showMessage('请输入分类', 'error');
                return;
            }
            if (!targetUserId) {
                showMessage('目标用户ID缺失，请刷新页面', 'error');
                return;
            }
            
            const formData = new URLSearchParams({
                action: 'create_tag',
                tagname: tagname,
                description: description,
                tagclass: tagclass,
                if_for_one_user: if_for_one_user,
                target_user_id: targetUserId
            });
            
            fetch(window.location.pathname, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showMessage('🎉 ' + data.message, 'success');
                    // 将新标签动态添加到页面
                    addNewTagToPage(data.tag, targetUserId);
                    // 关闭模态框并清空
                    modal.classList.remove('active');
                    document.getElementById('new-tagname').value = '';
                    document.getElementById('new-description').value = '';
                    document.getElementById('new-tagclass').value = '';
                    document.getElementById('new-if-for-one-user').checked = false;
                } else {
                    showMessage('❌ ' + data.message, 'error');
                }
            })
            .catch(() => showMessage('网络错误，请重试', 'error'));
        });
    }
    
    // ---------- 4. 动态添加新标签到界面 ----------
    function addNewTagToPage(tag, targetUserId) {
        // 查找对应的分类组
        let groupDiv = null;
        const groups = document.querySelectorAll('.tag-group');
        for (let g of groups) {
            if (g.dataset.tagclass === tag.tagclass) {
                groupDiv = g;
                break;
            }
        }
        
        // 如果分类组不存在，则创建一个新的
        if (!groupDiv) {
            const container = document.querySelector('.rate-header + div'); // 获取评价内容容器
            const newGroup = document.createElement('div');
            newGroup.className = 'tag-group';
            newGroup.dataset.tagclass = tag.tagclass;
            newGroup.innerHTML = `<h3>${escapeHtml(tag.tagclass)}</h3><div class="tag-list"></div>`;
            // 插入到创建标签按钮之前
            const createBtnDiv = document.querySelector('.btn-create-tag')?.parentNode;
            if (createBtnDiv) {
                createBtnDiv.parentNode.insertBefore(newGroup, createBtnDiv.parentNode);
            } else {
                container.appendChild(newGroup);
            }
            groupDiv = newGroup;
        }
        
        // 构建标签元素
        const tagItem = document.createElement('div');
        let tagClass = 'tag-item';
        if (tag.if_for_one_user == 1) tagClass += ' special-tag';
        else if (tag.if_special == 1) tagClass += ' user-created';
        tagItem.className = tagClass;
        tagItem.dataset.tagId = tag.id;
        tagItem.dataset.userId = targetUserId;
        tagItem.dataset.description = tag.description || '';
        tagItem.dataset.used = '0';
        tagItem.dataset.special = tag.if_for_one_user;
        
        let innerHtml = `<span class="tag-name">${escapeHtml(tag.tagname)}</span>`;
        if (tag.description) {
            innerHtml += `<span class="has-desc" title="鼠标悬停查看描述">📘</span>`;
        }
        if (tag.if_for_one_user == 1) {
            innerHtml += `<span style="margin-left:5px; font-size:0.9rem;">✨专属</span>`;
        } else if (tag.if_special == 1) {
            innerHtml += `<span style="margin-left:5px; font-size:0.9rem;">🛠️</span>`;
        }
        tagItem.innerHTML = innerHtml;
        
        // 添加点击事件
        tagItem.addEventListener('click', handleTagClick);
        
        // 插入到对应分类的tag-list末尾
        const tagList = groupDiv.querySelector('.tag-list');
        tagList.appendChild(tagItem);
    }
    
    // 简易HTML转义
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
    
    // 显示消息提示
    function showMessage(text, type = 'info') {
        const toast = document.getElementById('message-toast');
        toast.textContent = text;
        toast.style.display = 'block';
        toast.className = 'message-toast';
        setTimeout(() => { toast.style.display = 'none'; }, 2500);
    }
});
</script>

