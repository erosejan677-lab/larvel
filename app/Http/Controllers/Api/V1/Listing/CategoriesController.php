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
    public function index(Request $request)
    {
        // Group the categories by the 'group' field (Men, Women, Kids, Everything else)
        $categoriesQuery = Category::query();

        if ($request->query('group')) {
            $categoriesQuery->where('group', $request->query('group'));
        }

        $categories = $categoriesQuery->get()->groupBy('group');

        return $this->successResponse($categories);
    }

    public function show($id) {
        $category = Category::find($id);
        return $this->successResponse($category);
    }
}
