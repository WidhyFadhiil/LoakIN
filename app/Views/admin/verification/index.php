<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<h1>Verifikasi pendaur ulang</h1>
<h2>Menunggu tinjauan (<?= count($pending) ?>)</h2>
<?php if (! $pending): ?>
    <div class="empty"><p class="mb-0">Tidak ada pengajuan yang menunggu.</p></div>
<?php endif; ?>
<?php foreach ($pending as $u): ?>
    <div class="panel">
        <div class="flex flex-between">
            <div><strong><?= esc($u['name']) ?></strong><div class="small muted"><?= esc($u['email']) ?>, <?= esc($u['phone']) ?>, <?= esc($u['city']) ?></div></div>
            <?php if ($u['verification_doc']): ?><a class="btn btn-ghost btn-sm" href="<?= site_url('admin/verification/' . $u['id'] . '/document') ?>" target="_blank" rel="noopener">Buka dokumen</a><?php endif; ?>
        </div>
        <p class="mt-1" style="white-space:pre-wrap"><?= esc($u['verification_note']) ?></p>
        <div class="flex">
            <form class="inline" action="<?= site_url('admin/verification/' . $u['id'] . '/approve') ?>" method="post"><?= csrf_field() ?><button class="btn btn-primary btn-sm" type="submit">Setujui</button></form>
            <form class="flex" action="<?= site_url('admin/verification/' . $u['id'] . '/reject') ?>" method="post">
                <?= csrf_field() ?>
                <label class="sr-only" for="reason<?= $u['id'] ?>">Alasan penolakan</label>
                <input id="reason<?= $u['id'] ?>" style="width:280px" type="text" name="reason" placeholder="Alasan penolakan" required>
                <button class="btn btn-danger btn-sm" type="submit">Tolak</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<h2 class="mt-3">Sudah diproses</h2>
<?php if (! $done): ?><div class="empty"><p class="mb-0">Belum ada.</p></div><?php else: ?>
<div class="table-wrap"><table class="t">
    <thead><tr><th>Nama</th><th>Status</th><th>Diproses</th></tr></thead>
    <tbody><?php foreach ($done as $u): ?>
        <tr><td><?= esc($u['name']) ?></td><td><span class="badge <?= status_class($u['verification_status']) ?>"><?= esc(verification_label($u['verification_status'])) ?></span></td><td><?= esc(tanggal($u['updated_at'])) ?></td></tr>
    <?php endforeach; ?></tbody>
</table></div>
<?php endif; ?>
<?= $this->endSection() ?>
