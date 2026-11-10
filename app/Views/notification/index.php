<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="mid">
    <div class="flex flex-between">
        <h1>Notifikasi</h1>
        <?php if ($items): ?>
            <form action="<?= site_url('notifications/read-all') ?>" method="post"><?= csrf_field() ?><button class="btn btn-ghost btn-sm" type="submit">Tandai semua dibaca</button></form>
        <?php endif; ?>
    </div>
    <?php if (! $items): ?>
        <div class="empty"><h3>Belum ada notifikasi</h3><p>Kabar penting seperti pengajuan baru atau pesan akan muncul di sini.</p></div>
    <?php else: ?>
        <ul class="list">
            <?php foreach ($items as $n): ?>
                <li style="<?= $n['is_read'] ? '' : 'border-color:var(--leaf);background:var(--leaf-tint)' ?>">
                    <div>
                        <a href="<?= site_url('notifications/open/' . $n['id']) ?>"><strong><?= esc($n['title']) ?></strong></a>
                        <?php if ($n['body']): ?><div class="small muted"><?= esc($n['body']) ?></div><?php endif; ?>
                    </div>
                    <span class="small muted"><?= esc(time_ago($n['created_at'])) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
