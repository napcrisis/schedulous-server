<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function () {
    return View::make('hello');
});

Route::get('/referral/{code?}', function ($code = null) {
    if (is_null($code)) {
        return View::make('hello');
    }
//    Route::controller('referral', 'ReferralsController');
    ReferralsController::markIncomingReferral($code);
});

//Route::get('referral/{code?}','ReferralsController@markIncomingReferral');

Route::controller('user', 'UsersController');
//Route::controller('verifications', 'VerificationsController');

/*
Route::group(array('prefix' => ''), function () {
    Route::group(array('before' => 'all_mobile_traffic_filter'), function () {
        Route::controller('authentication', 'AuthenticationController'); // for users to login to facebook => postFacebook
        Route::group(array('before' => 'authenticated_traffic_filter'), function () {
            Route::controller('group', 'GroupController');
            Route::controller('user', 'UserController');
            Route::controller('gcm', 'GCMController');
            Route::controller('outings', 'OutingsController');
            Route::controller('snowtrails', 'SnowtrailsController');
            Route::controller('vendors', 'VendorsController');
//			Route::controller('timetable', 'TimetablesController');
        });
    });
});
*/

