<?php

namespace App\Http\Controllers;


use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use Auth;
use function compact;
use function view;

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

        if ($cart = $user->cartItems()->where('product_sku_id', $sku_id)->first()) {
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

    public function index()
    {
        $cart_items = Auth::user()->cartItems()->with(['productSku.product'])->get();
        $addresses = Auth::user()->addresses()->orderBy('last_used_at', 'desc')->get();
        return view('cart.index', compact(['cart_items', 'addresses']));
    }

    public function destroy(ProductSku $sku)
    {
        Auth::user()->cartItems()->where('product_sku_id', $sku->id)->delete();
        return [];
    }
}
