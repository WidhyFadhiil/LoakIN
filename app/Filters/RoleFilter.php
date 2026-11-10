<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Pembatasan akses per role. Contoh di Routes: ['filter' => 'role:admin']
 * atau 'role:penyetor,pendaur'. Dijalankan SETELAH filter auth.
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = session()->get('role');

        if (! $role || ! in_array($role, (array) $arguments, true)) {
            return redirect()->to('/')->with('error', 'Kamu tidak punya akses ke halaman itu.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
