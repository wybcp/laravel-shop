<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\OrderItem;
use App\Models\Product;
use Auth;
use function boolval;
use function compact;
use Illuminate\Http\Request;
use function view;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $builder = Product::query()->where('on_sale', true);
        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orwhere('description', 'like', $like)
                    ->orwherehas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }
        if ($order = $request->input('order', '')) {
            // 是否是以 _asc 或者 _desc 结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根据传入的排序值来构造排序参数
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }
        $products = $builder->paginate(16);

        $filters = ['search' => $search, 'order' => $order];

        return view('products.index', compact(['products', 'filters']));
    }

    public function show(Product $product, Request $request)
    {
        if (!$product->on_sale) {
            throw new InvalidRequestException('商品未上架');
        }
        $is_favor = false;
        if ($user = Auth::user()) {
            $is_favor = boolval($user->favoriteProducts()->find($product->id));
        }

        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku'])
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at')
            ->orderBy('reviewed_at', 'desc')
            ->limit(10)
            ->get();
        return view('products.show', [
            'product'  => $product,
            'is_favor' => $is_favor,
            'reviews'  => $reviews,
        ]);
    }

    public function favor(Product $product, Request $request)
    {
        $user = Auth::user();
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }
        $user->favoriteProducts()->attach($product);
        return [];
    }

    public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();
        $user->favoriteProducts()->detach($product);

        return [];
    }

}
