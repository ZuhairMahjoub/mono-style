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

            // قفل المخزون لمنع التزامن
            $pros = Product::lockForUpdate()->findOrFail($product['id']);

            // التحقق من حالة المنتج
            if ($pros->status !== 'approved') {
                return response()->json([
                    'message' => 'This product cannot be ordered because it is not approved yet'
                ], 403);
            }

            // تحقق المخزون
            if ($product['quantity'] > $pros->stock) {
                return response()->json([
                    'message' => 'Quantity not available',
                    'available_stock' => $pros->stock
                ], 400);
            }

            // تحديث المخزون
            $pros->decrement('stock', $product['quantity']);

            // زيادة السعر الإجمالي
            $total_price += $pros->price * $product['quantity'];
        }

        // المستخدم (مسجل أو زائر)
        $user = Auth::guard('sanctum')->user();

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

        // إنشاء العناصر (order_items)
        foreach ($request->products as $product) {

            $pros = Product::find($product['id']);

            $order->items()->create([
                'product_id' => $pros->id,         // ممكن ينحذف لاحقاً
                'name'       => $pros->name,       // اسم المنتج وقت الشراء
                'price'      => $pros->price,      // سعر المنتج وقت الشراء
                'quantity'   => $product['quantity'],
            ]);
        }

        return response()->json([
            'message' => 'Operation Completed Successfully',
            'order' => $order->load('items'),
            'total_price' => $total_price,
        ]);
    });
}

public function update(OrderUpdateRequest $request, Order $order)
{
    $this->authorize('update', $order);

    return DB::transaction(function () use ($request, $order) {

        // 1) أرجع المخزون القديم
        foreach ($order->items as $item) {
            if ($item->product_id) {
                $product = Product::lockForUpdate()->find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }
        }

        // 2) حذف العناصر القديمة
        $order->items()->delete();

        // 3) إعادة حساب السعر
        $total_price = 0;

        // 4) إضافة العناصر الجديدة وتحديث المخزون
        foreach ($request->products as $p) {

            $product = Product::lockForUpdate()->findOrFail($p['id']);

            if ($product->status !== 'approved') {
                return response()->json([
                    'message' => 'This product cannot be ordered because it is not approved yet'
                ], 403);
            }

            if ($p['quantity'] > $product->stock) {
                return response()->json([
                    'message' => 'Quantity not available',
                    'available_stock' => $product->stock
                ], 400);
            }

            // خصم الكمية
            $product->decrement('stock', $p['quantity']);

            // حساب السعر
            $total_price += $product->price * $p['quantity'];

            // إنشاء العنصر
            $order->items()->create([
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => $product->price,
                'quantity'   => $p['quantity']
            ]);
        }

        // 5) تحديث الطلب
        $order->update([
            'total_price' => $total_price,
        ]);

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->load('items'),
        ]);
    });
}


public function destroy(Order $order)
{
    $this->authorize('delete', $order);

    return DB::transaction(function () use ($order) {

        // حمل العناصر
        $order->load('items');

        //  رجّع المخزون فقط لو المنتج ما زال موجود
        foreach ($order->items as $item) {
            if ($item->product_id) {
                $product = Product::lockForUpdate()->find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }
        }

        //  احذف العناصر (order_items)
        $order->items()->delete();

        //  احذف الطلب نفسه
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    });
}


    
    public function index()
{
    $this->authorize('viewAny', Order::class);

    $orders = Order::with([
        'items.product' // تحميل order items + معلومات المنتج
    ])->get();

    return response()->json([
        'message' => 'success',
        'orders' => $orders
    ]);
}


public function getAllOrdersInDifferentSituations()
{
    $this->authorize('viewAny', Order::class);

    $orders = Order::withTrashed()
        ->with([
            'items' => function ($q) {
                $q->withTrashed();   // مهم جداً
            },
            'items.product'
        ])
        ->get()
        ->map(function ($order) {
            return [
                'order_id'   => $order->id,
                'is_deleted' => $order->trashed(),

                'items' => $order->items->map(function ($item) {
                    return [
                        'product_id'  => $item->product_id,
                        'name'        => $item->name,
                        'price'       => $item->price,
                        'quantity'    => $item->quantity,
                        'is_deleted'  => $item->trashed(), // إذا بدك تعرف إذا محذوف

                        'product' => $item->product ? [
                            'id'          => $item->product->id,
                            'stock'       => $item->product->stock,
                            'description' => $item->product->description,
                            'status'      => $item->product->status,
                        ] : null
                    ];
                })
            ];
        });

    return response()->json([
        'message' => 'success',
        'orders'  => $orders
    ]);
}


    public function show(Order $order){
         $this->authorize('view',$order);
        return response()->json([
            'message'=>'success',
            'order'=>$order
        ],200);
    }
   }




 

