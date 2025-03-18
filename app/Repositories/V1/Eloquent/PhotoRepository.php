<?php

namespace App\Repositories\V1\Eloquent;

use App\Models\Photo;

class PhotoRepository
{
    /**
     * Create a new photo record.
     *
     * @param array $data
     * @return Photo
     */
    public function create(array $data)
    {
        return Photo::create($data);
    }
}
