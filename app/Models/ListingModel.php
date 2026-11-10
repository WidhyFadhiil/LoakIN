<?php

namespace App\Models;

use CodeIgniter\Model;

class ListingModel extends Model
{
    protected $table         = 'listings';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'user_id', 'category_id', 'title', 'description', 'weight_kg', 'item_condition', 'city',
        'address', 'price_type', 'price', 'handover', 'status', 'accepted_offer_id',
        'expires_at', 'completed_at', 'views',
    ];

    public const ST_OPEN      = 'tersedia';
    public const ST_PROCESS   = 'diproses';
    public const ST_DONE      = 'selesai';
    public const ST_CANCELLED = 'dibatalkan';
    public const ST_EXPIRED   = 'kedaluwarsa';

    /** Status yang tampil di pencarian publik. */
    public const PUBLIC_STATUSES = [self::ST_OPEN, self::ST_PROCESS];

    /** Dasar query listing: join kategori, pemilik, dan foto utama. */
    public function baseQuery()
    {
        return $this->select(
            'listings.*, categories.name AS category_name, users.name AS owner_name, users.rating_avg AS owner_rating, '
            . 'users.rating_count AS owner_rating_count, '
            . '(SELECT filename FROM listing_images li WHERE li.listing_id = listings.id ORDER BY li.sort_order, li.id LIMIT 1) AS cover'
        )
            ->join('categories', 'categories.id = listings.category_id')
            ->join('users', 'users.id = listings.user_id');
    }

    /**
     * Pencarian publik dengan filter: q, category, city, price (gratis|berbayar), sort.
     */
    public function search(array $f): self
    {
        $this->baseQuery()->whereIn('listings.status', self::PUBLIC_STATUSES);

        if (! empty($f['q'])) {
            $this->groupStart()
                ->like('listings.title', $f['q'])
                ->orLike('listings.description', $f['q'])
                ->orLike('categories.name', $f['q'])
                ->groupEnd();
        }
        if (! empty($f['category'])) {
            $this->where('listings.category_id', (int) $f['category']);
        }
        if (! empty($f['city'])) {
            $this->like('listings.city', $f['city']);
        }
        if (! empty($f['price'])) {
            if ($f['price'] === 'gratis') {
                $this->where('listings.price_type', 'gratis');
            } elseif ($f['price'] === 'berbayar') {
                $this->whereIn('listings.price_type', ['berbayar', 'nego']);
            }
        }
        if (! empty($f['handover'])) {
            $this->where('listings.handover', $f['handover']);
        }

        switch ($f['sort'] ?? 'terbaru') {
            case 'termurah':
                $this->orderBy('listings.price', 'ASC');
                break;
            case 'terberat':
                $this->orderBy('listings.weight_kg', 'DESC');
                break;
            default:
                $this->orderBy('listings.id', 'DESC');
        }

        return $this;
    }

    public function findWithRelations(int $id): ?array
    {
        return $this->baseQuery()
            ->select('users.city AS owner_city, users.photo AS owner_photo, users.verification_status AS owner_verif')
            ->where('listings.id', $id)->first();
    }

    public function forUser(int $userId): array
    {
        return $this->baseQuery()->where('listings.user_id', $userId)->orderBy('listings.id', 'DESC')->findAll();
    }

    public function expiryFromNow(): string
    {
        $days = (int) config('Loakin')->expireDays;

        return date('Y-m-d H:i:s', strtotime("+{$days} days"));
    }
}
