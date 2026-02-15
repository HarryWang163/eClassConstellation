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

// 获取当前用户ID
$current_user_id = $_SESSION['user_id'] ?? 0;

// ==================== 数据库操作函数 ====================

/**
 * 获取用户当前进行的组和题号
 * @return array|null ['group' => int, 'index' => int] 或 null（全部完成）
 */
function getCurrentProgress($user_id) {
    $db = getDB();

    // 1. 获取所有已完成组（user_score 中存在记录）
    $stmt = $db->prepare("SELECT question_group FROM user_score WHERE user_id = ? ORDER BY question_group");
    $stmt->execute([$user_id]);
    $finishedGroups = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. 确定当前应进行的组（如果没有完成组，从组1开始；否则从已完成组的下一组开始）
    $currentGroup = empty($finishedGroups) ? 1 : max($finishedGroups) + 1;

    // 如果超过总组数（假设有3组），则全部完成
    if ($currentGroup > 3) {
        return null;
    }

    // 3. 查询当前组已经回答了多少题
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM user_answer 
        WHERE user_id = ? AND question_id IN (
            SELECT id FROM questions WHERE groupAffiliation = ?
        )
    ");
    $stmt->execute([$user_id, $currentGroup]);
    $answered = $stmt->fetchColumn();

    // 4. 根据已答数量返回不同状态
    if ($answered >= 10) {
        // 异常情况：已答满10题但 user_score 中无记录（可能是之前逻辑遗漏）
        // 尝试自动完成组并记录分数
        try {
            finishGroup($user_id, $currentGroup);
            // 完成后推进到下一组
            $currentGroup++;
            if ($currentGroup > 3) return null;
            return ['group' => $currentGroup, 'index' => 0];
        } catch (Exception $e) {
            // 如果自动完成失败，至少让用户看到当前组的结果页
            return ['status' => 'finished_group', 'group' => $currentGroup];
        }
    } elseif ($answered > 0) {
        // 已开始但未完成，返回下一题索引（从0开始）
        return ['group' => $currentGroup, 'index' => $answered];
    } else {
        // 未开始当前组，从第一题开始
        return ['group' => $currentGroup, 'index' => 0];
    }
}

/**
 * 获取指定组和索引的题目
 */
function getQuestion($group, $index) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT id, question, optionA, optionB, optionC, optionD, answer, description
        FROM questions
        WHERE groupAffiliation = ? AND indexInGroup = ?
        LIMIT 1
    ");
    $stmt->execute([$group, $index + 1]); // 索引从1开始
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * 记录用户答案
 */
function recordAnswer($user_id, $question_id, $selected_answer, $is_correct) {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO user_answer (user_id, question_id, selector_answer, if_right)
        VALUES (?, ?, ?, ?)
    ");
    // 强制转换为整数
    $user_id = (int)$user_id;
    $question_id = (int)$question_id;
    $selected_answer = (int)$selected_answer;
    $is_correct = $is_correct ? 1 : 0;  // 布尔值转 0/1
    return $stmt->execute([$user_id, $question_id, $selected_answer, $is_correct]);
}

