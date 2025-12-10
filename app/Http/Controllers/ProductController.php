<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

use App\Models\Product;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
//use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Illuminate\Container\Attributes\Storage;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Profiler\Profile;


class ProductController extends Controller
{
     use AuthorizesRequests;
    public function store( ProductStoreRequest $request){
        $validate_data=$request->validated();
        $validate_data['user_id']=$request->user()->id;
        $validate_data['status']='pending';
        if($request->hasFile('image')){
            $path=$request->file('image')->store('Products','public');
            $validate_data['image']=$path;
        }
     $product=Product::create($validate_data);
     $product->image_url = $product->image
        ? asset('storage/' . $product->image)
        : null;
         if($request->has('extra_categories')){
            $product->categories()->sync($request->extra_categories);
        }
     return response()->json([
      'message'=>'Operation Completed Successfully',
      'product'=>$product->load('category','categories')
     ],201);
    }
    public function show(){
        $products=Product::with('category')->where('status', 'approved')->get();
        $products->transform(function($product){
          $product->image = asset('storage/' . $product->image);
          return $product;
        });
        return response()->json([
         'status'=>'success',
         'code'=>200,
         'message'=>'products retrieved successfully',
         'data'=>[
            'count'=>$products->count(),
            'items'=>$products
         ],
         'meta' => [
            'api_version' => '1.0',
            'timestamp' => now()->toIso8601String(),
        ],
        ],200);
    }
    public function showByCategory($category_id){
        $products=Product::with('category')->where('category_id',$category_id)->where('status', 'approved') ->get();
        $products->transform(function($product){
          $product->image = asset('storage/' . $product->image);
          return $product;
        });
        return response()->json([
         'status'=>'success',
         'code'=>200,
         'message'=>'products retrieved successfully',
         'data'=>[
            'count'=>$products->count(),
            'items'=>$products
         ],
         'meta' => [
            'api_version' => '1.0',
            'timestamp' => now()->toIso8601String(),
        ],
        ],200);
    }
    public function showProduct($product_id){
        
     $product= Product::with('category')->where('status', 'approved')->findOrfail($product_id);
     $product->image = asset('storage/' . $product->image);
    return response([
        'status'=>'success',
        'code'=>200,
        'message'=>'product retrieved successfully',
        'product'=>$product
    ],200);
}

    public function delete($product_id)
{
    $product = Product::findOrFail($product_id);
      $this->authorize('delete',$product);
      if($product->status!=='approved'){
        return response()->json([
            'message'=> 'can not delete until admin approves it'
        ],403);
      }
    if ($product->image && Storage::disk('public')->exists($product->image)) {
        Storage::disk('public')->delete($product->image);
    }
    $product->delete();
    return response()->json([
        'message' => 'Product deleted successfully',
    ], 200);
}
   

public function update(ProductUpdateRequest $request, $product_id)
{
    $validate_data = $request->validated();
    
    $product = Product::findOrFail($product_id);

    $this->authorize('update', $product);

    // 1️⃣ check status first
    if ($product->status !== 'approved') {
        return response()->json([
            'message' => 'can not update it until admin approves it'
        ], 403);
    }

    // 2️⃣ update image normally
    if ($request->hasFile('image')) {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $path = $request->file('image')->store('products', 'public');
        $validate_data['image'] = $path;
    }

    // 3️⃣ update other fields
    $product->update($validate_data);

    $product->refresh();

    $imageUrl = $product->image ? asset('storage/' . $product->image) : null;

    return response()->json([
        'message' => 'Product updated successfully',
        'product' => [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,
            'category_id' => $product->category_id,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
            'image' => $product->image,
            'image_url' => $imageUrl
        ]
    ], 200);
}

public function adminIndex()
{ 
    $products = Product::with('category', 'user')->get();
    $products->transform(function ($product) {
        $product->image_url = $product->image ? asset('storage/' . $product->image) : null;
        return $product;
    });

    return response()->json([
        'status' => 'success',
        'message' => 'All products retrieved successfully',
        'data' => [
            'count' => $products->count(),
            'items' => $products
        ],
    ]);
}
public function pending()
{  
    $products = Product::with('category', 'user')
        ->where('status', 'pending')
        ->get();
    $products->transform(function ($product) {
        $product->image_url = $product->image ? asset('storage/' . $product->image) : null;
        return $product;
    });

    return response()->json([
        'status' => 'success',
        'message' => 'Pending products retrieved',
        'data' => $products
    ]);
}
public function approve($id)
{
    $product = Product::findOrFail($id);
    $product->update([
        'status' => 'approved'
    ]);
    return response()->json([
        'status' => 'success',
        'message' => 'Product approved successfully',
        'product' => $product
    ]);
}
public function reject($id)
{ 
    $product = Product::findOrFail($id);
    $product->update([
        'status' => 'rejected'
    ]);
    return response()->json([
        'status' => 'success',
        'message' => 'Product rejected'
    ]);
}
}


