<?php

namespace Axdlee\UpyunStorage;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Upyun\Upyun;
use Upyun\Config;
use Axdlee\UpyunStorage\UpyunAdapter;
use Axdlee\UpyunStorage\Plugins\PutFile;
use League\Flysystem\Filesystem;

class UpyunStorageServiceProvider extends ServiceProvider
{
    /**
     * 运行服务注册后的启动进程
     *
     * @return void
     */
    public function boot()
    {

        Storage::extend('upyun', function ($app, $config) {
            $bucket = $config['bucket'];
            $operatorName = $config['operator_name'];
            $operatorPassword = $config['operator_password'];
            $bucketConfig = new Config($bucket, $operatorName, $operatorPassword);
            $adapter = new UpyunAdapter($bucketConfig);
            $filesystem = new Filesystem($adapter);
            $filesystem->addPlugin(new PutFile());
            return $filesystem;
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
