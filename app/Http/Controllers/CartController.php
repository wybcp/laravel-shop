<?php

namespace App\Http\Controllers;


use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use Auth;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => []]);
    }

    public function add(AddCartRequest $request)
    {
        $user = Auth::user();
        $sku_id = $request->input('sku_id');
        $amount = $request->input('amount');

        if ($cart = $user->cartItem()->where('product_sku_id', $sku_id)->first()) {
            $cart->update([
                'amount' => $cart->amount + $amount,
            ]);
        } else {
            $cart = new CartItem(['amount' => $amount]);
            $cart->user()->associate($user);
            $cart->productSku()->associate($sku_id);
            $cart->save();
        }

        return [];
    }
}
