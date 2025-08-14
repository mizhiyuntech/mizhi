# 工具函数 API

本页面介绍各种实用的工具函数和辅助 API。

## 验证工具

### 邮箱验证

验证邮箱地址的有效性和可用性。

**请求**

```http
POST /api/v1/utils/validate/email
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "email": "user@example.com",
  "checkMx": true,
  "checkDisposable": true
}
```

**响应**

```json
{
  "success": true,
  "data": {
    "valid": true,
    "format": true,
    "mx": true,
    "disposable": false,
    "suggestion": null
  }
}
```

### 手机号验证

**请求**

```http
POST /api/v1/utils/validate/phone
Content-Type: application/json
```

**请求体**

```json
{
  "phone": "+1234567890",
  "country": "US"
}
```

## 格式化工具

### 日期格式化

格式化日期时间字符串。

**请求**

```http
POST /api/v1/utils/format/date
Content-Type: application/json
```

**请求体**

```json
{
  "date": "2024-01-01T00:00:00Z",
  "format": "YYYY-MM-DD HH:mm:ss",
  "timezone": "America/New_York",
  "locale": "en-US"
}
```

### 货币格式化

**请求**

```http
POST /api/v1/utils/format/currency
Content-Type: application/json
```

**请求体**

```json
{
  "amount": 1234.56,
  "currency": "USD",
  "locale": "en-US",
  "options": {
    "minimumFractionDigits": 2,
    "maximumFractionDigits": 2
  }
}
```

## 加密工具

### 生成哈希

生成数据的哈希值。

**请求**

```http
POST /api/v1/utils/crypto/hash
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "data": "Hello, World!",
  "algorithm": "sha256",
  "encoding": "hex"
}
```

**支持的算法**

- `md5`（不推荐用于安全场景）
- `sha1`
- `sha256`
- `sha384`
- `sha512`
- `bcrypt`（用于密码）

### 加密数据

**请求**

```http
POST /api/v1/utils/crypto/encrypt
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "data": "sensitive information",
  "algorithm": "aes-256-gcm",
  "key": "base64-encoded-key"
}
```

### 解密数据

**请求**

