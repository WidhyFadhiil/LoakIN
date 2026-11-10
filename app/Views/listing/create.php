<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="mid">
    <h1>Posting barang</h1>
    <p class="muted">Postinganmu tayang <?= (int) config('Loakin')->expireDays ?> hari. Kalau belum ada yang mengambil, statusnya berubah otomatis menjadi Kedaluwarsa.</p>
    <?= view('listing/_form', ['action' => site_url('listings'), 'submit' => 'Tayangkan barang', 'listing' => null, 'images' => [], 'categories' => $categories, 'defaults' => $defaults]) ?>
</div>
<?= $this->endSection() ?>
