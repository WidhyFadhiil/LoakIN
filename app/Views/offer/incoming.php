<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h1>Pengajuan masuk</h1>
<?php if (! $offers): ?>
    <div class="empty">
        <h3>Belum ada pengajuan yang menunggu</h3>
        <p>Pengajuan dari pendaur ulang untuk barangmu akan muncul di sini.</p>
        <a class="btn btn-ghost" href="<?= site_url('my/listings') ?>">Lihat barang saya</a>
    </div>
<?php else: ?>
    <ul class="list"><?php foreach ($offers as $o): ?><?= view('offer/_row', ['o' => $o, 'asOwner' => true]) ?><?php endforeach; ?></ul>
<?php endif; ?>
<?= $this->endSection() ?>
