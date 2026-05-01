# Currency API

> 基于 Workerman + PHPSocketIO + Redis 开发的实时汇率查询 API 服务

## 项目概述

| 项目 | 说明 |
|------|------|
| 服务端 | Workerman (PHP socket 框架) |
| 通信协议 | Socket.IO |
| 缓存 | Redis (60秒 TTL) |
| 数据源 | Yahoo Finance API、东方财富 |

## 目录结构

```
currency_api/
├── Socket/                    # 主服务目录
│   ├── start.php             # 服务启动入口
│   ├── start.bat            # Windows 启动脚本
│   ├── config.php           # 配置文件
│   ├── lock.lock            # 文件锁
│   ├── workerman/           # Workerman 核心
│   ├── phpsocketio/         # Socket.IO PHP 实现
│   └── vendor/              # 业务代码
│       ├── api.php          # 汇率数据获取
│       └── redis.php        # Redis 缓存封装
├── Redis/                    # Redis 可执行文件 (Windows)
└── vendor/                   # Composer 依赖
```

## 快速开始

### 1. 环境要求

| 要求 | 版本 |
|------|------|
| PHP | >= 5.4.0 |
| Redis | >= 2.6 |
| PHP 扩展 | php_socket (unix) / Windows 无需 |

### 2. 启动服务

```bash
# Windows
cd Socket
start.bat

# Linux/Mac
cd Socket
php start.php
```

服务启动后监听端口：`2345`

### 3. 客户端连接示例

```javascript
var socket = io("http://localhost:2345");

setInterval(draw, 1000);

// 发送给服务器 (货币对: AUDCNY, USDCNY, EURCNY 等)
function draw(){
    socket.emit("api", "AUDCNY");
}

socket.on('api', function(msg){
    var json = JSON.parse(msg);
    // console.log(json); 
});
```

## 配置说明

编辑 `Socket/config.php`：

```php
<?php
return array(
    'port'      => 1234,          // 端口
    'process'   => 4,              // 启动进程数
    'redis'     => 'tcp://127.0.0.1:6379'  // Redis 地址
);
```

## 核心逻辑

```
客户端请求 (Socket.IO)
        ↓
文件锁 (lock.lock) → 防止并发
        ↓
检查 Redis 缓存 (60秒 TTL)
    ├─ 有缓存 → 直接返回
    └─ 无缓存 → 请求外部 API → 存入 Redis → 返回
        ↓
释放文件锁
```

## 数据源

| 数据源 | 用途 |
|--------|------|
| Yahoo Finance | 汇率数据查询 |
| 东方财富 | K线图表数据 |

## API 返回格式

```json
{
  "query": {
    "results": {
      "rate": {
        "id": "AUDCNY",
        "Rate": "4.8234"
      },
      "kline": {
        "d": "日K线图片URL",
        "w": "周K线图片URL",
        "m": "月K线图片URL"
      },
      "today": ["实时数据"]
    }
  }
}
```

## 依赖

- [Workerman](https://www.workerman.net/) - 高性能 PHP socket 框架
- [PHPSocketIO](https://github.com/walkor/phpsocketio) - Socket.IO 服务端 PHP 实现
- [Predis](https://github.com/predis/predis) - PHP Redis 客户端
