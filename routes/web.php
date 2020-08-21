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

Route::group(["middleware" => "auth"], function(){

    Route::get("/", "CloudController@home");

    Route::get("/district/{id}", "CloudController@district");

    Route::get("/device/{id}", "CloudController@device");
    
    Route::get("/device/{id}/history", "CloudController@deviceHistory");

    Route::post("/device/{id}/update", "CloudController@deviceUpdate");

    Route::get("/device/{id}/consumption", "CloudController@deviceConsumption");

    Route::post("/device/{id}/consumption", "CloudController@setIncome");

    Route::post("/devicebydistrict", "CloudController@devicesByDistricts");    

    Route::post("/tempcard/create", "CloudController@createTempCard");

    Route::post("/tempcard/update/{id}", "CloudController@updateTempCard");

    Route::get("/tempcard/remove/{id}", "CloudController@removeTempCard");
    
    Route::get("profile", "UserController@profile");

    Route::post("profile", "UserController@changePassword");

    Route::group(["prefix" => "users"], function(){
	
        Route::get("/", "UserController@index");
            
        Route::get("/{id}", "UserController@userInfo");

        Route::get("/{id}/delete", "UserController@deleteUser");

        Route::get("/{user_id}/detach/district/{object_id}", "UserController@detachDistrict");

        Route::get("/{user_id}/detach/object/{object_id}", "UserController@detachObject");

        Route::post("/{user_id}/attach", "UserController@attachObject");

        Route::post("/{user_id}/changerole", "UserController@changeRole");

        Route::get("/{user_id}/notattached", "UserController@getNotAttachedObjects");

    });

    Route::group(["prefix" => "events"], function(){
	
	    Route::get("/", "EventController@index");

        Route::get("/device/{id}", "EventController@deviceEvents");

        Route::get("/device/{id}/{start}/{end}", "EventController@deviceEvents");
        
        Route::get("/graph/{id}", "EventController@graph");

    });
    
    Route::group(["prefix" => "alarms"], function(){
	
        Route::get("/", "AlarmController@index");
        
        Route::get("/{start}/{end}", "AlarmController@index");

    });

    Route::group(["prefix" => "audit"], function(){

        Route::group(["prefix" => "types"], function(){
            
            Route::get("/", "AuditController@auditControl");
    
            Route::get("/{id}", "AuditController@showAudit");

            Route::post("/addaudit", "AuditController@addAudit");

            Route::get("/{id}/addquestion", "AuditController@addQuestion");

            Route::post("/{id}/addquestion", "AuditController@saveQuestion");
    
            Route::get("/delete/{id}", "AuditController@deleteAudit");

            Route::get("/removequestion/{id}", "AuditController@removeQuestion");

            Route::get("/updatequestion/{id}", "AuditController@updateQuestion");

        });

        Route::group(["prefix" => "results"], function(){

            Route::get("/", "AuditController@results");

            Route::get("/{id}", "AuditController@showResult");

        });        

    });

    Route::group(["prefix" => "analytics"], function(){

        Route::group(["prefix" => "monitor"], function(){

            Route::get("/", "AuditController@monitorIndex");

            Route::get("/{district_id}", "AuditController@getMonitorAnalytics");

            Route::get("/excell/{district_id}", "AuditController@createExcel");

            Route::get("/{month}/{object_id}", "AuditController@monitorDetails");

        });

        Route::group(["prefix" => "audit"], function(){

            Route::get("/", "AuditController@auditIndex");

            Route::get("/analytics/{district_id}/{month}", "AuditController@getAuditAnalytics");

            Route::get("/excell/{district_id}/{date}", "AuditController@createExcelAuditAnalytics");

        });

        Route::group(["prefix" => "consumption"], function(){

            Route::get("/", "AuditController@consumptionIndex");

            Route::get("/analytics/{district_id}/{date}", "AuditController@getConsumptionAnalytics");

            Route::get("/season/{district_id}", "AuditController@getConsumptionSeasonAnalytics");

            Route::get("/excell/{district_id}/{date}", "AuditController@createExcelConsumptionAnalytics");

            Route::post("/edit/{district_id}", "AuditController@editConsumption");

        });

        Route::get("/kpi", "AnalyticsController@index");

        Route::post("/kpi/excell", "AnalyticsController@getExcell");

        Route::get("/kpi/{date}", "AnalyticsController@getKPIData");

    });

    Route::group(["prefix" => "settings"], function(){

        Route::get("/", "SettingsController@index");

        Route::post("/update/{id}", "SettingsController@update");

        Route::post("/create", "SettingsController@create");
    
    });
    
});