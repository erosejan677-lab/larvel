<?php

namespace App\Repositories\V1\Eloquent;

use App\Models\Product;

class ProductRepository
{
    /**
     * Create a new product record.
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }
}
