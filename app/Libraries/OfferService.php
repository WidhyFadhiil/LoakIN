<?php

namespace App\Libraries;

use App\Models\ListingModel;
use App\Models\OfferModel;
use App\Models\UserModel;

/**
 * Seluruh aturan transaksi LoakIN ada di sini, supaya controller tetap tipis:
 *
 *  Tersedia -> (pengajuan diterima) -> Diproses -> (konfirmasi penyetor) -> Selesai
 *  Tersedia/Diproses -> Dibatalkan (penyetor) | Kedaluwarsa (otomatis N hari)
 *
 * Setiap method mengembalikan [bool berhasil, string pesan].
 */
class OfferService
{
    private OfferModel $offers;
    private ListingModel $listings;
    private NotificationService $notify;

    public function __construct()
    {
        $this->offers   = model(OfferModel::class);
        $this->listings = model(ListingModel::class);
        $this->notify   = new NotificationService();
    }

    private function ok(string $m): array
    {
        return [true, $m];
    }

    private function fail(string $m): array
    {
        return [false, $m];
    }

    private function offerUrl(int $id): string
    {
        return 'offers/' . $id;
    }

    // ---------------------------------------------------------------- pendaur

    /** Pendaur menekan "Ajukan Ambil". */
    public function submit(array $listing, array $user, ?int $price, ?string $note): array
    {
        if (! UserModel::canRecycle($user)) {
            return $this->fail('Hanya akun pendaur ulang yang sudah terverifikasi yang bisa mengajukan.');
        }
        if ((int) $listing['user_id'] === (int) $user['id']) {
            return $this->fail('Kamu tidak bisa mengajukan barang milikmu sendiri.');
        }
        if ($listing['status'] !== ListingModel::ST_OPEN) {
            return $this->fail('Barang ini sudah tidak menerima pengajuan.');
        }
        if ($this->offers->activeFor((int) $listing['id'], (int) $user['id'])) {
            return $this->fail('Kamu sudah punya pengajuan aktif untuk barang ini.');
        }

        if ($listing['price_type'] === 'gratis') {
            $price = 0;
        } elseif ($listing['price_type'] === 'berbayar') {
            $price = (int) $listing['price'];
        } else {
            $price = $price ?? (int) $listing['price'];
        }

        $id = $this->offers->insert([
            'listing_id'    => $listing['id'],
            'recycler_id'   => $user['id'],
            'offered_price' => max(0, $price),
            'note'          => $note ?: null,
            'status'        => 'menunggu',
        ], true);

        $this->notify->push(
            (int) $listing['user_id'],
            'offer_new',
            'Pengajuan baru untuk "' . $listing['title'] . '"',
            $user['name'] . ' ingin mengambil barangmu' . ($price > 0 ? ' dengan tawaran ' . rupiah($price) : '') . '.',
            $this->offerUrl((int) $id)
        );

        return $this->ok($id);
    }

    /** Pendaur menerima harga counter dari penyetor. */
    public function acceptCounter(int $offerId, int $recyclerId): array
    {
        $o = $this->offers->detail($offerId);
        if (! $o || (int) $o['recycler_id'] !== $recyclerId) {
            return $this->fail('Pengajuan tidak ditemukan.');
        }
        if ($o['status'] !== 'counter' || $o['counter_price'] === null) {
            return $this->fail('Tidak ada harga balasan yang bisa diterima.');
        }

        return $this->activate($o, (int) $o['counter_price'], $o['recycler_name'] . ' menerima harga balasanmu.');
    }

