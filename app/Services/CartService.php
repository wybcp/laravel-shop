<?php
namespace App\Services;

use App\Models\CartItem;
use Auth;
use function is_array;

class CartService{

    public function get()
    {
        return Auth::user()->cartItems()->with(['productSku.product'])->get();
    }

    public function add(int $sku_id,int $amount)
    {
        $user=Auth::user();

        if ($item=$user->cartItems()->where('product_sku_id',$sku_id)->first()){
            $item->update([
                'amount'=>$item->amount+$amount,
            ]);
        }else{
            $item=new CartItem(['amount'=>$amount]);
            $item->user()->associate($user);
            $item->productSku()->associate($sku_id);
            $item->save();
        }

        return $item;
    }

    public function remove($sku_ids)
    {
        if (!is_array($sku_ids)){
            $sku_ids=[$sku_ids];
        }
        Auth::user()->cartItems()->whereIn('product_sku_id',$sku_ids)->delete();
    }
}