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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//
//});
Route::group(['middleware' => ['cors']], function () {

    Route::post('login','UserController@login');
    Route::post('register','UserController@register');
    Route::get('get_currency','UserController@test');

    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('details/{id}','UserController@details');
    });
    Route::post('password/email', 'Auth\ForgotPasswordController@getResetToken');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');

    Route::resource('company', 'CompanyController');
    Route::resource('milestone' , 'MilestoneController');
    Route::resource('target_marget_graph' , 'TargetMargetGraphController');
    Route::resource('team_role', 'TeamRoleController');

    /*Pitch Routes*/
    Route::resource('update_pitch','PitchController@updatePitch');
    Route::resource('pitch', 'PitchController');


});