    /** Pendaur menawar lagi setelah di-counter. */
    public function rebid(int $offerId, int $recyclerId, int $price): array
    {
        $o = $this->offers->detail($offerId);
        if (! $o || (int) $o['recycler_id'] !== $recyclerId) {
            return $this->fail('Pengajuan tidak ditemukan.');
        }
        if ($o['status'] !== 'counter' || $o['listing_status'] !== ListingModel::ST_OPEN) {
            return $this->fail('Pengajuan ini tidak bisa ditawar lagi.');
        }

        $this->offers->update($offerId, [
            'offered_price' => max(0, $price),
            'counter_price' => null,
            'status'        => 'menunggu',
        ]);
        $this->notify->push(
            (int) $o['owner_id'],
            'offer_rebid',
            'Tawaran baru: ' . rupiah($price),
            $o['recycler_name'] . ' menawar lagi untuk "' . $o['listing_title'] . '".',
            $this->offerUrl($offerId)
        );

        return $this->ok('Tawaran barumu sudah dikirim.');
    }

    /** Pendaur membatalkan pengajuan yang belum diterima. */
    public function withdraw(int $offerId, int $recyclerId): array
    {
        $o = $this->offers->detail($offerId);
        if (! $o || (int) $o['recycler_id'] !== $recyclerId) {
            return $this->fail('Pengajuan tidak ditemukan.');
        }
        if (! in_array($o['status'], ['menunggu', 'counter'], true)) {
            return $this->fail('Pengajuan ini tidak bisa dibatalkan lagi.');
        }

        $this->offers->update($offerId, ['status' => 'dibatalkan', 'responded_at' => date('Y-m-d H:i:s')]);
        $this->notify->push(
            (int) $o['owner_id'],
            'offer_withdrawn',
            'Pengajuan dibatalkan',
            $o['recycler_name'] . ' membatalkan pengajuan untuk "' . $o['listing_title'] . '".',
            $this->offerUrl($offerId)
        );

        return $this->ok('Pengajuan dibatalkan.');
    }

    // ---------------------------------------------------------------- penyetor

    public function accept(int $offerId, int $ownerId): array
    {
        $o = $this->offers->detail($offerId);
        if (! $o || (int) $o['owner_id'] !== $ownerId) {
            return $this->fail('Pengajuan tidak ditemukan.');
        }
        if ($o['status'] !== 'menunggu') {
            return $this->fail('Pengajuan ini sedang tidak menunggu jawabanmu.');
        }

        return $this->activate($o, (int) $o['offered_price'], 'Pengajuanmu untuk "' . $o['listing_title'] . '" diterima.');
    }

    public function reject(int $offerId, int $ownerId): array
    {
        $o = $this->offers->detail($offerId);
        if (! $o || (int) $o['owner_id'] !== $ownerId) {
            return $this->fail('Pengajuan tidak ditemukan.');
        }
        if (! in_array($o['status'], ['menunggu', 'counter'], true)) {
            return $this->fail('Pengajuan ini sudah tidak bisa ditolak.');
        }

        $this->offers->update($offerId, ['status' => 'ditolak', 'responded_at' => date('Y-m-d H:i:s')]);
        $this->notify->push(
            (int) $o['recycler_id'],
            'offer_rejected',
            'Pengajuan ditolak',
            'Pengajuanmu untuk "' . $o['listing_title'] . '" ditolak penyetor.',
            $this->offerUrl($offerId)
        );

        return $this->ok('Pengajuan ditolak.');
    }

