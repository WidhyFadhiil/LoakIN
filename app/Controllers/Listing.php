<?php

namespace App\Controllers;

use App\Libraries\ImageUploader;
use App\Libraries\OfferService;
use App\Models\CategoryModel;
use App\Models\ListingImageModel;
use App\Models\ListingModel;
use App\Models\OfferModel;
use App\Models\UserModel;

class Listing extends BaseController
{
    // ------------------------------------------------------------ publik

    public function index(): string
    {
        $filters = [
            'q'        => trim((string) $this->request->getGet('q')),
            'category' => (int) $this->request->getGet('category'),
            'city'     => trim((string) $this->request->getGet('city')),
            'price'    => (string) $this->request->getGet('price'),
            'handover' => (string) $this->request->getGet('handover'),
            'sort'     => (string) $this->request->getGet('sort'),
        ];

        $model = model(ListingModel::class);
        $items = $model->search($filters)->paginate(config('Loakin')->perPage);

        return view('listing/index', [
            'title'      => 'Cari barang',
            'items'      => $items,
            'pager'      => $model->pager,
            'filters'    => $filters,
            'categories' => model(CategoryModel::class)->active(),
        ]);
    }

    public function show(int $id): string
    {
        $listing = model(ListingModel::class)->findWithRelations($id);
        if (! $listing) {
            $this->notFound('Barang tidak ditemukan.');
        }

        $isOwner = $this->uid === (int) $listing['user_id'];
        $isAdmin = $this->role === 'admin';
        $offers  = model(OfferModel::class);

        // Listing yang sudah tidak aktif hanya terlihat oleh pemilik, admin, dan pendaur yang pernah terlibat
        if (! in_array($listing['status'], ListingModel::PUBLIC_STATUSES, true) && ! $isOwner && ! $isAdmin) {
            $involved = $this->uid && $offers->where('listing_id', $id)->where('recycler_id', $this->uid)->countAllResults() > 0;
            if (! $involved) {
                $this->notFound('Barang ini sudah tidak tersedia.');
            }
        }

        if (! $isOwner && in_array($listing['status'], ListingModel::PUBLIC_STATUSES, true)) {
            model(ListingModel::class)->update($id, ['views' => (int) $listing['views'] + 1]);
        }

        $me = $this->me();

        return view('listing/show', [
            'title'       => $listing['title'],
            'listing'     => $listing,
            'images'      => model(ListingImageModel::class)->forListing($id),
            'isOwner'     => $isOwner,
            'canOffer'    => UserModel::canRecycle($me) && ! $isOwner && $listing['status'] === ListingModel::ST_OPEN,
            'myOffer'     => $me && $me['role'] === 'pendaur' ? $offers->activeFor($id, $this->uid) : null,
            'offers'      => $isOwner ? $offers->forListing($id) : [],
            'me'          => $me,
        ]);
    }

    // ------------------------------------------------------------ penyetor

    public function mine(): string
    {
        return view('listing/mine', [
            'title' => 'Barang saya',
            'items' => model(ListingModel::class)->forUser($this->uid),
        ]);
    }

    public function create(): string
    {
        $me = $this->me();

        return view('listing/create', [
            'title'      => 'Posting barang',
            'categories' => model(CategoryModel::class)->active(),
            'defaults'   => ['city' => $me['city'], 'address' => $me['address']],
        ]);
    }

    public function store()
    {
        $data = $this->collect();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $up    = new ImageUploader();
        $files = $this->request->getFileMultiple('images') ?: [];
        $res   = $up->saveListingImages($files);

        $model = model(ListingModel::class);
        $id    = $model->insert($data + [
            'user_id'    => $this->uid,
            'status'     => ListingModel::ST_OPEN,
            'expires_at' => $model->expiryFromNow(),
        ], true);

        foreach ($res['saved'] as $i => $name) {
            model(ListingImageModel::class)->insert(['listing_id' => $id, 'filename' => $name, 'sort_order' => $i, 'created_at' => date('Y-m-d H:i:s')]);
        }

        $flash = redirect()->to('/listings/' . $id)->with('success', 'Barangmu sudah tayang. Pendaur ulang bisa mengajukan sekarang.');

        return $res['errors'] ? $flash->with('error', implode(' ', $res['errors'])) : $flash;
    }

