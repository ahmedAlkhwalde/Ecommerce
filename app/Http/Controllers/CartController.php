<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index() {
        $user = Auth::user()->id;
        $cart = $user->cart()->with('products')->first();
        if (!$cart) {
            return response()->json([
                'message' => 'لم يتم العثور على سلة التسوق.',
            ], 404);
        }
        return response()->json($cart);
    }
}
