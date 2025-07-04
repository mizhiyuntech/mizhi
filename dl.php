<?php
// 漂流瓶程序 - 打捞功能
session_start();

// API配置
$api_url = 'http://api.bbs.sunding.cn/sd/api/user/bottle-message/find-bottle-message';

// 处理打捞漂流瓶的请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'find_bottle') {
    $token = trim($_POST['token']);
    
    // 验证输入
    if (empty($token)) {
        $error = '请输入用户令牌';
    } else {
        // 调用API
        $result = findBottleMessage($api_url, $token);
        
        if ($result['success']) {
            $success = '成功打捞到漂流瓶！';
            $bottle_data = $result['data'];
            $_SESSION['last_token'] = $token; // 记住上次的token
        } else {
            $error = '打捞失败：' . $result['message'];
        }
    }
}

// 调用打捞漂流瓶API的函数
function findBottleMessage($url, $token) {
    $headers = [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ];
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);
    
    if ($curl_error) {
        return [
            'success' => false,
            'message' => 'Network error: ' . $curl_error
        ];
    }
    
    $response_data = json_decode($response, true);
    
    if ($http_code === 200) {
        return [
            'success' => true,
            'message' => '打捞成功',
            'data' => $response_data
        ];
    } else {
        $error_message = '请求失败 (HTTP ' . $http_code . ')';
        if ($response_data && isset($response_data['message'])) {
            $error_message .= ': ' . $response_data['message'];
        }
        return [
            'success' => false,
            'message' => $error_message
        ];
    }
}

// 获取上次使用的token
$last_token = isset($_SESSION['last_token']) ? $_SESSION['last_token'] : '';
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>打捞漂流瓶</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2.5em;
            position: relative;
        }

        h1::after {
            content: '🎣';
            position: absolute;
            right: -50px;
            top: 0;
            font-size: 0.8em;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }

        input[type="text"], 
        input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus, 
        input[type="password"]:focus {
            outline: none;
            border-color: #4facfe;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 172, 254, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin-top: 10px;
        }

        .btn-secondary:hover {
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .help-text {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .api-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #4facfe;
        }

        .api-info h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .api-info p {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .bottle-content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
            border-left: 4px solid #4facfe;
        }

        .bottle-content h3 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bottle-message {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #e1e8ed;
            margin-bottom: 15px;
            font-size: 16px;
            line-height: 1.6;
        }

        .bottle-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .meta-item {
            background-color: white;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #e1e8ed;
        }

        .meta-label {
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }

        .meta-value {
            color: #333;
            font-size: 14px;
            margin-top: 5px;
        }

        .navigation {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .navigation .btn {
            flex: 1;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            
            h1 {
                font-size: 2em;
            }

            .bottle-meta {
                grid-template-columns: 1fr;
            }

            .navigation {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>打捞漂流瓶</h1>
        
        <div class="api-info">
            <h3>API 信息</h3>
            <p><strong>接口地址：</strong><?php echo htmlspecialchars($api_url); ?></p>
            <p><strong>请求方式：</strong>GET</p>
            <p><strong>需要认证：</strong>Bearer Token</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="action" value="find_bottle">
            
            <div class="form-group">
                <label for="token">用户令牌 (Token)</label>
                <input type="password" id="token" name="token" value="<?php echo htmlspecialchars($last_token); ?>" required>
                <div class="help-text">请输入您的Bearer Token，格式如：your_token_here</div>
            </div>

            <button type="submit" class="btn">打捞漂流瓶 🎣</button>
        </form>

        <?php if (isset($bottle_data) && !empty($bottle_data)): ?>
            <div class="bottle-content">
                <h3>🍶 您捞到的漂流瓶</h3>
                
                <?php if (isset($bottle_data['data']) && !empty($bottle_data['data'])): ?>
                    <?php $bottle = $bottle_data['data']; ?>
                    
                    <div class="bottle-message">
                        <?php echo nl2br(htmlspecialchars($bottle['content'] ?? '暂无内容')); ?>
                    </div>
                    
                    <div class="bottle-meta">
                        <?php if (isset($bottle['id'])): ?>
                            <div class="meta-item">
                                <div class="meta-label">漂流瓶ID</div>
                                <div class="meta-value"><?php echo htmlspecialchars($bottle['id']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($bottle['user_id'])): ?>
                            <div class="meta-item">
                                <div class="meta-label">投放者ID</div>
                                <div class="meta-value"><?php echo htmlspecialchars($bottle['user_id']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($bottle['created_at'])): ?>
                            <div class="meta-item">
                                <div class="meta-label">投放时间</div>
                                <div class="meta-value"><?php echo htmlspecialchars($bottle['created_at']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($bottle['updated_at'])): ?>
                            <div class="meta-item">
                                <div class="meta-label">更新时间</div>
                                <div class="meta-value"><?php echo htmlspecialchars($bottle['updated_at']); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="bottle-message">
                        暂时没有捞到漂流瓶，请稍后再试 😊
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="navigation">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">
                投放漂流瓶 🍶
            </button>
            <button type="button" class="btn" onclick="window.location.reload()">
                再次打捞 🎣
            </button>
        </div>
    </div>

    <script>
        // 简单的表单验证
        document.querySelector('form').addEventListener('submit', function(e) {
            const token = document.getElementById('token').value.trim();
            
            if (!token) {
                alert('请输入用户令牌');
                e.preventDefault();
                return;
            }
            
            // 显示加载状态
            const btn = document.querySelector('.btn');
            btn.textContent = '打捞中...';
            btn.disabled = true;
        });
    </script>
</body>
</html>