function finishGroup($user_id, $group) {
    $db = getDB();

    // 先检查该组是否已有分数记录
    $stmt = $db->prepare("SELECT id FROM user_score WHERE user_id = ? AND question_group = ?");
    $stmt->execute([$user_id, $group]);
    if ($stmt->fetch()) {
        // 已有记录，直接返回现有分数
        $stmt = $db->prepare("SELECT score FROM user_score WHERE user_id = ? AND question_group = ?");
        $stmt->execute([$user_id, $group]);
        $score = $stmt->fetchColumn();
        return ['correct' => $score / 10, 'score' => $score];
    }

    // 统计该组答对题数
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM user_answer
        WHERE user_id = ? AND if_right = 1 AND question_id IN (
            SELECT id FROM questions WHERE groupAffiliation = ?
        )
    ");
    $stmt->execute([$user_id, $group]);
    $correct = $stmt->fetchColumn();
    $score = $correct * 10;

    // 插入 user_score
    $stmt = $db->prepare("
        INSERT INTO user_score (user_id, score, question_group)
        VALUES (?, ?, ?)
    ");
    $success = $stmt->execute([$user_id, $score, $group]);

    if (!$success) {
        throw new Exception('无法保存分数');
    }

    return ['correct' => $correct, 'score' => $score];
}

/**
 * 获取某个组的结果数据
 */
function getGroupResult($user_id, $group) {
    $db = getDB();

    // 获取该组得分
    $stmt = $db->prepare("SELECT score FROM user_score WHERE user_id = ? AND question_group = ?");
    $stmt->execute([$user_id, $group]);
    $scoreData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$scoreData) return null;

    $score = $scoreData['score'];
    $correct = $score / 10;

    // 计算超过百分比：查询所有用户在该组的分数，计算排名
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM user_score WHERE question_group = ? AND score < ?
    ");
    $stmt->execute([$group, $score]);
    $less = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM user_score WHERE question_group = ?");
    $stmt->execute([$group]);
    $total = $stmt->fetchColumn();

    $percent = $total > 0 ? round($less / $total * 100, 1) : 0;

    return [
        'group' => $group,
        'correct' => $correct,
        'score' => $score,
        'percent' => $percent,
        'total_answers' => 10 // 固定每组10题
    ];
}

/**
 * 获取当前用户已完成的组列表
 */
