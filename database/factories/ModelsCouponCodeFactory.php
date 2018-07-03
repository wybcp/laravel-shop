<?php

use Faker\Generator as Faker;

$factory->define(App\Models\CouponCode::class, function (Faker $faker) {
    $type = $faker->randomElement(array_keys(\App\Models\CouponCode::$type_map));
    $value = ($type === App\Models\CouponCode::TYPE_FIXED ? random_int(1, 200) : random_int(1, 50));

    if ($type === \App\Models\CouponCode::TYPE_FIXED) {
        $min_amount = $value + 0.01;
    } else {
        // 如果是百分比折扣，有 50% 概率不需要最低订单金额
        if (random_int(0, 100) < 50) {
            $min_amount = 0;
        } else {
            $min_amount = random_int(100, 1000);
        }
    }

    return [
        'name'       => join(' ', $faker->words),
        'code'       => \App\Models\CouponCode::getAvailableCode(),
        'type'       => $type,
        'value'      => $value,
        'total'      => random_int(100, 1000),
        'used'       => 0,
        'min_amount' => $min_amount,
        'effected_at' => null,
        'invalid_at'  => null,
        'enabled'    => true,
    ];
});
