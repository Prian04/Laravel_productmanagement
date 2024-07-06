<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
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

Route::get('products', [ProductController::class, 'index'])->name('products');
Route::post('products/store', [ProductController::class, 'store'])->name('products_store');

Route::get('products/edit/{id}', [ProductController::class, 'edit'])->name('products_edit');
Route::put('products/update/{id}', [ProductController::class, 'update'])->name('products_update');
Route::delete('products/delete/{id}', [ProductController::class, 'destroy'])->name('products_delete');

Route::get('products/list', [ProductController::class, 'getProducts'])->name('products.list');