<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->namespace('api\v1')->group(function(){
    // todo: at least group them with their controller or change big controllers to smaller and readable ones.
    Route::post('/vlans' , 'RouterController@vlans');
    Route::post('/showVlan' , 'RouterController@showVlan');
    Route::post('/showVlanOption' , 'RouterController@showVlanOption');
    Route::post('/vpns' , 'RouterController@vpns');
    Route::post('/showPlans' , 'UserController@showPlans');
    Route::post('/login' , 'UserController@login');
    Route::post('/addPlans' , 'UserController@addPlans');
    Route::post('/addExtra' , 'UserController@addExtra');
    Route::post('/pay' , 'UserController@pay');
    Route::post('/profileFirst' , 'UserController@profileFirst');
    Route::post('/profileSecond' , 'UserController@profileSecond');
    Route::post('/verifyPay' , 'UserController@verifyPay');
    Route::post('/showUser' , 'UserController@showUser');
    Route::post('/register' , 'UserController@register');
    Route::post('/showPay' , 'UserController@showPay');
    Route::post('/adminDashboard' , 'RouterController@adminDashboard');
    Route::post('/enable' , 'RouterController@enable');
    Route::post('/userDetail' , 'UserController@userDetail');
    Route::post('/buy' , 'UserController@buy');
    Route::post('/planDetail' , 'UserController@planDetail');
    Route::post('/extraDetail' , 'UserController@extraDetail');
    Route::post('/userDashboard' , 'UserController@userDashboard');
    Route::post('/myServices' , 'UserController@myServices');
    Route::post('/planBills' , 'UserController@planBills');
    Route::post('/extraBills' , 'UserController@extraBills');
    Route::post('/jobQueryDaily' , 'UserController@jobQueryDaily');

    Route::post('/sms' , 'UserController@sms');




//api check user is login or not
    Route::middleware('auth:api')->group(function() {
        Route::get('/user', function () {
            return auth()->user();
        });
    });
});
