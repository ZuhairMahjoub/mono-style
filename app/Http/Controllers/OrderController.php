<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; 

class OrderController extends Controller
{
    use AuthorizesRequests; //هاد trait موجود داخل الcontroller

    public function store(OrderStoreRequest $request)
    {
        return DB::transaction(function () use ($request) {

            $total_price = 0;

            foreach ($request->products as $product) {
                $pros = Product::findOrFail($product['id']);
                 $pros->decrement('stock', $product['quantity']);

                $total_price += $pros->price * $product['quantity'];
            }
            

            if (Auth::check()) {
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'total_price' => $total_price,
                    'role'=>'user'
                ]);
            } else {
                $order = Order::create([
                    'customer_name' => $request->customer_name,
                    'customer_email' => $request->customer_email,
                    'total_price' => $total_price,
                     'role' => 'guest'
                ]);
            }

            if ($request->has('products')) {
                foreach ($request->products as $product) {
                    // the benefit of method attach to insert row in pivot table
                  $order->products()->attach($product['id'],['quantity'=>$product['quantity']]);
                    
                }
            }

            return response()->json([
                'message' => 'Operation Completed Successfully',
                'order' => $order->load('products'),
                'total_price' => $total_price,
            ]);
        });
    }

    public function update(OrderUpdateRequest $request, Order $order)
    {
        
        $this->authorize('update', $order);
        
        $validated = $request->validated();

        $order->update($validated);
         $order->load('products');
    foreach ($order->products as $oldProduct) {
        $oldProduct->increment('stock', $oldProduct->pivot->quantity);
    }

    $syncData = [];
    $total_price=0;
    foreach ($request->products as $product) {
        $pros = Product::findOrFail($product['id']);
        $total_price += $pros->price * $product['quantity'];
        $pros->decrement('stock', $product['quantity']);

        $syncData[$product['id']] = ['quantity' => $product['quantity']];//تجهيز بيانات ال Pivot
    }
    $order->update(['total_price' => $total_price]);
    $order->products()->sync($syncData);

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->load('products'),//عم يرجع الطلب مع المنجات من ال pivot
        ]);
    }
    public function delete(Order $order){
      $this->authorize('delete',$order);
        $order->delete();
        return response()->json([
            'title'=>'Order Removed',
            'message'=>'Your Order has been Successfully cancelled.If you have any questions,we are here to help',
             'status'=>'success'
        ],200);
    }
    public function show(Order $order){
        $this->authorize('view',$order);
        return response()->json([
           'message' =>'the information about the order which you choosed',
            'order:'=>$order
        ]);
    }
    public function index(){
        $this->authorize('viewAny',Order::class);
        $order=Order::all();
        return response()->json([
            'message'=>'success',
            'order'=>$order
        ]);
    }

    
  
   }




 

