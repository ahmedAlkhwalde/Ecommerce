<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->status == 'all') {
                $orders = Order::with('products')->get();
            } else if ($request->status == 'pending' || $request->status == 'completed' || $request->status == 'processing' || $request->status == 'canceled') {
                $orders = Order::with('products')->where('status', $request->status)->get();
            } else {
                return response()->json([
                    'message' => 'حالة الطلب غير صالحة.',
                ], 400);
            }

            if (!$orders) {
                return response()->json([
                    'message' => 'حدث خطأ أثناء جلب الطلبات.',
                ], 500);
            }
            return response()->json([
                'message' => 'تم جلب الطلبات بنجاح.',
                'data'    => $orders,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب الطلبات.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function editStatus(Request $request, $id)
    {

        try {
            $request->validate([
                'status' => 'required|in:completed,processing,canceled',
            ]);
            $order = Order::find($id);
            if (!$order) {
                return response()->json([
                    'message' => 'الطلب غير موجود.',
                ], 404);
            }
            $status = $request->status;
            $order->status = $status;
            $order->save();
            return response()->json([
                'message' => 'تم تحديث حالة الطلب الى (' . $status . ') بنجاح.',
                'data'    => $order,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء تحديث حالة الطلب.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function myorders(Request $request)
    {
        try {
            $user = Auth::user()->id;
            if ($request->status == 'all') {
                $orders = Order::where('user_id', $user)->with('products')->get();
            } else if ($request->status == 'pending' || $request->status == 'completed' || $request->status == 'processing' || $request->status == 'canceled') {
                $orders = Order::with('products')->where('status', $request->status)->get();
            } else {
                return response()->json([
                    'message' => 'حالة الطلب غير صالحة.',
                ], 400);
            }

            if (!$orders) {
                return response()->json([
                    'message' => 'حدث خطأ أثناء جلب الطلبات.',
                ], 500);
            }
            return response()->json([
                'message' => 'تم جلب الطلبات بنجاح.',
                'data'    => $orders,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب الطلبات.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $cart = $user->cart()->with('products')->first();

            if (!$cart || $cart->products->isEmpty()) {
                return response()->json([
                    'message' => 'سلة التسوق فارغة.',
                ], 400);
            }

            return DB::transaction(function () use ($cart, $user) {

                foreach ($cart->products as $product) {
                    if ($product->stock < $product->pivot->quantity) {
                        throw new Exception("الكمية المتاحة للمنتج '{$product->title}' غير كافية في المخزون.");
                    }
                }

                $totalPrice = $cart->products->sum(function ($product) {
                    return $product->price * $product->pivot->quantity;
                });

                $order = Order::create([
                    'user_id'     => $user->id,
                    'total_price' => $totalPrice,
                    'status'      => 'pending',
                ]);

                foreach ($cart->products as $product) {
                    $quantity = $product->pivot->quantity;

                    $order->products()->attach($product->id, [
                        'quantity'   => $quantity,
                        'unit_price' => $product->price,
                    ]);

                    $product->decrement('stock', $quantity);
                }

                $cart->products()->detach();

                return response()->json([
                    'message' => 'تم إنشاء الطلب وتحديث المخزون بنجاح.',
                    'data'    => $order->load('products'),
                ], 201);
            });
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء الطلب.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function cancel($id)
    {
        try {
            $user = Auth::user()->id;
            $order = Order::where('user_id', $user)->find($id);
            if (!$order) {
                return response()->json([
                    'message' => 'الطلب غير موجود.',
                ], 404);
            }
            if ($order->status === 'cancelled') {
                return response()->json([
                    'message' => 'الطلب ملغى بالفعل.',
                ], 400);
            } else if ($order->status === 'completed' || $order->status === 'processing') {
                return response()->json([
                    'message' => 'لا يمكن إلغاء الطلب لأنه في حالة ' . $order->status . '.',
                ], 400);
            }
            $order->status = 'cancelled';
            $order->save();
            return response()->json([
                'message' => 'تم إلغاء الطلب بنجاح.',
                'data'    => $order,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إلغاء الطلب.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
