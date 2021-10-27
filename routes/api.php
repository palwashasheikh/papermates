<?php
use App\Http\Controllers\paperMateorder\Order;
use App\Http\Controllers\paperMateReview\Review;
use App\Http\Controllers\paperMatesuser\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('forget-password', [User::class, 'submitForgetPasswordForm'])->name('forget.password.post');

Route::post('User/register',[User::class,'register']);
Route::post('User/login',[User::class,'login']);
Route::get('User/countData',[User::class,'countdata']);
Route::get('User/AcademicLevels',[User::class,'AcademicLevels']);
Route::get('User/writerList',[User::class,'writersList']);
Route::get('User/staffList',[User::class,'staffList']);
Route::get('User/studentList',[User::class,'studentList']);
Route::get('User/dashboardCount',[User::class,'dashboardCount']);
Route::get('Review/userReview',[Review::class,'userReview']);
Route::get('Order/OrderList',[Order::class,'OrderList']);
Route::get('Order/categoryList',[Order::class,'CategoryList']);
Route::get('Order/OrderListByOrderId',[Order::class,'OrderListByorderId']);
Route::post('User/forgotPassword',[User::class,'forgotPassword']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('User/updateProfile',[User::class,'updateProfile']);
    Route::put('User/ChangeUserPassword',[User::class,'ChangeUserPassword']);
    Route::post('Order/createOrders',[Order::class,'createorders']);
    Route::Post('Order/paperCategory',[Order::class,'paperCategory']);
    Route::delete('Order/categoryDelete',[Order::class,'categoryDelete']);
    Route::delete('User/studentDelete',[User::class,'studentDelete']);
    Route::delete('User/academicLevelDelete',[User::class,'academicLevelDelete']);
    Route::delete('User/writerDelete',[User::class,'writerDelete']);
    Route::delete('User/staffDelete',[User::class,'staffDelete']);
    Route::post('User/createAcademicLevel',[User::class,'createAcademicLevel']);
    Route::post('Order/orderStatusUpdate',[Order::class,'orderStatusUpdate']);
    Route::post('User/addUser',[User::class,'addUser']);
    Route::post('Review/CreateReview',[Review::class,'CreateReview']);
    Route::get('Order/studentOrders',[Order::class,'studentOrders']);
    Route::get('Order/writerPendingOrders',[Order::class,'writerPendingOrders']);
    Route::get('Order/writerCompletedOrders',[Order::class,'writerCompletedOrders']);
    Route::post('User/WriterAppliedForOrder',[User::class,'WriterAppliedForOrder']);
    Route::get('Order/studentPaper',[Order::class,'studentpaper']);
    Route::get('Order/orderDocument',[Order::class,'orderDocument']);

});
Route::get('/clear', function() {

    Artisan::call('key:generate');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');

    return "Cleared!";

});
