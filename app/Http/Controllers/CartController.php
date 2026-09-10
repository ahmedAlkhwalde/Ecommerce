<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartRequest;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $cart = $user->cart()->with('products')->first();
            if (!$cart) {
                return response()->json([
                    'message' => 'لم يتم العثور على سلة التسوق.',
                ], 404);
            }
            return response()->json($cart);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب السلة.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function add(StoreCartRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $user = Auth::user();
            $cart = $user->cart()->firstOrCreate([]);

            $product = Product::find($validatedData['product_id']);

            if (!$product) {
                return response()->json([
                    'message' => 'المنتج غير موجود.',
                ], 404);
            }

            $existingProduct = $cart->products()->where('product_id', $product->id)->first();
            $requestedTotalQuantity = $validatedData['quantity'];

            if ($requestedTotalQuantity > $product->stock) {
                return response()->json([
                    'message' => 'الكمية المطلوبة غير متوفرة في المخزون.',
                    'available_stock' => $product->stock,
                ], 400);
            }

            if ($existingProduct) {
                $cart->products()->updateExistingPivot($product->id, [
                    'quantity' => $requestedTotalQuantity,
                ]);
            } else {
                $cart->products()->attach($product->id, [
                    'quantity' => $validatedData['quantity'],
                ]);
            }

            return response()->json([
                'message' => 'تمت إضافة المنتج إلى السلة بنجاح.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إضافة المنتج إلى السلة.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function updateQuantity(StoreCartRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $user = Auth::user();
            $cart = $user->cart()->first();

            if (!$cart) {
                return response()->json([
                    'message' => 'لم يتم العثور على سلة التسوق.',
                ], 404);
            }

            // 1. التحقق من وجود المنتج داخل السلة أولاً
            $productInCart = $cart->products()->where('product_id', $validatedData['product_id'])->first();

            if (!$productInCart) {
                return response()->json([
                    'message' => 'هذا المنتج غير موجود في سلة التسوق.',
                ], 404);
            }

            if ($productInCart->stock < $validatedData['quantity']) {
                return response()->json([
                    'message' => 'الكمية المطلوبة غير متوفرة في المخزون.',
                    'available_stock' => $productInCart->stock,
                ], 400);
            }

            $cart->products()->updateExistingPivot($validatedData['product_id'], [
                'quantity' => $validatedData['quantity'],
            ]);

            return response()->json([
                'message' => 'تم تحديث كمية المنتج في السلة بنجاح.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء تحديث كمية المنتج في السلة.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function remove(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);

            $user = Auth::user();
            $cart = $user->cart()->first();

            if (!$cart) {
                return response()->json([
                    'message' => 'لم يتم العثور على سلة التسوق.',
                ], 404);
            }

            $productInCart = $cart->products()->where('product_id', $validatedData['product_id'])->first();

            if (!$productInCart) {
                return response()->json([
                    'message' => 'هذا المنتج غير موجود في سلة التسوق.',
                ], 404);
            }

            $cart->products()->detach($validatedData['product_id']);

            return response()->json([
                'message' => 'تمت إزالة المنتج من السلة بنجاح.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إزالة المنتج من السلة.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function clear()
    {
        try {
            $user = Auth::user();
            $cart = $user->cart()->first();

            if (!$cart) {
                return response()->json([
                    'message' => 'لم يتم العثور على سلة التسوق.',
                ], 404);
            }

            $cart->products()->detach();

            return response()->json([
                'message' => 'تمت إزالة جميع المنتجات من السلة بنجاح.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إزالة جميع المنتجات من السلة.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
