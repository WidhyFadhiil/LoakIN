<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="flex flex-between">
    <h1>Pengajuan saya</h1>
    <a class="btn btn-ghost" href="<?= site_url('listings') ?>">Cari barang lain</a>
</div>
<?php if (! $offers): ?>
    <div class="empty">
        <h3>Belum ada pengajuan yang berjalan</h3>
        <p>Cari barang yang kamu butuhkan lalu tekan "Ajukan Ambil".</p>
        <a class="btn btn-primary" href="<?= site_url('listings') ?>">Cari barang</a>
    </div>
<?php else: ?>
    <ul class="list"><?php foreach ($offers as $o): ?><?= view('offer/_row', ['o' => $o, 'asOwner' => false]) ?><?php endforeach; ?></ul>
<?php endif; ?>
<?= $this->endSection() ?>
