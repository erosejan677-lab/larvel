<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\CreateAddressRequest;
use App\Models\Address;
use App\Traits\ApiResponse;

class AddressController extends Controller
{
    use ApiResponse;

    public function store(CreateAddressRequest $request) {
        $input = $request->validated();

        $user = auth()->user();

        $address = $user->addresses()->create($input);

        return $this->successResponse($address);

    }

    public function show()
    {
        $user = auth()->user();
        $address = $user->addresses;

        if (!$address) {
            return $this->noContentResponse();
        }

        return $this->successResponse($address);
    }
}
