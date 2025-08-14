# 基础示例

本页面提供各种基础使用示例，帮助您快速上手。

## 快速开始示例

### 初始化项目

```javascript
// 安装 SDK
npm install @yourscope/sdk

// 引入并初始化
import { Client } from '@yourscope/sdk'

const client = new Client({
  apiKey: process.env.API_KEY,
  baseURL: 'https://api.example.com/v1',
  timeout: 5000
})

// 测试连接
async function testConnection() {
  try {
    const health = await client.utils.health()
    console.log('API 状态:', health.status)
  } catch (error) {
    console.error('连接失败:', error)
  }
}

testConnection()
```

## 用户管理示例

### 用户注册流程

```javascript
async function registerUser(userData) {
  try {
    // 1. 验证邮箱
    const emailValidation = await client.utils.validate.email({
      email: userData.email,
      checkMx: true
    })
    
    if (!emailValidation.valid) {
      throw new Error('邮箱地址无效')
    }
    
    // 2. 创建用户
    const user = await client.users.create({
      username: userData.username,
      email: userData.email,
      password: userData.password,
      profile: {
        firstName: userData.firstName,
        lastName: userData.lastName
      }
    })
    
    // 3. 发送欢迎邮件
    await client.utils.email.send({
      to: user.email,
      template: 'welcome',
      variables: {
        username: user.username
      }
    })
    
    console.log('用户注册成功:', user.id)
    return user
    
  } catch (error) {
    console.error('注册失败:', error)
    throw error
  }
}

// 使用示例
registerUser({
  username: 'john_doe',
  email: 'john@example.com',
  password: 'SecurePass123!',
  firstName: 'John',
  lastName: 'Doe'
})
```

### 用户登录和会话管理

```javascript
class AuthManager {
  constructor(client) {
    this.client = client
    this.session = null
  }
  
  async login(email, password) {
    try {
      const response = await this.client.auth.login({
        email,
        password
      })
      
      this.session = {
        token: response.token,
        refreshToken: response.refreshToken,
        expiresAt: response.expiresAt,
        user: response.user
      }
      
      // 保存到本地存储
      localStorage.setItem('session', JSON.stringify(this.session))
      
      // 设置自动刷新
      this.scheduleTokenRefresh()
      
      return this.session
    } catch (error) {
      console.error('登录失败:', error)
      throw error
    }
  }
  
  async refreshToken() {
    if (!this.session?.refreshToken) {
      throw new Error('无刷新令牌')
    }
    
    const response = await this.client.auth.refresh({
      refreshToken: this.session.refreshToken
    })
    
    this.session.token = response.token
    this.session.expiresAt = response.expiresAt
    
    localStorage.setItem('session', JSON.stringify(this.session))
    
    return this.session
  }
  
  scheduleTokenRefresh() {
    if (!this.session) return
    
    const expiresIn = new Date(this.session.expiresAt) - Date.now()
    const refreshTime = expiresIn - (5 * 60 * 1000) // 提前5分钟刷新
    
    setTimeout(() => {
      this.refreshToken()
        .then(() => this.scheduleTokenRefresh())
        .catch(error => console.error('令牌刷新失败:', error))
    }, refreshTime)
  }
  
  logout() {
    this.session = null
    localStorage.removeItem('session')
    this.client.auth.logout()
  }
}

// 使用示例
const authManager = new AuthManager(client)
await authManager.login('john@example.com', 'password')
```

## 文件操作示例

### 文件上传带进度

```javascript
class FileUploader {
  constructor(client) {
    this.client = client
  }
  
  async uploadWithProgress(file, onProgress) {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('folder', 'uploads')
    
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest()
      
      // 监听上传进度
      xhr.upload.addEventListener('progress', (event) => {
        if (event.lengthComputable) {
          const percentComplete = (event.loaded / event.total) * 100
          onProgress?.(percentComplete)
        }
      })
      
      // 监听完成事件
      xhr.addEventListener('load', () => {
        if (xhr.status === 200) {
          const response = JSON.parse(xhr.responseText)
          resolve(response.data)
        } else {
          reject(new Error(`上传失败: ${xhr.status}`))
        }
      })
      
      // 监听错误事件
      xhr.addEventListener('error', () => {
        reject(new Error('网络错误'))
      })
      
      // 发送请求
      xhr.open('POST', `${this.client.baseURL}/files/upload`)
      xhr.setRequestHeader('Authorization', `Bearer ${this.client.apiKey}`)
      xhr.send(formData)
    })
  }
}

// 使用示例
const uploader = new FileUploader(client)
const fileInput = document.getElementById('file-input')

fileInput.addEventListener('change', async (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  try {
    const result = await uploader.uploadWithProgress(file, (progress) => {
      console.log(`上传进度: ${progress.toFixed(2)}%`)
      document.getElementById('progress-bar').style.width = `${progress}%`
    })
    
    console.log('文件上传成功:', result)
  } catch (error) {
    console.error('上传失败:', error)
  }
})
```

