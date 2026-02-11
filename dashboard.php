<?php
// 引入认证工具
require_once __DIR__ . '/app/includes/auth.php';

// 检查是否已登录
if (!isLoggedIn()) {
        // 未登录，显示登录提示
        redirectToLogin();
}
?>

<?php
// 引入公共头部
require_once __DIR__ . '/app/includes/header.php';
?>

        <header>
            <h1>同在计划</h1>
            <p class="subtitle">时光同溯，星辰同辉</p>
            <p class="header-description">
                "同在"，不仅是时间与空间的重叠，更是记忆的共享、心灵的照见与未来的共赴。<br>
                我们相聚于高二11班，已共度一年半的星辰与晨昏。那些黑板上的涂鸦、课堂上的笑声、运动会上的呐喊，共同编织成我们独一无二的青春叙事。
            </p>
        </header>
        
        <div class="activities-grid">
            <!-- 第一重：记忆同在 -->
            <div class="activity-card">
                <div class="activity-icon">🔐</div>
                <h2 class="activity-title">同在密码</h2>
                <p class="activity-description">11班终极认证测试，通过50道融合班级特色的题目，完成一次温暖的集体身份认证。答对的不是知识，而是我们共同度过的时光。</p>
                <a href="password.php" class="btn">开始测试</a>
            </div>
            
            <div class="activity-card">
                <div class="activity-icon">⏰</div>
                <h2 class="activity-title">时光同轨</h2>
                <p class="activity-description">我们的记忆交互轴线，班级大事记与日常照片并置，设置共鸣按钮，拼接共同经历的宏大画卷。让时间轴因我们的互动而拥有温度与心跳。</p>
                <a href="timeline.php" class="btn">查看时间轴</a>
            </div>
            
            <!-- 第二重：心灵同在 -->
            <div class="activity-card">
                <div class="activity-icon">✨</div>
                <h2 class="activity-title">星光互映</h2>
                <p class="activity-description">"你在我眼中的样子"，为好友贴标签，生成专属星光图谱，打破"熟悉的陌生人"状态。让你知道，你的独特光韵正被身边的"同在者"清晰地看见并珍视着。</p>
                <a href="stars.php" class="btn">查看星光</a>
            </div>
            
            <!-- 第三重：未来同在 -->
            <div class="activity-card">
                <div class="activity-icon">🌟</div>
                <h2 class="activity-title">星屿共筑</h2>
                <p class="activity-description">我们的未来星空，选择代表自己颜色的星星，带着祝福升空，共赴一片星辰大海。每个人都是夜空中最亮的星，照亮彼此的前行路。</p>
                <a href="future.php" class="btn">筑梦星空</a>
            </div>
            
            <div class="activity-card">
                <div class="activity-icon">🎓</div>
                <h2 class="activity-title">同在见证</h2>
                <p class="activity-description">领航者之音，林老师、许老师、Fiona的寄语，为我们的高中时光增添温暖注脚。他们的话语如明灯，指引我们前行的方向。</p>
                <a href="witness.php" class="btn">聆听寄语</a>
            </div>
        </div>

<?php
// 引入公共页脚
require_once __DIR__ . '/app/includes/footer.php';
?>