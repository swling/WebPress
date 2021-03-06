- @date 2022.02.11
- @author Swling tangfou@gmail.com

# WP Framework

从08年使用WordPress至今，18年开始自学开发，19年开始开发基于WordPress的前端插件 wnd-frontend ，最终尝试完全从零开发一个属于自己的 php 框架。
显然，框架核心理念甚至代码段主要来自 WordPress 内核，及 wnd-frontend 插件。

## QA
- 和WP的区别是什么：
本项目的主要目的是基于WP现有的设计理念，构建一款高性能框架。旨在保持WP优秀的设计理念和灵活性的同时，大幅精简非必要功能同时提高性能。
WP 面向广大普通用户。本项目主要面向具有WP使用经验，同时具有一定开发能力，追求高性能的开发者。项目仅在底层设计借鉴WP，但不会兼容WP生态。

总之：性能，性能，还是 TMD 性能！

- https://WordPress.org
- https://github.com/swling/wnd-frontend
- https://github.com/swling/WP-Framework

## 核心功能
数据库参考 WP 针对性能优化，做适当修改
- 优化字段
- 新增附件数据表 attachments
- 调整 users 及 posts 字段

## API 路由
Api 部分参考 wnd-frontend 插件
- /{{api_prefix}}/

### 内容筛选
- /{{api_prefix}}/users/
- /{{api_prefix}}/posts/
- /{{api_prefix}}/comments/
- /{{api_prefix}}/terms/

### 操作节点
- /{{api_prefix}}/action/
- /{{api_prefix}}/module/
- /{{api_prefix}}/endpoint/
- /{{api_prefix}}/query/

### 主题拓展操作节点
- /{{api_prefix}}/theme/action/
……

### 插件拓展操作节点
- /{{api_prefix}}/extend/action/
……

### 数据读取
- /{{api_prefix}}/query/
- /{{api_prefix}}/query/user/{{id_or_slug}}
- /{{api_prefix}}/query/post/{{id_or_slug}}
- /{{api_prefix}}/query/term/{{id_or_slug}}
- /{{api_prefix}}/query/comment/{{id_or_slug}}

## 渲染 URL 路由
- /user/ 			                用户
- /console/			                控制台
- /{{post_type}}/{{id_or_slug}}	    正文
- /{{taxonomy}}/{{id_or_slug}}		分类
