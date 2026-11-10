<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h1>Cari barang</h1>
<form class="filters" action="<?= site_url('listings') ?>" method="get" role="search">
    <div class="field">
        <label for="q">Kata kunci</label>
        <input id="q" type="search" name="q" value="<?= esc($filters['q']) ?>" placeholder="botol, kardus, besi...">
    </div>
    <div class="field">
        <label for="category">Kategori</label>
        <select id="category" name="category">
            <option value="">Semua kategori</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= (int) $filters['category'] === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="city">Kota</label>
        <input id="city" type="text" name="city" value="<?= esc($filters['city']) ?>" placeholder="Bekasi">
    </div>
    <div class="field">
        <label for="price">Harga</label>
        <select id="price" name="price">
            <option value="">Semua</option>
            <option value="gratis" <?= $filters['price'] === 'gratis' ? 'selected' : '' ?>>Gratis</option>
            <option value="berbayar" <?= $filters['price'] === 'berbayar' ? 'selected' : '' ?>>Berbayar</option>
        </select>
    </div>
    <div class="field">
        <label for="sort">Urutkan</label>
        <select id="sort" name="sort">
            <option value="terbaru" <?= $filters['sort'] === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
            <option value="termurah" <?= $filters['sort'] === 'termurah' ? 'selected' : '' ?>>Termurah</option>
            <option value="terberat" <?= $filters['sort'] === 'terberat' ? 'selected' : '' ?>>Terberat</option>
        </select>
    </div>
    <button class="btn btn-primary" type="submit">Terapkan</button>
</form>

<?php if ($items): ?>
    <div class="grid">
        <?php foreach ($items as $l): ?><?= view('partials/listing_card', ['l' => $l]) ?><?php endforeach; ?>
    </div>
    <?= $pager->links() ?>
<?php else: ?>
    <div class="empty">
        <h3>Tidak ada barang yang cocok</h3>
        <p>Coba kata kunci lain, hapus filter kota, atau pilih semua kategori.</p>
        <a class="btn btn-ghost" href="<?= site_url('listings') ?>">Hapus semua filter</a>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
