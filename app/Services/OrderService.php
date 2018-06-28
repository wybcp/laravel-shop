<?php

namespace App\Services;

use function app;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\Carbon;
use function config;
use DB;
use function dispatch;

class OrderService
{
    public function store(User $user, UserAddress $address, $remark, $items)
    {
        $order = DB::transaction(function () use ($user, $address, $remark, $items) {
            $address->update(['last_used_at' => Carbon::now()]);

            $order = new Order([
                'address'      => [
                    'address'       => $address->full_address,
                    'zip'           => $address->zip,
                    'contact_name'  => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark'       => $remark,
                'total_amount' => 0,
            ]);

            $order->user()->associate($user);
            $order->save();
            $totalAmount = 0;
            // 遍历用户提交的 SKU
            foreach ($items as $data) {
                $sku = ProductSku::find($data['sku_id']);
                // 创建一个 OrderItem 并直接与当前订单关联
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price'  => $sku->price,
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $data['amount'];
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }
            // 更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id')->all();
/*CartService 的调用方式改为了通过 app() 函数创建，因为这个 store() 方法是我们手动调用的，
无法通过 Laravel 容器的自动解析来注入。在我们代码里调用封装的库时一定 不可以 使用 new 关键字来初始化，
而是应该通过 Laravel 的容器来初始化，因为在之后的开发过程中 CartService 类的构造函数可能会发生变化，
比如注入了其他的类，如果我们使用 new 来初始化的话，就需要在每个调用此类的地方进行修改；
而使用 app() 或者自动解析注入等方式 Laravel 则会自动帮我们处理掉这些依赖。*/
            app(CartService::class)->remove($skuIds);
            return $order;
        });

        dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
    }
}