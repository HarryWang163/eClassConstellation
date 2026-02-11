        <footer>
            <p>© 2026 高二11班 · 同在计划</p>
            <p style="margin-top: 10px;">时光不老，我们不散</p>
        </footer>
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
        
        // 平滑滚动效果
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                // 这里可以添加页面跳转逻辑
                alert('功能开发中，敬请期待！');
            });
        });
        
        // 卡片悬停效果增强
        document.querySelectorAll('.activity-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-15px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
        
        // 页面加载完成后生成星光
        window.addEventListener('load', createStars);
    </script>
</body>
</html>