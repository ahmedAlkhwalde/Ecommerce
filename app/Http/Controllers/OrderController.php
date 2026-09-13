<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Traits\HasDynamicNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    use HasDynamicNotification;
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
                'status' => 'required|in:completed,processing,cancelled',
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
                $this->sendNotification(
                    $user,
                    'طلب جديد',
                    "تمت إضافة طلب جديد برقم: {$order->id} بمبلغ إجمالي: {$totalPrice}",
                    'order_created',
                );

                $cart->products()->detach();
                Log::info('تم إنشاء الطلب بنجاح', [
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'total_price' => $totalPrice,
                ]);
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
            $userId = Auth::id();

            $order = Order::with('products')->where('user_id', $userId)->find($id);

            if (!$order) {
                return response()->json([
                    'message' => 'الطلب غير موجود.',
                ], 404);
            }

            if ($order->status === 'cancelled') {
                return response()->json([
                    'message' => 'الطلب ملغى بالفعل.',
                ], 400);
            }

            if (in_array($order->status, ['completed', 'processing'])) {
                return response()->json([
                    'message' => 'لا يمكن إلغاء الطلب لأنه في حالة ' . $order->status . '.',
                ], 400);
            }

            DB::transaction(function () use ($order) {
                foreach ($order->products as $product) {
                    $quantity = (int)$product->pivot->quantity;
                    $product->increment('stock', $quantity);
                }

                $order->status = 'cancelled';
                $order->save();
            });
            $this->sendNotification(
                Auth::user(),
                'طلب جديد!',
                'تم إلغاء الطلب وإعادة الكميات للمخزون بنجاح.',
                'order_cancelled',
                ['order_id' => $order->id]
            );

            Log::info('تم إلغاء الطلب وإعادة الكميات للمخزون بنجاح.', [
                'user_id' => $userId,
                'order_id' => $order->id,
                'status' => $order->status,
            ]);
            return response()->json([
                'message' => 'تم إلغاء الطلب وإعادة الكميات للمخزون بنجاح.',
                'data'    => $order->fresh(['products']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إلغاء الطلب.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
