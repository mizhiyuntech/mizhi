# 进阶示例

本页面展示更复杂的使用场景和高级功能示例。

## 微服务架构示例

### 服务注册与发现

```javascript
class ServiceRegistry {
  constructor(config) {
    this.services = new Map()
    this.healthChecks = new Map()
    this.config = config
  }
  
  // 注册服务
  async register(service) {
    const serviceInfo = {
      id: `${service.name}-${Date.now()}`,
      name: service.name,
      version: service.version,
      endpoints: service.endpoints,
      metadata: service.metadata,
      status: 'healthy',
      registeredAt: new Date().toISOString(),
      lastHeartbeat: new Date().toISOString()
    }
    
    this.services.set(serviceInfo.id, serviceInfo)
    
    // 启动健康检查
    this.startHealthCheck(serviceInfo.id)
    
    console.log(`服务注册成功: ${serviceInfo.id}`)
    return serviceInfo
  }
  
  // 发现服务
  discover(serviceName, version = null) {
    const available = []
    
    for (const [id, service] of this.services) {
      if (service.name === serviceName && 
          service.status === 'healthy' &&
          (!version || service.version === version)) {
        available.push(service)
      }
    }
    
    // 负载均衡策略
    return this.loadBalance(available)
  }
  
  // 负载均衡
  loadBalance(services) {
    if (services.length === 0) return null
    
    // 简单轮询
    const index = Math.floor(Math.random() * services.length)
    return services[index]
  }
  
  // 健康检查
  startHealthCheck(serviceId) {
    const interval = setInterval(async () => {
      const service = this.services.get(serviceId)
      if (!service) {
        clearInterval(interval)
        return
      }
      
      try {
        const response = await fetch(`${service.endpoints.health}`)
        if (response.ok) {
          service.status = 'healthy'
          service.lastHeartbeat = new Date().toISOString()
        } else {
          service.status = 'unhealthy'
        }
      } catch (error) {
        service.status = 'unhealthy'
        console.error(`健康检查失败: ${serviceId}`, error)
      }
    }, this.config.healthCheckInterval || 30000)
    
    this.healthChecks.set(serviceId, interval)
  }
  
  // 注销服务
  unregister(serviceId) {
    const interval = this.healthChecks.get(serviceId)
    if (interval) {
      clearInterval(interval)
      this.healthChecks.delete(serviceId)
    }
    
    this.services.delete(serviceId)
    console.log(`服务注销: ${serviceId}`)
  }
}

// 使用示例
const registry = new ServiceRegistry({
  healthCheckInterval: 30000
})

// 注册用户服务
const userService = await registry.register({
  name: 'user-service',
  version: '1.0.0',
  endpoints: {
    base: 'http://localhost:3001',
    health: 'http://localhost:3001/health'
  },
  metadata: {
    region: 'us-east-1',
    zone: 'a'
  }
})

// 发现并调用服务
const service = registry.discover('user-service')
if (service) {
  const response = await fetch(`${service.endpoints.base}/api/users`)
  const users = await response.json()
}
```

### API 网关实现

