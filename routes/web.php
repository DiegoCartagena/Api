<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\WebhookShopify;
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
Route::get('/getProductos',[ProductosController::class,'getProductos']);
Route::get('/update',[WebhookShopify::class,'update']);
Route::get('/webhook/venta',[WebhookShopify::class,'sale']);
Route::post('/sale',[VentasController::class,'setSale']);