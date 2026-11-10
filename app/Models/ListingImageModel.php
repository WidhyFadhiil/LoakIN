<?php

namespace App\Models;

use CodeIgniter\Model;

class ListingImageModel extends Model
{
    protected $table         = 'listing_images';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['listing_id', 'filename', 'sort_order', 'created_at'];

    public function forListing(int $listingId): array
    {
        return $this->where('listing_id', $listingId)->orderBy('sort_order')->orderBy('id')->findAll();
    }
}
