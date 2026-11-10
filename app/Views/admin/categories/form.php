<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php $action = $cat ? site_url('admin/categories/' . $cat['id'] . '/edit') : site_url('admin/categories'); ?>
<h1><?= $cat ? 'Ubah kategori' : 'Kategori baru' ?></h1>
<form class="panel" style="max-width:560px" action="<?= $action ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <div class="field">
        <label for="name">Nama</label>
        <input id="name" class="<?= err_class('name') ?>" type="text" name="name" value="<?= esc(old('name', $cat['name'] ?? '')) ?>" required>
        <?= field_error('name') ?>
    </div>
    <div class="field">
        <label for="description">Deskripsi singkat</label>
        <input id="description" type="text" name="description" maxlength="255" value="<?= esc(old('description', $cat['description'] ?? '')) ?>">
    </div>
    <div class="field">
        <label for="co2_factor">Faktor CO2 (kg CO2 yang dihemat per 1 kg barang)</label>
        <input id="co2_factor" class="<?= err_class('co2_factor') ?>" type="text" inputmode="decimal" name="co2_factor" value="<?= esc(old('co2_factor', isset($cat['co2_factor']) ? (string) (float) $cat['co2_factor'] : '1')) ?>" required>
        <p class="hint">Estimasi untuk dashboard dampak. Contoh: plastik 1,5, logam 4.</p>
        <?= field_error('co2_factor') ?>
    </div>
    <label class="choice"><input type="checkbox" name="is_active" value="1" <?= old('is_active', $cat['is_active'] ?? 1) ? 'checked' : '' ?>><span>Aktif (bisa dipilih saat posting)</span></label>
    <div class="flex mt-2"><button class="btn btn-primary" type="submit">Simpan</button><a class="btn btn-ghost" href="<?= site_url('admin/categories') ?>">Batal</a></div>
</form>
<?= $this->endSection() ?>
