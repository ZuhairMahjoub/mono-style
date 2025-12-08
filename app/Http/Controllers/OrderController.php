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

    // public function store(OrderStoreRequest $request)
    // {
    //     return DB::transaction(function () use ($request) {
    //         $total_price = 0;
    //         foreach ($request->products as $product) {
    //             $pros = Product::findOrFail($product['id']);
    //             if($pros->status!=='approved'){
    //         return response()->json([
    //             'message'=>'This product cannot be ordered because it is not approved yet'
    //         ],403);
    //     }
    //             if($product['quantity']>$pros->stock){
    //                 return response()->json([
    //                     'message'=>'Quantity not available',
    //                     'available_stock'=>$pros->stock
    //                 ],400);
    //             }
    //              $pros->decrement('stock', $product['quantity']);

    //             $total_price += $pros->price * $product['quantity'];
    //         }
    //         if (Auth::check()) {
    //             $order = Order::create([
    //                 'user_id' => Auth::id(),
    //                 'total_price' => $total_price,
    //                 'role'=>'user'
    //             ]);
    //         } else {
    //             $order = Order::create([
    //                 'customer_name' => $request->customer_name,
    //                 'customer_email' => $request->customer_email,
    //                 'total_price' => $total_price,
    //                  'role' => 'guest'
    //             ]);
    //         }
    //         if ($request->has('products')) {
    //             foreach ($request->products as $product) {
    //                 // the benefit of method attach to insert row in pivot table
    //               $order->products()->attach($product['id'],['quantity'=>$product['quantity']]);
                    
    //             }
    //         }
    //         return response()->json([
    //             'message' => 'Operation Completed Successfully',
    //             'order' => $order->load('products'),
    //             'total_price' => $total_price,
    //         ]);
    //     });
    // }
    public function store(OrderStoreRequest $request)
{
    return DB::transaction(function () use ($request) {

        $total_price = 0;
        $productsData = [];

        foreach ($request->products as $product) {

            // قفل الصف لضمان عدم وجود مشاكل تزامن المخزون
            $pros = Product::lockForUpdate()->findOrFail($product['id']);

            // التحقق من حالة المنتج
            if ($pros->status !== 'approved') {
                return response()->json([
                    'message' => 'This product cannot be ordered because it is not approved yet'
                ], 403);
            }

            // التحقق من المخزون
            if ($product['quantity'] > $pros->stock) {
                return response()->json([
                    'message' => 'Quantity not available',
                    'available_stock' => $pros->stock
                ], 400);
            }

            // تحديث المخزون
            $pros->decrement('stock', $product['quantity']);
            
            // تجهيز بيانات pivot table
            $productsData[$product['id']] = ['quantity' => $product['quantity']];

            // حساب السعر الإجمالي
            $total_price += $pros->price * $product['quantity'];
        }

        // التحقق من المستخدم (مسجل أم زائر)
        $user = Auth::guard('sanctum')->user(); // استخدم guard المناسب (sanctum أو api)

        $order_data = [
            'total_price' => $total_price,
            'role' => $user ? 'user' : 'guest'
        ];

        if ($user) {
            $order_data['user_id'] = $user->id;
        } else {
            $order_data['customer_name']  = $request->customer_name;
            $order_data['customer_email'] = $request->customer_email;
        }

        // إنشاء الطلب
        $order = Order::create($order_data);

        // إضافة المنتجات للطلب
        $order->products()->attach($productsData);

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

    return DB::transaction(function () use ($request, $order, $validated) {

        $order->load('products');

        //  تحقق من كل المنتجات القديمة (status only)
        foreach ($order->products as $oldProduct) {
            if ($oldProduct->status !== 'approved') {
                return response()->json([
                    'message' => 'This product cannot be modified because it is not approved yet'
                ], 403);
            }
        }

        //  تحقق من كل المنتجات الجديدة (status + stock)
        foreach ($request->products as $product) {
            $pros = Product::findOrFail($product['id']);
            if ($pros->status !== 'approved') {
                return response()->json([
                    'message' => 'This product cannot be modified because it is not approved yet'
                ], 403);
            }
            if ($product['quantity'] > $pros->stock) {
                return response()->json([
                    'message' => 'Quantity not available',
                    'available_stock' => $pros->stock
                ], 400);
            }
        }

        //  ارجع stock للمنتجات القديمة بعد التحقق
        foreach ($order->products as $oldProduct) {
            $oldProduct->increment('stock', $oldProduct->pivot->quantity);
        }

        // 4️⃣ حدث order الرئيسي
        $order->update($validated);

        // حضر بيانات الـ pivot وحدث stock للمنتجات الجديدة
        $syncData = [];
        $total_price = 0;
        foreach ($request->products as $product) {
            $pros = Product::findOrFail($product['id']);
            $pros->decrement('stock', $product['quantity']);
            $total_price += $pros->price * $product['quantity'];
            $syncData[$product['id']] = ['quantity' => $product['quantity']];
        }

        $order->update(['total_price' => $total_price]);
        $order->products()->sync($syncData);

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->load('products'),
        ]);
    });
}
public function destroy(Order $order)
{
    $this->authorize('delete', $order);

    return DB::transaction(function () use ($order) {

        $order->load('products');

        //  تحقق من كل المنتجات قبل الحذف (اختياري حسب requirement)
        foreach ($order->products as $product) {
            if ($product->status !== 'approved') {
                return response()->json([
                    'message' => 'This order cannot be deleted because it contains products that are not approved'
                ], 403);
            }
        }
        //  ارجع stock للمنتجات
        foreach ($order->products as $product) {
            $product->increment('stock', $product->pivot->quantity);
        }
        //  احذف الـ pivot records
        $order->products()->detach();

        //  احذف الـ order نفسه
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    });
}
    public function index(){
        $this->authorize('viewAny',Order::class);
        $orders=Order::all();
        return response()->json([
            'message'=>'success',
            'orders'=>$orders
        ]);
    }
    public function getAllOrdersInDifferentSituations()
{
    $this->authorize('viewAny', Order::class);

    $orders = Order::withTrashed()
        ->with(['products' => function ($q) {
            $q->withPivot('quantity');
        }])
        ->get();

    $orders = $orders->map(function ($order) {
        return [
            'order_id' => $order->id,
            'is_deleted' => $order->trashed(),
            'products' => $order->products->map(function ($product) {
                return [
                    'price' => $product->price,
                    'name' => $product->name,
                    'description' => $product->description,
                    'id' => $product->id,
                    'stock' => $product->stock,
                    'quantity' => $product->pivot->quantity,
                ];
            })
        ];
    });

    return response()->json([
        'message' => 'success',
        'orders' => $orders
    ], 200);
}

    public function show(Order $order){
         $this->authorize('view',$order);
        return response()->json([
            'message'=>'success',
            'order'=>$order
        ],200);
    }
   }




 

