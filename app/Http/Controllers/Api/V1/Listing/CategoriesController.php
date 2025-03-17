<?php

namespace App\Http\Controllers\Api\V1\Listing;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    use ApiResponse;

    /**
     * Fetch all categories grouped by category group.
     */
    public function index()
    {
        // Group the categories by the 'group' field (Men, Women, Kids, Everything else)
        $categories = Category::all()->groupBy('group');

        return $this->successResponse($categories);
    }
}
