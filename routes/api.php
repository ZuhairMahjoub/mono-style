<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/users', function (Request $request) {
    return $request->user();

})->middleware('auth:sanctum');
                         //Categories
Route::get('children/categories/{parent_id}',[UserController::class,'getChildrenCategories']);
Route::get('parent/category/{children_id}',[UserController::class,'getParentCategory']);
                           //Users
Route::middleware('auth:sanctum')->group(function(){
Route::get('account',[UserController::class,'getUsers']);
Route::get('account/{user_id}',[UserController::class,'getUser']);
Route::put('account/{user_id}',[UserController::class,'update']);
Route::delete('account/{user_id}',[UserController::class,'delete']);
Route::post('logout',[UserController::class,'logout']);
});
Route::middleware('auth:sanctum','checkAdmin')->delete('account',[UserController::class,'deleteall']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
/////////////////////////////// The user section has ended////////////////////////////////////////////////
                           //Products
//Routes for users
Route::prefix('product')->middleware('auth:sanctum')->group(function(){
Route::post('/',[ProductController::class,'store']);
Route::put('/{id}',[ProductController::class,'update']);
Route::delete('/{id}',[ProductController::class,'delete']);
});
//
//Routes for admin
Route::prefix('admin/products')->middleware(['auth:sanctum','checkAdmin'])->group(function(){
Route::get('/all',[ProductController::class,'adminIndex']);
Route::get('/pending',[ProductController::class,'pending']);
Route::post('/approve/{id}',[ProductController::class,'approve']);
Route::post('/reject/{id}',[ProductController::class,'reject']);
});
//Routes for all but it showes only approve products
Route::get('products',[ProductController::class,'show']);
Route::get('product/{product_id}',[ProductController::class,'showProduct']);
Route::get('products/category/{category_id}',[ProductController::class,'showByCategory']);
//////////////////////////////////The product section has ended/////////////////////////////////////////////
                               //Orders
Route::post('/orders', [OrderController::class, 'store']);
Route::prefix('/orders')->middleware('auth:sanctum')->group(function(){
Route::get('/all', [OrderController::class, 'getAllOrdersInDifferentSituations']);
Route::get('', [OrderController::class, 'index']);
Route::put('/{order}', [OrderController::class, 'update']);
Route::delete('/{order}', [OrderController::class, 'destroy']);
Route::get('/{order}', [OrderController::class, 'show']);
});



