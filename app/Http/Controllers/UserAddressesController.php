<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAddressRequest;
use App\Models\UserAddress;
use function compact;
use function dd;
use Illuminate\Http\Request;
use function redirect;
use function view;

class UserAddressesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $addresses = $request->user()->addresses;
        return view('user_addresses.index', compact('addresses'));
    }

    public function create(UserAddress $address)
    {
        return view('user_addresses.create_and_edit', compact('address'));
    }

    public function store(UserAddressRequest $request)
    {
        $request->user()->addresses()->create($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        ]))
        ;
        return redirect()->route('user_addresses.index');
    }
}
