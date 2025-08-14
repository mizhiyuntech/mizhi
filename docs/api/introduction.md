# API 介绍

欢迎使用我们的 API 文档。本文档提供了完整的 API 参考，帮助您快速集成和使用我们的服务。

## API 概述

我们的 API 采用 RESTful 设计风格，支持标准的 HTTP 方法，返回 JSON 格式的数据。

### 基础信息

- **基础 URL**: `https://api.example.com/v1`
- **协议**: HTTPS
- **数据格式**: JSON
- **编码**: UTF-8
- **API 版本**: v1.0.0

## 认证方式

### API Key 认证

在请求头中添加 API Key：

```http
GET /api/v1/users
Authorization: Bearer YOUR_API_KEY
```

### OAuth 2.0

支持标准的 OAuth 2.0 认证流程：

```javascript
// 获取授权码
const authUrl = 'https://api.example.com/oauth/authorize?' +
  'client_id=YOUR_CLIENT_ID&' +
  'redirect_uri=YOUR_REDIRECT_URI&' +
  'response_type=code&' +
  'scope=read write'

// 交换访问令牌
const response = await fetch('https://api.example.com/oauth/token', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    grant_type: 'authorization_code',
    code: 'AUTHORIZATION_CODE',
    client_id: 'YOUR_CLIENT_ID',
    client_secret: 'YOUR_CLIENT_SECRET'
  })
})
```

## 请求格式

### 请求头

所有 API 请求应包含以下请求头：

| Header | 值 | 说明 |
|--------|-----|------|
| `Content-Type` | `application/json` | 请求体格式 |
| `Accept` | `application/json` | 期望的响应格式 |
| `Authorization` | `Bearer TOKEN` | 认证令牌 |
| `X-Request-ID` | `UUID` | 请求追踪 ID（可选） |

### 请求示例

```javascript
const response = await fetch('https://api.example.com/v1/users', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_TOKEN'
  },
  body: JSON.stringify({
    name: 'John Doe',
    email: 'john@example.com',
    role: 'user'
  })
})
```

## 响应格式

### 成功响应

```json
{
  "success": true,
  "data": {
    "id": "123",
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-01-01T00:00:00Z"
  },
  "meta": {
    "request_id": "req_123456",
    "timestamp": "2024-01-01T00:00:00Z"
  }
}
```

### 分页响应

```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "name": "Item 1"
    },
    {
      "id": "2",
      "name": "Item 2"
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 100,
    "total_pages": 5,
    "has_next": true,
    "has_prev": false
  }
}
```

### 错误响应

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "请求参数验证失败",
    "details": [
      {
        "field": "email",
        "message": "邮箱格式不正确"
      }
    ]
  },
  "meta": {
    "request_id": "req_123456",
    "timestamp": "2024-01-01T00:00:00Z"
  }
}
```

## 状态码

我们使用标准的 HTTP 状态码：

| 状态码 | 含义 | 说明 |
|--------|------|------|
| `200` | OK | 请求成功 |
| `201` | Created | 资源创建成功 |
| `204` | No Content | 请求成功，无返回内容 |
| `400` | Bad Request | 请求参数错误 |
| `401` | Unauthorized | 未认证 |
| `403` | Forbidden | 无权限 |
| `404` | Not Found | 资源不存在 |
| `409` | Conflict | 资源冲突 |
| `422` | Unprocessable Entity | 请求格式正确但语义错误 |
| `429` | Too Many Requests | 请求过于频繁 |
| `500` | Internal Server Error | 服务器内部错误 |
| `503` | Service Unavailable | 服务暂时不可用 |

## 错误码

### 通用错误码

| 错误码 | 说明 |
|--------|------|
| `INVALID_REQUEST` | 无效的请求 |
| `AUTHENTICATION_FAILED` | 认证失败 |
| `PERMISSION_DENIED` | 权限不足 |
| `RESOURCE_NOT_FOUND` | 资源不存在 |
| `VALIDATION_ERROR` | 验证错误 |
| `RATE_LIMIT_EXCEEDED` | 超出速率限制 |
| `INTERNAL_ERROR` | 内部错误 |

### 业务错误码

| 错误码 | 说明 |
|--------|------|
| `USER_ALREADY_EXISTS` | 用户已存在 |
| `INVALID_PASSWORD` | 密码不正确 |
| `EMAIL_NOT_VERIFIED` | 邮箱未验证 |
| `SUBSCRIPTION_EXPIRED` | 订阅已过期 |
| `INSUFFICIENT_BALANCE` | 余额不足 |

## 速率限制

API 请求受到速率限制保护：

- **默认限制**: 1000 请求/小时
- **认证用户**: 5000 请求/小时
- **企业用户**: 10000 请求/小时

速率限制信息在响应头中返回：

```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640995200
```

## 版本控制

API 版本通过 URL 路径指定：

- 当前版本: `https://api.example.com/v1`
- 旧版本: `https://api.example.com/v0`（已弃用）

### 版本迁移

当 API 版本更新时，我们会：

1. 提前 6 个月发布弃用通知
2. 保持旧版本运行至少 12 个月
3. 提供详细的迁移指南

## Webhook

支持 Webhook 事件通知：

### 配置 Webhook

```javascript
const webhook = await api.webhooks.create({
  url: 'https://your-server.com/webhook',
  events: ['user.created', 'user.updated', 'order.completed'],
  secret: 'your-webhook-secret'
})
```

### 验证 Webhook 签名

```javascript
const crypto = require('crypto')

function verifyWebhookSignature(payload, signature, secret) {
  const hash = crypto
    .createHmac('sha256', secret)
    .update(payload)
    .digest('hex')
  
  return hash === signature
}
```

## SDK 和工具

### 官方 SDK

- [JavaScript/TypeScript SDK](https://github.com/example/js-sdk)
- [Python SDK](https://github.com/example/python-sdk)
- [Go SDK](https://github.com/example/go-sdk)
- [Java SDK](https://github.com/example/java-sdk)

### 开发工具

- [API Explorer](https://api.example.com/explorer) - 在线测试 API
- [Postman Collection](https://api.example.com/postman) - Postman 集合
- [OpenAPI Spec](https://api.example.com/openapi.json) - OpenAPI 规范

## 最佳实践

1. **使用 HTTPS**: 始终使用 HTTPS 协议
2. **处理错误**: 正确处理各种错误状态
3. **实现重试**: 对临时失败实现指数退避重试
4. **缓存响应**: 适当缓存不经常变化的数据
5. **批量请求**: 使用批量 API 减少请求次数
6. **异步处理**: 对长时间运行的操作使用异步模式

::: warning 安全提示
- 不要在客户端代码中暴露 API 密钥
- 定期轮换 API 密钥
- 使用环境变量存储敏感信息
- 实施适当的 CORS 策略
:::

## 获取帮助

- 📧 技术支持: api-support@example.com
- 💬 开发者社区: [Discord](https://discord.gg/example)
- 📚 更多文档: [开发者中心](https://developers.example.com)

## 下一步

- 查看[核心 API](/api/core) 了解主要接口
- 浏览[工具函数](/api/utils) 查看辅助功能
- 参考[示例代码](/examples/basic) 快速上手