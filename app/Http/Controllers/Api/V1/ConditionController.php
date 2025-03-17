<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Condition;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ConditionController extends Controller
{
    use ApiResponse;

    public function index() {
        $conditions = Condition::all();

        return $this->successResponse($conditions);
    }
}