### 图片处理示例

```javascript
async function processImage(imageFile) {
  try {
    // 1. 上传原始图片
    const uploaded = await client.files.upload({
      file: imageFile,
      folder: 'images/original'
    })
    
    // 2. 创建缩略图
    const thumbnail = await client.utils.image.resize({
      fileId: uploaded.id,
      width: 200,
      height: 200,
      fit: 'cover',
      quality: 85
    })
    
    // 3. 创建不同尺寸版本
    const sizes = [
      { name: 'small', width: 400 },
      { name: 'medium', width: 800 },
      { name: 'large', width: 1200 }
    ]
    
    const versions = await Promise.all(
      sizes.map(size => 
        client.utils.image.resize({
          fileId: uploaded.id,
          width: size.width,
          fit: 'inside',
          quality: 90
        })
      )
    )
    
    // 4. 转换为 WebP 格式
    const webpVersion = await client.utils.image.convert({
      fileId: uploaded.id,
      format: 'webp',
      quality: 85
    })
    
    return {
      original: uploaded,
      thumbnail,
      versions,
      webp: webpVersion
    }
    
  } catch (error) {
    console.error('图片处理失败:', error)
    throw error
  }
}
```

## 实时通信示例

### WebSocket 聊天室

```javascript
class ChatRoom {
  constructor(roomId, userId) {
    this.roomId = roomId
    this.userId = userId
    this.ws = null
    this.messages = []
    this.listeners = new Map()
  }
  
  connect() {
    const wsUrl = `wss://api.example.com/v1/ws?token=${client.apiKey}`
    this.ws = new WebSocket(wsUrl)
    
    this.ws.onopen = () => {
      console.log('WebSocket 连接成功')
      
      // 订阅聊天室
      this.send({
        type: 'subscribe',
        channel: `chat:${this.roomId}`
      })
      
      // 发送在线状态
      this.send({
        type: 'presence',
        data: {
          userId: this.userId,
          status: 'online'
        }
      })
    }
    
    this.ws.onmessage = (event) => {
      const message = JSON.parse(event.data)
      this.handleMessage(message)
    }
    
    this.ws.onerror = (error) => {
      console.error('WebSocket 错误:', error)
    }
    
    this.ws.onclose = () => {
      console.log('WebSocket 连接关闭')
      // 自动重连
      setTimeout(() => this.connect(), 5000)
    }
  }
  
  handleMessage(message) {
    switch (message.type) {
      case 'message':
        this.messages.push(message.data)
        this.emit('message', message.data)
        break
        
      case 'typing':
        this.emit('typing', message.data)
        break
        
      case 'presence':
        this.emit('presence', message.data)
        break
        
      default:
        console.log('未知消息类型:', message.type)
    }
  }
  
  send(data) {
    if (this.ws?.readyState === WebSocket.OPEN) {
      this.ws.send(JSON.stringify(data))
    }
  }
  
  sendMessage(content) {
    const message = {
      type: 'message',
      channel: `chat:${this.roomId}`,
      data: {
        userId: this.userId,
        content,
        timestamp: new Date().toISOString()
      }
    }
    
    this.send(message)
  }
  
  sendTyping(isTyping) {
    this.send({
      type: 'typing',
      channel: `chat:${this.roomId}`,
      data: {
        userId: this.userId,
        isTyping
      }
    })
  }
  
  on(event, callback) {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, [])
    }
    this.listeners.get(event).push(callback)
  }
  
  emit(event, data) {
    const callbacks = this.listeners.get(event) || []
    callbacks.forEach(callback => callback(data))
  }
  
  disconnect() {
    if (this.ws) {
      this.ws.close()
      this.ws = null
    }
  }
}

// 使用示例
const chatRoom = new ChatRoom('room123', 'user456')

// 监听消息
chatRoom.on('message', (message) => {
  console.log('收到消息:', message)
  displayMessage(message)
})

// 监听输入状态
chatRoom.on('typing', (data) => {
  if (data.isTyping) {
    showTypingIndicator(data.userId)
  } else {
    hideTypingIndicator(data.userId)
  }
})

