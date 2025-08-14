# 安装配置

本页面将详细介绍如何安装和配置我们的产品。

## 系统要求

### 最低要求

| 项目 | 要求 |
|------|------|
| Node.js | >= 16.0.0 |
| npm | >= 7.0.0 |
| 内存 | >= 4GB |
| 磁盘空间 | >= 2GB |

### 推荐配置

| 项目 | 推荐 |
|------|------|
| Node.js | >= 18.0.0 |
| npm | >= 9.0.0 |
| 内存 | >= 8GB |
| 磁盘空间 | >= 5GB |

## 安装方式

### 使用 npm（推荐）

```bash
# 全局安装
npm install -g @yourscope/yourpackage

# 项目内安装
npm install @yourscope/yourpackage --save
```

### 使用 yarn

```bash
# 全局安装
yarn global add @yourscope/yourpackage

# 项目内安装
yarn add @yourscope/yourpackage
```

### 使用 pnpm

```bash
# 全局安装
pnpm add -g @yourscope/yourpackage

# 项目内安装
pnpm add @yourscope/yourpackage
```

## 配置文件

### 基础配置

在项目根目录创建 `config.js` 文件：

```javascript
module.exports = {
  // 基础配置
  base: {
    name: 'My App',
    version: '1.0.0',
    port: 3000
  },
  
  // 数据库配置
  database: {
    host: 'localhost',
    port: 5432,
    username: 'admin',
    password: 'password',
    database: 'myapp'
  },
  
  // API 配置
  api: {
    baseURL: 'https://api.example.com',
    timeout: 5000,
    retries: 3
  }
}
```

### 环境变量

创建 `.env` 文件管理环境变量：

```env
# 应用配置
APP_NAME=MyApp
APP_PORT=3000
APP_ENV=development

# 数据库配置
DB_HOST=localhost
DB_PORT=5432
DB_USER=admin
DB_PASS=password
DB_NAME=myapp

# API 密钥
API_KEY=your-api-key-here
SECRET_KEY=your-secret-key-here
```

## 高级配置

### 自定义构建配置

创建 `build.config.js`：

```javascript
export default {
  // 构建目标
  target: 'es2020',
  
  // 输出配置
  output: {
    dir: 'dist',
    format: 'esm',
    sourcemap: true
  },
  
  // 优化选项
  optimize: {
    minify: true,
    treeshake: true,
    bundle: true
  },
  
  // 插件配置
  plugins: [
    // 添加您的插件
  ]
}
```

### 代理配置

配置开发服务器代理：

```javascript
// vite.config.js
export default {
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8080',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, '')
      }
    }
  }
}
```

## 验证安装

运行以下命令验证安装是否成功：

```bash
# 检查版本
yourpackage --version

# 运行测试
npm test

# 启动开发服务器
npm run dev
```

## 常见问题

### 安装失败

如果遇到安装失败，请尝试：

1. 清除 npm 缓存：`npm cache clean --force`
2. 删除 node_modules：`rm -rf node_modules`
3. 重新安装：`npm install`

### 权限问题

在 macOS 或 Linux 上可能需要使用 sudo：

```bash
sudo npm install -g @yourscope/yourpackage
```

### 网络问题

如果在中国大陆，可以使用淘宝镜像：

```bash
npm config set registry https://registry.npmmirror.com
```

::: info 提示
配置文件支持热重载，修改后会自动生效，无需重启服务。
:::

## 下一步

完成安装配置后，您可以：

- 了解[基础功能](/guide/features)
- 查看[API 文档](/api/introduction)
- 浏览[示例代码](/examples/basic)