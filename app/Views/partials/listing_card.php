<?php /** @var array $l */ ?>
<article class="card">
    <a class="stretch" href="<?= site_url('listings/' . $l['id']) ?>" aria-label="<?= esc($l['title']) ?>"></a>
    <span class="tag<?= $l['price_type'] === 'gratis' ? ' free' : '' ?>"><?= esc(price_label($l)) ?></span>
    <?php if ($l['status'] !== 'tersedia'): ?>
        <span class="state badge <?= status_class($l['status']) ?>"><?= esc(listing_status_label($l['status'])) ?></span>
    <?php endif; ?>
    <div class="thumb"><img src="<?= cover_url($l['cover'] ?? null) ?>" alt="" loading="lazy"></div>
    <div class="body">
        <h3><?= esc($l['title']) ?></h3>
        <div class="meta"><?= esc($l['category_name']) ?>, <?= esc(kg($l['weight_kg'])) ?></div>
        <div class="meta"><?= esc($l['city']) ?>, <?= esc(time_ago($l['created_at'])) ?></div>
    </div>
</article>