```javascript
class APIGateway {
  constructor(config) {
    this.config = config
    this.registry = new ServiceRegistry(config)
    this.rateLimiter = new RateLimiter(config.rateLimit)
    this.cache = new Map()
  }
  
  // 路由请求
  async route(request) {
    const { method, path, headers, body } = request
    
    // 1. 认证
    const auth = await this.authenticate(headers)
    if (!auth.valid) {
      return { status: 401, body: { error: 'Unauthorized' } }
    }
    
    // 2. 速率限制
    const rateLimitOk = await this.rateLimiter.check(auth.userId)
    if (!rateLimitOk) {
      return { status: 429, body: { error: 'Too Many Requests' } }
    }
    
    // 3. 路由匹配
    const route = this.matchRoute(path)
    if (!route) {
      return { status: 404, body: { error: 'Not Found' } }
    }
    
    // 4. 服务发现
    const service = this.registry.discover(route.service)
    if (!service) {
      return { status: 503, body: { error: 'Service Unavailable' } }
    }
    
    // 5. 请求转发
    try {
      const response = await this.forward(service, {
        method,
        path: route.targetPath,
        headers: this.buildHeaders(headers, auth),
        body
      })
      
      // 6. 响应处理
      return this.processResponse(response)
      
    } catch (error) {
      console.error('请求转发失败:', error)
      return { status: 500, body: { error: 'Internal Server Error' } }
    }
  }
  
  // 认证
  async authenticate(headers) {
    const token = headers['authorization']?.replace('Bearer ', '')
    if (!token) {
      return { valid: false }
    }
    
    try {
      const decoded = jwt.verify(token, this.config.jwtSecret)
      return { valid: true, userId: decoded.userId, roles: decoded.roles }
    } catch (error) {
      return { valid: false }
    }
  }
  
  // 路由匹配
  matchRoute(path) {
    for (const route of this.config.routes) {
      const regex = new RegExp(route.pattern)
      const match = path.match(regex)
      
      if (match) {
        return {
          service: route.service,
          targetPath: route.rewrite ? route.rewrite(path, match) : path
        }
      }
    }
    
    return null
  }
  
  // 请求转发
  async forward(service, request) {
    const url = `${service.endpoints.base}${request.path}`
    
    const response = await fetch(url, {
      method: request.method,
      headers: request.headers,
      body: request.body ? JSON.stringify(request.body) : undefined
    })
    
    return {
      status: response.status,
      headers: Object.fromEntries(response.headers),
      body: await response.json()
    }
  }
  
  // 构建请求头
  buildHeaders(originalHeaders, auth) {
    return {
      ...originalHeaders,
      'X-User-Id': auth.userId,
      'X-User-Roles': auth.roles.join(','),
      'X-Request-Id': crypto.randomUUID(),
      'X-Forwarded-For': originalHeaders['x-forwarded-for'] || originalHeaders['x-real-ip']
    }
  }
  
  // 处理响应
  processResponse(response) {
    // 添加 CORS 头
    response.headers = {
      ...response.headers,
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
      'Access-Control-Allow-Headers': 'Content-Type, Authorization'
    }
    
    return response
  }
}

// 配置示例
const gateway = new APIGateway({
  jwtSecret: process.env.JWT_SECRET,
  routes: [
    {
      pattern: '^/api/users(/.*)?$',
      service: 'user-service',
      rewrite: (path) => path.replace('/api/users', '/users')
    },
    {
      pattern: '^/api/products(/.*)?$',
      service: 'product-service',
      rewrite: (path) => path.replace('/api/products', '/products')
    }
  ],
  rateLimit: {
    windowMs: 60000,
    maxRequests: 100
  }
})
```

## 分布式任务队列

### 任务队列实现

