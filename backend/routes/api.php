<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthMetric\HealthMetricController;

Route::group(["prefix" => "v0.1"], function () {

    Route::group(["prefix" => "auth"], function () {
        Route::post('/signup', [AuthController::class, "signup"]);
        Route::post('/login', [AuthController::class, "login"]);
    });


    //authenticated routes
    Route::group(["middleware" => "auth:api"], function () {
        Route::group(["prefix" => "metrics"], function () {
            Route::post('/uploadcsv', [HealthMetricController::class, "uploadCsv"]);
            
        });

    });

});
