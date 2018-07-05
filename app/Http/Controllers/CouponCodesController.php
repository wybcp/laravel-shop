<?php

namespace App\Http\Controllers;

use function abort;
use App\Http\Requests\Request;
use App\Models\CouponCode;
use Carbon\Carbon;
use function response;

class CouponCodesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => []]);
    }

    public function show(string $code,Request $request)
    {
        if ((!$record = CouponCode::where('code', $code)->first()) || !$record->enabled) {
            abort(404);
        }

        $record->checkAvailable($request->user());

        return $record;

    }
}