// 连接聊天室
chatRoom.connect()

// 发送消息
chatRoom.sendMessage('大家好！')

// 显示输入状态
let typingTimer
inputField.addEventListener('input', () => {
  chatRoom.sendTyping(true)
  clearTimeout(typingTimer)
  typingTimer = setTimeout(() => {
    chatRoom.sendTyping(false)
  }, 1000)
})
```

## 数据处理示例

### 批量数据导入

```javascript
async function importCSVData(csvFile) {
  try {
    // 1. 解析 CSV 文件
    const csvData = await client.utils.convert.csvToJson({
      file: csvFile,
      headers: true,
      delimiter: ','
    })
    
    // 2. 验证数据
    const validationErrors = []
    const validData = []
    
    for (let i = 0; i < csvData.length; i++) {
      const row = csvData[i]
      const errors = validateRow(row)
      
      if (errors.length > 0) {
        validationErrors.push({
          row: i + 1,
          errors
        })
      } else {
        validData.push(row)
      }
    }
    
    if (validationErrors.length > 0) {
      console.warn('数据验证警告:', validationErrors)
    }
    
    // 3. 批量导入
    const batchSize = 100
    const results = []
    
    for (let i = 0; i < validData.length; i += batchSize) {
      const batch = validData.slice(i, i + batchSize)
      
      const result = await client.batch.create({
        resource: 'products',
        items: batch,
        options: {
          skipDuplicates: true
        }
      })
      
      results.push(result)
      
      // 显示进度
      const progress = ((i + batch.length) / validData.length) * 100
      console.log(`导入进度: ${progress.toFixed(2)}%`)
    }
    
    // 4. 生成报告
    const report = {
      total: csvData.length,
      imported: validData.length,
      skipped: validationErrors.length,
      errors: validationErrors,
      results
    }
    
    return report
    
  } catch (error) {
    console.error('导入失败:', error)
    throw error
  }
}

function validateRow(row) {
  const errors = []
  
  if (!row.name || row.name.trim() === '') {
    errors.push('名称不能为空')
  }
  
  if (!row.price || isNaN(row.price)) {
    errors.push('价格必须是数字')
  }
  
  if (row.email && !isValidEmail(row.email)) {
    errors.push('邮箱格式不正确')
  }
  
  return errors
}
```

### 数据分析和报表

```javascript
async function generateAnalyticsReport(dateRange) {
  try {
    // 1. 获取原始数据
    const data = await client.analytics.getData({
      dateRange: {
        from: dateRange.from,
        to: dateRange.to
      },
      metrics: ['views', 'clicks', 'conversions'],
      dimensions: ['date', 'source', 'country']
    })
    
    // 2. 聚合计算
    const aggregated = await client.analytics.aggregate({
      data: data.results,
      metrics: [
        { field: 'views', operation: 'sum', alias: 'totalViews' },
        { field: 'clicks', operation: 'sum', alias: 'totalClicks' },
        { field: 'conversions', operation: 'sum', alias: 'totalConversions' },
        { field: 'clicks', operation: 'avg', alias: 'avgClicks' }
      ],
      groupBy: ['source'],
      having: {
        totalViews: { $gte: 100 }
      }
    })
    
    // 3. 计算转化率
    const withConversionRate = aggregated.map(item => ({
      ...item,
      conversionRate: (item.totalConversions / item.totalClicks * 100).toFixed(2)
    }))
    
    // 4. 生成图表数据
    const chartData = {
      labels: withConversionRate.map(item => item.source),
      datasets: [
        {
          label: '浏览量',
          data: withConversionRate.map(item => item.totalViews)
        },
        {
          label: '点击量',
          data: withConversionRate.map(item => item.totalClicks)
        },
        {
          label: '转化率 (%)',
          data: withConversionRate.map(item => item.conversionRate)
        }
      ]
    }
    
    // 5. 导出报表
    const exportUrl = await client.utils.export({
      data: withConversionRate,
      format: 'excel',
      filename: `analytics_${dateRange.from}_${dateRange.to}.xlsx`
    })
    
    return {
      summary: {
        totalViews: withConversionRate.reduce((sum, item) => sum + item.totalViews, 0),
        totalClicks: withConversionRate.reduce((sum, item) => sum + item.totalClicks, 0),
        totalConversions: withConversionRate.reduce((sum, item) => sum + item.totalConversions, 0)
      },
      details: withConversionRate,
      chartData,
      exportUrl
    }
    
  } catch (error) {
    console.error('报表生成失败:', error)
    throw error
  }
}

