<?php

namespace App\Http\Controllers;


use App\Events\OrderReviewed;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\ApplyRefundRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\SendReviewRequest;
use App\Jobs\CloseOrder;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Services\CartService;
use App\Services\OrderService;
use Auth;
use function back;
use Carbon\Carbon;
use function config;
use DB;
use App\Models\Order;
use function dd;
use function event;
use function ord;
use function view;

class OrdersController extends Controller
{
//    public function store(OrderRequest $request,CartService $cart_service)
//    {
//        $user = Auth::user();
//        $order = DB::transaction(function () use ($user, $request,$cart_service) {
//            $address = UserAddress::find($request->input('address_id'));
//            $address->update(['last_used_at' => Carbon::now()]);
//            // 创建一个订单
//            $order = new Order([
//                'address'      => [ // 将地址信息放入订单中
//                    'address'       => $address->full_address,
//                    'zip'           => $address->zip,
//                    'contact_name'  => $address->contact_name,
//                    'contact_phone' => $address->contact_phone,
//                ],
//                'remark'       => $request->input('remark'),
//                'total_amount' => 0,
//            ]);
//
//            $order->user()->associate($user);
//// 写入数据库
//            $order->save();
//
//            $totalAmount = 0;
//            $items = $request->input('items');
//            // 遍历用户提交的 SKU
//            foreach ($items as $data) {
//                $sku = ProductSku::find($data['sku_id']);
//                // 创建一个 OrderItem 并直接与当前订单关联
//                $item = $order->items()->make([
//                    'amount' => $data['amount'],
//                    'price'  => $sku->price,
//                ]);
//                $item->product()->associate($sku->product_id);
//                $item->productSku()->associate($sku);
//                $item->save();
//                $totalAmount += $sku->price * $data['amount'];
//
//                if ($sku->decreaseStock($data['amount']) <= 0) {
//                    throw new InvalidRequestException('该商品库存不足');
//                }
//            }
//
//            // 更新订单总金额
//            $order->update(['total_amount' => $totalAmount]);
//            // 将下单的商品从购物车中移除
//            $skuIds = collect($request->input('items'))->pluck('sku_id');
//
//            $cart_service->remove($skuIds);
//            return $order;
//        });
//
//        $this->dispatch(new CloseOrder($order, config('app.order_ttl')));
//        return $order;
//    }

    public function store(OrderRequest $request, OrderService $service)
    {
        $user = $request->user();
        $address = UserAddress::find($request->input('address_id'));
        return $service->store($user, $address, $request->input('remark'), $request->input('items'));
    }

    public function index()
    {
        $orders = Order::query()
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate();

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Order $order)
    {
        $this->authorize('own', $order);
//        load() 方法与上一章节介绍的 with() 预加载方法有些类似，称为 延迟预加载，不同点在于 load() 是在已经查询出来的模型上调用，而 with() 则是在 ORM 查询构造器上调用。
        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    public function received(Order $order)
    {
        $this->authorize('own', $order);
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('发货状态不正确');
        }

        $order->update([
            'ship_status' => Order::SHIP_STATUS_RECEIVED,
        ]);

        return $order;
    }

    public function review(Order $order)
    {
        $this->authorize('own', $order);
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评论!');
        }

        return view('orders.review', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    public function sendReview(Order $order, SendReviewRequest $request)
    {
        $this->authorize('own', $order);
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评论!');
        }

        if ($order->reviewed) {
            throw new InvalidRequestException('该订单已支付，不可重复提交');
        }

        $reviews = $request->input('reviews');

        DB::transaction(function () use ($reviews, $order) {
            foreach ($reviews as $review) {
                $order_item = $order->items()->find($review['id']);
                $order_item->update([
                    'rating'      => $review['rating'],
                    'review'      => $review['review'],
                    'reviewed_at' => Carbon::now(),
                ]);
                $order->update(['reviewed' => true]);
            }
            event(new OrderReviewed($order));
        });

        return back();
    }

    public function applyRefund(Order $order, ApplyRefundRequest $request)
    {
        $this->authorize('own', $order);
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可退款!');
        }

        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已申请退款，请不要重复提交!');
        }
        $extra = $order->extra ?: [];
        $extra['refund_reason'] = $request->input('reason');
        // 将订单退款状态改为已申请退款
        $order->update([
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra'         => $extra,
        ]);

        return $order;
    }
}
