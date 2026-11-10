<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Halaman yang butuh login. Akun yang diblokir admin langsung dikeluarkan.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('user_id')) {
            $session->set('intended_url', current_url());

            return redirect()->to('/login')->with('error', 'Silakan masuk dulu untuk melanjutkan.');
        }

        $user = model(UserModel::class)->find($session->get('user_id'));

        if (! $user || ! (int) $user['is_active']) {
            $session->destroy();

            return redirect()->to('/login')->with('error', 'Akun tidak ditemukan atau sedang diblokir.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
