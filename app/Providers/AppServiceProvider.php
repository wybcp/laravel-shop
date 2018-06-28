<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Order::observe(OrderObserver::class);

        Carbon::setLocale('zh');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 往服务容器中注入一个名为 alipay 的单例对象
//        $this->app->singleton('alipay')
    }
}
