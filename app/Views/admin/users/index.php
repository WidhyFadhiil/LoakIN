<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<h1>Pengguna</h1>
<form class="flex mb-2" method="get" action="<?= site_url('admin/users') ?>" role="search">
    <label class="sr-only" for="q">Cari nama atau email</label>
    <input id="q" style="max-width:260px" type="search" name="q" value="<?= esc($q) ?>" placeholder="Nama atau email">
    <label class="sr-only" for="role">Peran</label>
    <select id="role" style="max-width:180px" name="role">
        <option value="">Semua peran</option>
        <?php foreach (['penyetor', 'pendaur', 'admin'] as $r): ?><option value="<?= $r ?>" <?= $role === $r ? 'selected' : '' ?>><?= esc(role_label($r)) ?></option><?php endforeach; ?>
    </select>
    <button class="btn btn-ghost" type="submit">Cari</button>
</form>
<div class="table-wrap"><table class="t">
    <thead><tr><th>Nama</th><th>Peran</th><th>Kota</th><th>Verifikasi</th><th>Rating</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><strong><?= esc($u['name']) ?></strong><div class="small muted"><?= esc($u['email']) ?></div></td>
            <td><?= esc(role_label($u['role'])) ?></td>
            <td><?= esc($u['city'] ?: '-') ?></td>
            <td><?= $u['role'] === 'pendaur' ? '<span class="badge ' . status_class($u['verification_status']) . '">' . esc(verification_label($u['verification_status'])) . '</span>' : '-' ?></td>
            <td><?= $u['rating_count'] ? esc(number_format((float) $u['rating_avg'], 1)) . ' (' . (int) $u['rating_count'] . ')' : '-' ?></td>
            <td><span class="badge <?= $u['is_active'] ? 'is-ok' : 'is-off' ?>"><?= $u['is_active'] ? 'Aktif' : 'Diblokir' ?></span></td>
            <td class="right">
                <?php if ($u['role'] !== 'admin'): ?>
                <form class="inline" action="<?= site_url('admin/users/' . $u['id'] . '/toggle') ?>" method="post" data-confirm="<?= $u['is_active'] ? 'Blokir akun ini?' : 'Aktifkan kembali akun ini?' ?>">
                    <?= csrf_field() ?><button class="btn btn-sm <?= $u['is_active'] ? 'btn-danger' : 'btn-ghost' ?>" type="submit"><?= $u['is_active'] ? 'Blokir' : 'Aktifkan' ?></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
<?= $pager->links() ?>
<?= $this->endSection() ?>
