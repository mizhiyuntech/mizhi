# VitePress 文档站点

这是一个使用 VitePress 构建的文档网站示例。

## 功能特点

- 📝 基于 Markdown 的内容编写
- ⚡️ 基于 Vite 的极速开发体验
- 🎨 美观的默认主题
- 🔍 全文搜索功能
- 📱 响应式设计
- 🌐 中文界面

## 项目结构

```
.
├── docs/                      # 文档目录
│   ├── .vitepress/           # VitePress 配置
│   │   └── config.mjs        # 配置文件
│   ├── index.md              # 首页
│   ├── guide/                # 指南目录
│   │   ├── getting-started.md
│   │   ├── installation.md
│   │   └── features.md
│   ├── api/                  # API 文档目录
│   │   ├── introduction.md
│   │   ├── core.md
│   │   └── utils.md
│   └── examples/             # 示例目录
│       ├── basic.md
│       └── advanced.md
├── package.json              # 项目配置
└── README.md                 # 本文件
```

## 快速开始

### 安装依赖

```bash
npm install
```

### 启动开发服务器

```bash
npm run docs:dev
```

然后在浏览器中访问 http://localhost:5173

### 构建生产版本

```bash
npm run docs:build
```

构建后的文件将生成在 `docs/.vitepress/dist` 目录中。

### 预览生产版本

```bash
npm run docs:preview
```

## 文档内容

### 指南部分
- **快速开始** - 快速了解和使用产品
- **安装配置** - 详细的安装和配置说明
- **基础功能** - 核心功能介绍

### API 文档
- **API 介绍** - RESTful API 概述
- **核心 API** - 主要接口文档
- **工具函数** - 辅助功能 API

### 示例代码
- **基础示例** - 常见使用场景
- **进阶示例** - 复杂功能实现

## 自定义配置

### 修改站点信息

编辑 `docs/.vitepress/config.mjs` 文件：

```javascript
export default defineConfig({
  title: '您的站点名称',
  description: '您的站点描述',
  // ... 其他配置
})
```

### 添加新页面

1. 在 `docs` 目录下创建新的 `.md` 文件
2. 在配置文件中添加导航或侧边栏链接
3. 编写 Markdown 内容

### 修改主题

VitePress 支持自定义主题，可以通过修改配置文件来调整外观。

## 部署

### 部署到 GitHub Pages

1. 在 `.github/workflows/` 创建部署脚本
2. 推送代码到 GitHub
3. 启用 GitHub Pages

### 部署到其他平台

- Netlify
- Vercel
- Cloudflare Pages
- 自建服务器

## 常用命令

| 命令 | 说明 |
|------|------|
| `npm run docs:dev` | 启动开发服务器 |
| `npm run docs:build` | 构建生产版本 |
| `npm run docs:preview` | 预览生产版本 |

## 技术栈

- [VitePress](https://vitepress.dev/) - 静态站点生成器
- [Vue 3](https://vuejs.org/) - 渐进式 JavaScript 框架
- [Vite](https://vitejs.dev/) - 下一代前端构建工具

## 许可证

MIT

## 贡献

欢迎提交 Issue 和 Pull Request！

## 联系方式

- GitHub: https://github.com/yourusername/yourrepo
- Email: your-email@example.com