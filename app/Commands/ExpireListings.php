<?php

namespace App\Commands;

use App\Libraries\OfferService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Auto-kedaluwarsa: listing "Tersedia" yang tidak ada yang mengambil selama N hari
 * (config Loakin::$expireDays, default 14) berubah menjadi "Kedaluwarsa" dan penyetor diberi notifikasi.
 *
 * Jalankan harian lewat cron / Task Scheduler:
 *     php spark listings:expire
 */
class ExpireListings extends BaseCommand
{
    protected $group       = 'LoakIN';
    protected $name        = 'listings:expire';
    protected $description = 'Ubah listing yang tidak diambil selama N hari menjadi Kedaluwarsa dan kirim notifikasi.';
    protected $usage       = 'listings:expire';
    protected $arguments   = [];
    protected $options     = [];

    public function run(array $params)
    {
        $n = (new OfferService())->expireListings();
        CLI::write($n . ' listing dikedaluwarsakan.', $n > 0 ? 'yellow' : 'green');
    }
}
