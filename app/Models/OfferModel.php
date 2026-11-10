<?php

namespace App\Models;

use CodeIgniter\Model;

class OfferModel extends Model
{
    protected $table         = 'offers';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'listing_id', 'recycler_id', 'offered_price', 'counter_price', 'final_price', 'note',
        'status', 'responded_at', 'completed_at',
    ];

    /** Pengajuan yang masih "hidup" (belum final). */
    public const ACTIVE = ['menunggu', 'counter', 'diterima'];

    /** Detail pengajuan + data listing + kedua pihak. */
    public function detail(int $id): ?array
    {
        return $this->select(
            'offers.*, listings.title AS listing_title, listings.user_id AS owner_id, listings.status AS listing_status, '
            . 'listings.price_type, listings.price AS listing_price, listings.handover, listings.city AS listing_city, '
            . 'listings.address AS listing_address, listings.weight_kg, '
            . 'o.name AS owner_name, o.phone AS owner_phone, o.rating_avg AS owner_rating, '
            . 'r.name AS recycler_name, r.phone AS recycler_phone, r.rating_avg AS recycler_rating, r.city AS recycler_city'
        )
            ->join('listings', 'listings.id = offers.listing_id')
            ->join('users o', 'o.id = listings.user_id')
            ->join('users r', 'r.id = offers.recycler_id')
            ->where('offers.id', $id)->first();
    }

    /** Daftar pengajuan untuk satu listing (dilihat penyetor). */
    public function forListing(int $listingId): array
    {
        return $this->select('offers.*, r.name AS recycler_name, r.rating_avg AS recycler_rating, r.rating_count AS recycler_rating_count, r.city AS recycler_city')
            ->join('users r', 'r.id = offers.recycler_id')
            ->where('offers.listing_id', $listingId)
            ->orderBy('offers.id', 'DESC')->findAll();
    }

    private function listQuery()
    {
        return $this->select(
            'offers.*, listings.title AS listing_title, listings.user_id AS owner_id, listings.status AS listing_status, '
            . 'o.name AS owner_name, r.name AS recycler_name, '
            . '(SELECT filename FROM listing_images li WHERE li.listing_id = listings.id ORDER BY li.sort_order, li.id LIMIT 1) AS cover'
        )
            ->join('listings', 'listings.id = offers.listing_id')
            ->join('users o', 'o.id = listings.user_id')
            ->join('users r', 'r.id = offers.recycler_id');
    }

    public function byRecycler(int $userId, ?array $statuses = null): array
    {
        $q = $this->listQuery()->where('offers.recycler_id', $userId);
        if ($statuses) {
            $q->whereIn('offers.status', $statuses);
        }

        return $q->orderBy('offers.updated_at', 'DESC')->findAll();
    }

    public function byOwner(int $userId, ?array $statuses = null): array
    {
        $q = $this->listQuery()->where('listings.user_id', $userId);
        if ($statuses) {
            $q->whereIn('offers.status', $statuses);
        }

        return $q->orderBy('offers.updated_at', 'DESC')->findAll();
    }

    public function activeFor(int $listingId, int $recyclerId): ?array
    {
        return $this->where('listing_id', $listingId)
            ->where('recycler_id', $recyclerId)
            ->whereIn('status', self::ACTIVE)->first();
    }

    /** Harga yang sedang berlaku dalam negosiasi (untuk ditampilkan). */
    public static function currentPrice(array $o): int
    {
        if ($o['final_price'] !== null) {
            return (int) $o['final_price'];
        }
        if ($o['status'] === 'counter' && $o['counter_price'] !== null) {
            return (int) $o['counter_price'];
        }

        return (int) $o['offered_price'];
    }
}
