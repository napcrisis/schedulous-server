<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function ($request) {
    $request_uri = $request->getRequestUri();
    Log::info('[' . Request::getClientIp() . '] ' . $request->url());
//    Log::info('env ' . App::environment());
    //$input = Input::all();
});


App::after(function ($request, $response) {
    //
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function () {
    if (Auth::guest()) {
        if (Request::ajax()) {
            return Response::make('Unauthorized', 401);
        } else {
            return Redirect::guest('login');
        }
    }
});

Route::filter('authRequest', function () {
    $request_uri = Request::getRequestUri();
    Log::info($request_uri);
    Log::warning(Input::get('auth'));
    if (strcmp($request_uri, '/user/register') == 0 || strcmp($request_uri, '/user/verify') == 0 || App::isLocal()) {
        // do nothing
    } else {
        $user_id = Input::get('auth.user_id');
        $session_id = Input::get('auth.session_id');
        $count = Login::where('user_id', '=', $user_id)->where('session_id', '=', $session_id)->get()->count();
        Log::warning($count);
        if ($count == 1) {
            // do nothing
        } else {
            $error = array('status' => 'failed', 'message' => 'invalid auth');
            return Response::make($error, 401); //
        }
    }
});

Route::filter('auth.basic', function () {
    return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function () {
    if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function () {
    if (Session::token() != Input::get('_token')) {
        throw new Illuminate\Session\TokenMismatchException;
    }
});
