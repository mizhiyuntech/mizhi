# 核心 API

本页面详细介绍核心 API 接口。

## 用户管理 API

### 创建用户

创建新用户账号。

**请求**

```http
POST /api/v1/users
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "SecurePassword123!",
  "profile": {
    "firstName": "John",
    "lastName": "Doe",
    "phone": "+1234567890",
    "timezone": "America/New_York"
  }
}
```

**响应**

```json
{
  "success": true,
  "data": {
    "id": "user_123456",
    "username": "john_doe",
    "email": "john@example.com",
    "profile": {
      "firstName": "John",
      "lastName": "Doe",
      "phone": "+1234567890",
      "timezone": "America/New_York"
    },
    "status": "active",
    "emailVerified": false,
    "createdAt": "2024-01-01T00:00:00Z",
    "updatedAt": "2024-01-01T00:00:00Z"
  }
}
```

### 获取用户信息

获取指定用户的详细信息。

**请求**

```http
GET /api/v1/users/{userId}
Authorization: Bearer YOUR_TOKEN
```

**路径参数**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| userId | string | 是 | 用户 ID |

**响应示例**

```json
{
  "success": true,
  "data": {
    "id": "user_123456",
    "username": "john_doe",
    "email": "john@example.com",
    "profile": {
      "firstName": "John",
      "lastName": "Doe",
      "avatar": "https://cdn.example.com/avatars/john_doe.jpg",
      "bio": "Software Developer",
      "location": "New York, USA"
    },
    "stats": {
      "posts": 42,
      "followers": 1234,
      "following": 567
    },
    "preferences": {
      "language": "en",
      "theme": "dark",
      "notifications": {
        "email": true,
        "push": false
      }
    }
  }
}
```

### 更新用户

更新用户信息。

**请求**

```http
PATCH /api/v1/users/{userId}
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "profile": {
    "firstName": "Jane",
    "bio": "Full Stack Developer"
  },
  "preferences": {
    "theme": "light"
  }
}
```

### 删除用户

删除用户账号（软删除）。

**请求**

```http
DELETE /api/v1/users/{userId}
Authorization: Bearer YOUR_TOKEN
```

## 项目管理 API

### 创建项目

**请求**

```http
POST /api/v1/projects
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "name": "My Project",
  "description": "项目描述",
  "visibility": "private",
  "settings": {
    "allowComments": true,
    "requireApproval": false
  },
  "tags": ["web", "javascript", "react"]
}
```

### 获取项目列表

**请求**

```http
GET /api/v1/projects?page=1&limit=20&sort=created_at&order=desc
Authorization: Bearer YOUR_TOKEN
```

**查询参数**

| 参数 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| page | integer | 1 | 页码 |
| limit | integer | 20 | 每页数量 |
| sort | string | created_at | 排序字段 |
| order | string | desc | 排序方向 (asc/desc) |
| search | string | - | 搜索关键词 |
| status | string | - | 项目状态 |
| tags | array | - | 标签过滤 |

### 更新项目

**请求**

