<?php

use Illuminate\Database\Seeder;

class UserAddressesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //        取出所有用户id
        $user_ids = \App\Models\User::all()->pluck('id')->toArray();

        // 获取 Faker 实例
        $faker = app(Faker\Generator::class);
        $addresses = factory(\App\Models\UserAddress::class)->times(3)->make()->each(
            function ($address) use ($user_ids, $faker) {
                // 从用户 ID 数组中随机取出一个并赋值
                $address->user_id = $faker->randomElement($user_ids);
            })
        ;

        \App\Models\UserAddress::insert($addresses->toArray());
    }
}
