# LoakIN

Platform web yang mempertemukan **penyetor** (pemilik barang bekas/rusak) dengan **pendaur ulang**.
Dibangun dengan CodeIgniter 4.7 (PHP 8.2+), MySQL, tanpa library JavaScript.
Web hanya menghubungkan dan mencatat; barang dan uang berpindah langsung antar pengguna.

## Menjalankan (XAMPP / Laragon)

1. Pastikan PHP 8.2+ dengan ekstensi `intl`, `mbstring`, `mysqli`, `gd` aktif (php.ini).
2. Buat database kosong bernama `loakin` di phpMyAdmin (collation `utf8mb4_general_ci`).
3. Cek `.env` (default: user `root`, tanpa password, database `loakin`).
4. Di folder proyek:
   ```
   php spark migrate
   php spark db:seed DatabaseSeeder     # kategori + akun admin
   php spark db:seed DemoSeeder         # opsional: data contoh untuk demo
   php spark serve
   ```
5. Buka http://localhost:8080

## Akun

| Peran | Email | Password |
|---|---|---|
| Admin | admin@loakin.test | admin12345 |
| Penyetor (demo) | sari@loakin.test, budi@loakin.test | password123 |
| Pendaur terverifikasi (demo) | hijau@loakin.test, kriya@loakin.test | password123 |
| Pendaur menunggu verifikasi (demo) | dedi@loakin.test | password123 |

Ganti password admin sebelum dipakai sungguhan.

## Kedaluwarsa otomatis

```
php spark listings:expire
```
Jadwalkan harian (cron / Windows Task Scheduler). Listing "Tersedia" yang tidak diambil
`Config\Loakin::$expireDays` hari (default 14) menjadi "Kedaluwarsa" dan penyetor diberi notifikasi.

## Struktur

```
app/Config/Loakin.php        pengaturan aplikasi (masa tayang, label status, batas foto)
app/Config/Routes.php        semua route, dikelompokkan per peran
app/Controllers/             Auth, Home, Listing, Offer, Message, Rating, Notification, Impact, Profile
app/Controllers/Admin/       Dashboard, Users, Verification, Categories
app/Models/                  User, Category, Listing, ListingImage, Offer, Message, Notification, Rating
app/Libraries/OfferService   seluruh aturan transaksi & status
app/Libraries/               NotificationService, ImpactCalculator, ImageUploader
app/Filters/                 AuthFilter, GuestFilter, RoleFilter
app/Database/Migrations/     8 tabel
app/Database/Seeds/          Category, Admin, Demo
app/Commands/ExpireListings  php spark listings:expire
app/Views/                   layouts, partials, dan halaman per fitur
public/assets/               CSS & JS
```

## Alur status

```
Barang : Tersedia -> Diproses -> Selesai      (atau Dibatalkan / Kedaluwarsa)
Pengajuan : Menunggu <-> Counter -> Diterima -> Selesai   (atau Ditolak / Dibatalkan)
```

## Rencana pengembangan (belum dikodekan, sesuai dokumen konsep)

Payment gateway + rekening bersama (Midtrans/Xendit), integrasi kurir (Biteship/RajaOngkir),
chat real-time (WebSocket), arah "permintaan" (pendaur memposting kebutuhan), listing premium,
langganan pendaur bisnis, komisi 3-5%.
