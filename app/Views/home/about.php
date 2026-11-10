<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="mid">
    <h1>Cara kerja LoakIN</h1>
    <p class="lead muted">"Loak" ditambah "-in": ajakan untuk meloakkan barang yang tidak terpakai atau rusak supaya bernilai lagi. Barang loak bukan lagi barang buangan, tapi gaya hidup yang ramah lingkungan.</p>

    <div class="panel">
        <h2>Alur transaksi</h2>
        <p>Yang tahu kondisi barang adalah pemiliknya, jadi penyetor yang memposting. Pendaur ulang yang butuh bahan, yang mencari dan mengambil. Barang tidak lewat web atau admin. Web hanya menghubungkan dan mencatat.</p>
        <ol>
            <li>Penyetor memposting barang: foto, kategori, kondisi, lokasi, harga (gratis, harga pas, atau bisa nego), dan metode serah terima.</li>
            <li>Pendaur ulang melihat listing lalu menekan "Ajukan Ambil", boleh dengan tawaran harga.</li>
            <li>Penyetor menerima, menolak, atau membalas dengan harga lain. Saat satu pengajuan diterima, status barang menjadi Diproses dan pengajuan lain otomatis ditolak.</li>
            <li>Keduanya sepakat jadwal lewat kolom pesan. Nomor WA baru terbuka setelah pengajuan diterima.</li>
            <li>Serah terima terjadi langsung. Penyetor menekan "Konfirmasi Selesai", lalu berat barang masuk dashboard dampak.</li>
            <li>Keduanya saling memberi rating.</li>
        </ol>
    </div>

    <div class="panel">
        <h2>Tiga cara serah terima</h2>
        <div class="table-wrap"><table class="t">
            <tr><th>Metode</th><th>Cara</th><th>Cocok untuk</th></tr>
            <tr><td>Diambil</td><td>Pendaur ulang datang ke rumah penyetor</td><td>Barang besar atau berat</td></tr>
            <tr><td>Diantar</td><td>Penyetor mengantar ke lokasi pendaur ulang</td><td>Barang kecil atau ringan</td></tr>
            <tr><td>Janjian ketemu</td><td>Bertemu di titik tengah</td><td>Lokasi saling berjauhan</td></tr>
        </table></div>
    </div>

    <div class="panel">
        <h2>Status barang</h2>
        <div class="table-wrap"><table class="t">
            <tr><th>Status</th><th>Artinya</th><th>Tampil di pencarian</th></tr>
            <tr><td>Tersedia</td><td>Belum ada pengajuan yang diterima</td><td>Ya</td></tr>
            <tr><td>Sedang diproses</td><td>Penyetor sudah menerima satu pengajuan</td><td>Ya, dengan label "Sedang diproses"</td></tr>
            <tr><td>Selesai</td><td>Barang sudah diserahkan dan dikonfirmasi</td><td>Tidak, pindah ke riwayat</td></tr>
            <tr><td>Dibatalkan</td><td>Penyetor membatalkan postingan</td><td>Tidak</td></tr>
            <tr><td>Kedaluwarsa</td><td>Tidak ada yang mengambil selama <?= (int) config('Loakin')->expireDays ?> hari</td><td>Tidak</td></tr>
        </table></div>
        <p class="mt-2 mb-0 muted">Data tidak pernah dihapus, jadi tetap bisa dipakai untuk laporan.</p>
    </div>

    <div class="panel">
        <h2>Rencana pengembangan</h2>
        <p>Dua hal berikut sengaja belum ada di versi ini karena butuh izin usaha, verifikasi, dan integrasi tambahan.</p>
        <div class="table-wrap"><table class="t">
            <tr><th>Fitur</th><th>Versi sekarang</th><th>Pengembangan lanjut</th></tr>
            <tr><td>Pembayaran</td><td>Bayar langsung (tunai atau transfer) saat serah terima. Web hanya mencatat harga yang disepakati.</td><td>Payment gateway (Midtrans, Xendit) dengan rekening bersama.</td></tr>
            <tr><td>Kurir</td><td>Tidak ada. Diambil, diantar, atau janjian ketemu, diatur kedua pihak.</td><td>Integrasi API kurir (Biteship, RajaOngkir) atau ojek lokal.</td></tr>
            <tr><td>Chat</td><td>Kolom pesan per pengajuan, tersimpan di database.</td><td>Chat real-time (WebSocket).</td></tr>
            <tr><td>Arah permintaan</td><td>Penyetor posting, pendaur mengajukan.</td><td>Pendaur ulang juga bisa memposting kebutuhan, misalnya "Butuh 20 botol bekas".</td></tr>
        </table></div>
    </div>

    <div class="panel">
        <h2>Model bisnis</h2>
        <ul>
            <li>Listing premium atau boost agar postingan tampil di atas.</li>
            <li>Langganan akun pendaur ulang bisnis: statistik, prioritas, dan lencana terverifikasi.</li>
            <li>Sponsor dan CSR dari perusahaan yang ingin citra ramah lingkungan.</li>
            <li>Kemitraan dengan pemda, dinas lingkungan hidup, dan komunitas.</li>
            <li>Komisi 3 sampai 5 persen dari transaksi berbayar setelah payment gateway tersedia.</li>
        </ul>
    </div>
</div>
<?= $this->endSection() ?>
