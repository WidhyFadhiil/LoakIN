<?php

namespace App\Controllers;

use App\Libraries\NotificationService;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function register(): string
    {
        return view('auth/register', [
            'title' => 'Daftar',
            'role'  => $this->request->getGet('as') === 'pendaur' ? 'pendaur' : 'penyetor',
        ]);
    }

    public function store()
    {
        $rules = [
            'name'             => 'required|min_length[3]|max_length[100]',
            'email'            => 'required|valid_email|max_length[150]|is_unique[users.email]',
            'password'         => 'required|min_length[8]|max_length[72]',
            'password_confirm' => 'required|matches[password]',
            'role'             => 'required|in_list[penyetor,pendaur]',
            'phone'            => 'required|regex_match[/^(\+62|62|0)8[0-9]{7,12}$/]',
            'city'             => 'required|max_length[80]',
        ];
        $messages = [
            'email' => ['is_unique' => 'Email ini sudah terdaftar. Coba masuk.'],
            'phone' => ['regex_match' => 'Nomor WA harus diawali 08 atau +628, contoh 081234567890.'],
            'password_confirm' => ['matches' => 'Konfirmasi password tidak sama.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $users = model(UserModel::class);
        $id    = $users->insert([
            'name'                => trim($this->request->getPost('name')),
            'email'               => strtolower(trim($this->request->getPost('email'))),
            'password'            => $this->request->getPost('password'),
            'role'                => $this->request->getPost('role'),
            'phone'               => preg_replace('/[^\d+]/', '', $this->request->getPost('phone')),
            'city'                => trim($this->request->getPost('city')),
            'verification_status' => 'none',
            'is_active'           => 1,
        ], true);

        $this->startSession($users->find($id));

        $msg = $this->request->getPost('role') === 'pendaur'
            ? 'Akun dibuat. Satu langkah lagi: ajukan verifikasi supaya bisa mengambil barang.'
            : 'Akun dibuat. Yuk posting barang pertamamu.';

        (new NotificationService())->push((int) $id, 'welcome', 'Selamat datang di LoakIN', $msg, $this->request->getPost('role') === 'pendaur' ? 'profile/edit' : 'listings/new');

        return redirect()->to($this->request->getPost('role') === 'pendaur' ? '/profile/edit#verifikasi' : '/listings/new')->with('success', $msg);
    }

    public function login(): string
    {
        return view('auth/login', ['title' => 'Masuk']);
    }

    public function attempt()
    {
        // Batasi percobaan login: 8 kali per menit per IP
        $throttler = service('throttler');
        if ($throttler->check(md5('login-' . $this->request->getIPAddress()), 8, MINUTE) === false) {
            return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.');
        }

        $email = (string) $this->request->getPost('email');
        $pass  = (string) $this->request->getPost('password');
        $user  = model(UserModel::class)->findByEmail($email);

        if (! $user || ! password_verify($pass, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Email atau password salah.');
        }
        if (! (int) $user['is_active']) {
            return redirect()->back()->withInput()->with('error', 'Akunmu sedang diblokir. Hubungi admin LoakIN.');
        }

        $this->startSession($user);

        $to = session()->get('intended_url');
        session()->remove('intended_url');
        if (! $to) {
            $to = $user['role'] === 'admin' ? site_url('admin') : site_url('/');
        }

        return redirect()->to($to)->with('success', 'Halo, ' . $user['name'] . '!');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/')->with('success', 'Kamu sudah keluar.');
    }

    private function startSession(array $user): void
    {
        $s = session();
        $s->regenerate(true); // cegah session fixation
        $s->set(['user_id' => (int) $user['id'], 'role' => $user['role'], 'user_name' => $user['name']]);
    }
}
