# Upyun 云储存 Laravel 5 Storage版

基于 https://github.com/upyun/php-sdk 开发

符合Laravel 5 的Storage用法。

## 更新

 v1.0 支持laravel5.4 的upyun存储适配器。

## 安装

 - ```composer require axdlee/upyun-laravel-storage```
 - ```config/app.php``` 里面的 ```providers``` 数组， 加上一行 ```Axdlee\UpyunStorage\UpyunStorageServiceProvider```
 - ```config/filesystem.php``` 里面的 ```disks```数组加上：

```php

    'disks' => [
        ... ,
        'upyun' => [
            'driver'        => 'upyun', 
            'bucket'        => 'your bucket name',
            'operator_name' => 'your operator name',
            'operator_password'  => 'your operator password',
        ],
    ],

```

 - 完成

## 使用

 - 参考 包内 UpyunAdapter.php 及 https://github.com/upyun/php-sdk



## 官方SDK / 手册

 - https://github.com/upyun/php-sdk
 - http://docs.upyun.com/api/


