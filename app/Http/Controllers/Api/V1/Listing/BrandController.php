<?php

namespace App\Http\Controllers\Api\V1\Listing;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    use ApiResponse;
    public function index(Request $request) {
        $brandsQuery = Brand::query();

        if ($request->query('name')) {
            $brandsQuery->where('name', 'like', '%' . $request->query('name') . '%');
        }

        $brands = $brandsQuery->get();

        return $this->successResponse($brands);
    }

    public function show($id) {
        $brand = Brand::find($id);
        return $this->successResponse($brand);
    }
}
