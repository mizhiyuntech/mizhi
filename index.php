<?php
// 漂流瓶程序
session_start();

// API配置
$api_url = 'http://api.bbs.sunding.cn/sd/api/user/bottle-message/cast-bottle-message';

// 处理投放漂流瓶的请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cast_bottle') {
    $token = trim($_POST['token']);
    $message = trim($_POST['message']);
    
    // 验证输入
    if (empty($token)) {
        $error = '请输入用户令牌';
    } elseif (empty($message)) {
        $error = '请输入漂流瓶消息';
    } else {
        // 调用API
        $result = castBottleMessage($api_url, $token, $message);
        
        if ($result['success']) {
            $success = '漂流瓶投放成功！';
            $_SESSION['last_token'] = $token; // 记住上次的token
        } else {
            $error = '投放失败：' . $result['message'];
        }
    }
}

// 调用投放漂流瓶API的函数
function castBottleMessage($url, $token, $message) {
    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    $data = json_encode([
        'message' => $message
    ]);
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
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
    
    if ($http_code === 200 || $http_code === 201) {
        return [
            'success' => true,
            'message' => '投放成功',
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
    <title>漂流瓶程序</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            max-width: 500px;
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
            content: '🍶';
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
        input[type="password"],
        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus, 
        input[type="password"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn:active {
            transform: translateY(0);
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
            border-left: 4px solid #667eea;
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

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            
            h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>漂流瓶程序</h1>
        
        <div class="api-info">
            <h3>API 信息</h3>
            <p><strong>接口地址：</strong><?php echo htmlspecialchars($api_url); ?></p>
            <p><strong>请求方式：</strong>POST</p>
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
            <input type="hidden" name="action" value="cast_bottle">
            
            <div class="form-group">
                <label for="token">用户令牌 (Token)</label>
                <input type="password" id="token" name="token" value="<?php echo htmlspecialchars($last_token); ?>" required>
                <div class="help-text">请输入您的Bearer Token，格式如：your_token_here</div>
            </div>

            <div class="form-group">
                <label for="message">漂流瓶消息</label>
                <textarea id="message" name="message" placeholder="写下您想要投放的漂流瓶消息..." required></textarea>
                <div class="help-text">请输入您想要投放的漂流瓶消息内容</div>
            </div>

            <button type="submit" class="btn">投放漂流瓶 🍶</button>
        </form>
    </div>

    <script>
        // 简单的表单验证
        document.querySelector('form').addEventListener('submit', function(e) {
            const token = document.getElementById('token').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if (!token) {
                alert('请输入用户令牌');
                e.preventDefault();
                return;
            }
            
            if (!message) {
                alert('请输入漂流瓶消息');
                e.preventDefault();
                return;
            }
            
            // 显示加载状态
            const btn = document.querySelector('.btn');
            btn.textContent = '投放中...';
            btn.disabled = true;
        });
    </script>
</body>
</html>