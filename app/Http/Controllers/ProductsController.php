<?php

namespace App\Http\Controllers;

use App\Models\Product;
use function compact;
use Illuminate\Http\Request;
use function view;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $products=Product::query()->where('on_sale',true)->paginate(16);

        return view('products.index',compact('products'));
    }
}
