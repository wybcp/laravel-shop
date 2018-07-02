<?php

namespace App\Listeners;

use App\Events\OrderReviewd;
use App\Models\OrderItem;
use DB;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateProductRating
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderReviewd $event
     * @return void
     */
    public function handle(OrderReviewd $event)
    {
        $items = $event->getOrder()->items()->with(['product'])->get();
        foreach ($items as $item) {
//            first() 方法接受一个数组作为参数，代表此次 SQL 要查询出来的字段，默认情况下 Laravel 会给数组里面的值的两边加上 ` 这个符号，比如 first(['name', 'email']) 生成的 SQL 会类似：
//select `name`, `email` from xxx
//            如果我们直接传入 first(['count(*) as review_count', 'avg(rating) as rating'])，最后生成的 SQL 肯定是不正确的。
//这里我们用 DB::raw() 方法来解决这个问题，Laravel 在构建 SQL 的时候如果遇到 DB::raw() 就会把 DB::raw() 的参数原样拼接到 SQL 里。
            $result = OrderItem::query()
                ->where('product_id', $item->product_id)
                ->whereHas('order', function ($query) {
                    $query->whereNotNull('paid_at');
                })->first([
                    DB::raw('count(*) as review_count'),
                    DB::raw('avg(rating) as rating'),
                ]);
            $item->product->update([
                'rating'       => $result->rating,
                'review_count' => $result->review_count,
            ]);
        }
    }
}
