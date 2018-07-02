<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Carbon\Carbon;
use function config;
use function dd;
use Endroid\QrCode\QrCode;
use function event;
use Exception;
use Illuminate\Http\Request;
use Log;
use function parse_xml;
use function response;
use function typeOf;
use Yansongda\LaravelPay\Facades\Pay;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => [
                'alipayNotify',
                'wechatNotify',
                'wechatRefundNotify',
            ]
        ]);
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

    /**
     * alipay 支付完成服务器异步通知处理
     * @return string
     */
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

        $this->afterPaid($order);
        return Pay::alipay()->success();

    }

    public function payByWechat(Order $order)
    {
        $this->authorize('own', $order);

        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }

        $wechat_order = Pay::wechat()->scan([
            'out_trade_no' => $order->order_no,
            'total_fee'    => $order->total_amount * 100,
            'body'         => '支付 Laravel Shop 的订单：' . $order->no,
        ]);

        $qr_code = new QrCode($wechat_order->code_url);

        return response($qr_code->writeString(), 200, ['Content-Type' => $qr_code->getContentType()]);
    }


    /**
     * 订单支付完成之后异步调用的事件
     * @param Order $order
     */
    protected function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }

    public function wechatRefundNotify(Request $request)
    {
        // 给微信的失败响应
        $failXml = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        // 把请求的 xml 内容解析成数组
        $input = parse_xml($request->getContent());
        // 如果解析失败或者没有必要的字段，则返回错误
        if (!$input || !isset($input['req_info'])) {
            return $failXml;
        }
        // 对请求中的 req_info 字段进行 base64 解码
        $encryptedXml = base64_decode($input['req_info'], true);
        // 对解码后的 req_info 字段进行 AES 解密
        $decryptedXml = openssl_decrypt($encryptedXml, 'AES-256-ECB', md5(config('pay.wechat.key')), OPENSSL_RAW_DATA, '');
        // 如果解密失败则返回错误
        if (!$decryptedXml) {
            return $failXml;
        }
        // 解析解密后的 xml
        $decryptedData = parse_xml($decryptedXml);
        // 没有找到对应的订单，原则上不可能发生，保证代码健壮性
        if (!$order = Order::where('no', $decryptedData['out_trade_no'])->first()) {
            return $failXml;
        }

        if ($decryptedData['refund_status'] === 'SUCCESS') {
            // 退款成功，将订单退款状态改成退款成功
            $order->update([
                'refund_status' => Order::REFUND_STATUS_SUCCESS,
            ]);
        } else {
            // 退款失败，将具体状态存入 extra 字段，并表退款状态改成失败
            $extra = $order->extra;
            $extra['refund_failed_code'] = $decryptedData['refund_status'];
            $order->update([
                'refund_status' => Order::REFUND_STATUS_FAILED,
            ]);
        }

        return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
    }
}
