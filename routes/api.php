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
    Route::get('get_pitch_by_publish_key/{publish_key}','PitchController@getPitchByPublishKey');

    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('details/{id}','UserController@details');



        /*Company Routes*/
        Route::resource('company', 'CompanyController');
        Route::post('update_company/{id}', 'CompanyController@updateCompany');

        /*Pitch Routes*/
        Route::post('update_pitch/{id}','PitchController@updatePitch');
        Route::post('delete_logo/{id}','PitchController@deleteLogo');
        Route::get('get_pitch_by_company/{id}','PitchController@getPitchByCompany');
        Route::post('publish_pitch_by_company/{id}','PitchController@publishPitchByCompany');
        Route::post('unpublish_pitch_by_company/{id}','PitchController@unpublishPitchByCompany');

        /*Plan Routes*/
        Route::resource('plan', 'PlanController');


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
        //Resource Routes For Chapter Not Being Used For Now
        //Route::resource('chapter', 'ChapterController');
        Route::post('update_chapter/{id}','ChapterController@updateChapter');
        Route::post('update_order_chapter','ChapterController@updateOrder');

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
        Route::get('get_revenue_by_forecast/{id}','RevenueController@getRevenueByForecast');

        /*Expense Routes*/
        Route::resource('expense' , 'ExpenseController');
        Route::post('update_expense/{id}','ExpenseController@updateExpense');

        /*Dividend Routes*/
        Route::resource('dividend' , 'DividendController');
        Route::post('update_dividend/{id}','DividendController@updateDividend');

        /*Cost Routes*/
        Route::resource('cost' , 'CostController');
        Route::post('update_cost/{id}','CostController@updateCost');
        Route::get('get_cost_by_forecast/{id}','CostController@getCostByForecast');

        /*Asset Routes*/
        Route::resource('asset' , 'AssetController');
        Route::get('get_asset_by_forecast/{id}' , 'AssetController@getAssetByForecast');


    });

});


