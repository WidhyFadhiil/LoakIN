<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<h1>Ringkasan</h1>
<?php if ($stats['pending'] > 0): ?>
    <div class="callout"><strong><?= (int) $stats['pending'] ?> pendaur ulang menunggu verifikasi</strong><a href="<?= site_url('admin/verification') ?>">Tinjau sekarang</a></div>
<?php endif; ?>
<div class="stat-grid">
    <div class="stat"><strong><?= (int) $stats['penyetor'] ?></strong><span>penyetor</span></div>
    <div class="stat"><strong><?= (int) $stats['pendaur'] ?></strong><span>pendaur ulang</span></div>
    <div class="stat"><strong><?= (int) $stats['listings'] ?></strong><span>barang diposting</span></div>
    <div class="stat"><strong><?= (int) $stats['offers'] ?></strong><span>pengajuan</span></div>
    <div class="stat"><strong><?= esc(kg($impact['total_kg'])) ?></strong><span>sudah berpindah tangan</span></div>
    <div class="stat"><strong><?= esc(kg($impact['total_co2'])) ?></strong><span>estimasi CO2 dihemat</span></div>
</div>
<div class="row">
    <div class="panel">
        <h3>Barang menurut status</h3>
        <?php foreach (config('Loakin')->listingStatuses as $k => $label): ?>
            <div class="flex flex-between"><span><span class="badge <?= status_class($k) ?>"><?= esc($label) ?></span></span><strong><?= (int) ($byStatus[$k] ?? 0) ?></strong></div>
        <?php endforeach; ?>
    </div>
    <div class="panel">
        <h3>Barang terbaru</h3>
        <?php if (! $recent): ?><p class="muted mb-0">Belum ada barang.</p><?php endif; ?>
        <?php foreach ($recent as $r): ?>
            <div class="flex flex-between"><a href="<?= site_url('listings/' . $r['id']) ?>"><?= esc(character_limiter($r['title'], 34)) ?></a><span class="small muted"><?= esc($r['owner']) ?></span></div>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>
