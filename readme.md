# Vendor-Phar

将 `composer` 所有依赖的 `vendor` 打包成一个`vendor.phar`


## 安装

```bash
composer global require mmhk/vendor-phar
```

## 运行
In project root folder.

> 注意：`phar`扩展默认`ini`配置是只读的，使用`vendor-phar`需要禁用 `phar.readonl` 
> 禁用方式可以修改 `ini` 文件 `phar.readonly=0` 
> 或者在PHP cli 执行的时候加上 `-d phar.readonly=0`


- 方式1
```bash
php -d phar.readonly=0 vendor-phar -v [vendor目录] -o [vendor.phar输出目录]
```

- 方式2
```bash
php -d phar.readonly=0 vendor-phar -c vendor-phar.json
```

### vendor-phar.json 配置文件结构

```json
{
  "baseDir": "", //vendor.phat 输出目录
  "vendorDir": "", // vendor 文件夹路径
  "exclude": [ //忽略路径模式匹配
    "phpunit/", //一般匹配 - 忽略大小写
    "regex:/phpunit\\/(.*)/i" // 正则匹配模式 - 注意字符转义
  ]
}
```