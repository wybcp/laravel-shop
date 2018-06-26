<?php
/**
 * Created by PhpStorm.
 * User: riverside
 * Date: 2018/6/26
 * Time: 14:31
 */

namespace App\Observers;


use App\Models\Order;

class OrderObserver
{
    public function creating(Order $order)
    {
//        if (!$order->order_no) {
            // 调用 findAvailableNo 生成订单流水号
            $order->order_no = $order::findAvailableNo();
            // 如果生成失败，则终止创建订单
            if (!$order->order_no) {
                return false;
            }
//        }
    }

}