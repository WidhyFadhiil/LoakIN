<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$cfg   = config('Loakin');
$free  = $listing['price_type'] === 'gratis';
$phone = null;
?>
<div class="detail">
    <div>
        <div class="gallery">
            <div class="main"><img src="<?= cover_url($images[0]['filename'] ?? null) ?>" alt="Foto utama <?= esc($listing['title']) ?>"></div>
            <?php if (count($images) > 1): ?>
                <div class="thumbs">
                    <?php foreach ($images as $i => $img): ?>
                        <button type="button" data-src="<?= cover_url($img['filename']) ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Foto <?= $i + 1 ?>"><img src="<?= cover_url($img['filename']) ?>" alt=""></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel mt-2">
            <h2>Deskripsi</h2>
            <p style="white-space:pre-wrap"><?= esc($listing['description'] ?: 'Penyetor belum menambahkan deskripsi.') ?></p>
        </div>
    </div>

    <div>
        <div class="flex">
            <span class="badge <?= status_class($listing['status']) ?>"><?= esc(listing_status_label($listing['status'])) ?></span>
            <span class="badge"><?= esc($listing['category_name']) ?></span>
        </div>
        <h1 class="mt-1"><?= esc($listing['title']) ?></h1>
        <div class="price-big<?= $free ? ' free' : '' ?>"><?= esc(price_label($listing)) ?></div>

        <dl class="facts">
            <dt>Berat estimasi</dt><dd><?= esc(kg($listing['weight_kg'])) ?></dd>
            <dt>Kondisi</dt><dd><?= esc($cfg->conditions[$listing['item_condition']] ?? $listing['item_condition']) ?></dd>
            <dt>Lokasi</dt><dd><?= esc($listing['city']) ?></dd>
            <dt>Serah terima</dt><dd><?= esc($cfg->handovers[$listing['handover']] ?? $listing['handover']) ?></dd>
            <dt>Diposting</dt><dd><?= esc(time_ago($listing['created_at'])) ?>, dilihat <?= (int) $listing['views'] ?> kali</dd>
            <?php if ($listing['status'] === 'tersedia' && $listing['expires_at']): ?>
                <dt>Tayang sampai</dt><dd><?= esc(tanggal($listing['expires_at'])) ?></dd>
            <?php endif; ?>
        </dl>

        <div class="panel">
            <div class="who">
                <a class="avatar" href="<?= site_url('u/' . $listing['user_id']) ?>" aria-hidden="true" tabindex="-1">
                    <?php if ($listing['owner_photo']): ?><img src="<?= base_url('uploads/avatars/' . $listing['owner_photo']) ?>" alt=""><?php else: ?><?= esc(initials($listing['owner_name'])) ?><?php endif; ?>
                </a>
                <div>
                    <a href="<?= site_url('u/' . $listing['user_id']) ?>"><strong><?= esc($listing['owner_name']) ?></strong></a><br>
                    <span class="small muted">
                        <?php if ((int) $listing['owner_rating_count'] > 0): ?>
                            <span class="stars"><?= stars((float) $listing['owner_rating']) ?></span> <?= number_format((float) $listing['owner_rating'], 1) ?> dari <?= (int) $listing['owner_rating_count'] ?> rating
                        <?php else: ?>Belum ada rating<?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <?php /* ---------- Aksi sesuai peran ---------- */ ?>
        <div class="panel">
        <?php if ($isOwner): ?>
            <h3>Kelola postingan</h3>
            <div class="flex">
                <?php if ($listing['status'] === 'tersedia'): ?>
                    <a class="btn btn-ghost btn-sm" href="<?= site_url('listings/' . $listing['id'] . '/edit') ?>">Ubah</a>
                <?php endif; ?>
                <?php if (in_array($listing['status'], ['tersedia', 'diproses'], true)): ?>
                    <form class="inline" action="<?= site_url('listings/' . $listing['id'] . '/cancel') ?>" method="post" data-confirm="Batalkan postingan ini? Semua pengajuan yang berjalan ikut dibatalkan.">
                        <?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Batalkan postingan</button>
                    </form>
                <?php endif; ?>
                <?php if (in_array($listing['status'], ['kedaluwarsa', 'dibatalkan'], true)): ?>
                    <form class="inline" action="<?= site_url('listings/' . $listing['id'] . '/repost') ?>" method="post">
                        <?= csrf_field() ?><button class="btn btn-primary btn-sm" type="submit">Posting ulang</button>
                    </form>
                <?php endif; ?>
                <?php if ($listing['status'] === 'tersedia' && ! $offers): ?>
                    <form class="inline" action="<?= site_url('listings/' . $listing['id'] . '/delete') ?>" method="post" data-confirm="Hapus permanen postingan ini?">
                        <?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                    </form>
                <?php endif; ?>
            </div>

            <h3 class="mt-2">Pengajuan (<?= count($offers) ?>)</h3>
            <?php if (! $offers): ?>
                <p class="muted mb-0">Belum ada pengajuan. Postinganmu tayang sampai <?= esc(tanggal($listing['expires_at'])) ?>.</p>
            <?php endif; ?>
            <ul class="list mt-1">
                <?php foreach ($offers as $o): ?>
                    <li>
                        <div>
                            <a href="<?= site_url('offers/' . $o['id']) ?>"><strong><?= esc($o['recycler_name']) ?></strong></a>
                            <div class="small muted">
                                <?= $o['recycler_rating_count'] ? esc(number_format((float) $o['recycler_rating'], 1)) . ' bintang, ' : '' ?>
                                tawaran <?= $o['offered_price'] > 0 ? esc(rupiah($o['offered_price'])) : 'gratis' ?>
                            </div>
                        </div>
                        <span class="badge <?= status_class($o['status']) ?>"><?= esc(offer_status_label($o['status'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

        <?php elseif ($canOffer && ! $myOffer): ?>
            <h3>Ajukan Ambil</h3>
            <form action="<?= site_url('listings/' . $listing['id'] . '/offers') ?>" method="post">
                <?= csrf_field() ?>
                <?php if ($listing['price_type'] === 'nego'): ?>
                    <div class="field">
                        <label for="offered_price">Tawaran harga kamu (Rp)</label>
                        <input id="offered_price" type="text" inputmode="numeric" name="offered_price" value="<?= esc(old('offered_price', (string) $listing['price'])) ?>">
                        <p class="hint">Harga awal <?= esc(rupiah($listing['price'])) ?>. Penyetor bisa menerima, menolak, atau membalas dengan harga lain.</p>
                    </div>
                <?php elseif ($listing['price_type'] === 'berbayar'): ?>
                    <p class="muted">Harga pas <?= esc(rupiah($listing['price'])) ?>, dibayar langsung saat serah terima.</p>
                <?php else: ?>
                    <p class="muted">Barang ini gratis.</p>
                <?php endif; ?>
                <div class="field">
                    <label for="note">Pesan untuk penyetor (opsional)</label>
                    <textarea id="note" name="note" rows="3" maxlength="500" placeholder="Kapan bisa mengambil, untuk apa barangnya..."><?= esc(old('note')) ?></textarea>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Ajukan Ambil</button>
            </form>

        <?php elseif ($myOffer): ?>
            <h3>Pengajuanmu</h3>
            <p><span class="badge <?= status_class($myOffer['status']) ?>"><?= esc(offer_status_label($myOffer['status'])) ?></span></p>
            <a class="btn btn-primary btn-block" href="<?= site_url('offers/' . $myOffer['id']) ?>">Buka pengajuan dan pesan</a>

        <?php elseif (! $me): ?>
            <h3>Mau mengambil barang ini?</h3>
            <p class="muted">Masuk sebagai pendaur ulang terverifikasi untuk mengajukan.</p>
            <div class="flex"><a class="btn btn-primary" href="<?= site_url('login') ?>">Masuk</a><a class="btn btn-ghost" href="<?= site_url('register?as=pendaur') ?>">Daftar pendaur ulang</a></div>

        <?php elseif ($me['role'] === 'pendaur' && $me['verification_status'] !== 'approved'): ?>
            <h3>Verifikasi dulu</h3>
            <p class="muted">Akun pendaur ulang perlu disetujui admin sebelum bisa mengajukan. Status kamu: <strong><?= esc(verification_label($me['verification_status'])) ?></strong>.</p>
            <a class="btn btn-primary" href="<?= site_url('profile/edit#verifikasi') ?>">Lihat status verifikasi</a>

        <?php elseif ($listing['status'] !== 'tersedia'): ?>
            <p class="muted mb-0">Barang ini sedang diproses atau sudah tidak menerima pengajuan.</p>
        <?php else: ?>
            <p class="muted mb-0">Hanya pendaur ulang terverifikasi yang bisa mengajukan pengambilan.</p>
        <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
