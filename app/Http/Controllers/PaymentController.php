<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Carbon\Carbon;
use function config;
use function dd;
use Endroid\QrCode\QrCode;
use Exception;
use Illuminate\Http\Request;
use Log;
use function response;
use function typeOf;
use Yansongda\LaravelPay\Facades\Pay;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['alipayNotify']]);
    }

    public function payByAlipay(Order $order)
    {
        $this->authorize('own', $order);

        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不对');
        }
        $data = [
            'out_trade_no' => $order->order_no,
            'total_amount' => $order->total_amount,
            'subject'      => '支付laravel shop 的订单' . $order->order_no,
        ];

        return Pay::alipay()->web($data);

    }

    public function alipayReturn()
    {
        try {
            Pay::alipay()->verify();
        } catch (Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        return view('pages.success', ['msg' => '付款成功']);

    }

    public function alipayNotify()
    {
        // 校验输入参数
        $data = Pay::alipay()->verify();
        // $data->out_trade_no 拿到订单流水号，并在数据库中查询
        $order = Order::where('order_no', $data->out_trade_no)->first();
        // 正常来说不太可能出现支付了一笔不存在的订单，这个判断只是加强系统健壮性。
        if (!$order) {
            return 'fail';
        }
        // 如果这笔订单的状态已经是已支付
        if ($order->paid_at) {
            // 返回数据给支付宝
            return Pay::alipay()->success();
        }

        $order->update([
            'paid_at'        => Carbon::now(), // 支付时间
            'payment_method' => 'alipay', // 支付方式
            'payment_no'     => $data->trade_no, // 支付宝订单号
        ]);

        return Pay::alipay()->success();

    }

    public function payByWechat(Order $order)
    {
        $this->authorize('own',$order);

        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }

        $wechat_order=Pay::wechat()->scan([
            'out_trade_no' => $order->order_no,
            'total_fee'    => $order->total_amount * 100,
            'body'         => '支付 Laravel Shop 的订单：'.$order->no,
        ]);

        $qr_code=new QrCode($wechat_order->code_url);

        return response($qr_code->writeString(),200,['Content-Type'=>$qr_code->getContentType()]);
    }

}
