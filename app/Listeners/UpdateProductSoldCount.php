<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\OrderItem;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class UpdateProductSoldCount implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  OrderPaid $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        $order = $event->getOrder();
        foreach ($order->items as $item) {
            $product = $item->product;
            // 计算对应商品的销量
            $soldCount = OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', function ($query) {
                    $query->whereNotNull('paid_at');  // 关联的订单状态是已支付
                })->sum('amount');
            // 更新商品销量
            $product->update([
                'sold_count' => $soldCount,
            ]);

//            或直接增加
//            $product->update([
//                'sold_count' => $product->sold_count + $item->amount,
//            ]);


        }
    }
}
