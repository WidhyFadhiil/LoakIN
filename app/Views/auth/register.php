<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $selected = old('role') ?: $role; ?>
<div class="narrow">
    <h1>Daftar</h1>
    <form class="panel" action="<?= site_url('register') ?>" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="field">
            <span class="label">Saya mendaftar sebagai</span>
            <label class="choice">
                <input type="radio" name="role" value="penyetor" <?= $selected === 'penyetor' ? 'checked' : '' ?>>
                <span>Penyetor<small>Punya barang bekas atau rusak yang ingin diloakkan.</small></span>
            </label>
            <label class="choice">
                <input type="radio" name="role" value="pendaur" <?= $selected === 'pendaur' ? 'checked' : '' ?>>
                <span>Pendaur ulang<small>Mencari bahan untuk didaur ulang atau dipakai ulang. Perlu verifikasi admin sebelum bisa mengajukan.</small></span>
            </label>
            <?= field_error('role') ?>
        </div>
        <div class="field">
            <label for="name">Nama lengkap atau nama usaha</label>
            <input id="name" class="<?= err_class('name') ?>" type="text" name="name" value="<?= esc(old('name')) ?>" autocomplete="name" required>
            <?= field_error('name') ?>
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input id="email" class="<?= err_class('email') ?>" type="email" name="email" value="<?= esc(old('email')) ?>" autocomplete="email" required>
            <?= field_error('email') ?>
        </div>
        <div class="row">
            <div class="field">
                <label for="phone">Nomor WA</label>
                <input id="phone" class="<?= err_class('phone') ?>" type="tel" name="phone" value="<?= esc(old('phone')) ?>" placeholder="081234567890" autocomplete="tel" required>
                <p class="hint">Tidak tampil di publik. Baru terbuka setelah pengajuan diterima.</p>
                <?= field_error('phone') ?>
            </div>
            <div class="field">
                <label for="city">Kota</label>
                <input id="city" class="<?= err_class('city') ?>" type="text" name="city" value="<?= esc(old('city')) ?>" placeholder="Bekasi" required>
                <?= field_error('city') ?>
            </div>
        </div>
        <div class="row">
            <div class="field">
                <label for="password">Password</label>
                <input id="password" class="<?= err_class('password') ?>" type="password" name="password" autocomplete="new-password" required>
                <p class="hint">Minimal 8 karakter.</p>
                <?= field_error('password') ?>
            </div>
            <div class="field">
                <label for="password_confirm">Ulangi password</label>
                <input id="password_confirm" class="<?= err_class('password_confirm') ?>" type="password" name="password_confirm" autocomplete="new-password" required>
                <?= field_error('password_confirm') ?>
            </div>
        </div>
        <button class="btn btn-primary btn-block" type="submit">Buat akun</button>
    </form>
    <p class="center mt-2 muted">Sudah punya akun? <a href="<?= site_url('login') ?>">Masuk</a></p>
</div>
<?= $this->endSection() ?>
