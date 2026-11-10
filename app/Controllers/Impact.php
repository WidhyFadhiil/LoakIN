<?php

namespace App\Controllers;

use App\Libraries\ImpactCalculator;

/**
 * Dashboard dampak lingkungan: hanya barang berstatus "Selesai" yang dihitung.
 * Tamu melihat angka seluruh platform, pengguna login juga melihat angkanya sendiri.
 */
class Impact extends BaseController
{
    public function index(): string
    {
        $calc = new ImpactCalculator();

        return view('impact/dashboard', [
            'title'  => 'Dampak lingkungan',
            'global' => $calc->summary(),
            'mine'   => $this->uid ? $calc->summary($this->uid, $this->role) : null,
        ]);
    }
}
