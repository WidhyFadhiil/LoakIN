<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h1>Dampak lingkungan</h1>
<p class="muted" style="max-width:62ch">Hanya barang yang benar-benar berpindah tangan dan dikonfirmasi selesai yang dihitung. Angka CO2 adalah estimasi dari faktor tiap kategori barang, bukan hasil pengukuran.</p>

<?php if ($mine !== null): ?>
    <h2 class="mt-3">Dampakmu</h2>
    <?= view('impact/_summary', ['s' => $mine]) ?>
<?php endif; ?>

<h2 class="mt-3">Seluruh LoakIN</h2>
<?= view('impact/_summary', ['s' => $global]) ?>
<?= $this->endSection() ?>