    /** Counter offer: hanya untuk barang "bisa nego". */
    public function counter(int $offerId, int $ownerId, int $price): array
    {
        $o = $this->offers->detail($offerId);
        if (! $o || (int) $o['owner_id'] !== $ownerId) {
            return $this->fail('Pengajuan tidak ditemukan.');
        }
        if ($o['status'] !== 'menunggu' || $o['listing_status'] !== ListingModel::ST_OPEN) {
            return $this->fail('Pengajuan ini tidak bisa dibalas dengan harga lain.');
        }
        if ($o['price_type'] !== 'nego') {
            return $this->fail('Barang ini tidak bisa nego.');
        }

        $this->offers->update($offerId, [
            'counter_price' => max(0, $price),
            'status'        => 'counter',
            'responded_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->notify->push(
            (int) $o['recycler_id'],
            'offer_counter',
            'Harga balasan: ' . rupiah($price),
            $o['owner_name'] . ' mengajukan harga lain untuk "' . $o['listing_title'] . '".',
            $this->offerUrl($offerId)
        );

        return $this->ok('Harga balasan terkirim ke pendaur ulang.');
    }

    /** Konfirmasi selesai: barang sudah berpindah tangan -> masuk dashboard dampak. */
    public function complete(int $offerId, int $ownerId): array
    {
        $o = $this->offers->detail($offerId);
        if (! $o || (int) $o['owner_id'] !== $ownerId) {
            return $this->fail('Pengajuan tidak ditemukan.');
        }
        if ($o['status'] !== 'diterima' || $o['listing_status'] !== ListingModel::ST_PROCESS) {
            return $this->fail('Hanya transaksi yang sedang diproses yang bisa dikonfirmasi selesai.');
        }

        $now = date('Y-m-d H:i:s');
        $db  = db_connect();
        $db->transStart();
        $this->offers->update($offerId, ['status' => 'selesai', 'completed_at' => $now]);
        $this->listings->update((int) $o['listing_id'], ['status' => ListingModel::ST_DONE, 'completed_at' => $now]);
        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->fail('Gagal menyimpan. Coba lagi.');
        }

        $this->notify->push(
            (int) $o['recycler_id'],
            'offer_done',
            'Transaksi selesai',
            '"' . $o['listing_title'] . '" sudah dikonfirmasi selesai. Jangan lupa beri rating.',
            'ratings/' . $offerId
        );

        return $this->ok('Transaksi selesai. Beratnya sudah masuk ke dashboard dampak lingkungan.');
    }

    // ---------------------------------------------------------------- kedua pihak

    /** Batalkan kesepakatan (status Diproses) -> listing kembali Tersedia. */
    public function cancelDeal(int $offerId, int $userId): array
    {
        $o = $this->offers->detail($offerId);
        if (! $o || ! in_array($userId, [(int) $o['owner_id'], (int) $o['recycler_id']], true)) {
            return $this->fail('Pengajuan tidak ditemukan.');
        }
        if ($o['status'] !== 'diterima' || $o['listing_status'] !== ListingModel::ST_PROCESS) {
            return $this->fail('Kesepakatan ini tidak bisa dibatalkan.');
        }

        $db = db_connect();
        $db->transStart();
        $this->offers->update($offerId, ['status' => 'dibatalkan', 'responded_at' => date('Y-m-d H:i:s')]);
        $this->listings->update((int) $o['listing_id'], [
            'status'            => ListingModel::ST_OPEN,
            'accepted_offer_id' => null,
            'expires_at'        => $this->listings->expiryFromNow(),
        ]);
        $db->transComplete();

        $other = $userId === (int) $o['owner_id'] ? (int) $o['recycler_id'] : (int) $o['owner_id'];
        $who   = $userId === (int) $o['owner_id'] ? $o['owner_name'] : $o['recycler_name'];
        $this->notify->push(
            $other,
            'deal_cancelled',
            'Kesepakatan dibatalkan',
            $who . ' membatalkan kesepakatan untuk "' . $o['listing_title'] . '".',
            $this->offerUrl($offerId)
        );

        return $this->ok('Kesepakatan dibatalkan. Barang kembali berstatus Tersedia.');
    }

    // ---------------------------------------------------------------- listing

    /** Penyetor membatalkan listing: semua pengajuan aktif ikut dibatalkan. */
    public function cancelListing(array $listing, int $ownerId): array
    {
        if ((int) $listing['user_id'] !== $ownerId) {
            return $this->fail('Barang tidak ditemukan.');
        }
        if (! in_array($listing['status'], [ListingModel::ST_OPEN, ListingModel::ST_PROCESS], true)) {
            return $this->fail('Barang ini sudah tidak aktif.');
        }

        $this->closeOffers((int) $listing['id'], 'Penyetor membatalkan postingan "' . $listing['title'] . '".', 'listing_cancelled');
        $this->listings->update((int) $listing['id'], ['status' => ListingModel::ST_CANCELLED]);

        return $this->ok('Postingan dibatalkan.');
    }

