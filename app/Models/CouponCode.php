<?php

namespace App\Models;

use App\Exceptions\CouponCodeUnavailableException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use function is_null;
use function number_format;
use function str_replace;
use function strtoupper;

class CouponCode extends Model
{
//    优惠券类型
    const TYPE_FIXED   = 'fixed';
    const TYPE_PERCENT = 'percent';

    public static $type_map = [
        self::TYPE_FIXED   => '固定金额',
        self::TYPE_PERCENT => '比例',
    ];

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'total',
        'used',
        'min_amount',
        'effected_at',
        'invalid_at',
        'enabled',
    ];

    protected $casts = [
        'enabled',
    ];

    protected $dates   = [
        'effected_at',
        'invalid_at',
    ];
    protected $appends = [
        'description',
    ];

    public static function getAvailableCode(int $length = 16)
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random($length));
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }

    public function getDescriptionAttribute()
    {
        $str = '';
        $value = str_replace('.00', '', $this->value);

        if ($this->min_amount > 0) {
            $str = '满' . str_replace('.00', '', $this->min_amount);
        }
        if ($this->type === self::TYPE_PERCENT) {
            return $str . '优惠' . $value . '%';
        }

        return $str . '减' . $value;
    }

    public function checkAvailable(User $user, $order_amount = null)
    {
        if (!$this->enabled) {
            throw new CouponCodeUnavailableException('优惠券不存在');
        }
        if ($this->total - $this->used <= 0) {
            throw new CouponCodeUnavailableException('该优惠券已被兑完');
        }
        if ($this->effected_at && $this->effected_at->gt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券现在还不能使用');
        }

        if ($this->invalid_at && $this->invalid_at->lt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券已过期');
        }

        if (!is_null($order_amount) && $this->min_amount >= $order_amount) {
            throw new CouponCodeUnavailableException('订单金额不满足该优惠券最低金额');
        }

        $used = Order::where('user_id', $user->id)
            ->where('coupon_code_id', $this->id)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereNull('paid_at')->where('closed', false);
                })->orWhere(function ($query) {
                    $query->whereNotNull('paid_at')
                        ->where('refund_status', Order::REFUND_STATUS_PENDING);
                });
            })->exists();
        if ($used) {
            throw new CouponCodeUnavailableException('你已使用过这张优惠券');
        }

    }

    public function getAdjustPrice($order_amount)
    {
        if ($this->type === self::TYPE_FIXED) {
            return max(0.01, $order_amount - $this->value);
        }

        return number_format($order_amount * (100 - $this->value) / 100, 2);
    }

    public function changeUsed($increase = true)
    {
        // 传入 true 代表新增用量，否则是减少用量
        if ($increase) {
            // 与检查 SKU 库存类似，这里需要检查当前用量是否已经超过总量
            return $this->newQuery()->where('id', $this->id)->where('used', '<', $this->total)->increment('used');
        } else {
            return $this->decrement('used');
        }
    }
}

