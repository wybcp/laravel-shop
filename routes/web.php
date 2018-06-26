<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', 'HomeController@index')->name('home');

Route::get('products', 'ProductsController@index')->name('products.index');
Route::get('products/{product}', 'ProductsController@show')->name('products.show');
//邮件需要激活
Route::get('/email-verify-notice', 'HomeController@emailVerifyNotice')->name('email_verify_notice');
Route::get('/email_verification/verify', 'EmailController@verify')->name('email_verification.verify');
Route::get('/email_verification/send', 'EmailController@sendVerifyEmail')->name('email_verification.send');


Route::group(['middleware' => 'email_verified'], function () {
    Route::get('user-addresses', 'UserAddressesController@index')->name('user_addresses.index');
    Route::get('user-addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
    Route::get('user-addresses/{address}', 'UserAddressesController@edit')->name('user_addresses.edit');
    Route::post('user-addresses', 'UserAddressesController@store')->name('user_addresses.store');
    Route::put('user_addresses/{address}', 'UserAddressesController@update')->name('user_addresses.update');
    Route::delete('user_addresses/{address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');
    Route::post('products/{product}/favorite', 'ProductsController@favor')->name('products.favor');
    Route::delete('products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');
    Route::post('cart', 'CartController@add')->name('cart.add');
});