<?php

namespace App\Http\Controllers;

use function abort;
use App\Models\CouponCode;
use Carbon\Carbon;
use function response;

class CouponCodesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => []]);
    }

    public function show(string $code)
    {
        if ((!$record = CouponCode::where('code', $code)->first()) || !$record->enabled) {
            abort(404);
        }

        if ($record->total - $record->used < 0) {
            return response()->json(['msg' => '该优惠券已用完'], 403);
        }
        if ($record->effected_at && $record->effected_at->gt(Carbon::now())) {
            return response()->json(['msg' => '该优惠券未生效'], 403);
        }
        if ($record->invalid_at && $record->invalid_at->lt(Carbon::now())) {
            return response()->json(['msg' => '该优惠券已过期'], 403);
        }
        return $record;

    }
}
