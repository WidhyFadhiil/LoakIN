<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="narrow">
    <h1>Masuk</h1>
    <form class="panel" action="<?= site_url('login') ?>" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?= esc(old('email')) ?>" autocomplete="email" required autofocus>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>
        </div>
        <button class="btn btn-primary btn-block" type="submit">Masuk</button>
    </form>
    <p class="center mt-2 muted">Belum punya akun? <a href="<?= site_url('register') ?>">Daftar gratis</a></p>
</div>
<?= $this->endSection() ?>
