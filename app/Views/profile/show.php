<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="mid">
    <div class="panel">
        <div class="who">
            <div class="avatar lg"><?php if ($profile['photo']): ?><img src="<?= base_url('uploads/avatars/' . $profile['photo']) ?>" alt=""><?php else: ?><?= esc(initials($profile['name'])) ?><?php endif; ?></div>
            <div>
                <h1 class="mb-0"><?= esc($profile['name']) ?></h1>
                <p class="mb-0 muted">
                    <?= esc(role_label($profile['role'])) ?>, <?= esc($profile['city'] ?: 'Kota belum diisi') ?>
                    <?php if ($profile['role'] === 'pendaur' && $profile['verification_status'] === 'approved'): ?> <span class="badge is-ok">Terverifikasi</span><?php endif; ?>
                </p>
                <p class="mb-0 small">
                    <?php if ((int) $profile['rating_count'] > 0): ?>
                        <span class="stars"><?= stars((float) $profile['rating_avg']) ?></span> <?= number_format((float) $profile['rating_avg'], 1) ?> dari <?= (int) $profile['rating_count'] ?> rating
                    <?php else: ?><span class="muted">Belum ada rating</span><?php endif; ?>
                </p>
            </div>
        </div>
        <?php if ($profile['bio']): ?><p class="mt-2 mb-0" style="white-space:pre-wrap"><?= esc($profile['bio']) ?></p><?php endif; ?>
    </div>

    <div class="stat-grid mt-2">
        <div class="stat"><strong><?= esc(kg($impact['total_kg'])) ?></strong><span>barang berpindah tangan</span></div>
        <div class="stat"><strong><?= (int) $impact['count'] ?></strong><span>transaksi selesai</span></div>
    </div>

    <?php if ($listings): ?>
        <h2>Barang yang sedang tersedia</h2>
        <div class="grid"><?php foreach ($listings as $l): ?><?= view('partials/listing_card', ['l' => $l]) ?><?php endforeach; ?></div>
    <?php endif; ?>

    <h2 class="mt-3">Ulasan</h2>
    <?php if (! $ratings): ?>
        <div class="empty"><p class="mb-0">Belum ada ulasan.</p></div>
    <?php else: ?>
        <ul class="list">
            <?php foreach ($ratings as $r): ?>
                <li><div>
                    <span class="stars"><?= stars((float) $r['score']) ?></span>
                    <strong><?= esc($r['rater_name']) ?></strong> <span class="small muted">untuk "<?= esc($r['listing_title']) ?>", <?= esc(time_ago($r['created_at'])) ?></span>
                    <?php if ($r['comment']): ?><div><?= esc($r['comment']) ?></div><?php endif; ?>
                </div></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