```javascript
class TaskQueue {
  constructor(config) {
    this.config = config
    this.queues = new Map()
    this.workers = new Map()
    this.deadLetterQueue = []
  }
  
  // 创建队列
  createQueue(name, options = {}) {
    const queue = {
      name,
      tasks: [],
      processing: new Set(),
      completed: [],
      failed: [],
      options: {
        maxRetries: options.maxRetries || 3,
        retryDelay: options.retryDelay || 1000,
        timeout: options.timeout || 30000,
        concurrency: options.concurrency || 5
      }
    }
    
    this.queues.set(name, queue)
    return queue
  }
  
  // 添加任务
  async enqueue(queueName, task) {
    const queue = this.queues.get(queueName)
    if (!queue) {
      throw new Error(`队列不存在: ${queueName}`)
    }
    
    const taskInfo = {
      id: crypto.randomUUID(),
      data: task,
      status: 'pending',
      attempts: 0,
      createdAt: Date.now(),
      scheduledAt: task.scheduledAt || Date.now()
    }
    
    queue.tasks.push(taskInfo)
    
    // 触发处理
    this.processQueue(queueName)
    
    return taskInfo
  }
  
  // 批量添加任务
  async enqueueBatch(queueName, tasks) {
    const results = []
    
    for (const task of tasks) {
      const result = await this.enqueue(queueName, task)
      results.push(result)
    }
    
    return results
  }
  
  // 注册处理器
  registerWorker(queueName, handler) {
    if (!this.workers.has(queueName)) {
      this.workers.set(queueName, [])
    }
    
    this.workers.get(queueName).push(handler)
    
    // 开始处理队列
    this.startWorker(queueName)
  }
  
  // 启动工作进程
  startWorker(queueName) {
    const queue = this.queues.get(queueName)
    if (!queue) return
    
    setInterval(() => {
      this.processQueue(queueName)
    }, 1000)
  }
  
  // 处理队列
  async processQueue(queueName) {
    const queue = this.queues.get(queueName)
    const handlers = this.workers.get(queueName) || []
    
    if (!queue || handlers.length === 0) return
    
    // 获取待处理任务
    const now = Date.now()
    const pending = queue.tasks.filter(task => 
      task.status === 'pending' && 
      task.scheduledAt <= now &&
      !queue.processing.has(task.id)
    )
    
    // 并发限制
    const available = queue.options.concurrency - queue.processing.size
    const toProcess = pending.slice(0, Math.max(0, available))
    
    // 处理任务
    for (const task of toProcess) {
      this.processTask(queue, task, handlers)
    }
  }
  
  // 处理单个任务
  async processTask(queue, task, handlers) {
    queue.processing.add(task.id)
    task.status = 'processing'
    task.startedAt = Date.now()
    
    try {
      // 设置超时
      const timeoutPromise = new Promise((_, reject) => {
        setTimeout(() => reject(new Error('Task timeout')), queue.options.timeout)
      })
      
      // 执行处理器
      const handlerPromise = this.executeHandlers(handlers, task.data)
      
      const result = await Promise.race([handlerPromise, timeoutPromise])
      
      // 任务成功
      task.status = 'completed'
      task.result = result
      task.completedAt = Date.now()
      queue.completed.push(task)
      
      // 从队列中移除
      const index = queue.tasks.indexOf(task)
      if (index > -1) {
        queue.tasks.splice(index, 1)
      }
      
    } catch (error) {
      console.error(`任务处理失败: ${task.id}`, error)
      
      task.attempts++
      task.lastError = error.message
      
      if (task.attempts < queue.options.maxRetries) {
        // 重试
        task.status = 'pending'
        task.scheduledAt = Date.now() + queue.options.retryDelay * task.attempts
      } else {
        // 失败
        task.status = 'failed'
        task.failedAt = Date.now()
        queue.failed.push(task)
        
        // 移至死信队列
        this.deadLetterQueue.push(task)
        
        // 从队列中移除
        const index = queue.tasks.indexOf(task)
        if (index > -1) {
          queue.tasks.splice(index, 1)
        }
      }
    } finally {
      queue.processing.delete(task.id)
    }
  }
  
  // 执行处理器
  async executeHandlers(handlers, data) {
    for (const handler of handlers) {
      const result = await handler(data)
      if (result === false) {
        throw new Error('Handler rejected task')
      }
    }
    
    return true
  }
  
  // 获取队列状态
  getQueueStatus(queueName) {
    const queue = this.queues.get(queueName)
    if (!queue) return null
    
    return {
      name: queue.name,
      pending: queue.tasks.filter(t => t.status === 'pending').length,
      processing: queue.processing.size,
      completed: queue.completed.length,
      failed: queue.failed.length,
      options: queue.options
    }
  }
}

// 使用示例
const taskQueue = new TaskQueue()

// 创建邮件队列
taskQueue.createQueue('email', {
  maxRetries: 3,
  retryDelay: 5000,
  concurrency: 10
})

// 注册邮件处理器
taskQueue.registerWorker('email', async (task) => {
  console.log('发送邮件:', task)
  
  await client.utils.email.send({
    to: task.to,
    subject: task.subject,
    html: task.html
  })
  
  return true
})

// 添加邮件任务
await taskQueue.enqueue('email', {
  to: 'user@example.com',
  subject: '欢迎',
  html: '<h1>欢迎使用我们的服务</h1>'
})

// 延迟任务
await taskQueue.enqueue('email', {
  to: 'user@example.com',
  subject: '提醒',
  html: '<p>这是一个延迟发送的提醒</p>',
  scheduledAt: Date.now() + 3600000 // 1小时后
})
```

## 事件驱动架构

### 事件总线实现

