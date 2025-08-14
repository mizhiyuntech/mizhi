# 快速开始

欢迎使用我们的文档！本指南将帮助您快速了解和使用我们的产品。

## 前置要求

在开始之前，请确保您的开发环境满足以下要求：

- Node.js 版本 >= 16.0
- npm 或 yarn 包管理器
- 一个现代的代码编辑器（推荐 VS Code）

## 安装步骤

### 1. 创建新项目

```bash
# 使用 npm
npm create vite@latest my-project

# 使用 yarn
yarn create vite my-project

# 使用 pnpm
pnpm create vite my-project
```

### 2. 安装依赖

进入项目目录并安装依赖：

```bash
cd my-project
npm install
```

### 3. 启动开发服务器

```bash
npm run dev
```

现在您可以在浏览器中访问 `http://localhost:5173` 查看您的应用。

## 项目结构

```
my-project/
├── src/
│   ├── components/     # 组件目录
│   ├── assets/         # 静态资源
│   ├── App.vue         # 根组件
│   └── main.js         # 入口文件
├── public/             # 公共资源
├── package.json        # 项目配置
└── vite.config.js      # Vite 配置
```

## 下一步

- 查看[安装配置](/guide/installation)了解更多配置选项
- 浏览[基础功能](/guide/features)学习核心功能
- 参考[API 文档](/api/introduction)了解详细接口

## 获取帮助

如果您在使用过程中遇到问题：

1. 查看[常见问题](#)
2. 搜索[GitHub Issues](https://github.com/yourusername/yourrepo/issues)
3. 加入我们的[社区讨论](https://github.com/yourusername/yourrepo/discussions)

::: tip 提示
确保始终使用最新版本的依赖包以获得最佳体验和最新功能。
:::

::: warning 注意
在生产环境部署前，请务必进行充分的测试。
:::