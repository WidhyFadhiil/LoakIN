<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Pengaturan khusus aplikasi LoakIN.
 * Dipanggil dengan: config('Loakin')
 */
class Loakin extends BaseConfig
{
    /** Listing otomatis kedaluwarsa jika tidak ada yang mengambil selama N hari */
    public int $expireDays = 14;

    public int $maxImages  = 5;
    public int $maxImageKb = 2048;
    public int $perPage    = 12;

    /** 1 pohon dewasa menyerap kira-kira 21 kg CO2 per tahun */
    public float $co2PerTree = 21.0;

    public array $conditions = [
        'baik'  => 'Masih bagus',
        'layak' => 'Bekas, layak pakai',
        'rusak' => 'Rusak / bahan daur ulang',
    ];

    public array $priceTypes = [
        'gratis'   => 'Gratis',
        'berbayar' => 'Harga pas',
        'nego'     => 'Bisa nego',
    ];

    public array $handovers = [
        'pickup'  => 'Diambil di rumah penyetor',
        'diantar' => 'Diantar ke pendaur ulang',
        'janjian' => 'Janjian ketemu di tengah',
    ];

    public array $listingStatuses = [
        'tersedia'    => 'Tersedia',
        'diproses'    => 'Sedang diproses',
        'selesai'     => 'Selesai',
        'dibatalkan'  => 'Dibatalkan',
        'kedaluwarsa' => 'Kedaluwarsa',
    ];

    public array $offerStatuses = [
        'menunggu'   => 'Menunggu jawaban penyetor',
        'counter'    => 'Penyetor mengajukan harga lain',
        'diterima'   => 'Diterima',
        'ditolak'    => 'Ditolak',
        'dibatalkan' => 'Dibatalkan',
        'selesai'    => 'Selesai',
    ];
}
