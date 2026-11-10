<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Users extends BaseController
{
    public function index(): string
    {
        $q    = trim((string) $this->request->getGet('q'));
        $role = (string) $this->request->getGet('role');

        $model = model(UserModel::class);
        if ($q !== '') {
            $model->groupStart()->like('name', $q)->orLike('email', $q)->groupEnd();
        }
        if (in_array($role, ['penyetor', 'pendaur', 'admin'], true)) {
            $model->where('role', $role);
        }

        return view('admin/users/index', [
            'title' => 'Pengguna',
            'users' => $model->orderBy('id', 'DESC')->paginate(20),
            'pager' => $model->pager,
            'q'     => $q,
            'role'  => $role,
        ]);
    }

    /** Blokir / aktifkan akun. Admin tidak bisa memblokir dirinya sendiri. */
    public function toggle(int $id)
    {
        $model = model(UserModel::class);
        $user  = $model->find($id);
        if (! $user) {
            $this->notFound('Pengguna tidak ditemukan.');
        }
        if ($id === $this->uid || $user['role'] === 'admin') {
            return redirect()->back()->with('error', 'Akun admin tidak bisa diblokir.');
        }

        $model->update($id, ['is_active' => (int) $user['is_active'] ? 0 : 1]);

        return redirect()->back()->with('success', $user['name'] . ((int) $user['is_active'] ? ' diblokir.' : ' diaktifkan kembali.'));
    }
}
