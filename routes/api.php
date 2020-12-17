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
//API AUTH
Route::post('login', 'APIController@login');

//API REGISTRATION (not used)
Route::post('register', 'APIController@register');

Route::group(["middleware" => 'auth:api'], function(){

    //User profile
    Route::get("/user", "APIController@user");
    
    //List of all districts
    Route::get("/districts", "APIController@allDistricts");

    //Devices of district
    Route::get("/districts/{id}/devices", "APIController@districtDevices");

    // Get Parameters from Device
    Route::get("/devices/params/{id}", "APIController@getParameters");
    
    Route::get("/devices/lastdata/{id}", "APIController@lastData");

    //Get available audits
    Route::get("/devices/{device_id}/audits", "APIController@audits");

    //Get Audit questions
    Route::get("/devices/{device_id}/audits/{audit_id}", "APIController@getQuestions");

    //Send audit result 
    Route::post("/devices/{device_id}/audits/{audit_id}/store", "APIController@saveResult");

    //Route::get("devices/{device_id}/coal", "APIController@consumeView");

    Route::post("devices/{device_id}/consume", "APIController@consumeCoal");

    Route::get("/consumption/districts", "APIController@allDistrictsConsumption"); 

    Route::get("/consumption/districts/{district_id}", "APIController@districtConsumption");

    Route::get("/consumption/devices/{device_id}", "APIController@deviceConsumption");

    Route::get("/devices/{device_id}/taketowork", "APIController@takeToWork");

    Route::get("/offline/audit/data", "APIController@offlineData");
});