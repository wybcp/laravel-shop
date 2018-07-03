<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        $value=str_replace('.00','',$this->value);

        if ($this->min_amount > 0) {
            $str = '满' . str_replace('.00','',$this->min_amount);
        }
        if ($this->type === self::TYPE_PERCENT) {
            return $str . '优惠' . $value . '%';
        }

        return $str . '减' . $value;
    }
}