    public function edit(int $id): string
    {
        $listing = $this->ownedListing($id);
        if ($listing['status'] !== ListingModel::ST_OPEN) {
            return $this->redirectBlocked();
        }

        return view('listing/edit', [
            'title'      => 'Ubah barang',
            'listing'    => $listing,
            'images'     => model(ListingImageModel::class)->forListing($id),
            'categories' => model(CategoryModel::class)->active(),
        ]);
    }

    public function update(int $id)
    {
        $listing = $this->ownedListing($id);
        if ($listing['status'] !== ListingModel::ST_OPEN) {
            return $this->redirectBlocked();
        }

        $data = $this->collect();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $imgModel = model(ListingImageModel::class);
        $up       = new ImageUploader();

        // hapus foto yang dicentang
        foreach ((array) $this->request->getPost('remove_images') as $imgId) {
            $img = $imgModel->where('listing_id', $id)->find((int) $imgId);
            if ($img) {
                $up->deletePublic('listings', $img['filename']);
                $imgModel->delete($img['id']);
            }
        }

        $have = count($imgModel->forListing($id));
        $res  = $up->saveListingImages($this->request->getFileMultiple('images') ?: [], $have);
        foreach ($res['saved'] as $i => $name) {
            $imgModel->insert(['listing_id' => $id, 'filename' => $name, 'sort_order' => $have + $i, 'created_at' => date('Y-m-d H:i:s')]);
        }

        model(ListingModel::class)->update($id, $data);

        $flash = redirect()->to('/listings/' . $id)->with('success', 'Perubahan disimpan.');

        return $res['errors'] ? $flash->with('error', implode(' ', $res['errors'])) : $flash;
    }

    /** Hapus permanen: hanya jika belum pernah ada pengajuan. Selebihnya gunakan "Batalkan". */
    public function delete(int $id)
    {
        $listing = $this->ownedListing($id);
        $hasOffers = model(OfferModel::class)->where('listing_id', $id)->countAllResults() > 0;

        if ($hasOffers || $listing['status'] !== ListingModel::ST_OPEN) {
            return redirect()->to('/listings/' . $id)->with('error', 'Barang yang sudah punya pengajuan tidak bisa dihapus. Gunakan "Batalkan postingan" agar riwayat tetap tersimpan.');
        }

        $up = new ImageUploader();
        foreach (model(ListingImageModel::class)->forListing($id) as $img) {
            $up->deletePublic('listings', $img['filename']);
        }
        model(ListingModel::class)->delete($id);

        return redirect()->to('/my/listings')->with('success', 'Postingan dihapus.');
    }

    public function cancel(int $id)
    {
        $listing = $this->ownedListing($id);
        [$ok, $msg] = (new OfferService())->cancelListing($listing, $this->uid);

        return redirect()->to($ok ? '/my/listings' : '/listings/' . $id)->with($ok ? 'success' : 'error', $msg);
    }

