<?php
$me     = current_user();
$unread = unread_notifications();
$role   = $me['role'] ?? null;

/* Ikon SVG kecil (garis, 20px) */
$ico = static function (string $d): string {
    return '<svg class="ico" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
};
$icons = [
    'search'  => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
    'leaf'    => '<path d="M5 19c0-8 5-13 14-14 0 9-5 14-13 14"/><path d="M5 19c2-4 5-7 9-9"/>',
    'info'    => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
    'box'     => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>',
    'inbox'   => '<path d="M4 13h4l2 3h4l2-3h4"/><path d="M5 5h14l2 8v6H3v-6z"/>',
    'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'send'    => '<path d="M21 3 10 14"/><path d="m21 3-7 18-4-7-7-4z"/>',
    'shield'  => '<path d="M12 3 5 6v6c0 4 3 7 7 9 4-2 7-5 7-9V6z"/>',
    'users'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.5-3.5 3-5.5 6.5-5.5s6 2 6.5 5.5"/><path d="M16 5a3.5 3.5 0 0 1 0 7M18 14.5c2 .6 3.3 2.3 3.5 5"/>',
    'tag'     => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1"/>',
    'grid'    => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    'plus'    => '<path d="M12 5v14M5 12h14"/>',
    'bell'    => '<path d="M6 17V11a6 6 0 0 1 12 0v6l1.5 2h-15z"/><path d="M10 21h4"/>',
    'user'    => '<circle cx="12" cy="8" r="4"/><path d="M4 21c.7-4 4-6 8-6s7.3 2 8 6"/>',
    'logout'  => '<path d="M10 4H5v16h5"/><path d="m16 8 4 4-4 4M20 12H9"/>',
    'login'   => '<path d="M14 4h5v16h-5"/><path d="m8 8-4 4 4 4M4 12h11"/>',
    'menu'    => '<path d="M4 7h16M4 12h16M4 17h10"/>',
    'close'   => '<path d="m6 6 12 12M18 6 6 18"/>',
    'chev'    => '<path d="m6 9 6 6 6-6"/>',
];

/* Struktur menu: grup => daftar tautan [label, path, ikon, exact?] */
$groups = [
    'Jelajah' => [
        ['Cari barang', 'listings', 'search'],
        ['Dampak lingkungan', 'impact', 'leaf'],
        ['Cara kerja', 'tentang', 'info'],
    ],
];
if ($role === 'penyetor') {
    $groups['Penyetor'] = [
        ['Barang saya', 'my/listings', 'box'],
        ['Pengajuan masuk', 'offers/incoming', 'inbox'],
        ['Riwayat', 'offers/history', 'clock'],
    ];
} elseif ($role === 'pendaur') {
    $groups['Pendaur ulang'] = [
        ['Pengajuan saya', 'my/offers', 'send'],
        ['Riwayat', 'offers/history', 'clock'],
    ];
} elseif ($role === 'admin') {
    $groups['Admin'] = [
        ['Ringkasan', 'admin', 'grid', true],
        ['Verifikasi pendaur', 'admin/verification', 'shield'],
        ['Pengguna', 'admin/users', 'users'],
        ['Kategori', 'admin/categories', 'tag'],
    ];
}
?>
<header class="topbar">
    <div class="topbar-inner">
        <button class="icon-btn" type="button" id="menu-open" aria-expanded="false" aria-controls="drawer" aria-label="Buka menu">
            <?= $ico($icons['menu']) ?>
        </button>
        <a class="brand" href="<?= site_url('/') ?>" aria-label="LoakIN, ke beranda">Loak<b>IN</b></a>

        <div class="topbar-right">
            <?php if ($me): ?>
                <?php if ($role === 'penyetor'): ?>
                    <a class="btn btn-tag btn-sm post-btn" href="<?= site_url('listings/new') ?>">
                        <?= $ico($icons['plus']) ?><span>Posting barang</span>
                    </a>
                <?php endif; ?>
                <a class="icon-btn bell" href="<?= site_url('notifications') ?>" aria-label="Notifikasi<?= $unread ? ', ' . $unread . ' belum dibaca' : '' ?>">
                    <?= $ico($icons['bell']) ?>
                    <?php if ($unread): ?><span class="count"><?= $unread > 99 ? '99+' : $unread ?></span><?php endif; ?>
                </a>
                <details class="user-menu">
                    <summary aria-label="Menu akun">
                        <span class="avatar sm"><?php if (avatar_url($me)): ?><img src="<?= esc(avatar_url($me)) ?>" alt=""><?php else: ?><?= esc(initials($me['name'])) ?><?php endif; ?></span>
                        <span class="user-name"><?= esc(explode(' ', $me['name'])[0]) ?></span>
                        <?= $ico($icons['chev']) ?>
                    </summary>
                    <div class="user-pop">
                        <div class="user-pop-head">
                            <strong><?= esc($me['name']) ?></strong>
                            <span class="muted small"><?= esc(role_label($me['role'])) ?></span>
                        </div>
                        <a href="<?= site_url('profile/edit') ?>"><?= $ico($icons['user']) ?>Profil saya</a>
                        <form action="<?= site_url('logout') ?>" method="post">
                            <?= csrf_field() ?>
                            <button type="submit"><?= $ico($icons['logout']) ?>Keluar</button>
                        </form>
                    </div>
                </details>
            <?php else: ?>
                <a class="btn btn-ghost btn-sm" href="<?= site_url('login') ?>">Masuk</a>
                <a class="btn btn-primary btn-sm" href="<?= site_url('register') ?>">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="scrim" id="scrim" hidden></div>
<aside class="drawer" id="drawer" aria-label="Menu utama" aria-hidden="true" inert>
    <div class="drawer-head">
        <a class="brand" href="<?= site_url('/') ?>">Loak<b>IN</b></a>
        <button class="icon-btn" type="button" id="menu-close" aria-label="Tutup menu"><?= $ico($icons['close']) ?></button>
    </div>

    <nav class="drawer-nav">
        <?php foreach ($groups as $title => $links): ?>
            <details class="drawer-group" open>
                <summary><?= esc($title) ?><?= $ico($icons['chev']) ?></summary>
                <?php foreach ($links as $l): ?>
                    <a href="<?= site_url($l[1]) ?>"<?= nav_current($l[1], $l[3] ?? false) ?>><?= $ico($icons[$l[2]]) ?><?= esc($l[0]) ?></a>
                <?php endforeach; ?>
            </details>
        <?php endforeach; ?>
    </nav>

    <div class="drawer-foot">
        <?php if ($me): ?>
            <a class="who-card" href="<?= site_url('profile/edit') ?>">
                <span class="avatar"><?php if (avatar_url($me)): ?><img src="<?= esc(avatar_url($me)) ?>" alt=""><?php else: ?><?= esc(initials($me['name'])) ?><?php endif; ?></span>
                <span><strong><?= esc($me['name']) ?></strong><small><?= esc(role_label($me['role'])) ?></small></span>
            </a>
        <?php else: ?>
            <a class="btn btn-primary btn-block" href="<?= site_url('register') ?>">Daftar gratis</a>
            <a class="btn btn-ghost btn-block" href="<?= site_url('login') ?>">Masuk</a>
        <?php endif; ?>
    </div>
</aside>
