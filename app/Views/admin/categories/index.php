<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<div class="flex flex-between"><h1>Kategori</h1><a class="btn btn-primary" href="<?= site_url('admin/categories/new') ?>">Tambah kategori</a></div>
<div class="table-wrap"><table class="t">
    <thead><tr><th>Nama</th><th>Faktor CO2 (kg per kg)</th><th>Barang</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($cats as $c): ?>
        <tr>
            <td><strong><?= esc($c['name']) ?></strong><div class="small muted"><?= esc($c['description']) ?></div></td>
            <td><?= esc(number_format((float) $c['co2_factor'], 2, ',', '.')) ?></td>
            <td><?= (int) $c['listing_count'] ?></td>
            <td><span class="badge <?= $c['is_active'] ? 'is-ok' : 'is-off' ?>"><?= $c['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
            <td class="right">
                <a class="btn btn-ghost btn-sm" href="<?= site_url('admin/categories/' . $c['id'] . '/edit') ?>">Ubah</a>
                <form class="inline" action="<?= site_url('admin/categories/' . $c['id'] . '/delete') ?>" method="post" data-confirm="<?= $c['listing_count'] ? 'Kategori ini dipakai barang, jadi akan dinonaktifkan. Lanjut?' : 'Hapus kategori ini?' ?>">
                    <?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit"><?= $c['listing_count'] ? 'Nonaktifkan' : 'Hapus' ?></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
<?= $this->endSection() ?>
