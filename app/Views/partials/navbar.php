<?php $me = current_user(); $unread = unread_notifications(); ?>
<header class="nav">
    <div class="container nav-inner">
        <a class="brand" href="<?= site_url('/') ?>" aria-label="LoakIN, ke beranda">Loak<b>IN</b></a>
        <button class="btn btn-ghost btn-sm nav-toggle" type="button" aria-expanded="false" aria-controls="menu">Menu</button>
        <nav class="nav-links" id="menu" aria-label="Menu utama">
            <a href="<?= site_url('listings') ?>"<?= nav_current('listings') ?>>Cari barang</a>
            <a href="<?= site_url('impact') ?>"<?= nav_current('impact') ?>>Dampak</a>
            <a href="<?= site_url('tentang') ?>"<?= nav_current('tentang') ?>>Cara kerja</a>
            <?php if ($me && $me['role'] === 'penyetor'): ?>
                <a href="<?= site_url('my/listings') ?>"<?= nav_current('my/listings') ?>>Barang saya</a>
                <a href="<?= site_url('offers/incoming') ?>"<?= nav_current('offers/incoming') ?>>Pengajuan masuk</a>
                <a href="<?= site_url('offers/history') ?>"<?= nav_current('offers/history') ?>>Riwayat</a>
            <?php elseif ($me && $me['role'] === 'pendaur'): ?>
                <a href="<?= site_url('my/offers') ?>"<?= nav_current('my/offers') ?>>Pengajuan saya</a>
                <a href="<?= site_url('offers/history') ?>"<?= nav_current('offers/history') ?>>Riwayat</a>
            <?php elseif ($me && $me['role'] === 'admin'): ?>
                <a href="<?= site_url('admin') ?>"<?= nav_current('admin') ?>>Admin</a>
            <?php endif; ?>
        </nav>
        <div class="nav-right">
            <?php if ($me): ?>
                <?php if ($me['role'] === 'penyetor'): ?>
                    <a class="btn btn-tag btn-sm" href="<?= site_url('listings/new') ?>">Posting barang</a>
                <?php endif; ?>
                <a class="btn btn-ghost btn-sm bell" href="<?= site_url('notifications') ?>" aria-label="Notifikasi<?= $unread ? ', ' . $unread . ' belum dibaca' : '' ?>">
                    Notifikasi<?php if ($unread): ?><span class="count"><?= $unread > 99 ? '99+' : $unread ?></span><?php endif; ?>
                </a>
                <a class="btn btn-ghost btn-sm" href="<?= site_url('profile/edit') ?>"><?= esc(explode(' ', $me['name'])[0]) ?></a>
                <form class="inline" action="<?= site_url('logout') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="btn btn-ghost btn-sm" type="submit">Keluar</button>
                </form>
            <?php else: ?>
                <a class="btn btn-ghost btn-sm" href="<?= site_url('login') ?>">Masuk</a>
                <a class="btn btn-primary btn-sm" href="<?= site_url('register') ?>">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</header>
