<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Exception;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            if($request->status == 'all') {
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

    public function editStatus(Request $request, $id) {
        
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



}
