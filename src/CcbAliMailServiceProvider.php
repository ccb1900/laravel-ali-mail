<?php
namespace Ccb\AliMail;
use Illuminate\Mail\TransportManager;

/**
 * Created by PhpStorm.
 * User: guojianhang
 * Date: 2018/6/24
 * Time: 13:05
 */

class CcbAliMailServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'ali-mail');
        $this->app->extend('swift.transport', function(TransportManager $service) {
            $service->extend('ali',function ($app){
                return new AliMail();
            });
            return $service;
        });
    }

    public function boot()
    {
        $this->publishes([
            $this->configPath() => config_path('ccb/ali-mail.php'),
        ]);
    }

    protected function configPath()
    {
        return __DIR__ . '/../config/ali-mail.php';
    }
}
