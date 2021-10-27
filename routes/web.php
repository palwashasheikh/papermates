<?php

use App\Http\Controllers\paperMatesuser\User;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});
//Route::get('User/verify?{code}',[User::class,'verifyUser'])->name('verify.user');
//Route::post('User/verify/{code?}',[User::class,'verifyUser'])->name('verify');
Route::get('User/verify/{code}', [User::class, 'verifyAccount'])->name('user.verify');
