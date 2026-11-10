<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\NotificationService;
use App\Models\UserModel;

/**
 * Verifikasi akun pendaur ulang supaya tidak sembarang orang mengambil barang.
 */
class Verification extends BaseController
{
    public function index(): string
    {
        $m = model(UserModel::class);

        return view('admin/verification/index', [
            'title'   => 'Verifikasi pendaur ulang',
            'pending' => $m->where('role', 'pendaur')->where('verification_status', 'pending')->orderBy('updated_at')->findAll(),
            'done'    => $m->where('role', 'pendaur')->whereIn('verification_status', ['approved', 'rejected'])->orderBy('updated_at', 'DESC')->findAll(30),
        ]);
    }

    public function approve(int $id)
    {
        $user = $this->pendaur($id);
        model(UserModel::class)->update($id, [
            'verification_status' => 'approved',
            'verified_at'         => date('Y-m-d H:i:s'),
        ]);
        (new NotificationService())->push($id, 'verification', 'Akunmu terverifikasi ✔', 'Sekarang kamu bisa mengajukan pengambilan barang.', 'listings');

        return redirect()->to('/admin/verification')->with('success', $user['name'] . ' diverifikasi.');
    }

    public function reject(int $id)
    {
        $user   = $this->pendaur($id);
        $reason = trim((string) $this->request->getPost('reason'));
        if ($reason === '') {
            return redirect()->back()->with('error', 'Tulis alasan penolakan agar pengguna bisa memperbaiki pengajuannya.');
        }

        model(UserModel::class)->update($id, ['verification_status' => 'rejected']);
        (new NotificationService())->push($id, 'verification', 'Verifikasi belum disetujui', mb_substr($reason, 0, 240), 'profile/edit#verifikasi');

        return redirect()->to('/admin/verification')->with('success', 'Pengajuan ' . $user['name'] . ' ditolak.');
    }

    /** Dokumen verifikasi bersifat privat: hanya admin yang bisa membukanya lewat sini. */
    public function document(int $id)
    {
        $user = $this->pendaur($id);
        $file = $user['verification_doc'] ?? '';
        $path = WRITEPATH . 'uploads/verification/' . basename((string) $file);

        if ($file === '' || ! is_file($path)) {
            $this->notFound('Dokumen tidak ada.');
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';

        return $this->response->setHeader('Content-Type', $mime)
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody(file_get_contents($path));
    }

    private function pendaur(int $id): array
    {
        $u = model(UserModel::class)->find($id);
        if (! $u || $u['role'] !== 'pendaur') {
            $this->notFound('Pengguna tidak ditemukan.');
        }

        return $u;
    }
}
