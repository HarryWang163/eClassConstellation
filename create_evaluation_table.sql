-- 创建 evaluation 表用于存储同学之间的评价
CREATE TABLE IF NOT EXISTS evaluation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    target_user_id INT NOT NULL COMMENT '被评价用户ID',
    author VARCHAR(255) NOT NULL COMMENT '评价者姓名',
    content TEXT NOT NULL COMMENT '评价内容',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    INDEX idx_target_user_id (target_user_id)
    -- 暂时移除外键约束，以避免插入测试数据时出现问题
    -- 如果确定 users 表存在且结构正确，可以加上外键约束：
    -- , FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 首先，你可以查询 users 表来获取实际的用户ID
-- SELECT id, username FROM users;

-- 然后使用实际的用户ID来插入评价数据
-- 以下是示例格式，你需要将 target_user_id 替换为实际存在的用户ID
/*
INSERT INTO evaluation (target_user_id, author, content) VALUES
(1, '张三', '你是一个非常有责任感的人，总是能够认真完成自己的任务。在班级活动中，你也总是积极参与，为班级做出了很多贡献。希望你在未来的学习和生活中能够继续保持这种积极的态度，不断进步！'),
(2, '李四', '你的思维非常敏捷，总是能够在课堂上快速回答老师的问题。同时，你也很乐于助人，经常帮助同学们解决学习上的困难。相信你在未来一定会取得更大的成就！'),
(3, '王五', '你是一个很有创意的人，总是能够提出一些独特的想法。在小组活动中，你的创意往往能够给我们带来很多惊喜。希望你能够继续发挥自己的创造力，为我们的班级增添更多的色彩！'),
(4, '赵六', '你是一个非常努力的人，无论是在学习还是在其他方面，你都付出了很多努力。你的努力也得到了回报，你的成绩一直都很优秀。希望你能够继续保持这种努力的态度，未来一定会更加美好！'),
(5, '钱七', '你是一个很有团队精神的人，总是能够和同学们很好地合作。在团队活动中，你总是能够发挥自己的优势，为团队做出贡献。希望你能够继续保持这种团队精神，未来一定会有更多的人愿意和你合作！'),
(6, '孙八', '你乐观开朗的性格总能感染身边的人，让班级氛围更加融洽。你的笑容是大家的开心果，希望你永远保持这份阳光的心态。'),
(7, '周九', '你在学习上刻苦钻研的精神值得所有人学习，遇到难题从不轻易放弃，这种坚持的品质会让你在未来的人生道路上走得更远。'),
(8, '吴十', '你为人善良，待人真诚，总是愿意伸出援手帮助他人。在这个快节奏的时代，你的这份真诚显得尤为珍贵。'),
(9, '郑一', '你的领导能力很强，在班级活动中总能统筹安排，让每个人都发挥所长。这样的能力在未来一定会成为你的重要优势。');
*/

CREATE TABLE user_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    img TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 创建祝福表用于存储用户的新年祝福
CREATE TABLE IF NOT EXISTS blessings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT '用户ID',
    content VARCHAR(255) NOT NULL COMMENT '祝福内容',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    INDEX idx_user_id (user_id)
    -- 暂时移除外键约束，以避免插入测试数据时出现问题
    -- 如果确定 users 表存在且结构正确，可以加上外键约束：
    -- , FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 插入示例祝福数据
/*
INSERT INTO blessings (user_id, content) VALUES
(1, '新年快乐'),
(2, '万事如意'),
(3, '心想事成'),
(4, '恭喜发财'),
(5, '身体健康'),
(6, '阖家幸福'),
(7, '学业有成'),
(8, '工作顺利'),
(9, '笑口常开');
*/

-- 创建画板元素表用于存储用户在画板上添加的图片元素
CREATE TABLE IF NOT EXISTS canvas_elements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT '用户ID',
    pos_x INT NOT NULL COMMENT '元素在画板上的X坐标',
    pos_y INT NOT NULL COMMENT '元素在画板上的Y坐标',
    -- image_data TEXT NOT NULL COMMENT '图片数据（URL或Base64）',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    INDEX idx_user_id (user_id)
    -- 暂时移除外键约束，以避免插入测试数据时出现问题
    -- 如果确定 users 表存在且结构正确，可以加上外键约束：
    -- , FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 插入示例画板元素数据
/*
INSERT INTO canvas_elements (user_id, pos_x, pos_y, image_data) VALUES
(1, 640, 400, 'https://example.com/image1.jpg'),
(2, 320, 200, 'https://example.com/image2.jpg'),
(3, 960, 600, 'https://example.com/image3.jpg');
*/
