<?php

namespace App\Models;

use CodeIgniter\Model;

class RatingModel extends Model
{
    protected $table         = 'ratings';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['offer_id', 'listing_id', 'rater_id', 'ratee_id', 'score', 'comment', 'created_at'];

    public function forUser(int $userId, int $limit = 10): array
    {
        return $this->select('ratings.*, u.name AS rater_name, l.title AS listing_title')
            ->join('users u', 'u.id = ratings.rater_id')
            ->join('listings l', 'l.id = ratings.listing_id')
            ->where('ratings.ratee_id', $userId)
            ->orderBy('ratings.id', 'DESC')->findAll($limit);
    }

    public function alreadyRated(int $offerId, int $raterId): bool
    {
        return $this->where('offer_id', $offerId)->where('rater_id', $raterId)->countAllResults() > 0;
    }

    /** Hitung ulang rata-rata rating user dan simpan di tabel users. */
    public function refreshUserAverage(int $userId): void
    {
        $row = $this->selectAvg('score', 'avg_score')->selectCount('id', 'cnt')
            ->where('ratee_id', $userId)->first();

        model(UserModel::class)->update($userId, [
            'rating_avg'   => round((float) ($row['avg_score'] ?? 0), 2),
            'rating_count' => (int) ($row['cnt'] ?? 0),
        ]);
    }
}
