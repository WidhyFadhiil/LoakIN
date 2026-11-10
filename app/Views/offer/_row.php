<?php /** @var array $o  @var bool $asOwner */ ?>
<li>
    <div class="who">
        <img class="thumb-sm" src="<?= cover_url($o['cover'] ?? null) ?>" alt="">
        <div>
            <a href="<?= site_url('offers/' . $o['id']) ?>"><strong><?= esc($o['listing_title']) ?></strong></a>
            <div class="small muted">
                <?= $asOwner ? 'Dari ' . esc($o['recycler_name']) : 'Penyetor ' . esc($o['owner_name']) ?>,
                harga <?= ($p = \App\Models\OfferModel::currentPrice($o)) > 0 ? esc(rupiah($p)) : 'gratis' ?>,
                <?= esc(time_ago($o['updated_at'])) ?>
            </div>
        </div>
    </div>
    <div class="flex">
        <span class="badge <?= status_class($o['status']) ?>"><?= esc(offer_status_label($o['status'])) ?></span>
        <?php if ($o['status'] === 'selesai' && isset($o['rated']) && ! $o['rated']): ?>
            <a class="btn btn-tag btn-sm" href="<?= site_url('ratings/' . $o['id']) ?>">Beri rating</a>
        <?php endif; ?>
        <a class="btn btn-ghost btn-sm" href="<?= site_url('offers/' . $o['id']) ?>">Buka</a>
    </div>
</li>
