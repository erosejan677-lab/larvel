<?php

namespace App\Http\Requests\Api\V1\Listing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id'       => 'sometimes|required|exists:users,id',
            'title'         => 'sometimes|required|string|max:255',
            'description'   => 'sometimes|nullable|string',
            'price'         => 'sometimes|required|numeric',
            'category_id'   => 'sometimes|required|exists:categories,id',
            'brand_id'      => 'sometimes|required|exists:brands,id',
            'condition_id'  => 'sometimes|required|exists:conditions,id',
            'address_id'    => 'sometimes|required|exists:addresses,id',
            'location'      => 'sometimes|required|string|max:20',
            'city'          => 'sometimes|required|string|max:20',
            'shipping_type' => 'sometimes|required|string|max:50',
            // Validate images array. Each file must be an image of the allowed types.
            'images'        => 'sometimes|nullable|array|max:8',
            'images.*'      => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:4028',
        ];
    }
}