// 使用示例
const report = await generateAnalyticsReport({
  from: '2024-01-01',
  to: '2024-01-31'
})

console.log('报表摘要:', report.summary)
renderChart(report.chartData)
```

## 错误处理示例

### 全局错误处理器

```javascript
class ErrorHandler {
  constructor() {
    this.handlers = new Map()
    this.defaultHandler = this.logError
  }
  
  register(errorCode, handler) {
    this.handlers.set(errorCode, handler)
  }
  
  handle(error) {
    // 获取错误码
    const errorCode = error.response?.data?.error?.code || error.code || 'UNKNOWN'
    
    // 查找对应的处理器
    const handler = this.handlers.get(errorCode) || this.defaultHandler
    
    // 执行处理
    handler.call(this, error)
  }
  
  logError(error) {
    console.error('未处理的错误:', error)
    
    // 发送错误报告
    this.reportError(error)
  }
  
  async reportError(error) {
    try {
      await client.utils.errorReport({
        error: {
          message: error.message,
          stack: error.stack,
          code: error.code
        },
        context: {
          url: window.location.href,
          userAgent: navigator.userAgent,
          timestamp: new Date().toISOString()
        }
      })
    } catch (reportError) {
      console.error('错误报告失败:', reportError)
    }
  }
}

// 配置错误处理器
const errorHandler = new ErrorHandler()

// 注册特定错误处理
errorHandler.register('AUTHENTICATION_FAILED', (error) => {
  console.log('认证失败，跳转到登录页')
  window.location.href = '/login'
})

errorHandler.register('RATE_LIMIT_EXCEEDED', (error) => {
  const retryAfter = error.response?.headers?.['retry-after'] || 60
  console.log(`请求过于频繁，${retryAfter}秒后重试`)
  showNotification(`请求过于频繁，请${retryAfter}秒后重试`)
})

errorHandler.register('VALIDATION_ERROR', (error) => {
  const details = error.response?.data?.error?.details || []
  details.forEach(detail => {
    showFieldError(detail.field, detail.message)
  })
})

// 全局拦截器
client.interceptors.response.use(
  response => response,
  error => {
    errorHandler.handle(error)
    return Promise.reject(error)
  }
)
```

## 性能优化示例

### 请求缓存

```javascript
class RequestCache {
  constructor(ttl = 60000) { // 默认缓存60秒
    this.cache = new Map()
    this.ttl = ttl
  }
  
  getCacheKey(config) {
    return `${config.method}:${config.url}:${JSON.stringify(config.params)}`
  }
  
  get(config) {
    const key = this.getCacheKey(config)
    const cached = this.cache.get(key)
    
    if (cached && Date.now() - cached.timestamp < this.ttl) {
      return cached.data
    }
    
    return null
  }
  
  set(config, data) {
    const key = this.getCacheKey(config)
    this.cache.set(key, {
      data,
      timestamp: Date.now()
    })
    
    // 自动清理过期缓存
    setTimeout(() => {
      this.cache.delete(key)
    }, this.ttl)
  }
  
  clear() {
    this.cache.clear()
  }
}

// 使用缓存的 API 客户端
class CachedClient extends Client {
  constructor(config) {
    super(config)
    this.cache = new RequestCache(config.cacheTTL)
  }
  
  async request(config) {
    // 只缓存 GET 请求
    if (config.method === 'GET') {
      const cached = this.cache.get(config)
      if (cached) {
        console.log('使用缓存数据')
        return cached
      }
    }
    
    const response = await super.request(config)
    
    if (config.method === 'GET') {
      this.cache.set(config, response)
    }
    
    return response
  }
}

// 使用示例
const cachedClient = new CachedClient({
  apiKey: process.env.API_KEY,
  cacheTTL: 30000 // 缓存30秒
})

// 第一次请求会发送网络请求
const data1 = await cachedClient.users.get('user123')

// 30秒内的相同请求会使用缓存
const data2 = await cachedClient.users.get('user123') // 使用缓存
```

::: tip 最佳实践
1. 始终处理异步操作的错误
2. 使用适当的缓存策略提高性能
3. 实现请求重试机制处理临时失败
4. 对敏感数据进行加密传输
5. 使用 WebSocket 进行实时通信
6. 批量处理大量数据以提高效率
:::

## 下一步

- 查看[进阶示例](/examples/advanced)
- 返回[API 文档](/api/introduction)
- 浏览[指南](/guide/getting-started)