<?php

namespace App\Controllers;

use App\Libraries\NotificationService;
use App\Models\MessageModel;
use App\Models\OfferModel;

/**
 * Kolom pesan per pengajuan (seperti komentar, tersimpan di database).
 */
class Message extends BaseController
{
    public function store(int $offerId)
    {
        $offer = model(OfferModel::class)->detail($offerId);
        if (! $offer || ! in_array($this->uid, [(int) $offer['owner_id'], (int) $offer['recycler_id']], true)) {
            $this->notFound('Pengajuan tidak ditemukan.');
        }
        if (! in_array($offer['status'], ['menunggu', 'counter', 'diterima'], true)) {
            return redirect()->to('/offers/' . $offerId)->with('error', 'Percakapan ini sudah ditutup.');
        }

        $body = trim((string) $this->request->getPost('body'));
        if ($body === '' || mb_strlen($body) > 1000) {
            return redirect()->to('/offers/' . $offerId . '#pesan')->with('error', 'Pesan tidak boleh kosong dan maksimal 1000 karakter.');
        }

        model(MessageModel::class)->insert([
            'offer_id'   => $offerId,
            'sender_id'  => $this->uid,
            'body'       => $body,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $isOwner = $this->uid === (int) $offer['owner_id'];
        (new NotificationService())->push(
            $isOwner ? (int) $offer['recycler_id'] : (int) $offer['owner_id'],
            'message',
            'Pesan baru dari ' . ($isOwner ? $offer['owner_name'] : $offer['recycler_name']),
            mb_substr($body, 0, 120),
            'offers/' . $offerId . '#pesan'
        );

        return redirect()->to('/offers/' . $offerId . '#pesan');
    }
}
