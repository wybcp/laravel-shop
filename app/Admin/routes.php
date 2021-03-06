<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->get('users','UsersController@index');
    $router->get('users/{id}/edit','UsersController@edit');

    $router->get('products', 'ProductsController@index');
    $router->get('products/create', 'ProductsController@create');
    $router->post('products', 'ProductsController@store');
    $router->get('products/{id}/edit', 'ProductsController@edit');
    $router->put('products/{id}', 'ProductsController@update');

    $router->get('orders', 'OrdersController@index')->name('admin.orders.index');
    $router->get('orders/{order}', 'OrdersController@show')->name('admin.orders.show');
    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('admin.orders.ship');
    $router->post('orders/{order}/refund', 'OrdersController@handleRefund')->name('admin.orders.handle_refund');

    $router->get('coupon-codes', 'CouponCodesController@index');
    $router->post('coupon-codes', 'CouponCodesController@store');
    $router->get('coupon-codes/create', 'CouponCodesController@create');
    $router->get('coupon-codes/{id}/edit', 'CouponCodesController@edit');
    $router->put('coupon-codes/{id}', 'CouponCodesController@update');
    $router->delete('coupon-codes/{id}', 'CouponCodesController@destroy');

});