```http
PUT /api/v1/projects/{projectId}
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

## 文件操作 API

### 上传文件

**请求**

```http
POST /api/v1/files/upload
Content-Type: multipart/form-data
Authorization: Bearer YOUR_TOKEN
```

**表单字段**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| file | file | 是 | 要上传的文件 |
| folder | string | 否 | 目标文件夹 |
| public | boolean | 否 | 是否公开 |

**响应**

```json
{
  "success": true,
  "data": {
    "id": "file_789012",
    "name": "document.pdf",
    "size": 1048576,
    "mimeType": "application/pdf",
    "url": "https://cdn.example.com/files/document.pdf",
    "thumbnail": "https://cdn.example.com/thumbnails/document.jpg",
    "metadata": {
      "width": null,
      "height": null,
      "duration": null
    },
    "uploadedAt": "2024-01-01T00:00:00Z"
  }
}
```

### 批量上传

支持同时上传多个文件。

**请求**

```http
POST /api/v1/files/batch-upload
Content-Type: multipart/form-data
Authorization: Bearer YOUR_TOKEN
```

### 获取文件信息

**请求**

```http
GET /api/v1/files/{fileId}
Authorization: Bearer YOUR_TOKEN
```

### 删除文件

**请求**

```http
DELETE /api/v1/files/{fileId}
Authorization: Bearer YOUR_TOKEN
```

## 数据查询 API

### 高级搜索

执行复杂的数据查询。

**请求**

```http
POST /api/v1/search
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "query": "javascript",
  "filters": {
    "type": ["article", "tutorial"],
    "author": "john_doe",
    "dateRange": {
      "from": "2024-01-01",
      "to": "2024-12-31"
    },
    "tags": ["web", "frontend"]
  },
  "sort": {
    "field": "relevance",
    "order": "desc"
  },
  "pagination": {
    "page": 1,
    "limit": 20
  },
  "highlight": {
    "fields": ["title", "content"],
    "preTag": "<mark>",
    "postTag": "</mark>"
  }
}
```

### 聚合查询

获取数据统计信息。

**请求**

```http
POST /api/v1/analytics/aggregate
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "metrics": [
    {
      "field": "views",
      "operation": "sum",
      "alias": "totalViews"
    },
    {
      "field": "likes",
      "operation": "avg",
      "alias": "avgLikes"
    }
  ],
  "groupBy": ["category", "date"],
  "dateRange": {
    "from": "2024-01-01",
    "to": "2024-01-31",
    "interval": "day"
  },
  "having": {
    "totalViews": {
      "$gte": 100
    }
  }
}
```

## 实时通信 API

### WebSocket 连接

建立 WebSocket 连接进行实时通信。

**连接 URL**

```
wss://api.example.com/v1/ws?token=YOUR_TOKEN
```

**消息格式**

```json
{
  "type": "subscribe",
  "channel": "notifications",
  "data": {
    "userId": "user_123456"
  }
}
```

**事件类型**

| 事件 | 说明 | 数据格式 |
|------|------|----------|
| `message` | 新消息 | `{sender, content, timestamp}` |
| `notification` | 系统通知 | `{type, title, body, action}` |
| `presence` | 用户在线状态 | `{userId, status, lastSeen}` |
| `typing` | 输入状态 | `{userId, isTyping}` |

### 发送消息

通过 WebSocket 发送消息。

```javascript
// JavaScript 示例
const ws = new WebSocket('wss://api.example.com/v1/ws?token=YOUR_TOKEN')

ws.onopen = () => {
  // 发送消息
  ws.send(JSON.stringify({
    type: 'message',
    channel: 'chat-room-1',
    data: {
      content: 'Hello, World!',
      mentions: ['user_456']
    }
  }))
}

ws.onmessage = (event) => {
  const message = JSON.parse(event.data)
  console.log('收到消息:', message)
}
```

## 批量操作 API

### 批量创建

一次创建多个资源。

**请求**

```http
POST /api/v1/batch/create
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "resource": "users",
  "items": [
    {
      "username": "user1",
      "email": "user1@example.com"
    },
    {
      "username": "user2",
      "email": "user2@example.com"
    }
  ],
  "options": {
    "skipDuplicates": true,
    "validateAll": true
  }
}
```

### 批量更新

**请求**

```http
PATCH /api/v1/batch/update
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "resource": "posts",
  "filter": {
    "status": "draft",
    "authorId": "user_123"
  },
  "update": {
    "status": "published",
    "publishedAt": "2024-01-01T00:00:00Z"
  }
}
```

### 批量删除

**请求**

```http
DELETE /api/v1/batch/delete
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "resource": "comments",
  "ids": ["comment_1", "comment_2", "comment_3"]
}
```

## 导入导出 API

### 数据导出

导出数据为指定格式。

**请求**

```http
POST /api/v1/export
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN
```

**请求体**

```json
{
  "resource": "users",
  "format": "csv",
  "fields": ["id", "username", "email", "createdAt"],
  "filter": {
    "status": "active"
  },
  "options": {
    "includeHeaders": true,
    "delimiter": ",",
    "encoding": "utf-8"
  }
}
```

### 数据导入

从文件导入数据。

**请求**

```http
POST /api/v1/import
Content-Type: multipart/form-data
Authorization: Bearer YOUR_TOKEN
```

**表单字段**

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| file | file | 是 | 导入文件 |
| resource | string | 是 | 目标资源类型 |
| mapping | json | 否 | 字段映射配置 |
| options | json | 否 | 导入选项 |

## 错误处理

所有 API 都遵循统一的错误处理规范：

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "请求参数验证失败",
    "details": [
      {
        "field": "email",
        "message": "邮箱格式不正确",
        "value": "invalid-email"
      }
    ],
    "stack": "Error stack trace (仅在开发环境)"
  },
  "meta": {
    "request_id": "req_abc123",
    "timestamp": "2024-01-01T00:00:00Z"
  }
}
```

::: tip 提示
- 所有时间戳均使用 ISO 8601 格式
- ID 字段使用字符串类型以支持各种 ID 格式
- 分页响应始终包含总数和页码信息
- 支持部分更新（PATCH）和完整更新（PUT）
:::

## 下一步

- 查看[工具函数 API](/api/utils)
- 浏览[示例代码](/examples/basic)
- 返回[API 介绍](/api/introduction)