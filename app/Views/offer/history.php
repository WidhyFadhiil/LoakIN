<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h1>Riwayat transaksi</h1>
<?php if (! $offers): ?>
    <div class="empty"><h3>Belum ada riwayat</h3><p>Transaksi yang selesai, ditolak, atau dibatalkan tercatat di sini.</p></div>
<?php else: ?>
    <ul class="list">
        <?php foreach ($offers as $o): ?>
            <?= view('offer/_row', ['o' => $o, 'asOwner' => $o['owner_id'] == session('user_id')]) ?>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?= $this->endSection() ?>
