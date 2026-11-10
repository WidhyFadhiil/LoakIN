<?php

namespace App\Controllers;

use App\Libraries\OfferService;
use App\Models\ListingModel;
use App\Models\MessageModel;
use App\Models\OfferModel;
use App\Models\RatingModel;

class Offer extends BaseController
{
    private OfferService $service;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->service = new OfferService();
    }

    // ------------------------------------------------------------ daftar

    /** Pengajuan saya (pendaur ulang) yang masih berjalan. */
    public function mine(): string
    {
        return view('offer/mine', [
            'title'  => 'Pengajuan saya',
            'offers' => model(OfferModel::class)->byRecycler($this->uid, OfferModel::ACTIVE),
        ]);
    }

    /** Pengajuan masuk ke barang-barang saya (penyetor). */
    public function incoming(): string
    {
        return view('offer/incoming', [
            'title'  => 'Pengajuan masuk',
            'offers' => model(OfferModel::class)->byOwner($this->uid, OfferModel::ACTIVE),
        ]);
    }

    /** Riwayat transaksi kedua pihak: selesai, ditolak, dibatalkan. */
    public function history(): string
    {
        $statuses = ['selesai', 'ditolak', 'dibatalkan'];
        $model    = model(OfferModel::class);
        $offers   = $this->role === 'pendaur' ? $model->byRecycler($this->uid, $statuses) : $model->byOwner($this->uid, $statuses);

        $ratings = model(RatingModel::class);
        foreach ($offers as &$o) {
            $o['rated'] = $o['status'] === 'selesai' && $ratings->alreadyRated((int) $o['id'], $this->uid);
        }

        return view('offer/history', ['title' => 'Riwayat transaksi', 'offers' => $offers]);
    }

    // ------------------------------------------------------------ detail

    public function show(int $id): string
    {
        $offer = $this->participantOffer($id);
        $isOwner = $this->uid === (int) $offer['owner_id'];
        $ratings = model(RatingModel::class);

        return view('offer/show', [
            'title'     => 'Pengajuan: ' . $offer['listing_title'],
            'offer'     => $offer,
            'isOwner'   => $isOwner,
            'messages'  => model(MessageModel::class)->thread($id),
            'canChat'   => in_array($offer['status'], ['menunggu', 'counter', 'diterima'], true),
            'showPhone' => in_array($offer['status'], ['diterima', 'selesai'], true),
            'rated'     => $offer['status'] === 'selesai' && $ratings->alreadyRated($id, $this->uid),
        ]);
    }

    // ------------------------------------------------------------ aksi pendaur

    public function store(int $listingId)
    {
        $listing = model(ListingModel::class)->find($listingId);
        if (! $listing) {
            $this->notFound('Barang tidak ditemukan.');
        }

        $price = $this->number($this->request->getPost('offered_price'));
        $note  = trim((string) $this->request->getPost('note'));

        if (mb_strlen($note) > 500 || ($price !== '' && ! ctype_digit($price))) {
            return redirect()->back()->withInput()->with('error', 'Tawaran harus berupa angka (tanpa titik/koma desimal) dan catatan maksimal 500 karakter.');
        }

        [$ok, $res] = $this->service->submit($listing, $this->me(), $price === '' ? null : (int) $price, $note);

        if (! $ok) {
            return redirect()->to('/listings/' . $listingId)->with('error', $res);
        }

        return redirect()->to('/offers/' . $res)->with('success', 'Pengajuan terkirim. Tunggu jawaban penyetor, atau kirim pesan di bawah.');
    }

    public function acceptCounter(int $id)
    {
        return $this->respond($id, $this->service->acceptCounter($id, $this->uid));
    }

    public function rebid(int $id)
    {
        $price = $this->number($this->request->getPost('offered_price'));
        if ($price === '' || ! ctype_digit($price)) {
            return redirect()->back()->with('error', 'Isi tawaran baru dengan angka.');
        }

        return $this->respond($id, $this->service->rebid($id, $this->uid, (int) $price));
    }

    public function withdraw(int $id)
    {
        return $this->respond($id, $this->service->withdraw($id, $this->uid));
    }

    // ------------------------------------------------------------ aksi penyetor

    public function accept(int $id)
    {
        return $this->respond($id, $this->service->accept($id, $this->uid));
    }

    public function reject(int $id)
    {
        return $this->respond($id, $this->service->reject($id, $this->uid));
    }

    public function counter(int $id)
    {
        $price = $this->number($this->request->getPost('counter_price'));
        if ($price === '' || ! ctype_digit($price)) {
            return redirect()->back()->with('error', 'Isi harga balasan dengan angka.');
        }

        return $this->respond($id, $this->service->counter($id, $this->uid, (int) $price));
    }

    public function complete(int $id)
    {
        [$ok, $msg] = $this->service->complete($id, $this->uid);
        if ($ok) {
            return redirect()->to('/ratings/' . $id)->with('success', $msg . ' Beri rating ke pendaur ulang.');
        }

        return redirect()->to('/offers/' . $id)->with('error', $msg);
    }

    // ------------------------------------------------------------ kedua pihak

    public function cancelDeal(int $id)
    {
        return $this->respond($id, $this->service->cancelDeal($id, $this->uid));
    }

    // ------------------------------------------------------------ internal

    private function respond(int $id, array $result)
    {
        [$ok, $msg] = $result;

        return redirect()->to('/offers/' . $id)->with($ok ? 'success' : 'error', $msg);
    }

    /** Pengajuan hanya boleh dibuka oleh penyetor pemilik barang atau pendaur yang mengajukan. */
    private function participantOffer(int $id): array
    {
        $o = model(OfferModel::class)->detail($id);
        if (! $o || ! in_array($this->uid, [(int) $o['owner_id'], (int) $o['recycler_id']], true)) {
            $this->notFound('Pengajuan tidak ditemukan.');
        }

        return $o;
    }
}