```javascript
class EventBus {
  constructor() {
    this.events = new Map()
    this.history = []
    this.middlewares = []
  }
  
  // 订阅事件
  on(eventName, handler, options = {}) {
    if (!this.events.has(eventName)) {
      this.events.set(eventName, [])
    }
    
    const subscription = {
      id: crypto.randomUUID(),
      handler,
      options: {
        once: options.once || false,
        priority: options.priority || 0,
        filter: options.filter || null
      }
    }
    
    const handlers = this.events.get(eventName)
    handlers.push(subscription)
    
    // 按优先级排序
    handlers.sort((a, b) => b.options.priority - a.options.priority)
    
    // 返回取消订阅函数
    return () => this.off(eventName, subscription.id)
  }
  
  // 一次性订阅
  once(eventName, handler) {
    return this.on(eventName, handler, { once: true })
  }
  
  // 取消订阅
  off(eventName, subscriptionId) {
    const handlers = this.events.get(eventName)
    if (!handlers) return
    
    const index = handlers.findIndex(h => h.id === subscriptionId)
    if (index > -1) {
      handlers.splice(index, 1)
    }
  }
  
  // 发布事件
  async emit(eventName, data, metadata = {}) {
    const event = {
      id: crypto.randomUUID(),
      name: eventName,
      data,
      metadata: {
        ...metadata,
        timestamp: Date.now(),
        source: metadata.source || 'unknown'
      }
    }
    
    // 执行中间件
    for (const middleware of this.middlewares) {
      const result = await middleware(event)
      if (result === false) {
        console.log('事件被中间件阻止:', eventName)
        return
      }
    }
    
    // 记录历史
    this.history.push(event)
    if (this.history.length > 1000) {
      this.history.shift()
    }
    
    // 获取处理器
    const handlers = this.events.get(eventName) || []
    const wildcardHandlers = this.events.get('*') || []
    const allHandlers = [...handlers, ...wildcardHandlers]
    
    // 执行处理器
    const results = []
    for (const subscription of allHandlers) {
      // 应用过滤器
      if (subscription.options.filter && !subscription.options.filter(event)) {
        continue
      }
      
      try {
        const result = await subscription.handler(event)
        results.push(result)
        
        // 一次性订阅
        if (subscription.options.once) {
          this.off(eventName, subscription.id)
        }
      } catch (error) {
        console.error(`事件处理器错误: ${eventName}`, error)
      }
    }
    
    return results
  }
  
  // 添加中间件
  use(middleware) {
    this.middlewares.push(middleware)
  }
  
  // 等待事件
  waitFor(eventName, timeout = 5000) {
    return new Promise((resolve, reject) => {
      const timer = setTimeout(() => {
        this.off(eventName, subscription)
        reject(new Error(`等待事件超时: ${eventName}`))
      }, timeout)
      
      const subscription = this.once(eventName, (event) => {
        clearTimeout(timer)
        resolve(event)
      })
    })
  }
  
  // 获取事件历史
  getHistory(filter = {}) {
    let history = [...this.history]
    
    if (filter.eventName) {
      history = history.filter(e => e.name === filter.eventName)
    }
    
    if (filter.since) {
      history = history.filter(e => e.metadata.timestamp >= filter.since)
    }
    
    if (filter.source) {
      history = history.filter(e => e.metadata.source === filter.source)
    }
    
    return history
  }
}

// 使用示例
const eventBus = new EventBus()

// 添加日志中间件
eventBus.use(async (event) => {
  console.log(`[Event] ${event.name}:`, event.data)
  return true
})

// 订阅用户注册事件
eventBus.on('user.registered', async (event) => {
  const user = event.data
  
  // 发送欢迎邮件
  await taskQueue.enqueue('email', {
    to: user.email,
    subject: '欢迎',
    template: 'welcome',
    data: { username: user.username }
  })
})

// 高优先级处理器
eventBus.on('user.registered', async (event) => {
  // 创建用户档案
  await createUserProfile(event.data)
}, { priority: 10 })

// 条件订阅
eventBus.on('order.created', async (event) => {
  // 只处理大额订单
  await notifyManager(event.data)
}, {
  filter: (event) => event.data.amount > 1000
})

// 发布事件
await eventBus.emit('user.registered', {
  id: 'user123',
  username: 'john_doe',
  email: 'john@example.com'
}, {
  source: 'registration-service'
})
```

## 状态机实现

### 工作流状态机

