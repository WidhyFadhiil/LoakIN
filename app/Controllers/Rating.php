<?php

namespace App\Controllers;

use App\Libraries\NotificationService;
use App\Models\OfferModel;
use App\Models\RatingModel;

/**
 * Setelah transaksi selesai, penyetor dan pendaur ulang saling memberi rating (1x per orang).
 */
class Rating extends BaseController
{
    public function form(int $offerId)
    {
        [$offer, $ratee] = $this->context($offerId);
        if (model(RatingModel::class)->alreadyRated($offerId, $this->uid)) {
            return redirect()->to('/offers/' . $offerId)->with('error', 'Kamu sudah memberi rating untuk transaksi ini.');
        }

        return view('rating/form', ['title' => 'Beri rating', 'offer' => $offer, 'ratee' => $ratee]);
    }

    public function store(int $offerId)
    {
        [$offer, $ratee] = $this->context($offerId);
        $ratings = model(RatingModel::class);

        if ($ratings->alreadyRated($offerId, $this->uid)) {
            return redirect()->to('/offers/' . $offerId)->with('error', 'Kamu sudah memberi rating untuk transaksi ini.');
        }
        if (! $this->validate([
            'score'   => 'required|is_natural_no_zero|less_than_equal_to[5]',
            'comment' => 'permit_empty|max_length[500]',
        ], ['score' => ['required' => 'Pilih jumlah bintang.']])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $ratings->insert([
            'offer_id'   => $offerId,
            'listing_id' => $offer['listing_id'],
            'rater_id'   => $this->uid,
            'ratee_id'   => $ratee['id'],
            'score'      => (int) $this->request->getPost('score'),
            'comment'    => trim((string) $this->request->getPost('comment')) ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $ratings->refreshUserAverage((int) $ratee['id']);

        (new NotificationService())->push((int) $ratee['id'], 'rating', 'Kamu mendapat rating baru', session('user_name') . ' memberi ' . (int) $this->request->getPost('score') . ' bintang.', 'u/' . $ratee['id']);

        return redirect()->to('/offers/' . $offerId)->with('success', 'Terima kasih! Ratingmu tersimpan.');
    }

    /** @return array{0: array, 1: array} [pengajuan, pihak yang dinilai] */
    private function context(int $offerId): array
    {
        $offer = model(OfferModel::class)->detail($offerId);
        if (! $offer || ! in_array($this->uid, [(int) $offer['owner_id'], (int) $offer['recycler_id']], true)) {
            $this->notFound('Transaksi tidak ditemukan.');
        }
        if ($offer['status'] !== 'selesai') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Rating hanya bisa diberikan setelah transaksi selesai.');
        }

        $isOwner = $this->uid === (int) $offer['owner_id'];
        $ratee   = [
            'id'   => $isOwner ? $offer['recycler_id'] : $offer['owner_id'],
            'name' => $isOwner ? $offer['recycler_name'] : $offer['owner_name'],
        ];

        return [$offer, $ratee];
    }
}
