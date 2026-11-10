<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="mid">
    <h1>Ubah barang</h1>
    <?= view('listing/_form', ['action' => site_url('listings/' . $listing['id'] . '/edit'), 'submit' => 'Simpan perubahan', 'listing' => $listing, 'images' => $images, 'categories' => $categories, 'defaults' => []]) ?>
</div>
<?= $this->endSection() ?>