```javascript
class StateMachine {
  constructor(config) {
    this.states = config.states
    this.transitions = config.transitions
    this.currentState = config.initialState
    this.context = config.context || {}
    this.history = []
    this.listeners = new Map()
  }
  
  // 获取当前状态
  getState() {
    return this.currentState
  }
  
  // 获取可用转换
  getAvailableTransitions() {
    return this.transitions.filter(t => t.from === this.currentState)
  }
  
  // 执行转换
  async transition(action, payload = {}) {
    const transition = this.transitions.find(t => 
      t.from === this.currentState && t.action === action
    )
    
    if (!transition) {
      throw new Error(`无效的转换: ${this.currentState} -> ${action}`)
    }
    
    // 检查守卫条件
    if (transition.guard) {
      const canTransition = await transition.guard(this.context, payload)
      if (!canTransition) {
        throw new Error(`转换被守卫阻止: ${action}`)
      }
    }
    
    const fromState = this.currentState
    const toState = transition.to
    
    // 执行退出动作
    const fromStateConfig = this.states[fromState]
    if (fromStateConfig?.onExit) {
      await fromStateConfig.onExit(this.context, payload)
    }
    
    // 执行转换动作
    if (transition.onTransition) {
      await transition.onTransition(this.context, payload)
    }
    
    // 更新状态
    this.currentState = toState
    
    // 执行进入动作
    const toStateConfig = this.states[toState]
    if (toStateConfig?.onEnter) {
      await toStateConfig.onEnter(this.context, payload)
    }
    
    // 记录历史
    this.history.push({
      from: fromState,
      to: toState,
      action,
      payload,
      timestamp: Date.now()
    })
    
    // 触发监听器
    this.emit('transition', {
      from: fromState,
      to: toState,
      action,
      payload
    })
    
    return toState
  }
  
  // 监听状态变化
  on(event, callback) {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, [])
    }
    this.listeners.get(event).push(callback)
  }
  
  // 触发事件
  emit(event, data) {
    const callbacks = this.listeners.get(event) || []
    callbacks.forEach(callback => callback(data))
  }
  
  // 获取状态历史
  getHistory() {
    return this.history
  }
  
  // 重置状态机
  reset() {
    this.currentState = this.config.initialState
    this.context = this.config.context || {}
    this.history = []
  }
}

// 订单状态机示例
const orderStateMachine = new StateMachine({
  initialState: 'pending',
  context: {
    orderId: null,
    items: [],
    total: 0,
    customer: null
  },
  states: {
    pending: {
      onEnter: async (context) => {
        console.log('订单创建:', context.orderId)
      }
    },
    confirmed: {
      onEnter: async (context) => {
        // 扣减库存
        await deductInventory(context.items)
      }
    },
    paid: {
      onEnter: async (context) => {
        // 记录支付
        await recordPayment(context)
      }
    },
    shipped: {
      onEnter: async (context) => {
        // 发送通知
        await notifyCustomer(context.customer, 'shipped')
      }
    },
    delivered: {
      onEnter: async (context) => {
        // 完成订单
        await completeOrder(context.orderId)
      }
    },
    cancelled: {
      onEnter: async (context) => {
        // 退还库存
        await restoreInventory(context.items)
        // 退款
        if (context.paid) {
          await processRefund(context)
        }
      }
    }
  },
  transitions: [
    {
      from: 'pending',
      to: 'confirmed',
      action: 'confirm',
      guard: async (context) => {
        // 检查库存
        return await checkInventory(context.items)
      }
    },
    {
      from: 'confirmed',
      to: 'paid',
      action: 'pay',
      onTransition: async (context, payload) => {
        context.paymentMethod = payload.method
        context.transactionId = payload.transactionId
        context.paid = true
      }
    },
    {
      from: 'paid',
      to: 'shipped',
      action: 'ship',
      onTransition: async (context, payload) => {
        context.trackingNumber = payload.trackingNumber
        context.shippedAt = Date.now()
      }
    },
    {
      from: 'shipped',
      to: 'delivered',
      action: 'deliver',
      onTransition: async (context) => {
        context.deliveredAt = Date.now()
      }
    },
    {
      from: ['pending', 'confirmed'],
      to: 'cancelled',
      action: 'cancel'
    },
    {
      from: 'paid',
      to: 'cancelled',
      action: 'cancel',
      guard: async (context) => {
        // 只能在发货前取消
        return !context.shippedAt
      }
    }
  ]
})

// 使用状态机
orderStateMachine.context.orderId = 'order123'
orderStateMachine.context.items = [{ id: 'item1', quantity: 2 }]

// 监听状态变化
orderStateMachine.on('transition', (event) => {
  console.log(`订单状态变更: ${event.from} -> ${event.to}`)
})

// 执行状态转换
await orderStateMachine.transition('confirm')
await orderStateMachine.transition('pay', {
  method: 'credit_card',
  transactionId: 'txn123'
})
await orderStateMachine.transition('ship', {
  trackingNumber: 'TRACK123'
})
```

## GraphQL 集成

