<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="flex flex-between">
    <h1>Barang saya</h1>
    <a class="btn btn-tag" href="<?= site_url('listings/new') ?>">Posting barang</a>
</div>
<?php if (! $items): ?>
    <div class="empty">
        <h3>Kamu belum memposting barang</h3>
        <p>Foto barang bekasmu, isi detailnya, dan biarkan pendaur ulang yang menghubungimu.</p>
        <a class="btn btn-primary" href="<?= site_url('listings/new') ?>">Posting barang pertama</a>
    </div>
<?php else: ?>
    <ul class="list">
        <?php foreach ($items as $l): ?>
            <li>
                <div class="who">
                    <img class="thumb-sm" src="<?= cover_url($l['cover'] ?? null) ?>" alt="">
                    <div>
                        <a href="<?= site_url('listings/' . $l['id']) ?>"><strong><?= esc($l['title']) ?></strong></a>
                        <div class="small muted"><?= esc($l['category_name']) ?>, <?= esc(kg($l['weight_kg'])) ?>, <?= esc(price_label($l)) ?>, diposting <?= esc(time_ago($l['created_at'])) ?></div>
                    </div>
                </div>
                <div class="flex">
                    <span class="badge <?= status_class($l['status']) ?>"><?= esc(listing_status_label($l['status'])) ?></span>
                    <a class="btn btn-ghost btn-sm" href="<?= site_url('listings/' . $l['id']) ?>">Buka</a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?= $this->endSection() ?>