```http
POST /api/v1/utils/crypto/decrypt
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

## 图像处理

### 调整图像大小

**请求**

```http
POST /api/v1/utils/image/resize
Content-Type: multipart/form-data
Authorization: Bearer YOUR_TOKEN
```

**表单参数**

| 参数 | 类型 | 说明 |
|------|------|------|
| file | file | 原始图像文件 |
| width | integer | 目标宽度 |
| height | integer | 目标高度 |
| fit | string | 适应模式 (cover/contain/fill/inside/outside) |
| quality | integer | 质量 (1-100) |

### 图像裁剪

**请求**

```http
POST /api/v1/utils/image/crop
Content-Type: multipart/form-data
```

**表单参数**

```json
{
  "x": 0,
  "y": 0,
  "width": 200,
  "height": 200
}
```

### 图像转换

转换图像格式。

**请求**

```http
POST /api/v1/utils/image/convert
Content-Type: multipart/form-data
```

**表单参数**

| 参数 | 类型 | 说明 |
|------|------|------|
| file | file | 原始图像 |
| format | string | 目标格式 (jpeg/png/webp/avif) |
| quality | integer | 质量设置 |

## 文本处理

### 文本摘要

生成文本摘要。

**请求**

```http
POST /api/v1/utils/text/summarize
Content-Type: application/json
```

**请求体**

```json
{
  "text": "长文本内容...",
  "maxLength": 200,
  "language": "zh",
  "format": "plain"
}
```

### 关键词提取

**请求**

```http
POST /api/v1/utils/text/keywords
Content-Type: application/json
```

**请求体**

```json
{
  "text": "文章内容...",
  "count": 10,
  "language": "zh",
  "algorithm": "tfidf"
}
```

### 文本相似度

计算两段文本的相似度。

**请求**

```http
POST /api/v1/utils/text/similarity
Content-Type: application/json
```

**请求体**

```json
{
  "text1": "第一段文本",
  "text2": "第二段文本",
  "algorithm": "cosine"
}
```

## URL 工具

### URL 缩短

创建短链接。

**请求**

```http
POST /api/v1/utils/url/shorten
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "url": "https://example.com/very/long/url/path",
  "customAlias": "my-link",
  "expiresAt": "2024-12-31T23:59:59Z"
}
```

**响应**

```json
{
  "success": true,
  "data": {
    "shortUrl": "https://short.link/my-link",
    "originalUrl": "https://example.com/very/long/url/path",
    "qrCode": "data:image/png;base64,...",
    "analytics": {
      "clicks": 0,
      "createdAt": "2024-01-01T00:00:00Z"
    }
  }
}
```

### QR 码生成

**请求**

```http
POST /api/v1/utils/qrcode/generate
Content-Type: application/json
```

**请求体**

```json
{
  "data": "https://example.com",
  "size": 300,
  "format": "png",
  "errorCorrection": "M",
  "color": {
    "dark": "#000000",
    "light": "#FFFFFF"
  }
}
```

## 地理位置

### IP 地址定位

根据 IP 地址获取地理位置信息。

**请求**

```http
GET /api/v1/utils/geo/ip/{ipAddress}
Authorization: Bearer YOUR_TOKEN
```

**响应**

```json
{
  "success": true,
  "data": {
    "ip": "8.8.8.8",
    "country": "United States",
    "countryCode": "US",
    "region": "California",
    "city": "Mountain View",
    "latitude": 37.4056,
    "longitude": -122.0775,
    "timezone": "America/Los_Angeles",
    "isp": "Google LLC"
  }
}
```

### 地址解析

将地址转换为坐标。

**请求**

```http
POST /api/v1/utils/geo/geocode
Content-Type: application/json
```

**请求体**

```json
{
  "address": "1600 Amphitheatre Parkway, Mountain View, CA",
  "country": "US"
}
```

### 距离计算

计算两点之间的距离。

**请求**

```http
POST /api/v1/utils/geo/distance
Content-Type: application/json
```

**请求体**

```json
{
  "from": {
    "latitude": 37.4056,
    "longitude": -122.0775
  },
  "to": {
    "latitude": 37.7749,
    "longitude": -122.4194
  },
  "unit": "km"
}
```

## 邮件工具

### 发送邮件

发送电子邮件。

**请求**

```http
POST /api/v1/utils/email/send
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "to": ["user@example.com"],
  "cc": ["cc@example.com"],
  "bcc": ["bcc@example.com"],
  "subject": "邮件主题",
  "html": "<h1>HTML 内容</h1>",
  "text": "纯文本内容",
  "attachments": [
    {
      "filename": "document.pdf",
      "content": "base64-encoded-content"
    }
  ],
  "template": {
    "name": "welcome",
    "data": {
      "username": "John Doe"
    }
  }
}
```

### 邮件模板

使用预定义模板发送邮件。

**请求**

```http
POST /api/v1/utils/email/template
Content-Type: application/json
```

**请求体**

```json
{
  "to": "user@example.com",
  "template": "password-reset",
  "variables": {
    "username": "John",
    "resetLink": "https://example.com/reset/token123"
  },
  "locale": "zh-CN"
}
```

## 通知推送

### 发送推送通知

**请求**

```http
POST /api/v1/utils/notification/push
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "userId": "user_123",
  "title": "新消息",
  "body": "您有一条新消息",
  "data": {
    "type": "message",
    "id": "msg_456"
  },
  "platforms": ["ios", "android", "web"],
  "priority": "high"
}
```

## 数据转换

### CSV 转 JSON

**请求**

```http
POST /api/v1/utils/convert/csv-to-json
Content-Type: multipart/form-data
```

**表单参数**

| 参数 | 类型 | 说明 |
|------|------|------|
| file | file | CSV 文件 |
| headers | boolean | 第一行是否为表头 |
| delimiter | string | 分隔符 |

### JSON 转 CSV

**请求**

```http
POST /api/v1/utils/convert/json-to-csv
Content-Type: application/json
```

**请求体**

```json
{
  "data": [
    {"id": 1, "name": "John"},
    {"id": 2, "name": "Jane"}
  ],
  "fields": ["id", "name"],
  "delimiter": ",",
  "includeHeaders": true
}
```

## 缓存管理

### 清除缓存

**请求**

```http
DELETE /api/v1/utils/cache/clear
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "pattern": "user:*",
  "type": "redis"
}
```

### 获取缓存统计

**请求**

```http
GET /api/v1/utils/cache/stats
Authorization: Bearer YOUR_TOKEN
```

**响应**

```json
{
  "success": true,
  "data": {
    "totalKeys": 1234,
    "memoryUsage": "256MB",
    "hitRate": 0.85,
    "missRate": 0.15,
    "evictions": 42
  }
}
```

## 系统工具

### 健康检查

**请求**

```http
GET /api/v1/utils/health
```

**响应**

```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "version": "1.0.0",
    "uptime": 86400,
    "services": {
      "database": "healthy",
      "redis": "healthy",
      "storage": "healthy"
    },
    "timestamp": "2024-01-01T00:00:00Z"
  }
}
```

### 系统信息

**请求**

```http
GET /api/v1/utils/system/info
Authorization: Bearer YOUR_TOKEN
```

**响应**

```json
{
  "success": true,
  "data": {
    "cpu": {
      "usage": 45.2,
      "cores": 8
    },
    "memory": {
      "total": "16GB",
      "used": "8GB",
      "free": "8GB",
      "usage": 50.0
    },
    "disk": {
      "total": "500GB",
      "used": "200GB",
      "free": "300GB",
      "usage": 40.0
    }
  }
}
```

::: tip 使用建议
- 工具函数 API 通常有速率限制，请合理使用
- 对于批量操作，优先使用批量 API
- 缓存频繁使用的结果以提高性能
- 敏感操作需要额外的权限验证
:::

## 下一步

- 查看[示例代码](/examples/basic)
- 返回[API 介绍](/api/introduction)
- 浏览[核心 API](/api/core)