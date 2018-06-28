<?php

namespace App\Http\Controllers;


use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use App\Services\CartService;
use Auth;
use function compact;
use function view;

class CartController extends Controller
{
    protected $cart_service;
    public function __construct(CartService $service)
    {
        $this->middleware('auth', ['except' => []]);
        $this->cart_service=$service;
    }

    public function add(AddCartRequest $request)
    {
        $this->cart_service->add($request->input('sku_id'),$request->input('amount'));
        return [];
    }

    public function index()
    {
        $cart_items = $this->cart_service->get();
        $addresses = Auth::user()->addresses()->orderBy('last_used_at', 'desc')->get();
        return view('cart.index', compact(['cart_items', 'addresses']));
    }

    public function destroy(ProductSku $sku)
    {
        $this->cart_service->remove($sku->id);
        return [];
    }
}
