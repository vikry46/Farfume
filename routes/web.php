<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;

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
// test github Vikry is husband Angelina christy
// yang namo risqi ny haluan istri kawan nyo vikry
Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-token', function () {
    $user = User::first(); 
    return $user->createToken('test')->plainTextToken;
});

Route::middleware(['web'])->group(function () {
    Route::get('/csrf', function () {
        return response()->json(['token' => csrf_token()]);
    });
});



// tes github Vikry is husband Angelina christy