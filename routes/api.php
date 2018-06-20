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
    Route::get('add_currency','UserController@addCurrency');
    Route::post('password/email', 'Auth\ForgotPasswordController@getResetToken');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('details/{id}','UserController@details');



        /*Company Routes*/
        Route::resource('company', 'CompanyController');
        Route::post('update_company/{id}', 'CompanyController@updateCompany');

        /*Pitch Routes*/
        Route::resource('pitch', 'PitchController');
        Route::post('update_pitch/{id}','PitchController@updatePitch');

        /*Milestone Routes*/
        Route::resource('milestone' , 'MilestoneController');
        Route::post('update_milestone/{id}' , 'MilestoneController@updateMilestone');

        /*Target Marget Graph Routes*/
        Route::resource('target_marget_graph' , 'TargetMargetGraphController');
        Route::post('update_target_marget_graph/{id}' , 'TargetMargetGraphController@updateTargetMargetGraph');

        /*Team Role Routes*/
        Route::resource('team_role', 'TeamRoleController');
        Route::post('update_team_role/{id}' , 'TeamRoleController@updateTeamRole');
        Route::post('update_team_role_order' , 'TeamRoleController@updateOrder');

        /*Competitor Routes*/
        Route::resource('competitor', 'CompetitorController');
        Route::post('update_competitor/{id}' , 'CompetitorController@updateCompetitor');
        Route::post('update_competitor_order' , 'CompetitorController@updateOrder');

        /*Chapter Routes*/
        Route::resource('chapter', 'ChapterController');
        Route::post('update_chapter/{id}','ChapterController@updateChapter');

        /*Section Routes*/
        Route::resource('section', 'SectionController');
        Route::post('update_section/{id}','SectionController@updateSection');

        /*Section Content Routes*/
        Route::resource('section_content', 'SectionContentController');
        Route::post('update_section_content/{id}','SectionContentController@updateSectionContent');

        /*Chart Routes*/
        Route::resource('chart', 'ChartController');
        Route::post('update_chart/{id}','ChartController@updateChart');

        /*Table Routes*/
        Route::resource('table', 'TableController');
        Route::post('update_table/{id}','TableController@updateTable');

        /*Topic Routes*/
        Route::resource('topic', 'TopicController');
        Route::post('update_topic/{id}','TopicController@updateTopic');

        /*Revenue Routes*/
        Route::resource('revenue', 'RevenueController');
        Route::post('update_revenue/{id}','RevenueController@updateRevenue');
    });

});