    /** Dipanggil oleh `php spark listings:expire` (cron harian). Mengembalikan jumlah listing yang kedaluwarsa. */
    public function expireListings(): int
    {
        $due = $this->listings
            ->where('status', ListingModel::ST_OPEN)
            ->where('expires_at IS NOT NULL', null, false)
            ->where('expires_at <', date('Y-m-d H:i:s'))
            ->findAll();

        foreach ($due as $l) {
            $this->closeOffers((int) $l['id'], 'Postingan "' . $l['title'] . '" sudah kedaluwarsa.', 'listing_expired');
            $this->listings->update((int) $l['id'], ['status' => ListingModel::ST_EXPIRED]);
            $this->notify->push(
                (int) $l['user_id'],
                'listing_expired',
                'Postinganmu kedaluwarsa',
                '"' . $l['title'] . '" tidak ada yang mengambil selama ' . config('Loakin')->expireDays . ' hari. Posting ulang kalau masih ada.',
                'listings/' . $l['id']
            );
        }

        return count($due);
    }

    // ---------------------------------------------------------------- internal

    /** Terima pengajuan (harga final sudah disepakati): listing -> Diproses, pengajuan lain ditolak. */
    private function activate(array $o, int $finalPrice, string $notifBody): array
    {
        if ($o['listing_status'] !== ListingModel::ST_OPEN) {
            return $this->fail('Barang ini sudah diproses atau tidak aktif lagi.');
        }

        $now = date('Y-m-d H:i:s');
        $db  = db_connect();
        $db->transStart();

        $this->offers->update((int) $o['id'], [
            'status'       => 'diterima',
            'final_price'  => $finalPrice,
            'responded_at' => $now,
        ]);
        $this->listings->update((int) $o['listing_id'], [
            'status'            => ListingModel::ST_PROCESS,
            'accepted_offer_id' => $o['id'],
        ]);

        $others = $this->offers->where('listing_id', $o['listing_id'])
            ->where('id !=', $o['id'])->whereIn('status', ['menunggu', 'counter'])->findAll();
        foreach ($others as $x) {
            $this->offers->update((int) $x['id'], ['status' => 'ditolak', 'responded_at' => $now]);
            $this->notify->push(
                (int) $x['recycler_id'],
                'offer_rejected',
                'Barang sudah diambil pendaur lain',
                'Pengajuanmu untuk "' . $o['listing_title'] . '" tidak dipilih.',
                $this->offerUrl((int) $x['id'])
            );
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->fail('Gagal menyimpan. Coba lagi.');
        }

        // Pihak lawan yang diberi tahu = yang tidak menekan tombol
        $actorIsOwner = ($o['status'] === 'menunggu');
        $this->notify->push(
            $actorIsOwner ? (int) $o['recycler_id'] : (int) $o['owner_id'],
            'offer_accepted',
            $actorIsOwner ? 'Pengajuan diterima 🎉' : 'Harga balasanmu diterima',
            $notifBody . ' Harga disepakati: ' . ($finalPrice > 0 ? rupiah($finalPrice) : 'Gratis') . '. Nomor WA kini terbuka.',
            $this->offerUrl((int) $o['id'])
        );

        return $this->ok('Disepakati dengan harga ' . ($finalPrice > 0 ? rupiah($finalPrice) : 'Gratis') . '. Nomor WA kedua pihak kini terbuka.');
    }

    /** Tutup semua pengajuan aktif milik satu listing dan beri tahu pendaurnya. */
    private function closeOffers(int $listingId, string $reason, string $type): void
    {
        $active = $this->offers->where('listing_id', $listingId)->whereIn('status', OfferModel::ACTIVE)->findAll();
        foreach ($active as $x) {
            $this->offers->update((int) $x['id'], ['status' => 'dibatalkan', 'responded_at' => date('Y-m-d H:i:s')]);
            $this->notify->push((int) $x['recycler_id'], $type, 'Pengajuan dibatalkan', $reason, $this->offerUrl((int) $x['id']));
        }
    }
}