function getFinishedGroups($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT question_group FROM user_score WHERE user_id = ? ORDER BY question_group");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// 在文件开头添加错误控制（确保不输出错误到响应）
ini_set('display_errors', 0);
error_reporting(E_ALL);

// ==================== API 请求处理 ====================

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit') {
    header('Content-Type: application/json');
    try {
        $question_id = intval($_POST['question_id'] ?? 0);
        $answer = intval($_POST['answer'] ?? 0);

        if (!$question_id) {
            throw new Exception('参数错误');
        }

        $db = getDB();
        // 获取题目信息
        $stmt = $db->prepare("SELECT answer, description, groupAffiliation FROM questions WHERE id = ?");
        $stmt->execute([$question_id]);
        $q = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$q) {
            throw new Exception('题目不存在');
        }

        $is_correct = (($answer + 1) == $q['answer']);  // 前端0-based -> 数据库1-based
        $db_answer = $answer + 1;
        $recorded = recordAnswer($current_user_id, $question_id, $db_answer, $is_correct);
        if (!$recorded) {
            throw new Exception('记录答案失败');
        }

        // 检查是否完成该组
        $group = $q['groupAffiliation'];
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM user_answer
            WHERE user_id = ? AND question_id IN (
                SELECT id FROM questions WHERE groupAffiliation = ?
            )
        ");
        $stmt->execute([$current_user_id, $group]);
        $answered = $stmt->fetchColumn();

        $group_finished = ($answered >= 10);
        $result = [
            'success' => true,
            'correct' => $is_correct,
            'correct_answer' => $q['answer'] - 1, // 数据库1-based → 前端0-based
            'description' => $q['description'] ?? '',
            'group_finished' => $group_finished
        ];

        if ($group_finished) {
            $finishData = finishGroup($current_user_id, $group);
            $result['group_result'] = $finishData;
        }

        echo json_encode($result);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '数据库错误：' . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// 其他 API 动作（如 get_current、get_result）也要类似地包裹 try-catch
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_current') {
    header('Content-Type: application/json');
    try {
        // 如果指定了 group 参数，则强制进入该组
        $forceGroup = isset($_GET['group']) ? intval($_GET['group']) : 0;
        if ($forceGroup) {
            $finished = getFinishedGroups($current_user_id);
            if (in_array($forceGroup, $finished)) {
                // 该组已完成，返回结果页
                echo json_encode(['status' => 'finished_group', 'group' => $forceGroup]);
                exit;
            }
            // 获取该组答题进度
            $db = getDB();
            $stmt = $db->prepare("SELECT COUNT(*) FROM user_answer WHERE user_id = ? AND question_id IN (SELECT id FROM questions WHERE groupAffiliation = ?)");
            $stmt->execute([$current_user_id, $forceGroup]);
            $answered = $stmt->fetchColumn();
            if ($answered >= 10) {
                echo json_encode(['status' => 'finished_group', 'group' => $forceGroup]);
                exit;
            }
            $question = getQuestion($forceGroup, $answered);
            if (!$question) {
                throw new Exception('题目不存在');
            }
            echo json_encode([
                'status' => 'in_progress',
                'group' => $forceGroup,
                'index' => $answered + 1,
                'total' => 10,
                'question' => [
                    'id' => $question['id'],
                    'text' => $question['question'],
                    'options' => [$question['optionA'], $question['optionB'], $question['optionC'], $question['optionD']],
                    'description' => $question['description']
                ]
            ]);
            exit;
        }

        // 原有逻辑
        $progress = getCurrentProgress($current_user_id);
        if ($progress === null) {
            $finished = getFinishedGroups($current_user_id);
            echo json_encode(['status' => 'all_finished', 'groups' => $finished]);
            exit;
        }
        if (isset($progress['status']) && $progress['status'] === 'finished_group') {
            echo json_encode(['status' => 'finished_group', 'group' => $progress['group']]);
            exit;
        }
        $question = getQuestion($progress['group'], $progress['index']);
        if (!$question) {
            throw new Exception('题目不存在');
        }
        echo json_encode([
            'status' => 'in_progress',
            'group' => $progress['group'],
            'index' => $progress['index'] + 1,
            'total' => 10,
            'question' => [
                'id' => $question['id'],
                'text' => $question['question'],
                'options' => [$question['optionA'], $question['optionB'], $question['optionC'], $question['optionD']],
                'description' => $question['description']
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_result') {
    header('Content-Type: application/json');
    try {
    $group = intval($_GET['group'] ?? 0);
    if (!$group) {
        // 如果没有指定组，则返回最近完成的一个组
        $finished = getFinishedGroups($current_user_id);
        if (empty($finished)) {
            echo json_encode(['status' => 'no_result']);
            exit;
        }
        $group = end($finished); // 最后一个完成的组
    }

    $result = getGroupResult($current_user_id, $group);
    if (!$result) {
        echo json_encode(['status' => 'no_result']);
        exit;
    }

    echo json_encode([
        'status' => 'result',
        'result' => $result
    ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '数据库错误：' . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ==================== 非API请求：输出HTML页面 ====================

// 引入公共头部（无导航栏）
require_once __DIR__ . '/app/includes/headerWithoutBar.php';
?>

<style>
    h2{
        color: #f8f9fa;
    }
    /* 原有样式保持不变，略作精简 */
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
        from { opacity: 0; transform: translateY(50px); }
        to { opacity: 1; transform: translateY(0); }
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
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
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
        border: none;
        font-size: 1rem;
        cursor: pointer;
        text-align: center;
    }
    .btn:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(255, 215, 0, 0.6);
    }
    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .btn-secondary {
        background: rgba(255, 255, 255, 0.1);
        color: #f8f9fa;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }
    .progress {
        margin-bottom: 40px;
    }
    .progress-bar {
        width: 100%;
        height: 12px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        overflow: hidden;
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
    .feedback.info {
        background: rgba(255, 215, 0, 0.2);
        color: #ffd700;
        border: 2px solid rgba(255, 215, 0, 0.4);
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
    .result-actions {
        display: flex;
        gap: 20px;
        justify-content: center;
        margin-top: 30px;
    }
    .result-container {
        text-align: center;
        animation: fadeIn 1s ease-out;
    }
    .result-stats {
        font-size: 1.2rem;
        line-height: 2;
        color: #f8f9fa;
        margin: 30px 0;
    }
    .result-stats p {
        margin: 10px 0;
    }
    .highlight {
        color: #ffd700;
        font-weight: bold;
        font-size: 1.5rem;
    }
    @media (max-width: 768px) {
        .test-container { padding: 25px; }
        .navigation { flex-direction: column; gap: 15px; }
        .result-actions { flex-direction: column; }
    }
</style>

<main>
    <div class="test-container" id="test-container">
        <!-- 动态内容由JS填充 -->
        <div id="loading" style="text-align: center; padding: 50px;">加载中...</div>
    </div>
</main>

<script>
    const container = document.getElementById('test-container');
    let currentQuestionId = null;
    let currentGroup = null;
    let currentIndex = null;
    let totalQuestions = 10;
    let selectedAnswer = null;

    // 加载初始状态
async function loadInitial() {
    // 获取当前 URL 中的 group 参数
    const urlParams = new URLSearchParams(window.location.search);
    const groupParam = urlParams.get('group');

    // 构建 API 请求 URL
    let apiUrl = '?action=get_current';
    if (groupParam) {
        apiUrl += '&group=' + groupParam;
    }

    const res = await fetch(apiUrl);
    const data = await res.json();

    if (data.status === 'all_finished') {
        showResult(data.groups[data.groups.length - 1]);
    } else if (data.status === 'finished_group') {
        showResult(data.group);
    } else if (data.status === 'in_progress') {
        currentGroup = data.group;
        currentIndex = data.index;
        currentQuestionId = data.question.id;
        displayQuestion(data.question, currentIndex, data.total);
    } else if (data.status === 'error') {
        container.innerHTML = `<div class="error">${data.message}</div>`;
    }
}

    // 显示题目
    function displayQuestion(q, index, total) {
        const progressPercent = (index / total) * 100;
        let html = `
            <div class="progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${progressPercent}%;"></div>
                </div>
                <div class="progress-text">第 ${index} 题 / 共 ${total} 题</div>
            </div>
            <div class="question">
                <div class="question-number">题目 ${index}</div>
                <div class="question-text">${q.text}</div>
            </div>
            <div class="options" id="options-container">
        `;

        q.options.forEach((opt, i) => {
            const letter = String.fromCharCode(65 + i); // A, B, C, D
            html += `
                <div class="option" data-opt-index="${i}">
                    <input type="radio" name="answer" value="${i}" id="opt${i}">
                    <label for="opt${i}"><strong>${letter}.</strong> ${opt}</label>
                </div>
            `;
        });

        html += `
            </div>
            <div id="feedback" class="feedback"></div>
            <div class="navigation">
                <button class="btn" id="submit-btn">提交答案</button>
                <button class="btn" id="next-btn" disabled>下一题</button>
            </div>
        `;

        container.innerHTML = html;

        // 绑定选项点击事件
        document.querySelectorAll('.option').forEach(opt => {
            opt.addEventListener('click', function() {
                document.querySelectorAll('.option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input[type="radio"]').checked = true;
                selectedAnswer = parseInt(this.dataset.optIndex);
            });
        });

        document.getElementById('submit-btn').addEventListener('click', submitAnswer);
        document.getElementById('next-btn').addEventListener('click', nextQuestion);
    }

async function submitAnswer() {
    if (selectedAnswer === null) {
        showFeedback('请选择一个答案', 'info');
        return;
    }

    const submitBtn = document.getElementById('submit-btn');
    const nextBtn = document.getElementById('next-btn');
    submitBtn.disabled = true;

    const formData = new URLSearchParams();
    formData.append('question_id', currentQuestionId);
    formData.append('answer', selectedAnswer);

    try {
        const res = await fetch('?action=submit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });

        if (!res.ok) {
            throw new Error(`HTTP错误 ${res.status}`);
        }

        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('服务器返回的不是JSON:', text);
            throw new Error('服务器返回格式错误，请查看控制台');
        }

       if (data.success) {
            const options = document.querySelectorAll('.option');

            // 1. 标记正确答案（总是绿色）
            options[data.correct_answer].classList.add('correct');

            // 2. 处理用户答案
            if (data.correct) {
                // 用户答对：用户选项即为正确答案，已经标记，无需额外操作
                showFeedback('✓ 回答正确！', 'success');
            } else {
                // 用户答错：将用户选项标记为红色
                options[selectedAnswer].classList.add('incorrect');
                showFeedback('✗ 回答错误', 'error');
            }

            // 3. 显示题目描述（如果有）
            if (data.description) {
                showFeedback(data.description, 'info');
            }

            // 4. 禁用所有选项，启用下一题按钮
            options.forEach(opt => opt.style.pointerEvents = 'none');
            nextBtn.disabled = false;

            if (data.group_finished) {
                nextBtn.textContent = '查看结果';
            }
        }
    } catch (err) {
        showFeedback('请求失败: ' + err.message, 'error');
        submitBtn.disabled = false;
    }
}

    // 下一题
   async function nextQuestion() {
    const nextBtn = document.getElementById('next-btn');
    if (nextBtn.textContent === '查看结果') {
        showResult(currentGroup);
        return;
    }

    // 获取当前 URL 中的 group 参数
    const urlParams = new URLSearchParams(window.location.search);
    const groupParam = urlParams.get('group');
    let apiUrl = '?action=get_current';
    if (groupParam) {
        apiUrl += '&group=' + groupParam;
    }

    const res = await fetch(apiUrl);
    const data = await res.json();

    if (data.status === 'in_progress') {
        currentGroup = data.group;
        currentIndex = data.index;
        currentQuestionId = data.question.id;
        displayQuestion(data.question, currentIndex, data.total);
    } else if (data.status === 'all_finished') {
        showResult(data.groups[data.groups.length - 1]);
    } else if (data.status === 'finished_group') {
        showResult(data.group);
    }
}

    // 显示某个组的结果
    async function showResult(group) {
        const res = await fetch(`?action=get_result&group=${group}`);
        const data = await res.json();

        if (data.status === 'result') {
            const r = data.result;
            container.innerHTML = `
                <div class="result-container">
                    <h2>第 ${r.group} 题组 结果</h2>
                    <div class="result-stats">
                        <p>本题组答对 <span class="highlight">${r.correct}</span> 题</p>
                        <p>得分 <span class="highlight">${r.score}</span> 分</p>
                    <!--    <p>正确率 <span class="highlight">${(r.correct/10*100).toFixed(1)}%</span></p> -->
                    <!--    <p>超过了 <span class="highlight">${r.percent}%</span> 的选手</p> -->
                        <p>总答题数 <span class="highlight">10</span></p>
                    </div>
                    <div class="result-actions">
                        ${r.group < 3 ? '<button class="btn" id="next-group">再来一个题组</button>' : ''}
                        <button class="btn btn-secondary" id="continue">继续下一个环节</button>
                    </div>
                </div>
            `;
            if (r.group < 3) {
                document.getElementById('next-group').addEventListener('click', () => {
                    window.location.href = `?group=${r.group + 1}`;
                });
            }
            document.getElementById('continue').addEventListener('click', () => {
                window.location.href = '/splashs/splash3.php';
            });
        } else {
            container.innerHTML = '<p>没有结果</p>';
        }
    }

    // 显示反馈
    function showFeedback(msg, type) {
        const fb = document.getElementById('feedback');
        fb.textContent = msg;
        fb.className = `feedback ${type}`;
        fb.style.display = 'block';
    }

    // 页面加载
    window.addEventListener('load', loadInitial);
</script>

<?php
// 引入公共页脚
require_once __DIR__ . '/app/includes/footer.php';
?>