### GraphQL 客户端封装

```javascript
class GraphQLClient {
  constructor(config) {
    this.endpoint = config.endpoint
    this.headers = config.headers || {}
    this.cache = new Map()
  }
  
  // 执行查询
  async query(query, variables = {}, options = {}) {
    const cacheKey = this.getCacheKey(query, variables)
    
    // 检查缓存
    if (options.cache !== false) {
      const cached = this.cache.get(cacheKey)
      if (cached && Date.now() - cached.timestamp < (options.cacheTTL || 60000)) {
        return cached.data
      }
    }
    
    const response = await fetch(this.endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...this.headers
      },
      body: JSON.stringify({
        query,
        variables
      })
    })
    
    const result = await response.json()
    
    if (result.errors) {
      throw new GraphQLError(result.errors)
    }
    
    // 缓存结果
    if (options.cache !== false) {
      this.cache.set(cacheKey, {
        data: result.data,
        timestamp: Date.now()
      })
    }
    
    return result.data
  }
  
  // 执行变更
  async mutate(mutation, variables = {}) {
    const response = await fetch(this.endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...this.headers
      },
      body: JSON.stringify({
        query: mutation,
        variables
      })
    })
    
    const result = await response.json()
    
    if (result.errors) {
      throw new GraphQLError(result.errors)
    }
    
    // 清理相关缓存
    this.invalidateCache()
    
    return result.data
  }
  
  // 订阅
  subscribe(subscription, variables = {}) {
    const ws = new WebSocket(this.endpoint.replace('http', 'ws'))
    
    ws.onopen = () => {
      ws.send(JSON.stringify({
        type: 'start',
        payload: {
          query: subscription,
          variables
        }
      }))
    }
    
    return {
      ws,
      on: (callback) => {
        ws.onmessage = (event) => {
          const message = JSON.parse(event.data)
          if (message.type === 'data') {
            callback(message.payload.data)
          }
        }
      },
      unsubscribe: () => {
        ws.close()
      }
    }
  }
  
  // 批量查询
  async batch(queries) {
    const batchQuery = queries.map((q, i) => `
      query${i}: ${q.query}
    `).join('\n')
    
    const result = await this.query(`{${batchQuery}}`)
    
    return queries.map((q, i) => result[`query${i}`])
  }
  
  // 获取缓存键
  getCacheKey(query, variables) {
    return `${query}:${JSON.stringify(variables)}`
  }
  
  // 清理缓存
  invalidateCache(pattern) {
    if (pattern) {
      for (const [key] of this.cache) {
        if (key.includes(pattern)) {
          this.cache.delete(key)
        }
      }
    } else {
      this.cache.clear()
    }
  }
}

// 使用示例
const graphql = new GraphQLClient({
  endpoint: 'https://api.example.com/graphql',
  headers: {
    'Authorization': `Bearer ${token}`
  }
})

// 查询
const users = await graphql.query(`
  query GetUsers($limit: Int) {
    users(limit: $limit) {
      id
      name
      email
      posts {
        id
        title
      }
    }
  }
`, { limit: 10 })

// 变更
const newUser = await graphql.mutate(`
  mutation CreateUser($input: UserInput!) {
    createUser(input: $input) {
      id
      name
      email
    }
  }
`, {
  input: {
    name: 'John Doe',
    email: 'john@example.com'
  }
})

// 订阅
const subscription = graphql.subscribe(`
  subscription OnUserCreated {
    userCreated {
      id
      name
    }
  }
`)

subscription.on((data) => {
  console.log('新用户创建:', data.userCreated)
})
```

::: tip 高级技巧
1. 使用服务注册与发现实现微服务架构
2. 实现 API 网关统一管理服务访问
3. 使用任务队列处理异步任务和定时任务
4. 采用事件驱动架构解耦系统组件
5. 使用状态机管理复杂的业务流程
6. 集成 GraphQL 提供灵活的数据查询
:::

## 总结

这些进阶示例展示了如何构建企业级的应用架构，包括：

- **微服务架构**：服务注册、发现、网关
- **异步处理**：任务队列、事件总线
- **流程控制**：状态机、工作流
- **数据查询**：GraphQL 集成

根据您的具体需求，可以选择和组合这些模式来构建健壮的应用系统。

## 下一步

- 返回[基础示例](/examples/basic)
- 查看[API 文档](/api/introduction)
- 浏览[指南](/guide/getting-started)