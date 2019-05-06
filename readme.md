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

## 注意

由于 `composer` 自动生成的一堆 `Autoloader` 都指定了的project root，所以 `vendor-phat` 会修改 `autoload_classmap.php`/ `autoload_files.php` / `autoload_namespaces.php` / `autoload_psr4.php` / `autoload_static.php`, 将 project root 引用到 `phar` 包里面 `.mount` 目录，并通过 `Phar::mount` 将真实的 project root 挂载到 phar 包里面的 `.mount`。

基于以上修改，`vendor.phar` 并不能随便移动位置!! 移动之后将会导致 `composer` 生成的 `autoloader` 失效。