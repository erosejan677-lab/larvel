<?php

namespace App\Http\Controllers\Api\V1\Listing;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    use ApiResponse;
    public function index() {
        $brands = Brand::all();
        return $this->successResponse($brands);
    }
}
