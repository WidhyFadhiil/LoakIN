<?php

namespace App\Controllers;

use App\Libraries\ImageUploader;
use App\Libraries\ImpactCalculator;
use App\Libraries\NotificationService;
use App\Models\ListingModel;
use App\Models\RatingModel;
use App\Models\UserModel;

class Profile extends BaseController
{
    /** Profil publik (tanpa nomor WA & alamat lengkap). */
    public function show(int $id): string
    {
        $user = model(UserModel::class)->find($id);
        if (! $user || ! (int) $user['is_active'] || $user['role'] === 'admin') {
            $this->notFound('Pengguna tidak ditemukan.');
        }

        $listingModel = model(ListingModel::class);
        $openListings = $user['role'] === 'penyetor'
            ? $listingModel->baseQuery()->where('listings.user_id', $id)->where('listings.status', 'tersedia')->orderBy('listings.id', 'DESC')->findAll(6)
            : [];

        return view('profile/show', [
            'title'    => $user['name'],
            'profile'  => $user,
            'ratings'  => model(RatingModel::class)->forUser($id),
            'impact'   => (new ImpactCalculator())->summary($id, $user['role']),
            'listings' => $openListings,
        ]);
    }

    public function edit(): string
    {
        return view('profile/edit', ['title' => 'Profil saya', 'user' => $this->me()]);
    }

    public function update()
    {
        $rules = [
            'name'  => 'required|min_length[3]|max_length[100]',
            'phone' => 'required|regex_match[/^(\+62|62|0)8[0-9]{7,12}$/]',
            'city'  => 'required|max_length[80]',
            'bio'   => 'permit_empty|max_length[500]',
        ];
        $newPass = (string) $this->request->getPost('new_password');
        if ($newPass !== '') {
            $rules['new_password']         = 'min_length[8]|max_length[72]';
            $rules['new_password_confirm'] = 'matches[new_password]';
            $rules['current_password']     = 'required';
        }
        $messages = ['phone' => ['regex_match' => 'Nomor WA harus diawali 08 atau +628.']];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $me   = $this->me();
        $data = [
            'name'    => trim($this->request->getPost('name')),
            'phone'   => preg_replace('/[^\d+]/', '', $this->request->getPost('phone')),
            'city'    => trim($this->request->getPost('city')),
            'address' => trim((string) $this->request->getPost('address')),
            'bio'     => trim((string) $this->request->getPost('bio')),
        ];

        if ($newPass !== '') {
            if (! password_verify((string) $this->request->getPost('current_password'), $me['password'])) {
                return redirect()->back()->withInput()->with('errors', ['current_password' => 'Password saat ini salah.']);
            }
            $data['password'] = $newPass;
        }

        $photo = $this->request->getFile('photo');
        if ($photo && $photo->getError() !== UPLOAD_ERR_NO_FILE) {
            $up   = new ImageUploader();
            $name = $up->saveAvatar($photo, $err);
            if ($name === null) {
                return redirect()->back()->withInput()->with('errors', ['photo' => $err]);
            }
            $up->deletePublic('avatars', $me['photo']);
            $data['photo'] = $name;
        }

        model(UserModel::class)->update($me['id'], $data);
        session()->set('user_name', $data['name']);

        return redirect()->to('/profile/edit')->with('success', 'Profil diperbarui.');
    }

    /** Pendaur ulang mengajukan verifikasi (dokumen + keterangan) untuk ditinjau admin. */
    public function requestVerification()
    {
        $me = $this->me();
        if ($me['role'] !== 'pendaur') {
            return redirect()->to('/profile/edit')->with('error', 'Verifikasi hanya untuk akun pendaur ulang.');
        }
        if (in_array($me['verification_status'], ['pending', 'approved'], true)) {
            return redirect()->to('/profile/edit')->with('error', 'Pengajuan verifikasimu sudah diproses.');
        }
        if (! $this->validate(['verification_note' => 'required|min_length[20]|max_length[1000]'], [
            'verification_note' => ['required' => 'Ceritakan usaha / kegiatan daur ulangmu.', 'min_length' => 'Keterangan minimal 20 karakter.'],
        ])) {
            return redirect()->to('/profile/edit#verifikasi')->withInput()->with('errors', $this->validator->getErrors());
        }

        $doc = $this->request->getFile('verification_doc');
        if (! $doc || $doc->getError() === UPLOAD_ERR_NO_FILE) {
            return redirect()->to('/profile/edit#verifikasi')->withInput()->with('errors', ['verification_doc' => 'Unggah foto KTP atau bukti usaha.']);
        }

        $up   = new ImageUploader();
        $name = $up->saveDocument($doc, $err);
        if ($name === null) {
            return redirect()->to('/profile/edit#verifikasi')->withInput()->with('errors', ['verification_doc' => $err]);
        }
        $up->deletePrivate('verification', $me['verification_doc']);

        model(UserModel::class)->update($me['id'], [
            'verification_status' => 'pending',
            'verification_note'   => trim($this->request->getPost('verification_note')),
            'verification_doc'    => $name,
        ]);

        $notify = new NotificationService();
        foreach (model(UserModel::class)->where('role', 'admin')->findAll() as $admin) {
            $notify->push((int) $admin['id'], 'verification', 'Pengajuan verifikasi baru', $me['name'] . ' menunggu verifikasi.', 'admin/verification');
        }

        return redirect()->to('/profile/edit#verifikasi')->with('success', 'Pengajuan verifikasi terkirim. Admin akan meninjaunya.');
    }
}
