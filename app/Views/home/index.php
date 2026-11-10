<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<section class="hero">
    <h1>Barang tak terpakai, jadi bahan orang lain.</h1>
    <p class="lead">Posting barang bekas atau rusak yang menumpuk di rumah. Pendaur ulang terverifikasi di sekitarmu akan mengambilnya, tanpa perlu keliling mencari pembeli.</p>
    <form class="searchbar" action="<?= site_url('listings') ?>" method="get" role="search">
        <label class="sr-only" for="q">Cari barang</label>
        <input id="q" type="search" name="q" placeholder="Cari botol plastik, kardus, besi tua...">
        <button class="btn btn-primary" type="submit">Cari barang</button>
    </form>
    <div class="chips" aria-label="Kategori">
        <?php foreach ($categories as $c): ?>
            <a class="chip" href="<?= site_url('listings?category=' . $c['id']) ?>"><?= esc($c['name']) ?><span><?= (int) $c['open_count'] ?></span></a>
        <?php endforeach; ?>
    </div>
</section>

<div class="strip" aria-label="Ringkasan platform">
    <div><strong><?= (int) $openCount ?></strong>barang menunggu diambil</div>
    <div><strong><?= esc(kg($impact['total_kg'])) ?></strong>sudah berpindah tangan</div>
    <div><strong><?= esc(kg($impact['total_co2'])) ?></strong>estimasi emisi CO2 yang dihemat</div>
</div>

<section>
    <div class="flex flex-between">
        <h2>Baru diposting</h2>
        <a href="<?= site_url('listings') ?>">Lihat semua barang</a>
    </div>
    <?php if ($latest): ?>
        <div class="grid mt-2">
            <?php foreach ($latest as $l): ?><?= view('partials/listing_card', ['l' => $l]) ?><?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty mt-2">
            <h3>Belum ada barang yang tayang</h3>
            <p>Jadilah yang pertama memposting barang bekasmu.</p>
            <a class="btn btn-primary" href="<?= site_url('register') ?>">Daftar dan posting</a>
        </div>
    <?php endif; ?>
</section>

<section class="mt-3">
    <h2>Cara kerjanya</h2>
    <ol class="how">
        <li><b>Penyetor memposting</b>Foto, kategori, kondisi, lokasi, harga, dan cara serah terima.</li>
        <li><b>Pendaur mengajukan</b>Pendaur ulang terverifikasi menekan "Ajukan Ambil" dan boleh menawar harga.</li>
        <li><b>Sepakat dan serah terima</b>Chat di web, nomor WA terbuka setelah diterima. Barang diserahkan langsung.</li>
        <li><b>Konfirmasi dan rating</b>Penyetor konfirmasi selesai, beratnya masuk dashboard dampak, lalu saling memberi rating.</li>
    </ol>
    <?php if (! logged_in()): ?>
        <div class="flex mt-2">
            <a class="btn btn-primary" href="<?= site_url('register') ?>">Saya punya barang</a>
            <a class="btn btn-ghost" href="<?= site_url('register?as=pendaur') ?>">Saya pendaur ulang</a>
        </div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