    /** Posting ulang listing yang dibatalkan / kedaluwarsa (membuat salinan baru, riwayat lama tetap). */
    public function repost(int $id)
    {
        $l = $this->ownedListing($id);
        if (! in_array($l['status'], [ListingModel::ST_EXPIRED, ListingModel::ST_CANCELLED], true)) {
            return redirect()->to('/listings/' . $id)->with('error', 'Hanya postingan yang kedaluwarsa atau dibatalkan yang bisa diposting ulang.');
        }

        $model = model(ListingModel::class);
        $copy  = $l;
        unset($copy['id'], $copy['created_at'], $copy['updated_at'], $copy['category_name'], $copy['owner_name'], $copy['owner_rating'], $copy['owner_rating_count'], $copy['cover'], $copy['owner_city'], $copy['owner_photo'], $copy['owner_verif']);
        $copy['status']            = ListingModel::ST_OPEN;
        $copy['accepted_offer_id'] = null;
        $copy['completed_at']      = null;
        $copy['views']             = 0;
        $copy['expires_at']        = $model->expiryFromNow();
        $newId = $model->insert($copy, true);

        // salin foto (file fisik disalin agar menghapus salah satu tidak merusak yang lain)
        $up = new ImageUploader();
        foreach (model(ListingImageModel::class)->forListing($id) as $img) {
            $src = FCPATH . 'uploads/listings/' . $img['filename'];
            if (is_file($src)) {
                $new = bin2hex(random_bytes(8)) . '.' . pathinfo($img['filename'], PATHINFO_EXTENSION);
                copy($src, $up->publicDir('listings') . $new);
                model(ListingImageModel::class)->insert(['listing_id' => $newId, 'filename' => $new, 'sort_order' => $img['sort_order'], 'created_at' => date('Y-m-d H:i:s')]);
            }
        }

        return redirect()->to('/listings/' . $newId)->with('success', 'Diposting ulang. Masa tayang dihitung dari hari ini.');
    }

    // ------------------------------------------------------------ internal

    private function ownedListing(int $id): array
    {
        $l = model(ListingModel::class)->find($id);
        if (! $l || (int) $l['user_id'] !== $this->uid) {
            $this->notFound('Barang tidak ditemukan.');
        }

        return $l;
    }

    private function redirectBlocked()
    {
        return redirect()->to('/my/listings')->with('error', 'Barang yang sedang diproses atau sudah selesai tidak bisa diubah.');
    }

    /** Validasi + normalisasi input form listing. Mengembalikan null jika gagal. */
    private function collect(): ?array
    {
        $cfg = config('Loakin');

        $post = $this->request->getPost();
        $post['weight_kg'] = $this->number($post['weight_kg'] ?? '');
        $post['price']     = $this->number($post['price'] ?? '');

        $rules = [
            'title'          => 'required|min_length[5]|max_length[150]',
            'category_id'    => 'required|is_natural_no_zero|is_not_unique[categories.id]',
            'description'    => 'permit_empty|max_length[2000]',
            'weight_kg'      => 'required|decimal|greater_than[0]|less_than_equal_to[10000]',
            'item_condition' => 'required|in_list[' . implode(',', array_keys($cfg->conditions)) . ']',
            'city'           => 'required|max_length[80]',
            'address'        => 'permit_empty|max_length[255]',
            'price_type'     => 'required|in_list[' . implode(',', array_keys($cfg->priceTypes)) . ']',
            'handover'       => 'required|in_list[' . implode(',', array_keys($cfg->handovers)) . ']',
        ];
        if (in_array($post['price_type'] ?? '', ['berbayar', 'nego'], true)) {
            $rules['price'] = 'required|is_natural_no_zero|less_than_equal_to[100000000]';
        }
        $messages = [
            'weight_kg'   => ['greater_than' => 'Berat harus lebih dari 0 kg.', 'decimal' => 'Isi berat dengan angka, contoh 2,5.'],
            'price'       => ['required' => 'Isi harga untuk barang berbayar / bisa nego.', 'is_natural_no_zero' => 'Harga harus berupa angka lebih dari 0.'],
            'category_id' => ['is_not_unique' => 'Pilih kategori yang tersedia.'],
        ];

        $validation = service('validation');
        $validation->setRules($rules, $messages);
        if (! $validation->run($post)) {
            $this->validator = $validation;

            return null;
        }

        return [
            'title'          => trim($post['title']),
            'category_id'    => (int) $post['category_id'],
            'description'    => trim((string) ($post['description'] ?? '')),
            'weight_kg'      => (float) $post['weight_kg'],
            'item_condition' => $post['item_condition'],
            'city'           => trim($post['city']),
            'address'        => trim((string) ($post['address'] ?? '')),
            'price_type'     => $post['price_type'],
            'price'          => $post['price_type'] === 'gratis' ? 0 : (int) $post['price'],
            'handover'       => $post['handover'],
        ];
    }
}
