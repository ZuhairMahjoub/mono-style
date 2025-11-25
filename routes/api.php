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

Route::get('account',[UserController::class,'getUsers']);
Route::get('account/{user_id}',[UserController::class,'getUser']);
Route::put('account/{user_id}',[UserController::class,'update']);
Route::delete('account/{user_id}',[UserController::class,'delete']);
Route::delete('account',[UserController::class,'deleteall']);
                           //Products
//Routes for users
Route::prefix('products')->middleware('auth:sanctum')->group(function(){
Route::post('/',[ProductController::class,'store']);
Route::put('/{id}',[ProductController::class,'update']);
Route::delete('/{id}',[ProductController::class,'delete']);


});
//Routes for admin
Route::prefix('admin/products')->middleware(['auth:sanctum','checkAdmin'])->group(function(){
Route::get('/all',[ProductController::class,'adminIndex']);
Route::get('/pending',[ProductController::class,'pending']);
Route::post('/approve/{id}',[ProductController::class,'approve']);
Route::post('/reject/{id}',[ProductController::class,'reject']);
});
//Routes for all but it showes only approve products
Route::get('products',[ProductController::class,'show']);
Route::get('products/{product_id}',[ProductController::class,'showProduct']);
Route::get('products/category/{category_id}',[ProductController::class,'showByCategory']);

//Route::post('/login', [UserController::class, 'login']);
//Orders

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
//orders
Route::middleware('auth:sanctum')->post('/orders', [OrderController::class, 'store']);
Route::middleware('auth:sanctum')->put('/orders/{order}', [OrderController::class, '  update']);
Route::middleware('auth:sanctum')->delete('/orders/{order}', [OrderController::class, 'delete']);
Route::middleware('auth:sanctum')->get('/orders/{order}', [OrderController::class, 'show']);
Route::middleware('auth:sanctum')->get('/orders', [OrderController::class, 'index']);
Route::middleware(['auth:sanctum','admin'])->get('/admin/products', [ProductController::class, 'adminIndex']);
Route::middleware(['auth:sanctum','admin'])->post('/admin/products/{id}/approve', [ProductController::class, 'approve']);
Route::middleware(['auth:sanctum','admin'])->post('/admin/products/{id}/reject', [ProductController::class, 'reject']);
Route::post('/categories/tree', [UserController::class, 'createCategoryTree']);
